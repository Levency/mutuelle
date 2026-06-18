<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MembersSeeder extends Seeder
{
    public function run(): void
    {
        $membersData = [
            ['CR001', 'FRANCOIS', 'REGINALD', 'M', '(+509) 37108090'],
            ['CR002', 'DORESTAND', 'PEGGUY', 'M', '(+1) 2672051667'],
            ['CR003', 'VICTOR FRANCOIS', 'GUETTELINE', 'F', '(+509) 36943578'],
            ['CR004', 'SEIDE', 'ERICKA', 'F', '(+1) 8099674861'],
            ['CR005', 'JEAN LOUIS', 'ERICK', 'M', '(+509) 33393420'],
            ['CR006', 'JEAN', 'LUCKERSON', 'M', '(+1) 7703341741'],
            ['CR007', 'FRANCOIS', 'STEPHANE', 'M', '(+223) 93907003'],
            ['CR008', 'TORCHON', 'PATRICK', 'M', '(+33) 752491875'],
            ['CR009', 'DESROSIER', 'CAMILLA', 'F', '(+509) 42965069'],
            ['CR010', 'FRANCOIS', 'MARIE MICHELLE', 'F', '(+509) 42692766'],
            ['CR011', 'JEAN BAPTISTE', 'EDDY', 'H', '(+225) 0769514930'],
            ['CR012', 'FRANCOIS', 'SARA STEPHALYNE', 'F', '(+509) 36943578'],
            ['CR013', 'MAXDANE', 'SILDOR', 'F', '(+1) 4074843584'],
            ['CR014', 'VERSAILLES', 'GUIFFORD', 'H', '(+509) 48311446'],
            ['CR015', 'ESDRAS STEPHEN', 'FRANCOIS', 'H', '(+509) 36943578'],
            ['CR016', 'DESROSIERS', 'JONAS', 'H', '(+509) 46255748'],
            ['CR017', 'DESROSIERS', 'POTEAUX JESULA', 'F', '(+509) 46255748'],
            ['CR018', 'MORE', 'NICOLE', 'F', '(+237) 697644390'],
            ['CR019', 'MORE', 'JAMES', 'H', '(+237) 697644390'],
            ['CR020', 'MORE', 'PARFAIT', 'H', '(+237) 697644390'],
            ['CR021', 'HOBERT', 'CANGER', 'H', '(+509) 31464251'],
            ['CR022', 'GEORGES', 'YVAR', 'F', '(+509) 40384539'],
            ['CR023', 'FRANCOIS', 'ROSEMOND', 'H', '(+509) 36854102'],
            ['CR024', 'JUDES', 'BRUNO', 'H', '(+509) 44643597'],
            ['CR025', 'TORCHON', 'HENDEL', 'H', '(+33) 617 78 96 90'],
            ['CR026', 'CURTIS', 'FRANCOIS', 'H', null],
            ['CR027', 'ANNE-MARIE', 'PHILLIPE', 'F', null],
            ['CR028', 'MELE', 'SAMENJINA FALI', 'F', '(+1) 4074843584'],
        ];

        foreach ($membersData as $data) {
            [$memberNumber, $lastName, $firstName, $gender, $phone] = $data;

            // Créer l'utilisateur
            $user = User::firstOrCreate(
                ['email' => strtolower($memberNumber . '@mutuelle.local')],
                [
                    'name' => trim("{$firstName} {$lastName}"),
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            // Créer le membre
            Member::firstOrCreate(
                ['member_number' => $memberNumber],
                [
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'status' => 'active',
                    'joined_at' => now()->toDateString(),
                    'confidence_score' => 100,
                ]
            );
        }

        $this->command->info('Membres importés avec succès!');
    }
}
