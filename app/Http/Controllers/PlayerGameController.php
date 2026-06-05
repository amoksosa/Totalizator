<?php

namespace App\Http\Controllers;

use App\Events\CreditBalanceUpdated;
use App\Events\PlayerBetPlaced;
use App\Models\AgentCommission;
use App\Models\Bet;
use App\Models\CreditTransaction;
use App\Models\GameDeclaration;
use App\Models\GameEvent;
use App\Models\GameRound;
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
            ->with(['event', 'round'])
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

        $openEvent = GameEvent::where('status', 'open')
            ->latest()
            ->first();

        if (! $openEvent) {
            return response()->json([
                'success' => true,
                'totals' => [],
            ]);
        }

        $currentRound = GameRound::where('game_event_id', $openEvent->id)
            ->whereIn('status', ['open', 'closed'])
            ->latest()
            ->first();

        if (! $currentRound) {
            return response()->json([
                'success' => true,
                'totals' => [],
            ]);
        }

        $totals = Bet::query()
            ->select('side', 'odds', DB::raw('SUM(amount) as total_amount'))
            ->where('game_round_id', $currentRound->id)
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
            'round' => [
                'id' => $currentRound->id,
                'round_code' => $currentRound->round_code,
                'status' => $currentRound->status,
            ],
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

        $requestedAmount = round((float) $request->amount, 2);

        try {
            $result = DB::transaction(function () use ($request, $requestedAmount) {
                $openEvent = GameEvent::where('status', 'open')
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if (! $openEvent) {
                    throw new \RuntimeException('Betting is closed. No active event right now.');
                }

                $openRound = GameRound::where('game_event_id', $openEvent->id)
                    ->where('status', 'open')
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if (! $openRound) {
                    throw new \RuntimeException('Betting is closed. Please wait for the next round.');
                }

                $player = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $player) {
                    throw new \RuntimeException('Player not found.');
                }

                if ((float) $player->credit_balance < $requestedAmount) {
                    throw new \RuntimeException('Insufficient credit balance.');
                }

                /*
                |--------------------------------------------------------------------------
                | Bet logic
                |--------------------------------------------------------------------------
                | Accept the full requested amount first.
                | Excess will be refunded later when declare closes betting.
                */

                $acceptedAmount = $requestedAmount;
                $refundAmount = 0;

                $balanceBefore = (float) $player->credit_balance;
                $balanceAfter = round($balanceBefore - $acceptedAmount, 2);

                $player->update([
                    'credit_balance' => $balanceAfter,
                ]);

                $bet = Bet::create([
                    'user_id' => $player->id,
                    'game_event_id' => $openEvent->id,
                    'game_round_id' => $openRound->id,

                    'side' => $request->side,
                    'odds' => $request->odds,

                    'requested_amount' => $requestedAmount,
                    'amount' => $acceptedAmount,
                    'refunded_amount' => $refundAmount,

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
                    'amount' => $acceptedAmount,
                    'previous_balance' => $balanceBefore,
                    'current_balance' => $balanceAfter,
                    'reference_type' => Bet::class,
                    'reference_id' => $bet->id,
                    'description' => 'Player placed a bet.',
                    'meta' => [
                        'event_id' => $openEvent->id,
                        'event_name' => $openEvent->event_name,
                        'round_id' => $openRound->id,
                        'round_code' => $openRound->round_code,
                        'bet_id' => $bet->id,
                        'side' => $bet->side,
                        'odds' => $bet->odds,
                        'requested_amount' => $requestedAmount,
                        'accepted_amount' => $acceptedAmount,
                        'refunded_amount' => $refundAmount,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Commission logic
                |--------------------------------------------------------------------------
                | Player with agent:
                | - Agent commission = 3%
                | - Company commission = 2%
                | - Total commission = 5%
                |
                | Direct admin player with no agent:
                | - Agent commission = 0%
                | - Company commission = 2%
                | - Total commission = 2%
                */

                $agentCommissionRate = $player->agent_id ? 3.00 : 0.00;
                $companyCommissionRate = 2.00;

                $agentCommissionAmount = round($acceptedAmount * ($agentCommissionRate / 100), 2);
                $companyCommissionAmount = round($acceptedAmount * ($companyCommissionRate / 100), 2);

                $totalCommissionRate = $agentCommissionRate + $companyCommissionRate;
                $totalCommissionAmount = round($agentCommissionAmount + $companyCommissionAmount, 2);

                AgentCommission::create([
                    'agent_id' => $player->agent_id,
                    'player_id' => $player->id,
                    'bet_id' => $bet->id,
                    'bet_amount' => $acceptedAmount,

                    'commission_rate' => $agentCommissionRate,
                    'commission_amount' => $agentCommissionAmount,

                    'company_commission_rate' => $companyCommissionRate,
                    'company_commission_amount' => $companyCommissionAmount,

                    'total_commission_rate' => $totalCommissionRate,
                    'total_commission_amount' => $totalCommissionAmount,

                    'conversion_status' => 'pending',
                    'converted_amount' => 0,

                    'side' => $request->side,
                    'odds' => $request->odds,
                ]);

                $player->refresh();

                return [
                    'bet' => $bet,
                    'player' => $player,
                    'accepted_amount' => $acceptedAmount,
                    'refunded_amount' => $refundAmount,
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
                'message' => 'Bet placed successfully. Excess, if any, will be returned when betting is closed.',
                'bet' => $bet,
                'requested_amount' => number_format($requestedAmount, 2, '.', ''),
                'accepted_amount' => number_format($result['accepted_amount'], 2, '.', ''),
                'refunded_amount' => number_format($result['refunded_amount'], 2, '.', ''),
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