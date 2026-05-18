<?php

namespace App\Events;

use App\Models\Bet;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerBetPlaced implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Bet $bet)
    {
        $this->bet->load('user');
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
            'player_id' => $this->bet->user_id,
            'player_username' => $this->bet->user?->username,
            'side' => $this->bet->side,
            'odds' => $this->bet->odds,
            'amount' => number_format($this->bet->amount, 2, '.', ''),
            'formatted_amount' => '₱' . number_format($this->bet->amount, 2),
            'created_at' => $this->bet->created_at?->format('M d, Y h:i A'),
        ];
    }
}