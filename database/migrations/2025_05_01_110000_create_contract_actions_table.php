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
        Schema::create('contract_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
            $table->string('action_type'); // accepted, rejected, negotiating
            $table->string('actor_type'); // client or lawyer
            $table->unsignedBigInteger('actor_id');
            $table->text('details')->nullable();
            $table->string('signature_path')->nullable();
            $table->timestamps();

            $table->index(['actor_type', 'actor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_actions');
    }
}; 