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
        Schema::create('case_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime');
            $table->string('event_type')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['legal_case_id', 'start_datetime']);
            $table->index(['legal_case_id', 'is_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_events');
    }
};
