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
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('legal_case_id')->nullable();
            $table->string('attachment_path')->nullable();

            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['legal_case_id']);
            $table->dropColumn(['legal_case_id', 'attachment_path']);
        });
    }
};
