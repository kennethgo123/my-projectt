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
            $table->boolean('is_pro_bono')->default(false)->after('is_confidential');
            $table->timestamp('pro_bono_set_at')->nullable()->after('is_pro_bono');
            $table->text('pro_bono_note')->nullable()->after('pro_bono_set_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropColumn(['is_pro_bono', 'pro_bono_set_at', 'pro_bono_note']);
        });
    }
};