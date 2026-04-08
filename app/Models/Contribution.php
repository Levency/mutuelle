<?php

namespace App\Models;

use App\Services\FundService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'amount',
        'payment_date',
        'receipt_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Booted method to handle automatic fund distribution when a contribution is paid.
     */
    protected static function booted(): void
    {
        static::created(function (Contribution $contribution) {
            if ($contribution->status === 'paid') {
                $contribution->processFundDistribution();
            }
        });

        static::updated(function (Contribution $contribution) {
            // If status changed to 'paid' from something else
            if ($contribution->isDirty('status') && $contribution->status === 'paid') {
                $contribution->processFundDistribution();
            }
        });
    }

    /**
     * Logic to split contribution between Main Fund and Solidarity Fund.
     */
    public function processFundDistribution(): void
    {
        $fundService = app(FundService::class);
        $totalAmount = (float) $this->amount;

        // Get solidarity rate from settings (default 20%)
        $solidarityRate = (float) Setting::get('solidarity_rate', 20) / 100;
        $solidarityAmount = round($totalAmount * $solidarityRate, 2);
        $mainFundAmount = $totalAmount - $solidarityAmount;

        // 1. Log into Main Fund (inflow)
        $fundService->logMovement(
            'inflow',
            $mainFundAmount,
            "Cotisation #{$this->id} (Part Caisse) — {$this->member->full_name}",
            $this
        );

        // 2. Log into Solidarity Fund
        $currentSolBalance = SolidarityFund::currentBalance();
        SolidarityFund::create([
            'type'           => 'inflow',
            'amount'         => $solidarityAmount,
            'description'    => "Cotisation #{$this->id} (Part Solidarité) — {$this->member->full_name}",
            'balance_after'  => $currentSolBalance + $solidarityAmount,
            'reference_type' => self::class,
            'reference_id'   => $this->id,
        ]);
    }
}
