@extends('portal.layout')

@section('title', 'Mes prêts')

@section('content')
    <h1 class="mb-6 font-display text-2xl text-ink">Mes prêts</h1>

    <x-portal.panel>
        @if($loans->isEmpty())
            <p class="px-5 py-6 text-sm text-ink-soft">Vous n'avez aucun prêt enregistré.</p>
        @else
            <ul class="divide-y divide-rule">
                @foreach($loans as $loan)
                    <li>
                        <a href="{{ route('portal.loans.show', $loan) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-paper-soft">
                            <span>
                                <span class="block text-ink">
                                    Prêt du {{ $loan->disbursement_date?->format('d/m/Y') ?? $loan->created_at->format('d/m/Y') }}
                                </span>
                                <span class="block text-sm text-ink-soft">
                                    Principal <span class="font-mono">{{ number_format($loan->principal_amount, 2) }} {{ $currency }}</span>
                                    · {{ $loan->term_months }} mois
                                </span>
                            </span>
                            <span class="flex items-center gap-3">
                                <span class="text-right">
                                    <span class="block font-mono text-ink">{{ number_format($loan->balance_remaining, 2) }} {{ $currency }}</span>
                                    <span class="block text-xs text-ink-soft">restant</span>
                                </span>
                                <x-portal.status type="loan" :status="$loan->status" />
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-portal.panel>
@endsection
