<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionPenalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id', 'periods_late', 'period_type', 'amount', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
