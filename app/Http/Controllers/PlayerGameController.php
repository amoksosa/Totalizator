<?php

namespace App\Http\Controllers;

use App\Models\GameEvent;
use App\Models\CreditTransaction;
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

        $openEvent = GameEvent::where('status', 'open')
            ->latest()
            ->first();

        if (! $openEvent) {
            return response()->json([
                'success' => false,
                'message' => 'Betting is closed. No active event right now.',
            ], 422);
        }

        $amount = (float) $request->amount;

        try {
            $result = DB::transaction(function () use ($request, $amount) {
                $openEvent = GameEvent::where('status', 'open')
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if (! $openEvent) {
                    throw new \RuntimeException('Betting is closed. No active event right now.');
                }

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
                'game_event_id' => $openEvent->id,
                'side' => $request->side,
                'odds' => $request->odds,
                'amount' => $amount,
                'status' => 'pending',
                'win_amount' => 0,
                'payout_amount' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);

                CreditTransaction::create([
                    'user_id' => $player->id,
                    'agent_id' => $player->agent_id,
                    'type' => 'bet',
                    'amount' => $amount,
                    'previous_balance' => $balanceBefore,
                    'current_balance' => $balanceAfter,
                    'reference_type' => Bet::class,
                    'reference_id' => $bet->id,
                    'description' => 'Player placed a bet.',
                    'meta' => [
                        'event_id' => $openEvent->id,
                        'event_name' => $openEvent->event_name,
                        'bet_id' => $bet->id,
                        'side' => $bet->side,
                        'odds' => $bet->odds,
                        'amount' => $amount,
                    ],
                ]);

                if ($player->agent_id) {
                    $agentCommissionRate = 3.00;
                    $agentCommissionAmount = $amount * ($agentCommissionRate / 100);

                    $companyCommissionRate = 2.00;
                    $companyCommissionAmount = $amount * ($companyCommissionRate / 100);

                    $totalCommissionRate = $agentCommissionRate + $companyCommissionRate;
                    $totalCommissionAmount = $agentCommissionAmount + $companyCommissionAmount;

                    AgentCommission::create([
                        'agent_id' => $player->agent_id,
                        'player_id' => $player->id,
                        'bet_id' => $bet->id,
                        'bet_amount' => $amount,

                        'commission_rate' => $agentCommissionRate,
                        'commission_amount' => $agentCommissionAmount,

                        'company_commission_rate' => $companyCommissionRate,
                        'company_commission_amount' => $companyCommissionAmount,

                        'total_commission_rate' => $totalCommissionRate,
                        'total_commission_amount' => $totalCommissionAmount,

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