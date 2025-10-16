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
        Schema::create('lawyer_bios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_profile_id')->constrained()->onDelete('cascade');
            $table->text('about')->nullable();
            $table->text('education')->nullable();
            $table->text('experience')->nullable();
            $table->text('achievements')->nullable();
            $table->text('specializations')->nullable();
            $table->text('languages')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyer_bios');
    }
};
