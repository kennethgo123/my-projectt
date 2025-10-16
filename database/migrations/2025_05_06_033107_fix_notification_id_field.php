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
        // First check if we need to modify the table structure
        if (!Schema::hasColumn('notifications', 'id')) {
            // If there's no id column, we need to add it
            Schema::table('notifications', function (Blueprint $table) {
                $table->id()->first();
            });
        } else {
            // If id column exists but it's not auto_increment, fix it
            $columns = DB::select("SHOW COLUMNS FROM notifications WHERE Field = 'id'");
            if (!empty($columns) && $columns[0]->Extra !== 'auto_increment') {
                // We need to be careful if the column is a primary key
                $hasPrimaryKey = !empty($columns[0]->Key) && $columns[0]->Key === 'PRI';
                
                if ($hasPrimaryKey) {
                    DB::statement('ALTER TABLE notifications DROP PRIMARY KEY');
                }
                
                // Change to bigint auto_increment
                DB::statement('ALTER TABLE notifications MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed - we don't want to remove the id column
    }
};
