<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->json('phases')->nullable()->after('contract_signed_at');
            $table->json('client_tasks')->nullable()->after('phases');
            $table->string('current_phase')->nullable()->after('client_tasks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropColumn(['phases', 'client_tasks', 'current_phase']);
        });
    }
};
