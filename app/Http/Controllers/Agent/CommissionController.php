<?php

namespace App\Http\Controllers\Agent;

use App\Events\CreditBalanceUpdated;
use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $selectedDate = $request->date ?? now()->format('Y-m-d');

        $query = AgentCommission::query()
            ->with(['player', 'bet'])
            ->where('agent_id', auth()->id())
            ->whereDate('created_at', $selectedDate);

        $totalCommission = AgentCommission::where('agent_id', auth()->id())
            ->whereDate('created_at', $selectedDate)
            ->sum('commission_amount');

        $pendingCommission = AgentCommission::where('agent_id', auth()->id())
            ->whereDate('created_at', $selectedDate)
            ->where('conversion_status', 'pending')
            ->sum('commission_amount');

        $convertedCommission = AgentCommission::where('agent_id', auth()->id())
            ->whereDate('created_at', $selectedDate)
            ->where('conversion_status', 'converted')
            ->sum('commission_amount');

        $commissions = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('agent.commissions.index', [
            'commissions' => $commissions,
            'selectedDate' => $selectedDate,
            'totalCommission' => $totalCommission,
            'pendingCommission' => $pendingCommission,
            'convertedCommission' => $convertedCommission,
        ]);
    }

    public function convertToWallet(Request $request)
{
    if (auth()->user()->role !== 'agent') {
        abort(403);
    }

    $request->validate([
        'date' => ['required', 'date'],
        'amount' => ['required', 'numeric', 'min:1'],
    ]);

    $selectedDate = $request->date;
    $requestedAmount = (float) $request->amount;

    try {
        $updatedAgent = DB::transaction(function () use ($selectedDate, $requestedAmount) {
            $agent = User::where('id', auth()->id())
                ->lockForUpdate()
                ->first();

            if (! $agent) {
                throw new \RuntimeException('Agent not found.');
            }

            $commissions = AgentCommission::where('agent_id', $agent->id)
                ->whereDate('created_at', $selectedDate)
                ->where(function ($query) {
                    $query->where('conversion_status', 'pending')
                        ->orWhere('conversion_status', 'partial');
                })
                ->lockForUpdate()
                ->orderBy('created_at')
                ->get();

            $availableCommission = $commissions->sum(function ($commission) {
                return (float) $commission->commission_amount - (float) ($commission->converted_amount ?? 0);
            });

            if ($availableCommission <= 0) {
                throw new \RuntimeException('No pending commission to convert.');
            }

            if ($requestedAmount > $availableCommission) {
                throw new \RuntimeException('You cannot convert more than your pending commission.');
            }

            $remainingAmount = $requestedAmount;

            foreach ($commissions as $commission) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $commissionAmount = (float) $commission->commission_amount;
                $alreadyConverted = (float) ($commission->converted_amount ?? 0);
                $availableAmount = $commissionAmount - $alreadyConverted;

                if ($availableAmount <= 0) {
                    continue;
                }

                $amountToApply = min($availableAmount, $remainingAmount);
                $newConvertedAmount = $alreadyConverted + $amountToApply;

                $commission->update([
                    'converted_amount' => $newConvertedAmount,
                    'conversion_status' => $newConvertedAmount >= $commissionAmount ? 'converted' : 'partial',
                    'converted_at' => $newConvertedAmount >= $commissionAmount ? now() : $commission->converted_at,
                ]);

                $remainingAmount -= $amountToApply;
            }

            $previousBalance = (float) $agent->credit_balance;

            $agent->increment('credit_balance', $requestedAmount);
            $agent->refresh();

            CreditTransaction::create([
                'user_id' => $agent->id,
                'agent_id' => $agent->id,
                'type' => 'commission_to_wallet',
                'amount' => $requestedAmount,
                'previous_balance' => $previousBalance,
                'current_balance' => $agent->credit_balance,
                'description' => 'Agent manually converted commission to wallet.',
                'meta' => [
                    'date' => $selectedDate,
                    'requested_amount' => $requestedAmount,
                    'available_commission_before_convert' => $availableCommission,
                ],
            ]);

            return $agent;
        });

        try {
            broadcast(new CreditBalanceUpdated($updatedAgent));
        } catch (\Throwable $broadcastError) {
            Log::error('Commission conversion broadcast failed', [
                'message' => $broadcastError->getMessage(),
                'agent_id' => $updatedAgent->id,
            ]);
        }

        return back()->with('success', 'Commission converted to wallet successfully.');
    } catch (\RuntimeException $e) {
        return back()->with('error', $e->getMessage());
    } catch (\Throwable $e) {
        Log::error('Commission convert to wallet failed', [
            'message' => $e->getMessage(),
            'agent_id' => auth()->id(),
        ]);

        return back()->with('error', 'Something went wrong. Please try again.');
    }
}
}