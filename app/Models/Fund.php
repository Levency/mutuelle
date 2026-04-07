<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'balance_after',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($fund) {
            $lastBalance = static::latest()->first()?->balance_after ?? 0;
            $fund->balance_after = $fund->type === 'inflow' 
                ? $lastBalance + $fund->amount 
                : $lastBalance - $fund->amount;
        });
    }
}
