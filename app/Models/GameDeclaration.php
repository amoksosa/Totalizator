<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameDeclaration extends Model
{
    protected $fillable = [
        'declared_by',
        'winner',
        'round_code',
    ];

    public function declarer()
    {
        return $this->belongsTo(User::class, 'declared_by');
    }
}