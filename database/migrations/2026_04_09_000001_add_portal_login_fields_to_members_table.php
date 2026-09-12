<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Code d'accès au portail membre — toujours stocké haché (jamais en clair)
            $table->string('access_code')->nullable()->after('national_id');
            $table->timestamp('access_code_generated_at')->nullable()->after('access_code');
            $table->timestamp('portal_last_login_at')->nullable()->after('access_code_generated_at');
            $table->string('portal_last_login_ip', 45)->nullable()->after('portal_last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'access_code',
                'access_code_generated_at',
                'portal_last_login_at',
                'portal_last_login_ip',
            ]);
        });
    }
};
