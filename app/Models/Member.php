<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * Le Member est à la fois la fiche métier ET l'identité "guard:member"
 * du portail membre — un espace indépendant du panneau d'administration
 * Filament, protégé par un code d'accès distinct (jamais un mot de passe
 * partagé avec un compte User).
 */
class Member extends Authenticatable
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

    /**
     * access_code (haché) et les métadonnées de connexion au portail ne sont
     * jamais mass-assignables : ils passent uniquement par generateAccessCode()
     * ou par recordPortalLogin() ci-dessous.
     */
    protected $hidden = [
        'access_code',
    ];

    protected $casts = [
        'joined_at'                 => 'date',
        'birth_date'                => 'date',
        'confidence_score'          => 'float',
        'access_code_generated_at'  => 'datetime',
        'portal_last_login_at'      => 'datetime',
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

    // ─── Portail Membre (auth guard "member") ──────────────────────────────

    /**
     * Génère un nouveau code d'accès (6 chiffres) pour le portail membre.
     * Le code n'est JAMAIS stocké en clair — seul son hash est conservé.
     * La valeur en clair retournée doit être communiquée au membre puis
     * jetée (elle n'est plus jamais récupérable ensuite).
     */
    public function generateAccessCode(): string
    {
        $plain = (string) random_int(100000, 999999);

        $this->forceFill([
            'access_code'               => Hash::make($plain),
            'access_code_generated_at'  => now(),
        ])->save();

        return $plain;
    }

    public function hasAccessCode(): bool
    {
        return filled($this->access_code);
    }

    public function revokeAccessCode(): void
    {
        $this->forceFill([
            'access_code'              => null,
            'access_code_generated_at' => null,
        ])->save();
    }

    public function recordPortalLogin(string $ip): void
    {
        $this->forceFill([
            'portal_last_login_at' => now(),
            'portal_last_login_ip' => $ip,
        ])->save();
    }

    /**
     * Recherche les membres actifs correspondant à un prénom/nom donné et
     * disposant d'un code d'accès — comparaison insensible à la casse, sur
     * les champs directs OU sur le nom du compte utilisateur lié.
     * Retourne une collection (des homonymes sont possibles) ; l'appelant
     * doit ensuite vérifier le code d'accès pour chaque candidat.
     */
    public static function findForPortalLogin(string $firstName, string $lastName)
    {
        $firstName = mb_strtolower(trim($firstName));
        $lastName  = mb_strtolower(trim($lastName));

        return static::query()
            ->whereNotNull('access_code')
            ->with('user')
            ->get()
            ->filter(function (self $member) use ($firstName, $lastName) {
                $directMatch = mb_strtolower(trim((string) $member->first_name)) === $firstName
                    && mb_strtolower(trim((string) $member->last_name)) === $lastName;

                if ($directMatch) {
                    return true;
                }

                if ($member->user) {
                    return mb_strtolower(trim($member->user->name)) === trim("{$firstName} {$lastName}");
                }

                return false;
            });
    }

    // ─── Authenticatable overrides ──────────────────────────────────────────

    /**
     * Le "mot de passe" d'authentification du guard membre est le hash du
     * code d'accès (colonne access_code), jamais un mot de passe classique.
     */
    public function getAuthPassword(): string
    {
        return (string) $this->access_code;
    }

    /**
     * Pas de fonctionnalité "remember me" pour le portail membre — pas de
     * colonne remember_token en base, on neutralise proprement les accès.
     */
    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // Intentionnellement vide : non supporté pour le guard membre.
    }

    public function getRememberTokenName()
    {
        return '';
    }
}
