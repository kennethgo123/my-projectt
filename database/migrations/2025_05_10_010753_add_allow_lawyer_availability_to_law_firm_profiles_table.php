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
            $table->boolean('allow_lawyer_availability')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->dropColumn('allow_lawyer_availability');
        });
    }
};
