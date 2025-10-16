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
            // Drop the specializations column
            $table->dropColumn('specializations');
            
            // Modify the languages column to be JSON
            $table->json('languages')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            // Add back the specializations column
            $table->text('specializations')->nullable();
            
            // Change languages back to text
            $table->text('languages')->nullable()->change();
        });
    }
};
