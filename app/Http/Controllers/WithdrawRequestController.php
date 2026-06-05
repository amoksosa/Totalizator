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

                if (! in_array($user->role, ['agent', 'player'])) {
                    throw new \RuntimeException('Only agents and players can request withdrawal.');
                }

                if ((float) $user->credit_balance < $amount) {
                    throw new \RuntimeException('Insufficient credit balance.');
                }

                /*
                |--------------------------------------------------------------------------
                | Withdrawal receiver logic
                |--------------------------------------------------------------------------
                | Agent withdrawal:
                | - agent_id = null
                | - admin handles it
                |
                | Player under agent:
                | - agent_id = player's agent_id
                | - agent handles it
                |
                | Player without agent:
                | - agent_id = null
                | - admin handles it
                */

                $assignedAgentId = null;

                if ($user->role === 'player') {
                    $assignedAgentId = $user->agent_id;
                }

                $previousBalance = (float) $user->credit_balance;
                $currentBalance = $previousBalance - $amount;

                $user->update([
                    'credit_balance' => $currentBalance,
                ]);

                $withdrawRequest = WithdrawRequest::create([
                    'user_id' => $user->id,
                    'agent_id' => $assignedAgentId,
                    'amount' => $amount,
                    'status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'account_name' => $request->account_name,
                    'account_number' => $request->account_number,
                    'note' => $request->note,
                ]);

                CreditTransaction::create([
                    'user_id' => $user->id,
                    'agent_id' => $assignedAgentId,
                    'type' => 'withdraw',
                    'amount' => $amount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $currentBalance,
                    'reference_type' => WithdrawRequest::class,
                    'reference_id' => $withdrawRequest->id,
                    'description' => $this->withdrawDescription($user),
                    'meta' => [
                        'withdraw_id' => $withdrawRequest->id,
                        'withdraw_amount' => $amount,
                        'payment_method' => $request->payment_method,
                        'account_name' => $request->account_name,
                        'account_number' => $request->account_number,
                        'role' => $user->role,
                        'agent_id' => $assignedAgentId,
                        'handled_by' => $assignedAgentId ? 'agent' : 'admin',
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

    private function withdrawDescription(User $user): string
    {
        if ($user->role === 'agent') {
            return 'Agent requested withdrawal from admin.';
        }

        if ($user->role === 'player' && is_null($user->agent_id)) {
            return 'Player requested withdrawal from admin.';
        }

        return 'Player requested withdrawal from agent.';
    }
}