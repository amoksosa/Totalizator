<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserForceLoggedOut implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'force.logout';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'username' => $this->user->username,
            'role' => $this->user->role,
            'message' => 'Your account has been logged out by the admin.',
            'redirect_url' => route('login'),
        ];
    }
}