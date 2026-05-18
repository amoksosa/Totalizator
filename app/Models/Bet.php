<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $fillable = [
        'user_id',
        'side',
        'odds',
        'amount',
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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function declaration()
    {
        return $this->belongsTo(GameDeclaration::class, 'declaration_id');
    }
}