<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'amount_paid',
        'interest_paid',
        'principal_paid',
        'payment_date',
        'receipt_number',
        'payment_method',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'interest_paid' => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function ($repayment) {
            $loan = $repayment->loan;
            $loan->balance_remaining -= $repayment->amount_paid;
            if ($loan->balance_remaining <= 0) {
                $loan->status = 'repaid';
            }
            $loan->save();
        });
    }
}
