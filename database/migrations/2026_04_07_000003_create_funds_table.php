<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['inflow', 'outflow']);
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->string('reference_type')->nullable(); // e.g. contribution, loan, help_request
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funds');
    }
};
