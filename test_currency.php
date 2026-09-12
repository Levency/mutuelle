<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
try {
    echo "Result for USD: " . Illuminate\Support\Number::currency(100, 'USD') . "\n";
    echo "Result for HTG: " . Illuminate\Support\Number::currency(100, 'HTG') . "\n";
    echo "Result for $: " . Illuminate\Support\Number::currency(100, '$') . "\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
try {
    echo "Result for Gourdes: " . Illuminate\Support\Number::currency(100, 'Gourdes') . "\n";
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
