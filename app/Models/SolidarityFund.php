<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolidarityFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'amount', 'description', 'balance_after',
        'reference_type', 'reference_id',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Retourne le solde actuel du fonds de solidarité.
     */
    public static function currentBalance(): float
    {
        return (float) static::latest()->first()?->balance_after ?? 0;
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
