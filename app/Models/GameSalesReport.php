<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSalesReport extends Model
{
    protected $fillable = [
        'source_game',
        'source_id',
        'event_name',
        'round_label',
        'winner_id',
        'agent_id',
        'total_bet_amount',
        'gross_payout_amount',
        'net_payout_amount',
        'commission_amount',
        'agent_commission_amount',
        'company_commission_amount',
        'status',
        'settled_at',
    ];

    protected $casts = [
        'total_bet_amount' => 'decimal:2',
        'gross_payout_amount' => 'decimal:2',
        'net_payout_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'agent_commission_amount' => 'decimal:2',
        'company_commission_amount' => 'decimal:2',
        'settled_at' => 'datetime',
    ];
}