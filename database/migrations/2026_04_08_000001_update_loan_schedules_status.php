<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // On modifie l'énumération pour inclure 'partial'
        // Note: SQLite ne supporte pas bien le changement d'enum, on passe par un changement de type temporaire si nécessaire
        // Mais ici on vise MySQL/MariaDB en priorité.
        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_schedules', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'late'])->default('pending')->change();
        });
    }
};
