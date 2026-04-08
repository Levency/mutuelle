<?php

namespace App\Services;

use App\Models\Fund;
use App\Models\Loan;
use App\Models\Setting;

class FundService
{
    /**
     * Solde brut (dernière entrée du journal de caisse).
     */
    public function getGrossBalance(): float
    {
        return (float) Fund::latest()->first()?->balance_after ?? 0;
    }

    /**
     * Solde disponible = Solde brut - Encours des prêts actifs.
     */
    public function getAvailableBalance(): float
    {
        $gross   = $this->getGrossBalance();
        $loaned  = (float) Loan::where('status', 'active')->sum('balance_remaining');
        return max(0, $gross - $loaned);
    }

    /**
     * Vérifie si le fonds peut supporter le montant demandé.
     *
     * @param float  $amount  Montant demandé
     * @param string $type    'loan' ou 'help'
     */
    public function canApprove(float $amount, string $type = 'loan'): bool
    {
        $available = $this->getAvailableBalance();

        // Marge de sécurité configurable (% du montant demandé)
        $marginRate = match($type) {
            'loan' => (float) Setting::get('loan_fund_margin', 20) / 100,
            'help' => (float) Setting::get('help_fund_margin', 10) / 100,
            default => 0.20,
        };

        $required = $amount * (1 + $marginRate);

        return $available >= $required;
    }

    /**
     * Retourne un message explicatif si les fonds sont insuffisants.
     */
    public function getInsufficientFundsMessage(float $amount, string $type = 'loan'): string
    {
        $available = $this->getAvailableBalance();
        $margin    = match($type) {
            'loan' => (float) Setting::get('loan_fund_margin', 20),
            'help' => (float) Setting::get('help_fund_margin', 10),
            default => 20,
        };
        $required = $amount * (1 + $margin / 100);

        return sprintf(
            'Fonds insuffisants. Disponible : %s $ | Requis (avec marge %d%%) : %s $',
            number_format($available, 2),

            $margin,
            number_format($required, 2)
        );
    }

    /**
     * Enregistre un mouvement de fonds et met à jour le solde.
     */
    public function logMovement(string $type, float $amount, string $description, $reference = null): \App\Models\Fund
    {
        $current = $this->getGrossBalance();
        $newBalance = $type === 'inflow' ? $current + $amount : $current - $amount;

        return \App\Models\Fund::create([
            'type'           => $type,
            'amount'         => $amount,
            'description'    => $description,
            'balance_after'  => max(0, $newBalance),
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id'   => $reference?->id,
        ]);
    }
}
