@extends('portal.layout')

@section('title', 'Connexion')

@section('content')
    <div class="mx-auto max-w-sm">
        <div class="mb-8 text-center">
            <p class="font-display text-3xl text-ink">Carnet du Membre</p>
            <p class="mt-2 text-sm text-ink-soft">{{ $orgName }}</p>
        </div>

        <div class="border border-rule bg-paper rounded-sm p-6">
            <p class="mb-6 text-sm text-ink-soft">
                Entrez votre nom, votre prénom et le code d'accès qui vous a été remis pour consulter vos
                cotisations, vos prêts, vos aides et la solidarité.
            </p>

            @if ($errors->any())
                <div class="mb-5 rounded-sm border border-stamp/50 bg-stamp/[0.06] px-4 py-3 text-sm text-stamp">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('portal.login.attempt') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="first_name" class="mb-1 block text-sm font-medium text-ink">Prénom</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required autofocus
                           class="w-full rounded-sm border border-rule bg-white px-3 py-2 text-ink focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                </div>

                <div>
                    <label for="last_name" class="mb-1 block text-sm font-medium text-ink">Nom</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                           class="w-full rounded-sm border border-rule bg-white px-3 py-2 text-ink focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                </div>

                <div>
                    <label for="access_code" class="mb-1 block text-sm font-medium text-ink">Code d'accès</label>
                    <input type="text" inputmode="numeric" autocomplete="off" id="access_code" name="access_code" required
                           class="w-full rounded-sm border border-rule bg-white px-3 py-2 font-mono text-lg tracking-[0.3em] text-ink focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"
                           placeholder="••••••">
                    <p class="mt-1 text-xs text-ink-soft">Le code à 6 chiffres remis par un responsable de la mutuelle.</p>
                </div>

                <button type="submit"
                        class="w-full rounded-sm bg-brand px-4 py-2.5 font-medium text-white hover:bg-brand-dark">
                    Se connecter
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-ink-soft">
            Vous n'avez pas de code d'accès ? Demandez-le à un responsable de la mutuelle.
        </p>
    </div>
@endsection
