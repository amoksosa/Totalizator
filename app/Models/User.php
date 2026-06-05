<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'mobile_number',
        'username',
        'password',
        'role',
        'status',
        'agent_id',
        'referral_code',
        'credit_balance',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function players()
    {
        return $this->hasMany(User::class, 'agent_id')
            ->where('role', 'player');
    }

    public function downlines()
    {
        return $this->hasMany(User::class, 'agent_id')
            ->where('role', 'player');
    }

    public function creditTransactions()
    {
        return $this->hasMany(CreditTransaction::class, 'user_id');
    }
}