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
        if (!Schema::hasTable('case_phases')) {
            Schema::create('case_phases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
                $table->string('name');
                $table->text('description');
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_current')->default(false);
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_phases');
    }
}; 