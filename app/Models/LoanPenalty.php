<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPenalty extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id', 'member_id', 'periods_late', 'period_type', 'rate', 'amount', 'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate'   => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
