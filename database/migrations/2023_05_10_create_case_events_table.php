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
        if (!Schema::hasTable('case_events')) {
            Schema::create('case_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('legal_case_id')->constrained('legal_cases')->onDelete('cascade');
                $table->string('title');
                $table->text('description');
                $table->date('event_date');
                $table->time('event_time');
                $table->string('location')->nullable();
                $table->foreignId('created_by')->constrained('users');
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
        Schema::dropIfExists('case_events');
    }
}; 