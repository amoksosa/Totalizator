<?php

namespace App\Events;

use App\Models\PokemonLobby;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PokemonLobbyUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public array $lobby;

    public function __construct(PokemonLobby $lobby)
    {
        $lobby->refresh();

        $this->lobby = [
            'id' => $lobby->id,
            'status' => $lobby->status,
            'round_number' => (int) $lobby->round_number,

            'player_one_id' => $lobby->player_one_id,
            'player_two_id' => $lobby->player_two_id,

            'player_one_pokemon' => $lobby->player_one_pokemon,
            'player_two_pokemon' => $lobby->player_two_pokemon,

            'player_one_ready' => (bool) $lobby->player_one_ready,
            'player_two_ready' => (bool) $lobby->player_two_ready,

            'player_one_score' => (int) $lobby->player_one_score,
            'player_two_score' => (int) $lobby->player_two_score,

            'winner_id' => $lobby->winner_id,
            'finished_at' => optional($lobby->finished_at)->toDateTimeString(),
            'closed_at' => optional($lobby->closed_at)->toDateTimeString(),

            'pot_amount' => (float) $lobby->pot_amount,
            'payout_amount' => (float) $lobby->payout_amount,

            'updated_at' => optional($lobby->updated_at)->timestamp,
        ];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('pokemon-lobby.' . $this->lobby['id']);
    }

    public function broadcastAs(): string
    {
        return 'pokemon-lobby.updated';
    }
}