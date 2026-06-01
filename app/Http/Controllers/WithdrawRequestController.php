<?php

namespace App\Http\Controllers;

use App\Events\CreditBalanceUpdated;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawRequestController extends Controller
{
    public function index()
    {
        if (! in_array(auth()->user()->role, ['agent', 'player'])) {
            abort(403);
        }

        $withdrawRequests = WithdrawRequest::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('withdrawals.index', compact('withdrawRequests'));
    }

    public function store(Request $request)
    {
        if (! in_array(auth()->user()->role, ['agent', 'player'])) {
            abort(403);
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'account_name' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:150'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = (float) $request->amount;

        try {
            $result = DB::transaction(function () use ($request, $amount) {
                $user = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $user) {
                    throw new \RuntimeException('User not found.');
                }

                if ((float) $user->credit_balance < $amount) {
                    throw new \RuntimeException('Insufficient credit balance.');
                }

                $previousBalance = (float) $user->credit_balance;
                $currentBalance = $previousBalance - $amount;

                $user->update([
                    'credit_balance' => $currentBalance,
                ]);

                $withdrawRequest = WithdrawRequest::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'account_name' => $request->account_name,
                    'account_number' => $request->account_number,
                    'note' => $request->note,
                ]);

                /*
                 * Save transaction history.
                 * This is what the agent will see when clicking the player username.
                 */
                CreditTransaction::create([
                    'user_id' => $user->id,
                    'agent_id' => $user->role === 'player' ? $user->agent_id : null,
                    'type' => 'withdraw',
                    'amount' => $amount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $currentBalance,
                    'reference_type' => WithdrawRequest::class,
                    'reference_id' => $withdrawRequest->id,
                    'description' => 'User requested withdrawal.',
                    'meta' => [
                        'withdraw_id' => $withdrawRequest->id,
                        'withdraw_amount' => $amount,
                        'payment_method' => $request->payment_method,
                        'account_name' => $request->account_name,
                        'account_number' => $request->account_number,
                        'role' => $user->role,
                    ],
                ]);

                $user->refresh();

                return [
                    'user' => $user,
                    'withdraw_request' => $withdrawRequest,
                ];
            });

            try {
                broadcast(new CreditBalanceUpdated($result['user']));
            } catch (\Throwable $broadcastError) {
                Log::error('Withdraw request balance broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'user_id' => $result['user']->id,
                ]);
            }

            return back()->with('success', 'Withdraw request submitted successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Withdraw request failed', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}