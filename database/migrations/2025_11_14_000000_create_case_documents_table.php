<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('case_documents')) {
            Schema::create('case_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->string('file_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('case_documents');
    }
};
