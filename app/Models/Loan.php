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

    public function schedules(): HasMany
    {
        return $this->hasMany(LoanSchedule::class);
    }


    public function validations(): MorphMany
    {
        return $this->morphMany(Validation::class, 'valuable');
    }

    public function generateSchedules(): void
    {
        $this->schedules()->delete();

        // Sécurité pour les anciens prêts : recalculer le total si manquant
        if ($this->total_to_repay <= 0) {
            $principal = (float) $this->principal_amount;
            $rate = (float) $this->interest_rate;
            $months = (int) $this->term_months ?: 1;
            $interest = $principal * ($rate / 100) * ($months / 12);
            $this->total_to_repay = round($principal + $interest, 2);
            $this->balance_remaining = $this->total_to_repay;
            $this->save();
        }
        
        $term = $this->term_months ?: 1;

        $monthlyPrincipal = $this->total_to_repay / $term;
        $startDate = $this->disbursement_date ? \Carbon\Carbon::parse($this->disbursement_date) : now();
        
        $totalPlanned = 0;
        for ($i = 1; $i <= $term; $i++) {
            $amount = ($i === $term) 
                ? ($this->total_to_repay - $totalPlanned) 
                : round($monthlyPrincipal, 2);
                
            $this->schedules()->create([
                'due_date' => $startDate->copy()->addMonths($i),
                'amount_due' => $amount,
                'status' => 'pending',
            ]);
            $totalPlanned += $amount;
        }
    }

    public function recalculateSchedules(): void
    {
        // 1. Remise à zéro de l'échéancier
        $this->schedules()->update([
            'amount_paid' => 0,
            'status' => 'pending'
        ]);
        
        // 2. Réinitialisation du solde de calcul
        $totalToRepay = (float) $this->total_to_repay;
        $runningBalance = $totalToRepay;
        
        // 3. Traiter chaque remboursement dans l'ordre chronologique
        $repayments = $this->repayments()->orderBy('payment_date')->get();
        
        foreach ($repayments as $repayment) {
            $amountToDistribute = (float) $repayment->amount_paid;
            $runningBalance -= $amountToDistribute;
            
            // Trouver les échéances non encore payées
            $unpaidSchedules = $this->schedules()
                ->where('status', '!=', 'paid')
                ->orderBy('due_date', 'asc')
                ->get();
                
            foreach ($unpaidSchedules as $schedule) {
                if ($amountToDistribute <= 0) break;
                
                $due = (float) $schedule->amount_due;
                $alreadyPaid = (float) $schedule->amount_paid;
                $needed = $due - $alreadyPaid;
                
                if ($amountToDistribute >= $needed) {
                    $schedule->amount_paid = $due;
                    $schedule->status = 'paid';
                    $amountToDistribute -= $needed;
                } else {
                    $schedule->amount_paid += $amountToDistribute;
                    $schedule->status = 'partial';
                    $amountToDistribute = 0;
                }
                $schedule->save();
            }
        }
        
        // 4. Mise à jour finale du prêt
        $this->balance_remaining = round(max(0, $runningBalance), 2);
        if ($this->balance_remaining <= 0) {
            $this->status = 'repaid';
        } elseif ($this->status === 'repaid') {
            $this->status = 'active';
        }
        
        $this->save();
    }


    public static function boot()
    {
        parent::boot();

        static::creating(function ($loan) {
            // Simple interest calculation: Principal + (Principal * (InterestRate/100) * (Months/12))
            $principal = (float) $loan->principal_amount;
            $rate = (float) $loan->interest_rate;
            $months = (int) $loan->term_months;
            
            $interest = $principal * ($rate / 100) * ($months / 12);
            $loan->total_to_repay = round($principal + $interest, 2);
            $loan->balance_remaining = $loan->total_to_repay;
        });
    }

}
