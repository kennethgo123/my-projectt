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
        // First, check if the table exists
        if (Schema::hasTable('law_firm_ratings')) {
            // If it exists, try to check if it has all required columns
            // If not, drop it so we can recreate it properly
            try {
                $hasAllColumns = true;
                
                // Try a query to see if the essential columns exist
                try {
                    \DB::select('SELECT client_id, law_firm_id, legal_case_id, rating, feedback, rated_at FROM law_firm_ratings LIMIT 1');
                } catch (\Exception $e) {
                    $hasAllColumns = false;
                }
                
                if (!$hasAllColumns) {
                    // Drop the table if it's missing required columns
                    Schema::dropIfExists('law_firm_ratings');
                } else {
                    // Table exists and has the required columns, nothing to do
                    return;
                }
            } catch (\Exception $e) {
                // If we get here, something went wrong, so we'll drop and recreate
                Schema::dropIfExists('law_firm_ratings');
            }
        }
        
        // Create the table with all required columns
        Schema::create('law_firm_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('law_firm_id')->constrained('users')->comment('References law_firm users');
            $table->foreignId('legal_case_id')->constrained('legal_cases');
            $table->integer('rating')->comment('1-5 star rating');
            $table->text('feedback')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamp('rated_at')->useCurrent();
            $table->timestamps();
            
            // Ensure a client can only rate a law firm once per case
            $table->unique(['client_id', 'law_firm_id', 'legal_case_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't need to do anything here since we're just fixing
        // an existing table that should already have a migration.
    }
}; 