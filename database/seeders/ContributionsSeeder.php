<?php

namespace Database\Seeders;

use App\Models\Contribution;
use App\Models\Member;
use Illuminate\Database\Seeder;

class ContributionsSeeder extends Seeder
{
    public function run(): void
    {
        $contributionsData = [
            ['CR001', 'Fevrier 2026', 2500, 100, '2026-02-01'],
            ['CR001', 'Mars 2026', 5000, 200, '2026-03-01'],
            ['CR002', 'Fevrier 2026', 12500, 500, '2026-02-01'],
            ['CR002', 'Mars 2026', 25000, 1000, '2026-03-01'],
            ['CR003', 'Fevrier 2026', 5000, 200, '2026-02-01'],
            ['CR004', 'Fevrier 2026', 2500, 100, '2026-02-01'],
            ['CR005', 'Fevrier 2026', 15000, 500, '2026-02-01'],
            ['CR006', 'Fevrier 2026', 5000, 200, '2026-02-01'],
            ['CR007', 'Fevrier 2026', 10000, 400, '2026-02-01'],
            ['CR008', 'Fevrier 2026', 5000, 200, '2026-02-01'],
            ['CR009', 'Fevrier 2026', 2500, 100, '2026-02-01'],
            ['CR010', 'Fevrier 2026', 2500, 100, '2026-02-01'],
            ['CR011', 'Fevrier 2026', 2500, 100, '2026-02-01'],
            ['CR012', 'Fevrier 2026', 5000, 200, '2026-02-01'],
            ['CR015', 'Fevrier 2026', 5000, 200, '2026-02-01'],
            ['CR016', 'Fevrier 2026', 30000, 1200, '2026-02-01'],
            ['CR017', 'Fevrier 2026', 30000, 1200, '2026-02-01'],
            ['CR028', 'Fevrier 2026', 0, 0, '2026-02-01'],
            ['CR003', 'Mars 2026', 5000, 200, '2026-03-01'],
            ['CR004', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR006', 'Mars 2026', 10000, 400, '2026-03-01'],
            ['CR007', 'Mars 2026', 10000, 400, '2026-03-01'],
            ['CR008', 'Mars 2026', 5000, 200, '2026-03-01'],
            ['CR009', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR010', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR011', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR012', 'Mars 2026', 5000, 200, '2026-03-01'],
            ['CR014', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR015', 'Mars 2026', 5000, 200, '2026-03-01'],
            ['CR018', 'Mars 2026', 5000, 100, '2026-03-01'],
            ['CR019', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR020', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR021', 'Mars 2026', 2500, 100, '2026-03-01'],
            ['CR001', 'Avril 2026', 5000, 200, '2026-04-01'],
            ['CR002', 'Avril 2026', 17500, 2150, '2026-04-01'],
            ['CR004', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR010', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR013', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR014', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR018', 'Avril 2026', 5000, 200, '2026-04-01'],
            ['CR019', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR020', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR026', 'Avril 2026', 5000, 200, '2026-04-01'],
            ['CR027', 'Avril 2026', 5000, 200, '2026-04-01'],
            ['CR013', 'Mai 2026', 2500, 100, '2026-05-01'],
            ['CR013', 'Juin 2026', 2500, 100, '2026-06-01'],
            ['CR013', 'Juillet 2026', 2500, 100, '2026-07-01'],
            ['CR013', 'Aout 2026', 2500, 100, '2026-08-01'],
            ['CR011', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR023', 'Avril 2026', 5000, 200, '2026-04-01'],
            ['CR006', 'Avril 2026', 10000, 400, '2026-04-01'],
            ['CR008', 'Avril 2026', 20000, 400, '2026-04-01'],
            ['CR025', 'Avril 2026', 5000, 0, '2026-04-01'],
            ['CR009', 'Avril 2026', 2500, 100, '2026-04-01'],
            ['CR007', 'Avril 2026', 10000, 400, '2026-04-01'],
            ['CR003', 'Avril 2026', 5000, 200, '2026-04-01'],
            ['CR012', 'Avril 2026', 5000, 200, '2026-04-01'],
            ['CR015', 'Avril 2026', 5000, 200, '2026-04-01'],
        ];

        $counter = 1;

        foreach ($contributionsData as $data) {
            [$memberNumber, $month, $cotisation, $caisseRouge, $paymentDate] = $data;

            // Trouver le membre par member_number
            $member = Member::where('member_number', $memberNumber)->first();

            if (!$member) {
                $this->command->warn("Membre {$memberNumber} introuvable, contribution ignorée.");
                continue;
            }

            // Montant total = Cotisation + Caisse rouge
            $totalAmount = $cotisation + $caisseRouge;

            // Créer la contribution
            Contribution::create([
                'member_id' => $member->id,
                'amount' => $totalAmount,
                'payment_date' => $paymentDate,
                'receipt_number' => 'RECEIPT-' . str_pad($counter, 6, '0', STR_PAD_LEFT),
                'status' => 'paid',
                'notes' => "Cotisation: {$cotisation} HTG, Caisse rouge: {$caisseRouge} HTG ({$month})",
            ]);

            $counter++;
        }

        $this->command->info('Cotisations importées avec succès!');
    }
}
