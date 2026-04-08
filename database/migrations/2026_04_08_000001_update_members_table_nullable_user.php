<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Rendre user_id nullable — un membre peut exister sans compte
            $table->foreignId('user_id')->nullable()->change();

            // Informations personnelles directes (si pas de compte user)
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone', 20)->nullable()->after('last_name');
            $table->string('address')->nullable()->after('phone');
            $table->date('birth_date')->nullable()->after('address');
            $table->string('emergency_contact')->nullable()->after('birth_date');
            $table->string('emergency_phone', 20)->nullable()->after('emergency_contact');
            $table->string('national_id')->nullable()->after('emergency_phone');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->dropColumn([
                'first_name', 'last_name', 'phone', 'address',
                'birth_date', 'emergency_contact', 'emergency_phone', 'national_id',
            ]);
        });
    }
};
