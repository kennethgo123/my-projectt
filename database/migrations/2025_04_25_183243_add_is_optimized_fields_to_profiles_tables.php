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
            $table->boolean('is_optimized')->default(false)->after('rating');
        });

        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->boolean('is_optimized')->default(false)->after('bir_certificate_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->dropColumn('is_optimized');
        });

        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->dropColumn('is_optimized');
        });
    }
};
