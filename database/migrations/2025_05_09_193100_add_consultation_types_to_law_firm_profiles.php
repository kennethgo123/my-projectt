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
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('law_firm_profiles', 'offers_online_consultation')) {
                $table->boolean('offers_online_consultation')->default(false)->after('photo_path');
            }
            if (!Schema::hasColumn('law_firm_profiles', 'offers_inhouse_consultation')) {
                $table->boolean('offers_inhouse_consultation')->default(false)->after('offers_online_consultation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('law_firm_profiles', 'offers_online_consultation')) {
                $table->dropColumn('offers_online_consultation');
            }
            if (Schema::hasColumn('law_firm_profiles', 'offers_inhouse_consultation')) {
                $table->dropColumn('offers_inhouse_consultation');
            }
        });
    }
};
