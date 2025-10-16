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
        if (!Schema::hasTable('case_tasks')) {
            Schema::create('case_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
                $table->string('title');
                $table->text('description');
                $table->date('due_date');
                $table->foreignId('assigned_to')->constrained('users');
                $table->foreignId('assigned_by')->constrained('users');
                $table->boolean('is_completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_tasks');
    }
}; 