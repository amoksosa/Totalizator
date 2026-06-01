<?php

namespace App\Http\Controllers\Agent;

use App\Events\CreditBalanceUpdated;
use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $players = User::query()
            ->with([
                'creditTransactions' => function ($query) {
                    $query->latest()->limit(50);
                }
            ])
            ->where('agent_id', auth()->id())
            ->where('role', 'player')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('agent.users.index', compact('players'));
    }

    public function giveCredit(Request $request, User $user)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        if ($user->agent_id !== auth()->id() || $user->role !== 'player') {
            abort(403);
        }

        $request->validate([
            'credit_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $request->credit_amount;

        try {
            $updatedUsers = DB::transaction(function () use ($user, $amount) {
                $agent = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                $player = User::where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $agent || ! $player) {
                    throw new \RuntimeException('User not found.');
                }

                if ((float) $agent->credit_balance < $amount) {
                    throw new \RuntimeException('Insufficient agent credit balance.');
                }

                $agentPreviousBalance = (float) $agent->credit_balance;
                $playerPreviousBalance = (float) $player->credit_balance;

                $agent->decrement('credit_balance', $amount);
                $player->increment('credit_balance', $amount);

                $agent->refresh();
                $player->refresh();

                CreditTransaction::create([
                    'user_id' => $player->id,
                    'agent_id' => $agent->id,
                    'type' => 'agent_give_credit',
                    'amount' => $amount,
                    'previous_balance' => $playerPreviousBalance,
                    'current_balance' => $player->credit_balance,
                    'description' => 'Agent gave credit to player.',
                    'meta' => [
                        'agent_username' => $agent->username,
                        'player_username' => $player->username,
                    ],
                ]);

                CreditTransaction::create([
                    'user_id' => $agent->id,
                    'agent_id' => $agent->id,
                    'type' => 'agent_transfer_out',
                    'amount' => $amount,
                    'previous_balance' => $agentPreviousBalance,
                    'current_balance' => $agent->credit_balance,
                    'description' => 'Agent transferred credit to player: ' . $player->username,
                    'meta' => [
                        'player_id' => $player->id,
                        'player_username' => $player->username,
                    ],
                ]);

                return [
                    'agent' => $agent,
                    'player' => $player,
                ];
            });

            try {
                broadcast(new CreditBalanceUpdated($updatedUsers['agent']));
                broadcast(new CreditBalanceUpdated($updatedUsers['player']));
            } catch (\Throwable $broadcastError) {
                Log::error('Broadcast failed after credit transfer', [
                    'message' => $broadcastError->getMessage(),
                ]);
            }

            return back()->with('success', 'Credit transferred to player successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Agent credit transfer failed', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function getCredit(Request $request, User $user)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        if ($user->agent_id !== auth()->id() || $user->role !== 'player') {
            abort(403);
        }

        $request->validate([
            'credit_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $request->credit_amount;

        try {
            $updatedUsers = DB::transaction(function () use ($user, $amount) {
                $agent = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                $player = User::where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $agent || ! $player) {
                    throw new \RuntimeException('User not found.');
                }

                if ((float) $player->credit_balance < $amount) {
                    throw new \RuntimeException('Insufficient player credit balance.');
                }

                $agentPreviousBalance = (float) $agent->credit_balance;
                $playerPreviousBalance = (float) $player->credit_balance;

                $player->decrement('credit_balance', $amount);
                $agent->increment('credit_balance', $amount);

                $agent->refresh();
                $player->refresh();

                CreditTransaction::create([
                    'user_id' => $player->id,
                    'agent_id' => $agent->id,
                    'type' => 'agent_get_credit',
                    'amount' => $amount,
                    'previous_balance' => $playerPreviousBalance,
                    'current_balance' => $player->credit_balance,
                    'description' => 'Agent got credit from player.',
                    'meta' => [
                        'agent_username' => $agent->username,
                        'player_username' => $player->username,
                    ],
                ]);

                CreditTransaction::create([
                    'user_id' => $agent->id,
                    'agent_id' => $agent->id,
                    'type' => 'agent_transfer_in',
                    'amount' => $amount,
                    'previous_balance' => $agentPreviousBalance,
                    'current_balance' => $agent->credit_balance,
                    'description' => 'Agent got credit from player: ' . $player->username,
                    'meta' => [
                        'player_id' => $player->id,
                        'player_username' => $player->username,
                    ],
                ]);

                return [
                    'agent' => $agent,
                    'player' => $player,
                ];
            });

            try {
                broadcast(new CreditBalanceUpdated($updatedUsers['agent']));
                broadcast(new CreditBalanceUpdated($updatedUsers['player']));
            } catch (\Throwable $broadcastError) {
                Log::error('Broadcast failed after agent get credit', [
                    'message' => $broadcastError->getMessage(),
                ]);
            }

            return back()->with('success', 'Credit taken from player successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Agent get credit failed', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}