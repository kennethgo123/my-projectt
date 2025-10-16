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
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->text('pricing_description')->nullable()->after('max_budget');
        });
        
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->text('pricing_description')->nullable()->after('max_budget');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->dropColumn('pricing_description');
        });

        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->dropColumn('pricing_description');
        });
    }
};
