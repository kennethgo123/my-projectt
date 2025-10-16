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
        Schema::create('case_lawyer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('role')->nullable()->comment('Role of the lawyer in this case (lead, assistant, etc.)');
            $table->text('notes')->nullable()->comment('Notes about this lawyer assignment');
            $table->boolean('is_primary')->default(false)->comment('Whether this lawyer is the primary lawyer for the case');
            $table->timestamps();
            
            // Ensure a lawyer can only be assigned to a case once
            $table->unique(['legal_case_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_lawyer');
    }
};
