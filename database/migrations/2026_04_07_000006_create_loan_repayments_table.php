<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('interest_paid', 10, 2)->default(0);
            $table->decimal('principal_paid', 10, 2)->default(0);
            $table->date('payment_date');
            $table->string('receipt_number')->nullable();
            $table->enum('payment_method', ['cash', 'transfer', 'check'])->default('transfer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};
