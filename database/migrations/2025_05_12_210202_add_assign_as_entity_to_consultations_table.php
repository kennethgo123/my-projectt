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
        Schema::table('consultations', function (Blueprint $table) {
            $table->unsignedBigInteger('specific_lawyer_id')->nullable();
            $table->boolean('assign_as_entity')->default(false);
            
            $table->foreign('specific_lawyer_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['specific_lawyer_id']);
            $table->dropColumn(['specific_lawyer_id', 'assign_as_entity']);
        });
    }
};
