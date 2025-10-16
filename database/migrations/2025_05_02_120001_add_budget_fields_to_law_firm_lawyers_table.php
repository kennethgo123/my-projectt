<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            if (!Schema::hasColumn('law_firm_lawyers', 'min_budget')) {
                $table->decimal('min_budget', 15, 2)->nullable();
            }
            
            if (!Schema::hasColumn('law_firm_lawyers', 'max_budget')) {
                $table->decimal('max_budget', 15, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            $table->dropColumn(['min_budget', 'max_budget']);
        });
    }
}; 