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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade'); // Client who is reporting
            $table->foreignId('reported_user_id')->constrained('users')->onDelete('cascade'); // Lawyer/Law firm being reported
            $table->string('reported_type'); // 'lawyer' or 'law_firm'
            $table->string('reporter_name');
            $table->string('reporter_email');
            $table->string('reporter_phone')->nullable();
            $table->string('reported_name'); // Name of lawyer/law firm being reported
            $table->date('service_date')->nullable();
            $table->string('legal_matter_type')->nullable();
            $table->enum('category', [
                'professional_misconduct',
                'billing_disputes', 
                'communication_issues',
                'ethical_violations',
                'competency_concerns',
                'other'
            ]);
            $table->text('description');
            $table->json('supporting_documents')->nullable(); // Store file paths as JSON
            $table->text('timeline_of_events')->nullable();
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['reported_user_id', 'status']);
            $table->index(['reporter_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
