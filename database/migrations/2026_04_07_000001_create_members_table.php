<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('member_number')->unique();
            $table->string('profession')->nullable();
            $table->decimal('confidence_score', 5, 2)->default(100.00); // 0-100
            $table->enum('status', ['active', 'suspended', 'pending'])->default('active');
            $table->date('joined_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
