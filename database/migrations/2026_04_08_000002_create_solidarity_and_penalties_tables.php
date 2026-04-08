<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solidarity_funds', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['inflow', 'outflow'])->default('inflow');
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->decimal('balance_after', 10, 2)->default(0);
            $table->nullableMorphs('reference');
            $table->timestamps();
        });

        Schema::create('loan_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained();
            $table->integer('periods_late')->default(1); // semaines ou mois
            $table->string('period_type')->default('weekly'); // weekly | monthly
            $table->decimal('rate', 5, 2)->default(2); // % par période
            $table->decimal('amount', 10, 2); // montant calculé
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');
            $table->timestamps();
        });

        Schema::create('contribution_penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained();
            $table->integer('periods_late')->default(1);
            $table->string('period_type')->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_penalties');
        Schema::dropIfExists('loan_penalties');
        Schema::dropIfExists('solidarity_funds');
    }
};
