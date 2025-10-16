<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
            $table->foreignId('case_phase_id')->nullable()->constrained('case_phases')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // pending, completed, overdue
            $table->string('assigned_to_type'); // client, lawyer
            $table->unsignedBigInteger('assigned_to_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_tasks');
    }
}; 