<?php

namespace App\Filament\Widgets;

use App\Models\Contribution;
use App\Models\Fund;
use App\Models\HelpRequest;
use App\Models\Loan;
use App\Models\Member;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $globalBalance     = Fund::latest()->first()?->balance_after ?? 0;
        $activeMembers     = Member::where('status', 'active')->count();
        $pendingHelp       = HelpRequest::where('status', 'pending')->count();
        $pendingLoans      = Loan::where('status', 'pending')->count();
        $lateContributions = Contribution::where('status', 'late')->count();
        $totalLoaned       = Loan::where('status', 'active')->sum('balance_remaining');

        return [
            Stat::make('💰 Solde Global', \App\Models\Setting::get('currency', 'Gourdes') . ' ' . number_format($globalBalance, 2))
                ->description('Balance actuelle de la caisse')
                ->descriptionIcon('heroicon-o-building-library')
                ->color('success'),

            Stat::make('👥 Membres Actifs', $activeMembers)
                ->description('Membres en règle')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),

            Stat::make('⚠️ Demandes en Attente', $pendingHelp + $pendingLoans)
                ->description("{$pendingHelp} aides • {$pendingLoans} prêts")
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingHelp + $pendingLoans > 0 ? 'warning' : 'success'),

            Stat::make('🔴 Retards de Cotisation', $lateContributions)
                ->description('Paiements en retard')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($lateContributions > 0 ? 'danger' : 'success'),

            Stat::make('📊 Prêts en Cours', \App\Models\Setting::get('currency', 'Gourdes') . ' ' . number_format($totalLoaned, 2))
                ->description('Total des encours')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('primary'),
        ];
    }
}
