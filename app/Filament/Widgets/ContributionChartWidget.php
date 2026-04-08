<?php

namespace App\Filament\Widgets;

use App\Models\Contribution;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ContributionChartWidget extends ChartWidget
{
    protected static ?string $heading = '📈 Cotisations des 6 derniers mois';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $data[] = Contribution::where('status', 'paid')
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cotisations ($)',
                    'data' => $data,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
