<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('law_firm_lawyers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('law_firm_profile_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('contact_number');
            $table->text('address');
            $table->string('city');
            $table->string('valid_id_type');
            $table->string('valid_id_file');
            $table->string('bar_admission_type');
            $table->string('bar_admission_file');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('law_firm_lawyers');
    }
}; 