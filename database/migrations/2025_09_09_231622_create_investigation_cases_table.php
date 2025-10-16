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
        Schema::create('investigation_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->foreignId('investigator_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['assigned', 'in_progress', 'pending_review', 'completed', 'closed'])->default('assigned');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->text('investigation_notes')->nullable();
            $table->json('evidence_collected')->nullable(); // Store file paths and evidence references
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['report_id', 'status']);
            $table->index(['investigator_id', 'status']);
            $table->index(['status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigation_cases');
    }
};