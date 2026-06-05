<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameRound extends Model
{
    protected $fillable = [
        'game_event_id',
        'created_by',
        'round_code',
        'status',
        'winner',
        'betting_closed_at',
        'settled_at',
    ];

    protected $casts = [
        'betting_closed_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(GameEvent::class, 'game_event_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'game_round_id');
    }
}