<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(0); // Interest percentage
            $table->integer('term_months')->default(12);
            $table->decimal('total_to_repay', 12, 2); // Calculated value
            $table->decimal('balance_remaining', 12, 2);
            $table->enum('status', ['pending', 'active', 'repaid', 'defaulted', 'rejected'])->default('pending');
            $table->date('disbursement_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
