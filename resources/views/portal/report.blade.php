@extends('portal.layout')

@section('title', 'Rapport général')

@section('content')
    <h1 class="mb-1 font-display text-2xl text-ink">Rapport général</h1>
    <p class="mb-6 text-sm text-ink-soft">
        Vue d'ensemble des finances de la mutuelle — chiffres globaux uniquement, sans le détail des autres membres.
    </p>

    <!-- Résumé de la caisse (Bilan financier) -->
    <x-portal.panel title="Bilan Financier Global" class="mb-6 border-l-4 border-l-brand">
        <div class="grid grid-cols-1 divide-y divide-rule sm:grid-cols-3 sm:divide-y-0 sm:divide-x">
            <div class="px-5 py-5 bg-brand/5">
                <p class="text-sm font-semibold text-brand-dark">Montant Total Entré</p>
                <p class="mt-1 font-mono text-2xl text-ink">{{ number_format($totalInflows, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }} — Toutes entrées confondues</p>
            </div>
            <div class="px-5 py-5 bg-stamp/5">
                <p class="text-sm font-semibold text-stamp">Total des Dépenses (Sorties)</p>
                <p class="mt-1 font-mono text-2xl text-ink">- {{ number_format($totalExpenses, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }} — Prêts décaissés, aides, etc.</p>
            </div>
            <div class="px-5 py-5 bg-paper">
                <p class="text-sm font-semibold text-ink">Solde de Caisse Actuel</p>
                <!-- On affiche le calcul (Entrées - Sorties) qui équivaut au grossBalance -->
                <p class="mt-1 font-mono text-3xl font-bold text-ink">{{ number_format($grossBalance, 2) }}</p>
                <p class="text-xs font-semibold text-brand">{{ $currency }} — Montant physiquement disponible</p>
            </div>
        </div>
    </x-portal.panel>

    <x-portal.panel title="Fonds Spéciaux" class="mb-6">
        <div class="grid grid-cols-1 divide-y divide-rule sm:grid-cols-2 sm:divide-y-0 sm:divide-x">
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Fonds de solidarité</p>
                <p class="mt-1 font-mono text-2xl text-ink">{{ number_format($solidarityBalance, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }}</p>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Disponibilité pour nouveaux prêts</p>
                <p class="mt-1 font-mono text-2xl text-brand-dark">{{ number_format($availableBalance, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }} — après déduction des encours</p>
            </div>
        </div>
    </x-portal.panel>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <x-portal.panel title="Activité de la mutuelle">
            <ul class="divide-y divide-rule">
                <li class="flex items-center justify-between px-5 py-4">
                    <span class="text-ink">Membres actifs</span>
                    <span class="font-mono text-ink">{{ $totalMembers }}</span>
                </li>
                <li class="flex items-center justify-between px-5 py-4">
                    <span class="text-ink">Total des cotisations payées</span>
                    <span class="font-mono text-ink">{{ number_format($totalContributions, 2) }} {{ $currency }}</span>
                </li>
                <li class="flex items-center justify-between px-5 py-4">
                    <span class="text-ink">Prêts en cours</span>
                    <span class="font-mono text-ink">{{ $activeLoansCount }} ({{ number_format($activeLoansTotal, 2) }} {{ $currency }})</span>
                </li>
                <li class="flex items-center justify-between px-5 py-4">
                    <span class="text-ink">Demandes d'aide en attente</span>
                    <span class="font-mono text-ink">{{ $pendingHelpCount }}</span>
                </li>
            </ul>
        </x-portal.panel>

        <!-- Nouvelle section pour la liste des dépenses -->
        <x-portal.panel title="Détail des Dépenses (Sorties)">
            @if($expenses->isEmpty())
                <p class="px-5 py-6 text-sm text-ink-soft text-center">Aucune dépense enregistrée.</p>
            @else
                <div class="max-h-72 overflow-y-auto">
                    <ul class="divide-y divide-rule">
                        @foreach($expenses as $expense)
                            <li class="px-5 py-4">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-semibold text-ink">{{ $expense->description ?? 'Sortie non spécifiée' }}</span>
                                    <span class="font-mono font-bold text-stamp">-{{ number_format($expense->amount, 2) }}</span>
                                </div>
                                <div class="text-xs text-ink-soft flex justify-between">
                                    <span>Le {{ $expense->created_at->format('d/m/Y') }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-portal.panel>
    </div>
@endsection
