<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
