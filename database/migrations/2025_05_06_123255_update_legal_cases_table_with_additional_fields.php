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
            // Add additional fields if they don't exist
            if (!Schema::hasColumn('legal_cases', 'case_type')) {
                $table->string('case_type')->nullable()->after('service_id')->comment('Type of legal case (e.g., civil, criminal, corporate)');
            }
            
            if (!Schema::hasColumn('legal_cases', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('case_type');
            }
            
            if (!Schema::hasColumn('legal_cases', 'deadline')) {
                $table->timestamp('deadline')->nullable()->after('priority');
            }
            
            if (!Schema::hasColumn('legal_cases', 'billable_hours')) {
                $table->decimal('billable_hours', 8, 2)->default(0)->after('deadline');
            }
            
            if (!Schema::hasColumn('legal_cases', 'court_details')) {
                $table->json('court_details')->nullable()->after('billable_hours');
            }
            
            if (!Schema::hasColumn('legal_cases', 'opposing_party')) {
                $table->string('opposing_party')->nullable()->after('court_details');
            }
            
            if (!Schema::hasColumn('legal_cases', 'opposing_counsel')) {
                $table->string('opposing_counsel')->nullable()->after('opposing_party');
            }
            
            if (!Schema::hasColumn('legal_cases', 'billing_status')) {
                $table->enum('billing_status', ['pending', 'invoiced', 'paid', 'partial', 'disputed'])->default('pending')->after('status');
            }
            
            if (!Schema::hasColumn('legal_cases', 'is_confidential')) {
                $table->boolean('is_confidential')->default(false)->after('billing_status');
            }
            
            // Add indexes for faster querying
            $table->index(['status', 'priority']);
            $table->index('deadline');
            $table->index('billing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            // Remove the added columns
            $table->dropColumn([
                'case_type',
                'priority',
                'deadline',
                'billable_hours',
                'court_details',
                'opposing_party',
                'opposing_counsel',
                'billing_status',
                'is_confidential'
            ]);
            
            // Remove indexes
            $table->dropIndex(['status', 'priority']);
            $table->dropIndex(['deadline']);
            $table->dropIndex(['billing_status']);
        });
    }
};
