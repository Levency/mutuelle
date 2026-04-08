<?php

namespace App\Console\Commands;

use App\Models\Contribution;
use App\Models\ContributionPenalty;
use App\Models\Loan;
use App\Models\LoanPenalty;
use App\Models\LoanSchedule;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculatePenalties extends Command
{
    protected $signature   = 'mutuelle:calculate-penalties {--dry-run : Afficher sans sauvegarder}';
    protected $description = 'Calcule et enregistre les pénalités de retard (prêts + cotisations)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $today  = Carbon::today();

        $this->info('=== Calcul des Pénalités — ' . $today->format('d/m/Y') . ' ===');

        // ── 1. Pénalités sur les prêts ───────────────────────────────────────
        $this->calculateLoanPenalties($today, $dryRun);

        // ── 2. Pénalités sur les cotisations ────────────────────────────────
        $this->calculateContributionPenalties($today, $dryRun);

        $this->info('✅ Calcul terminé.');
        return self::SUCCESS;
    }

    private function calculateLoanPenalties(Carbon $today, bool $dryRun): void
    {
        $lateType = Setting::get('loan_late_fee_type', 'weekly');
        $lateRate = (float) Setting::get('loan_late_fee_rate', 2); // % par période

        $this->line("\n📊 Pénalités de prêts ({$lateRate}% par {$lateType})");

        /** @var \Illuminate\Database\Eloquent\Collection<int, LoanSchedule> $overdueSchedules */
        $overdueSchedules = LoanSchedule::where('status', '!=', 'paid')
            ->where('due_date', '<', $today)
            ->with('loan.member')
            ->get();

        foreach ($overdueSchedules as $schedule) {
            /** @var LoanSchedule $schedule */
            $loan   = $schedule->loan;
            
            if (!$loan || $loan->status !== 'active') continue;
            
            $member = $loan->member;

            // Calcule le nombre de périodes de retard
            $daysLate = $today->diffInDays($schedule->due_date);
            $periodsLate = match($lateType) {
                'weekly'  => max(1, (int) ceil($daysLate / 7)),
                'monthly' => max(1, (int) ceil($daysLate / 30)),
                default   => 1,
            };

            // Vérifie si une pénalité a déjà été créée pour cette période
            $existing = LoanPenalty::where('loan_id', $loan->id)
                ->where('periods_late', $periodsLate)
                ->where('period_type', $lateType)
                ->first();

            if ($existing) continue; // Déjà calculée

            $penaltyAmount = round($schedule->amount_due * ($lateRate / 100) * $periodsLate, 2);

            $this->line("  → Prêt #{$loan->id} ({$member->full_name}) : {$periodsLate} période(s) = {$penaltyAmount} HTG");

            if (!$dryRun) {
                LoanPenalty::create([
                    'loan_id'      => $loan->id,
                    'member_id'    => $member->id,
                    'periods_late' => $periodsLate,
                    'period_type'  => $lateType,
                    'rate'         => $lateRate,
                    'amount'       => $penaltyAmount,
                    'status'       => 'pending',
                ]);

                // Met à jour le statut de l'échéance
                $schedule->setAttribute('status', 'late');
                $schedule->save();

                // Réduit le score de confiance
                $member->decrement('confidence_score', 2);
            }
        }
    }

    private function calculateContributionPenalties(Carbon $today, bool $dryRun): void
    {
        $graceDays   = (int) Setting::get('contribution_late_days', 15);
        $lateType    = Setting::get('contribution_late_type', 'monthly');
        $lateFee     = (float) Setting::get('contribution_late_fee', 100); // HTG fixe par période

        $this->line("\n💸 Pénalités de cotisations ({$lateFee} HTG par {$lateType}, {$graceDays}j de grâce)");

        /** @var \Illuminate\Database\Eloquent\Collection<int, Contribution> $lateContributions */
        $lateContributions = Contribution::where('status', 'pending')
            ->where('payment_date', '<', $today->copy()->subDays($graceDays))
            ->with('member')
            ->get();

        foreach ($lateContributions as $contribution) {
            /** @var Contribution $contribution */
            $member    = $contribution->member;
            $daysLate  = $today->diffInDays($contribution->payment_date) - $graceDays;

            $periodsLate = match($lateType) {
                'weekly'  => max(1, (int) ceil($daysLate / 7)),
                'monthly' => max(1, (int) ceil($daysLate / 30)),
                default   => 1,
            };

            $penaltyAmount = $lateFee * $periodsLate;

            $this->line("  → {$member->full_name} : {$periodsLate} période(s) = {$penaltyAmount} HTG");

            if (!$dryRun) {
                ContributionPenalty::firstOrCreate(
                    ['member_id' => $member->id, 'periods_late' => $periodsLate, 'period_type' => $lateType],
                    ['amount' => $penaltyAmount, 'status' => 'pending']
                );

                // Marquer la cotisation en retard
                $contribution->setAttribute('status', 'late');
                $contribution->save();
            }
        }
    }
}
