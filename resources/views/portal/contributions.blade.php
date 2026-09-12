@extends('portal.layout')

@section('title', 'Mes cotisations')

@section('content')
    <div class="mb-8 flex items-end justify-between">
        <div>
            <h1 class="font-display text-3xl text-ink">Mes Cotisations</h1>
            <p class="mt-1 text-sm text-ink-soft">Consultez l'historique complet de vos paiements</p>
        </div>
    </div>

    <!-- Carte de résumé -->
    <div class="mb-8 overflow-hidden rounded-xl bg-brand text-white shadow-lg">
        <div class="px-6 py-8 relative">
            <!-- Décoration d'arrière-plan -->
            <svg class="absolute right-0 top-0 h-32 w-32 -translate-y-8 translate-x-8 text-white/10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.34-.87-2.57-2.49-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.64-2.25 1.64-1.74 0-2.35-.9-2.41-1.74H7.81c.08 1.53 1.25 2.52 3.09 2.81V19h2.34v-1.7c1.52-.3 2.76-1.16 2.76-2.9 0-2.3-1.92-2.98-3.69-3.41z"/></svg>

            <p class="text-sm font-medium text-white/80 uppercase tracking-wider mb-1">Total payé à ce jour</p>
            <div class="flex items-baseline gap-2">
                <span class="font-mono text-4xl font-bold tracking-tight">{{ number_format($total, 2) }}</span>
                <span class="text-lg text-white/80">{{ $currency }}</span>
            </div>
            
            <div class="mt-6 flex items-center gap-2 text-sm text-white/90 bg-black/10 inline-block px-3 py-1.5 rounded-full backdrop-blur-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Vous êtes à jour dans vos paiements</span>
            </div>
        </div>
    </div>

    <!-- Historique des paiements -->
    <h2 class="mb-4 text-xl font-semibold text-ink flex items-center gap-2">
        <svg class="h-5 w-5 text-ink-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        Historique des paiements
    </h2>
    
    <div class="rounded-xl bg-paper shadow-sm ring-1 ring-rule/50 overflow-hidden mb-8">
        @if($contributions->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-ink-soft/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                <p class="mt-4 text-sm text-ink-soft">Aucune cotisation n'a été enregistrée pour le moment.</p>
            </div>
        @else
            <ul class="divide-y divide-rule/40">
                @foreach($contributions as $contribution)
                    <li class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 hover:bg-paper-soft/50 transition-colors">
                        <div class="flex items-start gap-4">
                            <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-base font-semibold text-ink">
                                    Cotisation — {{ $contribution->payment_date?->translatedFormat('d F Y') ?? 'Date non définie' }}
                                </p>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                                        Reçu: {{ $contribution->receipt_number ?? 'N/A' }}
                                    </span>
                                    @if($contribution->notes)
                                        <span class="text-rule">•</span>
                                        <span class="italic text-ink-soft/80">"{{ $contribution->notes }}"</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-6 sm:w-1/3">
                            <div class="text-right">
                                <span class="block font-mono text-lg font-bold text-ink">{{ number_format($contribution->amount, 2) }} {{ $currency }}</span>
                            </div>
                            <div>
                                <x-portal.status type="contribution" :status="$contribution->status" />
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="bg-paper-soft/30 px-5 py-4 border-t border-rule/40">
                {{ $contributions->links() }}
            </div>
        @endif
    </div>

    @if($penalties->isNotEmpty())
        <h2 class="mb-4 mt-10 text-xl font-semibold text-stamp flex items-center gap-2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Pénalités de retard
        </h2>
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-stamp/20 overflow-hidden">
            <ul class="divide-y divide-stamp/10">
                @foreach($penalties as $penalty)
                    <li class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-red-50/30 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stamp/10 text-stamp">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <span class="block font-medium text-ink">{{ $penalty->created_at->translatedFormat('d F Y') }}</span>
                                <span class="block text-sm text-stamp/80">{{ $penalty->periods_late }} période(s) de retard</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="font-mono font-bold text-stamp">{{ number_format($penalty->amount, 2) }} {{ $currency }}</span>
                            <x-portal.status type="penalty" :status="$penalty->status" />
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
