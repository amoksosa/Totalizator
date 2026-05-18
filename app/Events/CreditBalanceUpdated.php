<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditBalanceUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user)
    {
        $this->user->load('agent');
    }

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('user.' . $this->user->id),
            new Channel('admin.balances'),
        ];

        if ($this->user->agent_id) {
            $channels[] = new Channel('agent.' . $this->user->agent_id . '.balances');
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'credit.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'username' => $this->user->username,
            'role' => $this->user->role,
            'agent_id' => $this->user->agent_id,
            'agent_username' => $this->user->agent?->username,
            'credit_balance' => number_format($this->user->credit_balance, 2, '.', ''),
            'formatted_balance' => '₱' . number_format($this->user->credit_balance, 2),
        ];
    }
}