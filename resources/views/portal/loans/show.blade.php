@extends('portal.layout')

@section('title', 'Détail du prêt')

@section('content')
    <a href="{{ route('portal.loans') }}" class="mb-4 inline-block text-sm text-ink-soft hover:text-ink">‹ Mes prêts</a>

    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl text-ink">
                Prêt du {{ $loan->disbursement_date?->format('d/m/Y') ?? $loan->created_at->format('d/m/Y') }}
            </h1>
            <p class="mt-1 text-sm text-ink-soft">Échéance finale le {{ $loan->due_date?->format('d/m/Y') ?? '—' }}</p>
        </div>
        <x-portal.status type="loan" :status="$loan->status" />
    </div>

    <x-portal.panel class="mb-6">
        <div class="grid grid-cols-2 divide-rule sm:grid-cols-4 sm:divide-x">
            <div class="px-5 py-4">
                <p class="text-xs text-ink-soft">Principal</p>
                <p class="font-mono text-ink">{{ number_format($loan->principal_amount, 2) }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs text-ink-soft">Taux d'intérêt</p>
                <p class="font-mono text-ink">{{ number_format($loan->interest_rate, 2) }}%</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs text-ink-soft">Total à rembourser</p>
                <p class="font-mono text-ink">{{ number_format($loan->total_to_repay, 2) }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs text-ink-soft">Solde restant</p>
                <p class="font-mono text-ink">{{ number_format($loan->balance_remaining, 2) }}</p>
            </div>
        </div>
    </x-portal.panel>

    <x-portal.panel title="Échéancier" class="mb-6">
        @if($loan->schedules->isEmpty())
            <p class="px-5 py-6 text-sm text-ink-soft">Échéancier non encore généré.</p>
        @else
            <ul class="divide-y divide-rule">
                @foreach($loan->schedules as $schedule)
                    <li class="flex items-center justify-between gap-4 px-5 py-3">
                        <span class="text-ink">{{ $schedule->due_date->format('d/m/Y') }}</span>
                        <span class="flex items-center gap-3">
                            <span class="font-mono text-sm text-ink-soft">
                                {{ number_format($schedule->amount_paid, 2) }} / {{ number_format($schedule->amount_due, 2) }} {{ $currency }}
                            </span>
                            <x-portal.status type="schedule" :status="$schedule->status" />
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-portal.panel>

    <x-portal.panel title="Remboursements effectués">
        @if($loan->repayments->isEmpty())
            <p class="px-5 py-6 text-sm text-ink-soft">Aucun remboursement enregistré pour ce prêt.</p>
        @else
            <ul class="divide-y divide-rule">
                @foreach($loan->repayments as $repayment)
                    <li class="flex items-center justify-between gap-4 px-5 py-3">
                        <span>
                            <span class="block text-ink">{{ $repayment->payment_date->format('d/m/Y') }}</span>
                            <span class="block text-xs text-ink-soft">
                                {{ $repayment->payment_method ?? 'Espèces' }}
                                @if($repayment->receipt_number) · Reçu {{ $repayment->receipt_number }} @endif
                            </span>
                        </span>
                        <span class="font-mono text-ink">{{ number_format($repayment->amount_paid, 2) }} {{ $currency }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-portal.panel>
@endsection
