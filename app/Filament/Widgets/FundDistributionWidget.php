<?php

namespace App\Filament\Widgets;

use App\Models\Fund;
use App\Models\HelpRequest;
use App\Models\Loan;
use Filament\Widgets\ChartWidget;

class FundDistributionWidget extends ChartWidget
{
    protected static ?string $heading = '🏦 Répartition des Fonds';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $totalInLoans = Loan::where('status', 'active')->sum('balance_remaining');
        $totalInHelp = HelpRequest::where('status', 'paid')->sum('amount_requested');
        $globalBalance = Fund::latest()->first()?->balance_after ?? 0;
        $available = max(0, $globalBalance - $totalInLoans);

        return [
            'datasets' => [
                [
                    'data' => [$available, $totalInLoans, $totalInHelp],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',  // Emerald — Disponible
                        'rgba(99, 102, 241, 0.8)',  // Indigo — Prêts actifs
                        'rgba(251, 146, 60, 0.8)',  // Amber — Aides accordées
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['💰 Disponible', '📊 Prêts Actifs', '🤝 Aides Accordées'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
