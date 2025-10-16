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
        Schema::dropIfExists('law_firm_ratings');
    }
};
