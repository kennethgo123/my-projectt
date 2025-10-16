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
            $table->string('case_number')->unique()->after('id');
            $table->string('contract_path')->nullable()->after('service_id');
            $table->string('contract_status')->default('pending')->after('contract_path');
            $table->timestamp('contract_signed_at')->nullable()->after('contract_status');
            $table->text('rejection_reason')->nullable()->after('contract_signed_at');
            // Update status field to include new statuses
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropColumn([
                'case_number',
                'contract_path',
                'contract_status',
                'contract_signed_at',
                'rejection_reason'
            ]);
        });
    }
};
