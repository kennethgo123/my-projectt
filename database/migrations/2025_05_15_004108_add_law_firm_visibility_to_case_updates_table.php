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
        // MySQL syntax to modify an enum field to add a new value
        DB::statement("ALTER TABLE case_updates MODIFY COLUMN visibility ENUM('lawyer', 'client', 'both', 'law_firm') NOT NULL DEFAULT 'both'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the original enum values
        // Note: This will cause errors if any records have 'law_firm' as visibility
        DB::statement("ALTER TABLE case_updates MODIFY COLUMN visibility ENUM('lawyer', 'client', 'both') NOT NULL DEFAULT 'both'");
    }
};
