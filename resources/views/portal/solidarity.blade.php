@extends('portal.layout')

@section('title', 'Fonds de solidarité')

@section('content')
    <h1 class="mb-1 font-display text-2xl text-ink">Fonds de solidarité</h1>
    <p class="mb-6 text-sm text-ink-soft">
        Une part de chaque cotisation alimente ce fonds commun, utilisé pour financer les aides accordées aux membres.
    </p>

    <x-portal.panel class="mb-6">
        <div class="grid grid-cols-2 divide-x divide-rule">
            <div class="px-5 py-4">
                <p class="text-xs text-ink-soft">Ma contribution totale</p>
                <p class="font-mono text-xl text-ink">{{ number_format($myTotal, 2) }} {{ $currency }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-xs text-ink-soft">Solde actuel du fonds (tous membres)</p>
                <p class="font-mono text-xl text-ink">{{ number_format($fundBalance, 2) }} {{ $currency }}</p>
            </div>
        </div>
    </x-portal.panel>

    <x-portal.panel title="Détail de ma contribution">
        @if($movements->isEmpty())
            <p class="px-5 py-6 text-sm text-ink-soft">Aucun mouvement de solidarité pour le moment.</p>
        @else
            <ul class="divide-y divide-rule">
                @foreach($movements as $movement)
                    <li class="flex items-center justify-between gap-4 px-5 py-3">
                        <span>
                            <span class="block text-ink">{{ $movement->created_at->format('d/m/Y') }}</span>
                            <span class="block text-sm text-ink-soft">{{ $movement->description }}</span>
                        </span>
                        <span class="font-mono text-ink">{{ number_format($movement->amount, 2) }} {{ $currency }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-portal.panel>
@endsection
