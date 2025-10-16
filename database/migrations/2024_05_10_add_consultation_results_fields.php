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
            $table->text('consultation_results')->nullable()->after('decline_reason');
            $table->text('meeting_notes')->nullable()->after('consultation_results');
            $table->boolean('is_completed')->default(false)->after('meeting_notes');
            $table->boolean('can_start_case')->default(false)->after('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn([
                'consultation_results',
                'meeting_notes',
                'is_completed',
                'can_start_case'
            ]);
        });
    }
}; 