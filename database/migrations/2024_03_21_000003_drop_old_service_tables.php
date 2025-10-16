<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('law_firm_service');
        Schema::dropIfExists('lawyer_service');
        Schema::dropIfExists('user_services');
        Schema::dropIfExists('law_services');
        Schema::dropIfExists('services');
    }

    public function down(): void
    {
        // We don't need to recreate the tables here since they will be created
        // by their original migrations if we need to rollback
    }
}; 