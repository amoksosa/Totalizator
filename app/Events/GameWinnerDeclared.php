<?php

namespace App\Events;

use App\Models\GameDeclaration;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameWinnerDeclared implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public GameDeclaration $declaration)
    {
        $this->declaration->load('declarer');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('game.declarations'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'winner.declared';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->declaration->id,
            'winner' => $this->declaration->winner,
            'round_code' => $this->declaration->round_code,
            'declared_by' => $this->declaration->declarer?->username,
            'created_at' => $this->declaration->created_at?->format('M d, Y h:i A'),
        ];
    }
}