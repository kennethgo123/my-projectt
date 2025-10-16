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
        Schema::table('case_updates', function (Blueprint $table) {
            // Add case_phase_id as nullable foreign key
            $table->foreignId('case_phase_id')->nullable()->after('user_id')->constrained('case_phases')->nullOnDelete();
            
            // First drop existing visibility enum column
            $table->dropColumn('visibility');
            
            // Then recreate it with the expanded enum options
            $table->enum('visibility', ['lawyer', 'client', 'both', 'law_firm'])->default('both')->after('content');
            
            // Add a boolean for client visibility (exists in the model but might not be in the table)
            if (!Schema::hasColumn('case_updates', 'is_client_visible')) {
                $table->boolean('is_client_visible')->default(true)->after('visibility');
            }
            
            // Add update_type field if not exists
            if (!Schema::hasColumn('case_updates', 'update_type')) {
                $table->string('update_type')->nullable()->after('visibility');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_updates', function (Blueprint $table) {
            // Remove case_phase_id
            $table->dropForeign(['case_phase_id']);
            $table->dropColumn('case_phase_id');
            
            // Reset visibility to original values
            $table->dropColumn('visibility');
            $table->enum('visibility', ['lawyer', 'client', 'both'])->default('both');
            
            // Remove added columns if they exist
            if (Schema::hasColumn('case_updates', 'update_type')) {
                $table->dropColumn('update_type');
            }
            
            if (Schema::hasColumn('case_updates', 'is_client_visible')) {
                $table->dropColumn('is_client_visible');
            }
        });
    }
};
