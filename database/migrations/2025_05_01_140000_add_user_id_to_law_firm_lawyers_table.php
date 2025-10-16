<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('law_firm_profile_id')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}; 