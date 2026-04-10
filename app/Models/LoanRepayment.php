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
            $amountToDistribute = (float) $repayment->amount_paid;

            // 1. Mise à jour du solde global du prêt
            $loan->balance_remaining -= $amountToDistribute;
            if ($loan->balance_remaining <= 0) {
                $loan->balance_remaining = 0;
                $loan->status = 'repaid';
            }
            $loan->save();

            // 2. Distribution intelligente sur l'échéancier
            $schedules = $loan->schedules()
                ->where('status', '!=', 'paid')
                ->orderBy('due_date', 'asc')
                ->get();

            foreach ($schedules as $schedule) {
                if ($amountToDistribute <= 0) break;

                $due = (float) $schedule->amount_due;
                $alreadyPaid = (float) $schedule->amount_paid;
                $remainingForThisMonth = $due - $alreadyPaid;

                if ($amountToDistribute >= $remainingForThisMonth) {
                    // On couvre entièrement cette échéance
                    $schedule->amount_paid = $due;
                    $schedule->status = 'paid';
                    $amountToDistribute -= $remainingForThisMonth;
                } else {
                    // On ne couvre qu'une partie
                    $schedule->amount_paid += $amountToDistribute;
                    $schedule->status = 'partial'; // Statut demandé : différent de 'pending'
                    $amountToDistribute = 0;
                }
                $schedule->save();
            }
        });
    }

}
