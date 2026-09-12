<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Cotisations
            ['key' => 'contribution_amount', 'value' => '500', 'group' => 'contributions', 'label' => 'Montant mensuel de cotisation ($)', 'type' => 'number'],
            ['key' => 'contribution_frequency', 'value' => 'monthly', 'group' => 'contributions', 'label' => 'Fréquence de cotisation', 'type' => 'string'],
            ['key' => 'contribution_late_days', 'value' => '15', 'group' => 'contributions', 'label' => "Jours de grâce avant retard", 'type' => 'number'],
            ['key' => 'contribution_late_fee', 'value' => '100', 'group' => 'contributions', 'label' => 'Pénalité fixe de retard ($)', 'type' => 'number'],
            ['key' => 'contribution_late_type', 'value' => 'monthly', 'group' => 'contributions', 'label' => 'Fréquence de pénalité (weekly/monthly)', 'type' => 'string'],
            ['key' => 'solidarity_rate', 'value' => '20', 'group' => 'contributions', 'label' => 'Part pour le Fonds de Solidarité (%)', 'type' => 'number'],

            // Prêts
            ['key' => 'default_loan_interest_rate', 'value' => '10', 'group' => 'loans', 'label' => "Taux d'intérêt par défaut (%)", 'type' => 'number'],
            ['key' => 'max_loan_multiplier', 'value' => '3', 'group' => 'loans', 'label' => 'Multiplicateur max du prêt (x cotisations)', 'type' => 'number'],
            ['key' => 'max_active_loans', 'value' => '1', 'group' => 'loans', 'label' => 'Nombre maximum de prêts actifs par membre', 'type' => 'number'],
            ['key' => 'loan_late_fee_rate', 'value' => '2', 'group' => 'loans', 'label' => "Taux de pénalité de retard (%)", 'type' => 'number'],
            ['key' => 'loan_late_fee_type', 'value' => 'weekly', 'group' => 'loans', 'label' => "Fréquence pénalité prêt (weekly/monthly)", 'type' => 'string'],
            ['key' => 'loan_fund_margin', 'value' => '20', 'group' => 'loans', 'label' => "Marge de sécurité fonds pour prêts (%)", 'type' => 'number'],

            // Aides
            ['key' => 'help_max_amount', 'value' => '5000', 'group' => 'help', 'label' => "Plafond d'aide sociale ($)", 'type' => 'number'],
            ['key' => 'help_max_per_year', 'value' => '2', 'group' => 'help', 'label' => "Nombre max de demandes d'aide par an", 'type' => 'number'],
            ['key' => 'help_fund_margin', 'value' => '10', 'group' => 'help', 'label' => "Marge de sécurité fonds pour aides (%)", 'type' => 'number'],

            // Général
            ['key' => 'organization_name', 'value' => 'Mutulle', 'group' => 'general', 'label' => 'Nom de la Mutuelle', 'type' => 'string'],
            ['key' => 'currency', 'value' => 'Gourdes', 'group' => 'general', 'label' => 'Devise', 'type' => 'string'],

        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
