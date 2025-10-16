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
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['legal_case_id']);
            
            // Change the column to nullable
            $table->foreignId('legal_case_id')->nullable()->change();
            
            // Add the foreign key constraint back with onDelete('set null')
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the nullable foreign key constraint
            $table->dropForeign(['legal_case_id']);
            
            // Change the column back to non-nullable
            $table->foreignId('legal_case_id')->nullable(false)->change();
            
            // Add the original foreign key constraint back
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->onDelete('cascade');
        });
    }
};
