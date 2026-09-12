<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ── Portail Membre ─────────────────────────────────────────────────────────
// Interface publique indépendante de l'admin Filament (/admin) : les membres
// s'y connectent avec nom + prénom + code d'accès et ne voient jamais que
// leurs propres données. C'est la page d'accueil du site.
Route::get('/', fn () => Auth::guard('member')->check()
    ? redirect()->route('portal.dashboard')
    : redirect()->route('portal.login')
);

Route::name('portal.')->group(function () {
    Route::middleware('guest.member')->group(function () {
        Route::get('/connexion', [PortalAuthController::class, 'showLogin'])->name('login');
        Route::post('/connexion', [PortalAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login.attempt');
    });

    Route::middleware('auth.member')->group(function () {
        Route::post('/deconnexion', [PortalAuthController::class, 'logout'])->name('logout');
        Route::get('/tableau-de-bord', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/cotisations', [PortalController::class, 'contributions'])->name('contributions');
        Route::get('/prets', [PortalController::class, 'loans'])->name('loans');
        Route::get('/prets/{loan}', [PortalController::class, 'loanShow'])->name('loans.show');
        Route::get('/aides', [PortalController::class, 'help'])->name('help');
        Route::get('/solidarite', [PortalController::class, 'solidarity'])->name('solidarity');
        Route::get('/rapport-general', [PortalController::class, 'report'])->name('report');
    });
});

// ── Rapports PDF (protégés par auth Filament) ─────────────────────────────
Route::middleware(['auth'])->prefix('admin/reports')->name('reports.')->group(function () {
    Route::get('/general',       [ReportController::class, 'general'])->name('general');
    Route::get('/members',       [ReportController::class, 'members'])->name('members');
    Route::get('/expenses',      [ReportController::class, 'expenses'])->name('expenses');
    Route::get('/balance',       [ReportController::class, 'balance'])->name('balance');
    Route::get('/contributions', [ReportController::class, 'contributions'])->name('contributions');
    Route::get('/loans',         [ReportController::class, 'loans'])->name('loans');
    Route::get('/repayments',    [ReportController::class, 'repayments'])->name('repayments');
    Route::get('/help-requests', [ReportController::class, 'helpRequests'])->name('help_requests');
});
