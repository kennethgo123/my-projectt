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
            if (!Schema::hasColumn('case_tasks', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('due_date')->constrained('users');
            }
            
            if (!Schema::hasColumn('case_tasks', 'assigned_by')) {
                $table->foreignId('assigned_by')->nullable()->after('assigned_to')->constrained('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('case_tasks', 'assigned_to')) {
                $table->dropConstrainedForeignId('assigned_to');
            }
            
            if (Schema::hasColumn('case_tasks', 'assigned_by')) {
                $table->dropConstrainedForeignId('assigned_by');
            }
        });
    }
};
