@extends('portal.layout')

@section('title', 'Rapport général')

@section('content')
    <h1 class="mb-1 font-display text-2xl text-ink">Rapport général</h1>
    <p class="mb-6 text-sm text-ink-soft">
        Vue d'ensemble des finances de la mutuelle — chiffres globaux uniquement, sans le détail des autres membres.
    </p>

    <x-portal.panel title="Fonds disponible" class="mb-6">
        <div class="grid grid-cols-1 divide-y divide-rule sm:grid-cols-3 sm:divide-y-0 sm:divide-x">
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Solde de caisse</p>
                <p class="mt-1 font-mono text-2xl text-ink">{{ number_format($grossBalance, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }}</p>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Fonds disponible</p>
                <p class="mt-1 font-mono text-2xl text-brand-dark">{{ number_format($availableBalance, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }} — après prêts en cours</p>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Fonds de solidarité</p>
                <p class="mt-1 font-mono text-2xl text-ink">{{ number_format($solidarityBalance, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }}</p>
            </div>
        </div>
    </x-portal.panel>

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
@endsection
