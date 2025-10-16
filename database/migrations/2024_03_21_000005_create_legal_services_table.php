<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Pivot table for lawyers and services
        Schema::create('lawyer_legal_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('legal_service_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('experience_years')->default(0);
            $table->timestamps();
        });

        // Pivot table for law firms and services
        Schema::create('law_firm_legal_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('law_firm_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('legal_service_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('law_firm_legal_service');
        Schema::dropIfExists('lawyer_legal_service');
        Schema::dropIfExists('legal_services');
    }
}; 