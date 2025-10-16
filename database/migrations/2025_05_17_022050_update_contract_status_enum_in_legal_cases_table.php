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
        DB::statement("ALTER TABLE legal_cases MODIFY COLUMN contract_status ENUM('pending', 'sent', 'signed', 'rejected', 'negotiating', 'counter_offered', 'revised_sent') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE legal_cases MODIFY COLUMN contract_status ENUM('pending', 'sent', 'signed', 'rejected', 'negotiating', 'counter_offered') DEFAULT 'pending'");
    }
};
