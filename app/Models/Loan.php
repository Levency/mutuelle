<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'principal_amount',
        'interest_rate',
        'term_months',
        'total_to_repay',
        'balance_remaining',
        'status',
        'disbursement_date',
        'due_date',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_to_repay' => 'decimal:2',
        'balance_remaining' => 'decimal:2',
        'disbursement_date' => 'date',
        'due_date' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function validations(): MorphMany
    {
        return $this->morphMany(Validation::class, 'valuable');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($loan) {
            // Simple interest calculation: Principal + (Principal * InterestRate/100)
            $loan->total_to_repay = $loan->principal_amount + ($loan->principal_amount * ($loan->interest_rate / 100));
            $loan->balance_remaining = $loan->total_to_repay;
        });
    }
}
