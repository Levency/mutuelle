<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class RapportDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Rapports & Analyses';
    protected static ?string $navigationLabel = 'Centre de Rapports';
    protected static ?string $title = 'Centre de Rapports & Filtres';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.rapport-dashboard';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'period' => 'this_month',
            'from'   => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'to'     => Carbon::now()->endOfMonth()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtres de Période')
                    ->description('Sélectionnez une période. Tous les boutons ci-dessous utiliseront ces dates.')
                    ->schema([
                        Select::make('period')
                            ->label('Période prédéfinie')
                            ->options([
                                'today' => 'Aujourd\'hui',
                                'this_week' => 'Cette semaine',
                                'this_month' => 'Ce mois-ci',
                                'last_month' => 'Mois dernier',
                                'this_year' => 'Cette année',
                                'all' => 'Tout (Historique complet)',
                                'custom' => 'Plage personnalisée...',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state === 'all') {
                                    $set('from', null);
                                    $set('to', null);
                                    return;
                                }
                                
                                $dates = match ($state) {
                                    'today' => [now()->format('Y-m-d'), now()->format('Y-m-d')],
                                    'this_week' => [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')],
                                    'this_month' => [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')],
                                    'last_month' => [now()->subMonth()->startOfMonth()->format('Y-m-d'), now()->subMonth()->endOfMonth()->format('Y-m-d')],
                                    'this_year' => [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
                                    default => [null, null],
                                };

                                if ($dates[0]) {
                                    $set('from', $dates[0]);
                                    $set('to', $dates[1]);
                                }
                            }),
                        
                        DatePicker::make('from')
                            ->label('Du')
                            ->required(fn ($get) => $get('period') !== 'all')
                            ->visible(fn ($get) => $get('period') !== 'all'),
                        
                        DatePicker::make('to')
                            ->label('Au')
                            ->required(fn ($get) => $get('period') !== 'all')
                            ->visible(fn ($get) => $get('period') !== 'all'),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    /**
     * Valide le formulaire et redirige vers le rapport.
     */
    public function generateReport(string $type, string $action = 'stream'): void
    {
        $this->form->validate();

        $params = $this->data;
        $params['action'] = $action;

        // Vérification de présence des données
        if (!$this->hasDataForReport($type, $params)) {
            Notification::make()
                ->title('Aucune donnée trouvée')
                ->body('Il n\'y a aucun enregistrement pour la période sélectionnée.')
                ->warning()
                ->send();
            return;
        }

        $url = route("reports.{$type}", $params);
        $this->dispatch('open-report', url: $url);
    }

    /**
     * Vérifie si des données existent pour le rapport demandé.
     */
    private function hasDataForReport(string $type, array $params): bool
    {
        $query = match($type) {
            'members'      => \App\Models\Member::query(),
            'expenses'     => \App\Models\Fund::where('type', 'outflow')->whereNull('reference_type'),
            'contributions'=> \App\Models\Contribution::query(),
            'loans'        => \App\Models\Loan::query(),
            'repayments'   => \App\Models\LoanRepayment::query(),
            'help_requests'=> \App\Models\HelpRequest::query(),
            'balance', 'general' => null, // Toujours accessibles car calculés dynamiquement
            default        => null,
        };

        if (!$query) return true;

        if ($params['period'] !== 'all') {
            $col = match($type) {
                'contributions', 'repayments' => 'payment_date',
                default => 'created_at',
            };
            if ($params['from']) $query->whereDate($col, '>=', $params['from']);
            if ($params['to']) $query->whereDate($col, '<=', $params['to']);
        }

        return $query->exists();
    }

}
