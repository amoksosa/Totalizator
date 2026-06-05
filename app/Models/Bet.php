<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $fillable = [
        'user_id',
        'game_event_id',
        'game_round_id',
        'side',
        'odds',
        'requested_amount',
        'amount',
        'refunded_amount',
        'status',
        'win_amount',
        'payout_amount',
        'declaration_id',
        'settled_at',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'settled_at' => 'datetime',
        'requested_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'win_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(GameEvent::class, 'game_event_id');
    }

    public function round()
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function declaration()
    {
        return $this->belongsTo(GameDeclaration::class, 'declaration_id');
    }
}