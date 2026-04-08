<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_number',
        'first_name',
        'last_name',
        'phone',
        'address',
        'birth_date',
        'emergency_contact',
        'emergency_phone',
        'national_id',
        'profession',
        'confidence_score',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at'        => 'date',
        'birth_date'       => 'date',
        'confidence_score' => 'float',
    ];

    // ─── Accesseurs ─────────────────────────────────────────────────────────

    /**
     * Retourne le nom complet — depuis le compte user OU les champs directs.
     */
    public function getFullNameAttribute(): string
    {
        // On vérifie d'abord si un compte utilisateur est lié
        if ($this->user_id && $this->user) {
            return $this->user->name;
        }

        // Sinon on utilise les noms saisis directement
        $name = trim("{$this->first_name} {$this->last_name}");
        
        return $name ?: "Membre #{$this->member_number}";
    }


    /**
     * Retourne l'email — depuis le compte user ou null.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Score de confiance normalisé en pourcentage.
     */
    public function getConfidenceLevelAttribute(): string
    {
        $score = $this->confidence_score ?? 100;
        return match(true) {
            $score >= 80 => 'excellent',
            $score >= 60 => 'bon',
            $score >= 40 => 'moyen',
            default      => 'risqué',
        };
    }

    /**
     * Total des cotisations payées par ce membre.
     */
    public function getTotalContributedAttribute(): float
    {
        return $this->contributions()->where('status', 'paid')->sum('amount');
    }

    // ─── Relations ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function helpRequests(): HasMany
    {
        return $this->hasMany(HelpRequest::class);
    }

    public function loanPenalties(): HasMany
    {
        return $this->hasMany(LoanPenalty::class);
    }

    public function contributionPenalties(): HasMany
    {
        return $this->hasMany(ContributionPenalty::class);
    }

    // ─── Méthodes Métier ────────────────────────────────────────────────────

    /**
     * Vérifie si le membre est éligible pour un prêt.
     */
    public function isEligibleForLoan(): bool
    {
        // Doit être actif
        if ($this->status !== 'active') return false;

        // Ne doit pas avoir de prêt actif
        if ($this->loans()->where('status', 'active')->exists()) return false;

        // Doit avoir au moins une cotisation payée
        if ($this->contributions()->where('status', 'paid')->count() === 0) return false;

        return true;
    }

    /**
     * Calcule le montant maximum de prêt autorisé (3x le total cotisé).
     */
    public function getMaxLoanAmountAttribute(): float
    {
        $multiplier = Setting::get('max_loan_multiplier', 3);
        return $this->total_contributed * $multiplier;
    }
}
