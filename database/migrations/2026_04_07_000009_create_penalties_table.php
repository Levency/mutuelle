<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->morphs('penalizable'); // loan or contribution
            $table->foreignId('member_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->enum('status', ['pending', 'paid', 'waived'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
