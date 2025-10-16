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
            if (!Schema::hasColumn('consultations', 'consultation_results')) {
                $table->text('consultation_results')->nullable()->after('is_completed');
            }
            
            if (!Schema::hasColumn('consultations', 'meeting_notes')) {
                $table->text('meeting_notes')->nullable()->after('consultation_results');
            }
            
            if (!Schema::hasColumn('consultations', 'consultation_document_path')) {
                $table->string('consultation_document_path')->nullable()->after('meeting_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $columns = ['consultation_results', 'meeting_notes', 'consultation_document_path'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('consultations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}; 