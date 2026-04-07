<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->string('valuable_type'); // Loan, HelpRequest...
            $table->unsignedBigInteger('valuable_id');
            $table->foreignId('user_id')->constrained(); // Validator (Comité)
            $table->enum('status', ['validated', 'rejected', 'correction_needed']);
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
