<?php

namespace App\Http\Controllers\Declare;

use App\Events\CreditBalanceUpdated;
use App\Events\RoundBetTotalsUpdated;
use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\Bet;
use App\Models\CreditTransaction;
use App\Models\GameEvent;
use App\Models\GameRound;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoundController extends Controller
{
    public function start(Request $request)
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        $request->validate([
            'round_code' => ['nullable', 'string', 'max:100'],
        ]);

        $openEvent = GameEvent::where('created_by', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if (! $openEvent) {
            return back()->with('error', 'Create an event first before starting a round.');
        }

        $hasActiveRound = GameRound::where('game_event_id', $openEvent->id)
            ->whereIn('status', ['open', 'closed'])
            ->exists();

        if ($hasActiveRound) {
            return back()->with('error', 'You still have an active round. Settle it first before starting a new round.');
        }

        GameRound::create([
            'game_event_id' => $openEvent->id,
            'created_by' => auth()->id(),
            'round_code' => $request->round_code ?: 'Round ' . now()->format('His'),
            'status' => 'open',
        ]);

        return back()->with('success', 'New round started. Players can now bet.');
    }

    public function close(GameRound $round)
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        try {
            $result = DB::transaction(function () use ($round) {
                $round = GameRound::where('id', $round->id)
                    ->lockForUpdate()
                    ->first();

                if (! $round) {
                    throw new \RuntimeException('Round not found.');
                }

                $round->load('event');

                if (! $round->event || (int) $round->event->created_by !== (int) auth()->id()) {
                    abort(403);
                }

                if ($round->status !== 'open') {
                    throw new \RuntimeException('This round is not open.');
                }

                $meronTotal = (float) Bet::where('game_round_id', $round->id)
                    ->where('status', 'pending')
                    ->where('side', 'MERON')
                    ->sum('amount');

                $walaTotal = (float) Bet::where('game_round_id', $round->id)
                    ->where('status', 'pending')
                    ->where('side', 'WALA')
                    ->sum('amount');

                $updatedPlayerIds = [];

                if ($meronTotal != $walaTotal) {
                    $excessSide = $meronTotal > $walaTotal ? 'MERON' : 'WALA';
                    $excessAmount = round(abs($meronTotal - $walaTotal), 2);

                    $excessBets = Bet::where('game_round_id', $round->id)
                        ->where('status', 'pending')
                        ->where('side', $excessSide)
                        ->where('amount', '>', 0)
                        ->latest()
                        ->lockForUpdate()
                        ->get();

                    foreach ($excessBets as $bet) {
                        if ($excessAmount <= 0) {
                            break;
                        }

                        $currentBetAmount = (float) $bet->amount;
                        $refundAmount = round(min($currentBetAmount, $excessAmount), 2);

                        if ($refundAmount <= 0) {
                            continue;
                        }

                        $player = User::where('id', $bet->user_id)
                            ->lockForUpdate()
                            ->first();

                        if (! $player) {
                            continue;
                        }

                        $previousBalance = (float) $player->credit_balance;

                        $player->increment('credit_balance', $refundAmount);
                        $player->refresh();

                        $newBetAmount = round($currentBetAmount - $refundAmount, 2);
                        $newRefundedAmount = round((float) ($bet->refunded_amount ?? 0) + $refundAmount, 2);

                        $bet->update([
                            'amount' => $newBetAmount,
                            'refunded_amount' => $newRefundedAmount,
                            'status' => $newBetAmount <= 0 ? 'refunded' : 'pending',
                        ]);

                        $this->updateCommissionAfterRefund($bet, $newBetAmount);

                        CreditTransaction::create([
                            'user_id' => $player->id,
                            'agent_id' => $player->agent_id,
                            'type' => 'bet_refund',
                            'amount' => $refundAmount,
                            'previous_balance' => $previousBalance,
                            'current_balance' => $player->credit_balance,
                            'reference_type' => Bet::class,
                            'reference_id' => $bet->id,
                            'description' => 'Excess bet amount returned after betting was closed.',
                            'meta' => [
                                'event_id' => $round->game_event_id,
                                'round_id' => $round->id,
                                'round_code' => $round->round_code,
                                'bet_id' => $bet->id,
                                'side' => $bet->side,
                                'refund_amount' => $refundAmount,
                                'final_bet_amount' => $newBetAmount,
                                'reason' => 'MERON and WALA totals must be equal.',
                            ],
                        ]);

                        $updatedPlayerIds[] = $player->id;
                        $excessAmount = round($excessAmount - $refundAmount, 2);
                    }
                }

                $round->update([
                    'status' => 'closed',
                    'betting_closed_at' => now(),
                ]);

                return [
                    'round_id' => $round->id,
                    'updated_player_ids' => array_values(array_unique($updatedPlayerIds)),
                ];
            });

            $closedRound = GameRound::find($result['round_id']);

            if ($closedRound) {
                try {
                    broadcast(new RoundBetTotalsUpdated($closedRound));
                } catch (\Throwable $broadcastError) {
                    Log::error('Round bet totals broadcast failed', [
                        'message' => $broadcastError->getMessage(),
                        'round_id' => $closedRound->id,
                    ]);
                }
            }

            foreach ($result['updated_player_ids'] as $playerId) {
                $player = User::find($playerId);

                if (! $player) {
                    continue;
                }

                try {
                    broadcast(new CreditBalanceUpdated($player));
                } catch (\Throwable $broadcastError) {
                    Log::error('Round close refund broadcast failed', [
                        'message' => $broadcastError->getMessage(),
                        'player_id' => $player->id,
                    ]);
                }
            }

            return back()->with('success', 'Betting closed. Excess bets were returned and totals were updated.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Close round failed', [
                'message' => $e->getMessage(),
                'round_id' => $round->id,
            ]);

            return back()->with('error', 'Something went wrong while closing the round.');
        }
    }

    private function updateCommissionAfterRefund(Bet $bet, float $finalBetAmount): void
    {
    $commission = AgentCommission::where('bet_id', $bet->id)->first();

    if (! $commission) {
        return;
    }

    $agentCommissionRate = $commission->agent_id ? 3.00 : 0.00;
    $companyCommissionRate = 2.00;
    $totalCommissionRate = $agentCommissionRate + $companyCommissionRate;

    $agentCommissionAmount = round($finalBetAmount * ($agentCommissionRate / 100), 2);
    $companyCommissionAmount = round($finalBetAmount * ($companyCommissionRate / 100), 2);
    $totalCommissionAmount = round($agentCommissionAmount + $companyCommissionAmount, 2);

    $commission->update([
        'bet_amount' => $finalBetAmount,

        'commission_rate' => $agentCommissionRate,
        'commission_amount' => $agentCommissionAmount,

        'company_commission_rate' => $companyCommissionRate,
        'company_commission_amount' => $companyCommissionAmount,

        'total_commission_rate' => $totalCommissionRate,
        'total_commission_amount' => $totalCommissionAmount,
    ]);
}
}