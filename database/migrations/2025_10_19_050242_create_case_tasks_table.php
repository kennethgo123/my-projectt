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
        Schema::create('case_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->string('assigned_to_type')->nullable();
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable(); // Legacy field
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['legal_case_id', 'is_completed', 'due_date']);
            $table->index('assigned_to_id');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_tasks');
    }
};
