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
        Schema::create('blocked_time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('reason')->default('consultation'); // consultation, lunch_break, custom
            $table->foreignId('consultation_id')->nullable()->constrained('consultations')->onDelete('cascade');
            $table->string('title')->nullable(); // for custom blocks
            $table->text('description')->nullable(); // for custom blocks
            $table->timestamps();
            
            // Add index for efficient querying
            $table->index(['user_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_time_slots');
    }
};
