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
        Schema::table('contract_actions', function (Blueprint $table) {
            $table->boolean('lawyer_acknowledged')->default(false)->after('signature_path');
            $table->timestamp('lawyer_acknowledged_at')->nullable()->after('lawyer_acknowledged');
            $table->unsignedBigInteger('acknowledged_by')->nullable()->after('lawyer_acknowledged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_actions', function (Blueprint $table) {
            $table->dropColumn(['lawyer_acknowledged', 'lawyer_acknowledged_at', 'acknowledged_by']);
        });
    }
};
