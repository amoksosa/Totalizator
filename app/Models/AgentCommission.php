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
        'converted_amount',

        'company_commission_rate',
        'company_commission_amount',

        'total_commission_rate',
        'total_commission_amount',

        'conversion_status',
        'converted_at',

        'side',
        'odds',
    ];

    protected $casts = [
        'converted_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'company_commission_amount' => 'decimal:2',
        'total_commission_amount' => 'decimal:2',
        'converted_at' => 'datetime',
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
        return $this->belongsTo(Bet::class, 'bet_id');
    }
}