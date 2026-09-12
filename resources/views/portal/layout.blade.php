<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Carnet du Membre') — {{ \App\Models\Setting::get('organization_name', 'Mutuelle') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-bg font-sans text-ink antialiased">

    @php($orgName = \App\Models\Setting::get('organization_name', 'Mutuelle'))

    @auth('member')
        <header class="border-b border-rule bg-paper">
            <div class="mx-auto flex max-w-2xl items-center justify-between px-4 py-3">
                <a href="{{ route('portal.dashboard') }}" class="font-display text-lg text-ink">
                    Carnet du Membre
                    <span class="block text-[11px] font-sans font-medium text-ink-soft">{{ $orgName }}</span>
                </a>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-ink-soft underline decoration-rule underline-offset-4 hover:text-stamp">
                        Se déconnecter
                    </button>
                </form>
            </div>
            <nav class="mx-auto flex max-w-2xl gap-1 overflow-x-auto px-4 pb-2 text-sm">
                @foreach([
                    ['route' => 'portal.dashboard', 'label' => 'Tableau de bord'],
                    ['route' => 'portal.contributions', 'label' => 'Cotisations'],
                    ['route' => 'portal.loans', 'label' => 'Prêts'],
                    ['route' => 'portal.help', 'label' => 'Aides'],
                    ['route' => 'portal.solidarity', 'label' => 'Solidarité'],
                    ['route' => 'portal.report', 'label' => 'Rapport général'],
                ] as $item)
                    <a href="{{ route($item['route']) }}"
                       class="shrink-0 whitespace-nowrap rounded-sm px-3 py-1.5 {{ request()->routeIs($item['route'].'*') ? 'bg-brand text-white' : 'text-ink-soft hover:bg-paper-soft hover:text-ink' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </header>
    @endauth

    <main class="mx-auto max-w-2xl px-4 py-8">
        @if(session('status'))
            <div class="mb-6 rounded-sm border border-brand/40 bg-brand/[0.06] px-4 py-3 text-sm text-brand-dark">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mx-auto max-w-2xl px-4 pb-10 pt-4 text-center text-xs text-ink-soft">
        {{ $orgName }} — Accès membre sécurisé.
    </footer>
</body>
</html>
