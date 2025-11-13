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
        // Add new polymorphic columns first
        Schema::table('case_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('case_documents', 'uploaded_by_id')) {
                $table->unsignedBigInteger('uploaded_by_id')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('case_documents', 'uploaded_by_type')) {
                $table->string('uploaded_by_type')->nullable()->after('uploaded_by_id');
            }
        });
        
        // Migrate existing data from uploaded_by to uploaded_by_id if needed
        if (Schema::hasColumn('case_documents', 'uploaded_by')) {
            DB::statement('UPDATE case_documents SET uploaded_by_id = uploaded_by, uploaded_by_type = ? WHERE uploaded_by IS NOT NULL AND uploaded_by_id IS NULL', [\App\Models\User::class]);
            
            // Make uploaded_by nullable using raw SQL
            DB::statement('ALTER TABLE case_documents MODIFY COLUMN uploaded_by BIGINT UNSIGNED NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_documents', function (Blueprint $table) {
            if (Schema::hasColumn('case_documents', 'uploaded_by_id')) {
                $table->dropColumn('uploaded_by_id');
            }
            if (Schema::hasColumn('case_documents', 'uploaded_by_type')) {
                $table->dropColumn('uploaded_by_type');
            }
        });
    }
};
