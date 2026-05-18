<?php

namespace App\Http\Controllers\Agent;

use App\Events\CreditBalanceUpdated;
use App\Http\Controllers\Controller;
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

                $agent->decrement('credit_balance', $amount);
                $player->increment('credit_balance', $amount);

                $agent->refresh();
                $player->refresh();

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
}