<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerCode extends Model
{
    protected $fillable = [
        'code',
        'created_by',
        'used_by',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}