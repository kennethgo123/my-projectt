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
        Schema::table('legal_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('legal_cases', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('contract_status');
            }
            if (!Schema::hasColumn('legal_cases', 'requested_changes_details')) {
                $table->text('requested_changes_details')->nullable()->after('rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            if (Schema::hasColumn('legal_cases', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('legal_cases', 'requested_changes_details')) {
                $table->dropColumn('requested_changes_details');
            }
        });
    }
};
