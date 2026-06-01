<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameEvent extends Model
{
    protected $fillable = [
        'created_by',
        'event_name',
        'event_date',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'closed_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function declarations()
    {
        return $this->hasMany(GameDeclaration::class, 'game_event_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'game_event_id');
    }
}