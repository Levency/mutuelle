<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\Fund;
use App\Models\HelpRequest;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\User;
use App\Models\Setting;
use App\Services\FundService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $fundService = new FundService();

        // 1. Création de quelques utilisateurs pour les membres (on évite les doublons d'email)
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $email = "demo" . rand(100, 999) . "{$i}@example.com";
            $users[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Utilisateur Démo {$i}",
                    'password' => Hash::make('password'),
                ]
            );
        }


        // 2. Création de 15 membres (mixte)
        $members = [];
        for ($i = 1; $i <= 15; $i++) {
            $hasUser = $i <= 5;
            $members[] = Member::create([
                'user_id' => $hasUser ? $users[$i-1]->id : null,
                'member_number' => 'DEMO-' . str_pad(rand(1000, 9999) + $i, 4, '0', STR_PAD_LEFT),
                'first_name' => $hasUser ? null : "Prénom{$i}",

                'last_name' => $hasUser ? null : "Nom{$i}",
                'phone' => '509-0000-00' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'address' => "Adresse du membre {$i}",
                'joined_at' => Carbon::now()->subMonths(8),
                'status' => 'active',
                'confidence_score' => 100,
            ]);
        }

        // 3. Génération de Cotisations (50+)
        // On remplit les 6 derniers mois
        foreach ($members as $member) {
            for ($m = 0; $m < 6; $m++) {
                $date = Carbon::now()->subMonths($m)->startOfMonth()->addDays(rand(1, 15));
                
                // On paye la cotisation (2000 HTG par défaut via booted logic si existant, sinon on force)
                $amount = 2000;
                $contribution = Contribution::create([
                    'member_id' => $member->id,
                    'amount' => $amount,
                    'payment_date' => $date,
                    'status' => 'paid',
                    'receipt_number' => 'REC-' . rand(10000, 99999),
                ]);

                // Le log de mouvement est géré par le booted hook de Contribution si j'ai bien implémenté, 
                // mais pour être sûr dans le seeder on peut aussi logger manuellement si besoin.
                // En fait, j'ai mis la logique dans app/Models/Contribution.php
            }
        }

        // 4. Quelques Dépenses générales (outflows sans référence)
        for ($i = 0; $i < 5; $i++) {
            $fundService->logMovement(
                'outflow',
                rand(500, 2500),
                "Dépense administrative démo " . ($i + 1),
                null
            );
        }

        // 5. Création de 5 Prêts
        for ($i = 0; $i < 5; $i++) {
            $member = $members[$i];
            
            // On vérifie le solde cotisé pour que ce soit réaliste (max 3x)
            $totalCotise = $member->total_contributed;
            $principal = min(50000, $totalCotise * 2.5);

            $loan = Loan::create([
                'member_id' => $member->id,
                'principal_amount' => $principal,
                'interest_rate' => 5,
                'term_months' => 6,
                'total_to_repay' => $principal * 1.05,
                'balance_remaining' => $principal * 1.05,
                'status' => 'active',
                'disbursement_date' => Carbon::now()->subMonths(3),
            ]);

            // Mouvement de sortie pour le prêt
            $fundService->logMovement('outflow', $principal, "Décaissment Prêt #{$loan->id}", $loan);

            // 6. Quelques Remboursements pour ces prêts
            for ($r = 1; $r <= 2; $r++) {
                $rembAmount = $loan->total_to_repay / 6;
                $repayment = LoanRepayment::create([
                    'loan_id' => $loan->id,
                    'amount_paid' => $rembAmount,
                    'principal_paid' => $rembAmount * 0.95, // Factice
                    'interest_paid' => $rembAmount * 0.05,  // Factice
                    'payment_date' => Carbon::now()->subMonths(3 - $r),
                    'payment_method' => 'transfer',
                    'receipt_number' => 'REP-' . rand(1000, 9999),
                ]);

                // Mouvement d'entrée
                $fundService->logMovement('inflow', $rembAmount, "Remboursement Prêt #{$loan->id}", $repayment);
                
                // Update loan balance
                $loan->balance_remaining -= $rembAmount;
                $loan->save();
            }
        }

        // 7. Deux Demandes d'Aide
        for ($i = 5; $i < 7; $i++) {
            $member = $members[$i];
            $help = HelpRequest::create([
                'member_id' => $member->id,
                'amount_requested' => 5000,
                'reason' => 'Urgence médicale démo',
                'status' => 'paid',
            ]);

            // Sortie de fonds
            $fundService->logMovement('outflow', 5000, "Aide Sociale #{$help->id}", $help);
        }


        $this->command->info('Données de test générées avec succès !');
        $this->command->info('15 Membres, 50+ Cotisations, 5 Prêts et 2 Aides créés.');
    }
}
