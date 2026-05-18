<?php

namespace App\Http\Controllers;

use App\Events\CreditBalanceUpdated;
use App\Events\PlayerBetPlaced;
use App\Models\AgentCommission;
use App\Models\Bet;
use App\Models\GameDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PlayerGameController extends Controller
{
    public function dashboard()
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        return view('player.dashboard');
    }

    public function history()
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        $bets = Bet::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('player.bet-history', compact('bets'));
    }

    public function latestDeclaration()
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        $declaration = GameDeclaration::latest()->first();

        if (! $declaration) {
            return response()->json([
                'success' => true,
                'declaration' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'declaration' => [
                'id' => $declaration->id,
                'winner' => $declaration->winner,
                'round_code' => $declaration->round_code,
                'created_at' => $declaration->created_at?->format('M d, Y h:i A'),
            ],
        ]);
    }

    public function currentBetTotals()
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        $totals = Bet::query()
            ->select('side', 'odds', DB::raw('SUM(amount) as total_amount'))
            ->where('status', 'pending')
            ->groupBy('side', 'odds')
            ->get()
            ->map(function ($row) {
                return [
                    'side' => $row->side,
                    'odds' => $row->odds,
                    'amount' => number_format($row->total_amount, 2, '.', ''),
                ];
            });

        return response()->json([
            'success' => true,
            'totals' => $totals,
        ]);
    }

    public function placeBet(Request $request)
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        $request->validate([
            'side' => ['required', Rule::in(['MERON', 'WALA', 'DRAW'])],
            'odds' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $request->amount;

        try {
            $result = DB::transaction(function () use ($request, $amount) {
                $player = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $player) {
                    throw new \RuntimeException('Player not found.');
                }

                if ((float) $player->credit_balance < $amount) {
                    throw new \RuntimeException('Insufficient credit balance.');
                }

                $balanceBefore = (float) $player->credit_balance;
                $balanceAfter = $balanceBefore - $amount;

                $player->update([
                    'credit_balance' => $balanceAfter,
                ]);

                $bet = Bet::create([
                    'user_id' => $player->id,
                    'side' => $request->side,
                    'odds' => $request->odds,
                    'amount' => $amount,
                    'status' => 'pending',
                    'win_amount' => 0,
                    'payout_amount' => 0,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                ]);

                if ($player->agent_id) {
                    $commissionRate = 5.00;
                    $commissionAmount = $amount * ($commissionRate / 100);

                    AgentCommission::create([
                        'agent_id' => $player->agent_id,
                        'player_id' => $player->id,
                        'bet_id' => $bet->id,
                        'bet_amount' => $amount,
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                        'side' => $request->side,
                        'odds' => $request->odds,
                    ]);
                }

                $player->refresh();

                return [
                    'bet' => $bet,
                    'player' => $player,
                ];
            });

            $bet = $result['bet'];
            $player = $result['player'];

            $bet->refresh();
            $bet->load('user');

            try {
                broadcast(new CreditBalanceUpdated($player));
                broadcast(new PlayerBetPlaced($bet));
            } catch (\Throwable $broadcastError) {
                Log::error('Player bet broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'user_id' => $player->id,
                    'bet_id' => $bet->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bet placed successfully.',
                'bet' => $bet,
                'new_balance' => number_format($bet->balance_after, 2, '.', ''),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Bet placement failed', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}