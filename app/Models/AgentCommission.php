<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    protected $fillable = [
        'agent_id',
        'player_id',
        'bet_id',
        'bet_amount',
        'commission_rate',
        'commission_amount',
        'side',
        'odds',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }
}