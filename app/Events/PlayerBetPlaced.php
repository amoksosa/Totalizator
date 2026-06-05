<?php

namespace App\Events;

use App\Models\Bet;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PlayerBetPlaced implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $totals = [];

    public function __construct(public Bet $bet)
    {
        $this->bet->load('user');

        $this->totals = Bet::query()
            ->select('side', 'odds', DB::raw('SUM(amount) as total_amount'))
            ->where('game_round_id', $this->bet->game_round_id)
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
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game.bets'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'player.bet.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'bet_id' => $this->bet->id,
            'round_id' => $this->bet->game_round_id,
            'player_id' => $this->bet->user_id,
            'player_username' => $this->bet->user?->username,
            'side' => $this->bet->side,
            'odds' => $this->bet->odds,
            'amount' => number_format($this->bet->amount, 2, '.', ''),
            'formatted_amount' => '₱' . number_format($this->bet->amount, 2),
            'created_at' => $this->bet->created_at?->format('M d, Y h:i A'),
            'totals' => $this->totals,
        ];
    }
}