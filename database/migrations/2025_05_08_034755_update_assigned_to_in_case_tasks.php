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
        // Check if the column exists and modify it to have a default value
        Schema::table('case_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('case_tasks', 'assigned_to')) {
                // Remove the foreign key constraint if it exists
                try {
                    $table->dropForeign(['assigned_to']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, continue
                }
                
                // Modify the column to allow NULL values (to fix the immediate error)
                $table->unsignedBigInteger('assigned_to')->nullable()->change();
            } else {
                // If the column doesn't exist, create it
                $table->unsignedBigInteger('assigned_to')->nullable()->after('due_date');
            }
        });
        
        // Update existing records to set assigned_to based on assigned_to_id
        DB::statement('UPDATE case_tasks SET assigned_to = assigned_to_id WHERE assigned_to IS NULL AND assigned_to_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse as we're just making a column nullable
    }
};
