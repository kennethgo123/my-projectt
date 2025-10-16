<?php

    // In a new migration file
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up()
        {
            Schema::table('legal_cases', function (Blueprint $table) {
                $table->string('court_level_main')->nullable()->after('priority'); // Or adjust position
                $table->string('court_level_specific')->nullable()->after('court_level_main');
                $table->json('assigned_judges')->nullable()->after('court_level_specific');
            });
        }

        public function down()
        {
            Schema::table('legal_cases', function (Blueprint $table) {
                $table->dropColumn(['court_level_main', 'court_level_specific', 'assigned_judges']);
            });
        }
    };
