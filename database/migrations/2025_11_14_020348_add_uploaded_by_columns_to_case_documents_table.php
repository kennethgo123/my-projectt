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
        Schema::table('case_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('case_documents', 'uploaded_by_id')) {
                $table->unsignedBigInteger('uploaded_by_id')->nullable()->after('file_size');
            }
            if (!Schema::hasColumn('case_documents', 'uploaded_by_type')) {
                $table->string('uploaded_by_type')->nullable()->after('uploaded_by_id');
            }
        });
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
