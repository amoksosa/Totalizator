<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameDeclaration extends Model
{
    protected $fillable = [
        'game_event_id',
        'declared_by',
        'winner',
        'round_code',
    ];

    public function declarer()
    {
        return $this->belongsTo(User::class, 'declared_by');
    }

    public function event()
    {
        return $this->belongsTo(GameEvent::class, 'game_event_id');
    }
}