@extends('portal.layout')

@section('title', 'Mes cotisations')

@section('content')
    <h1 class="mb-1 font-display text-2xl text-ink">Mes cotisations</h1>
    <p class="mb-6 text-sm text-ink-soft">
        Total payé à ce jour : <span class="font-mono text-ink">{{ number_format($total, 2) }} {{ $currency }}</span>
    </p>

    <x-portal.panel title="Historique des paiements">
        @if($contributions->isEmpty())
            <p class="px-5 py-6 text-sm text-ink-soft">Aucune cotisation enregistrée pour le moment.</p>
        @else
            <ul class="divide-y divide-rule">
                @foreach($contributions as $contribution)
                    <li class="flex items-center justify-between gap-4 px-5 py-4">
                        <span>
                            <span class="block text-ink">{{ $contribution->payment_date?->format('d/m/Y') ?? '—' }}</span>
                            <span class="block text-sm text-ink-soft">
                                Reçu {{ $contribution->receipt_number ?? '—' }}
                                @if($contribution->notes) · {{ $contribution->notes }} @endif
                            </span>
                        </span>
                        <span class="flex items-center gap-3">
                            <span class="font-mono text-ink">{{ number_format($contribution->amount, 2) }} {{ $currency }}</span>
                            <x-portal.status type="contribution" :status="$contribution->status" />
                        </span>
                    </li>
                @endforeach
            </ul>
            <div class="border-t border-rule px-5 py-4">
                {{ $contributions->links() }}
            </div>
        @endif
    </x-portal.panel>

    @if($penalties->isNotEmpty())
        <x-portal.panel title="Pénalités de retard" class="mt-6">
            <ul class="divide-y divide-rule">
                @foreach($penalties as $penalty)
                    <li class="flex items-center justify-between gap-4 px-5 py-4">
                        <span>
                            <span class="block text-ink">{{ $penalty->created_at->format('d/m/Y') }}</span>
                            <span class="block text-sm text-ink-soft">{{ $penalty->periods_late }} période(s) de retard</span>
                        </span>
                        <span class="flex items-center gap-3">
                            <span class="font-mono text-ink">{{ number_format($penalty->amount, 2) }} {{ $currency }}</span>
                            <x-portal.status type="penalty" :status="$penalty->status" />
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-portal.panel>
    @endif
@endsection
