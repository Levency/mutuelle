<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Loan;

$activeLoansWithoutSchedules = Loan::where('status', 'active')
    ->whereDoesntHave('schedules')
    ->get();

echo "Found " . $activeLoansWithoutSchedules->count() . " active loans without schedules.\n";

foreach ($activeLoansWithoutSchedules as $loan) {
    echo "Generating schedules for Loan #{$loan->id}...\n";
    $loan->generateSchedules();
}

echo "Done!\n";
