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
        Schema::table('case_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('case_tasks', 'assigned_by')) {
                $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_to_id');
            }
            
            if (!Schema::hasColumn('case_tasks', 'is_completed')) {
                $table->boolean('is_completed')->default(false)->after('assigned_by');
            }
            
            if (!Schema::hasColumn('case_tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('is_completed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('case_tasks', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            
            if (Schema::hasColumn('case_tasks', 'is_completed')) {
                $table->dropColumn('is_completed');
            }
            
            if (Schema::hasColumn('case_tasks', 'assigned_by')) {
                $table->dropColumn('assigned_by');
            }
        });
    }
};
