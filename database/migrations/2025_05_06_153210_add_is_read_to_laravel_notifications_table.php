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
        Schema::table('laravel_notifications', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('data');
        });
        
        // Update existing records - set is_read to true if read_at is not null
        DB::statement('UPDATE laravel_notifications SET is_read = (read_at IS NOT NULL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laravel_notifications', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
    }
};
