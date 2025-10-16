<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('law_firm_lawyer_legal_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('law_firm_lawyer_id')->constrained()->onDelete('cascade');
            $table->foreignId('legal_service_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->integer('experience_years')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('law_firm_lawyer_legal_service');
    }
}; 