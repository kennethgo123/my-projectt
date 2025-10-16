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
            // Make title nullable since it might not be needed for all update types
            $table->string('title')->nullable()->change();
            
            // Add update_type column
            $table->string('update_type')->nullable()->after('visibility');
            
            // Add is_client_visible column
            $table->boolean('is_client_visible')->default(true)->after('update_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_updates', function (Blueprint $table) {
            $table->dropColumn(['update_type', 'is_client_visible']);
            $table->string('title')->nullable(false)->change();
        });
    }
};
