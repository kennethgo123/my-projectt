<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type');
            $table->string('uploaded_by_type'); // client, lawyer
            $table->unsignedBigInteger('uploaded_by_id');
            $table->boolean('is_shared')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_documents');
    }
}; 