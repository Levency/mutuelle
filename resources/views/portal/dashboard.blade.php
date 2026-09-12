@extends('portal.layout')

@section('title', 'Tableau de bord')

@section('content')
    <div class="mb-8">
        <p class="text-sm text-ink-soft">Bonjour,</p>
        <h1 class="font-display text-3xl text-ink">{{ $member->full_name }}</h1>
        <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-ink-soft">
            <span class="font-mono">{{ $member->member_number }}</span>
            <span>·</span>
            <span>Membre depuis le {{ $member->joined_at?->format('d/m/Y') }}</span>
            <x-portal.stamp :tone="$member->status === 'active' ? 'success' : 'danger'">
                {{ $member->status === 'active' ? 'Actif' : 'Suspendu' }}
            </x-portal.stamp>
        </div>
    </div>

    @if($latePenalties > 0)
        <div class="mb-6 rounded-sm border border-stamp/40 bg-stamp/[0.06] px-4 py-3 text-sm text-stamp">
            Des pénalités de retard totalisant <strong class="font-mono">{{ number_format($latePenalties, 2) }} {{ $currency }}</strong> sont en attente sur votre dossier.
        </div>
    @endif

    <x-portal.panel class="mb-6">
        <div class="grid grid-cols-1 divide-y divide-rule sm:grid-cols-3 sm:divide-y-0 sm:divide-x">
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Total cotisé</p>
                <p class="mt-1 font-mono text-2xl text-ink">{{ number_format($totalContributed, 2) }}</p>
                <p class="text-xs text-ink-soft">{{ $currency }}</p>
            </div>
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Prêt actif</p>
                @if($activeLoan)
                    <p class="mt-1 font-mono text-2xl text-ink">{{ number_format($activeLoan->balance_remaining, 2) }}</p>
                    <p class="text-xs text-ink-soft">{{ $currency }} restant à rembourser</p>
                @else
                    <p class="mt-1 font-mono text-2xl text-ink-soft">—</p>
                    <p class="text-xs text-ink-soft">Aucun prêt en cours</p>
                @endif
            </div>
            <div class="px-5 py-5">
                <p class="text-sm text-ink-soft">Demandes d'aide en attente</p>
                <p class="mt-1 font-mono text-2xl text-ink">{{ $pendingHelp }}</p>
                <p class="text-xs text-ink-soft">{{ $pendingHelp > 1 ? 'demandes' : 'demande' }}</p>
            </div>
        </div>
    </x-portal.panel>

    @if($lastContribution)
        <p class="mb-6 text-sm text-ink-soft">
            Dernière cotisation enregistrée le {{ $lastContribution->payment_date?->format('d/m/Y') }} —
            <span class="font-mono">{{ number_format($lastContribution->amount, 2) }} {{ $currency }}</span>
        </p>
    @endif

    <x-portal.panel title="Consulter mon dossier">
        <ul class="divide-y divide-rule">
            @foreach([
                ['route' => 'portal.contributions', 'label' => 'Mes cotisations', 'desc' => 'Historique des paiements et pénalités éventuelles'],
                ['route' => 'portal.loans', 'label' => 'Mes prêts', 'desc' => 'Échéancier, remboursements et solde restant'],
                ['route' => 'portal.help', 'label' => 'Mes demandes d\'aide', 'desc' => 'Statut et suivi de vos demandes de solidarité'],
                ['route' => 'portal.solidarity', 'label' => 'Fonds de solidarité', 'desc' => 'Ma contribution au fonds commun d\'entraide'],
                ['route' => 'portal.report', 'label' => 'Rapport général', 'desc' => 'Entrées, sorties et fonds disponible de la mutuelle'],
            ] as $item)
                <li>
                    <a href="{{ route($item['route']) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-paper-soft">
                        <span>
                            <span class="block text-ink">{{ $item['label'] }}</span>
                            <span class="block text-sm text-ink-soft">{{ $item['desc'] }}</span>
                        </span>
                        <span aria-hidden="true" class="text-ink-soft">›</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-portal.panel>
@endsection
