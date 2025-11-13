<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('case_documents', 'is_shared')) {
                $table->boolean('is_shared')->default(true)->after('uploaded_by_type');
            }
        });
        
        // Set all existing documents as shared by default
        if (Schema::hasColumn('case_documents', 'is_shared')) {
            DB::statement('UPDATE case_documents SET is_shared = 1 WHERE is_shared IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_documents', function (Blueprint $table) {
            if (Schema::hasColumn('case_documents', 'is_shared')) {
                $table->dropColumn('is_shared');
            }
        });
    }
};
