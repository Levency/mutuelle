@extends('portal.layout')

@section('title', "Mes demandes d'aide")

@section('content')
    <h1 class="mb-6 font-display text-2xl text-ink">Mes demandes d'aide</h1>

    <x-portal.panel>
        @if($helpRequests->isEmpty())
            <p class="px-5 py-6 text-sm text-ink-soft">Vous n'avez soumis aucune demande d'aide.</p>
        @else
            <ul class="divide-y divide-rule">
                @foreach($helpRequests as $help)
                    <li class="px-5 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-ink">{{ $help->reason }}</p>
                                <p class="text-sm text-ink-soft">{{ $help->created_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-ink">{{ number_format($help->amount_requested, 2) }} {{ $currency }}</span>
                                <x-portal.status type="help" :status="$help->status" />
                            </div>
                        </div>

                        @if($help->description)
                            <p class="mt-2 text-sm text-ink-soft">{{ $help->description }}</p>
                        @endif

                        @if($help->validations->isNotEmpty())
                            <ul class="mt-3 space-y-1 border-l-2 border-rule pl-3">
                                @foreach($help->validations as $validation)
                                    <li class="text-sm text-ink-soft">
                                        <x-portal.status type="help" :status="$validation->status" />
                                        @if($validation->comments)
                                            — {{ $validation->comments }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-portal.panel>
@endsection
