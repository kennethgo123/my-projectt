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
        Schema::create('investigation_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investigation_case_id')->constrained('investigation_cases')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size'); // in bytes
            $table->text('description')->nullable();
            $table->enum('attachment_type', ['evidence', 'document', 'image', 'other'])->default('document');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['investigation_case_id', 'attachment_type'], 'inv_attachments_case_type_idx');
            $table->index('uploaded_by', 'inv_attachments_uploaded_by_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investigation_attachments');
    }
};