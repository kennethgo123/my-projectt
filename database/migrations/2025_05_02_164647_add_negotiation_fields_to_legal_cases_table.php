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
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->text('negotiation_terms')->nullable()->after('rejection_reason');
            $table->string('signature_path')->nullable()->after('contract_path');
            $table->string('lawyer_response')->nullable()->after('negotiation_terms');
            $table->text('lawyer_response_message')->nullable()->after('lawyer_response');
            // Update contract_status enum to include negotiating status
            $table->string('contract_status')->default('pending')->change();
        });

        // Update existing contract_status values
        DB::statement("ALTER TABLE legal_cases MODIFY COLUMN contract_status ENUM('pending', 'sent', 'signed', 'rejected', 'negotiating', 'counter_offered', 'revised_sent') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropColumn('negotiation_terms');
            $table->dropColumn('signature_path');
            $table->dropColumn('lawyer_response');
            $table->dropColumn('lawyer_response_message');
            // Revert contract_status to original enum values
            DB::statement("ALTER TABLE legal_cases MODIFY COLUMN contract_status ENUM('pending', 'sent', 'signed', 'rejected', 'negotiating', 'counter_offered') DEFAULT 'pending'");
        });
    }
};
