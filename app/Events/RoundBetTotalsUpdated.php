<?php

namespace App\Events;

use App\Models\Bet;
use App\Models\GameRound;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RoundBetTotalsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public GameRound $round)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game.bets'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'round.bet-totals.updated';
    }

    public function broadcastWith(): array
    {
        $totals = Bet::query()
            ->select('side', 'odds', DB::raw('SUM(amount) as total_amount'))
            ->where('game_round_id', $this->round->id)
            ->where('status', 'pending')
            ->groupBy('side', 'odds')
            ->get()
            ->map(function ($row) {
                return [
                    'side' => $row->side,
                    'odds' => $row->odds,
                    'amount' => number_format($row->total_amount, 2, '.', ''),
                    'formatted_amount' => '₱' . number_format($row->total_amount, 2),
                ];
            })
            ->toArray();

        return [
            'round_id' => $this->round->id,
            'round_status' => $this->round->status,
            'totals' => $totals,
        ];
    }
}