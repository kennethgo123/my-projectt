<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            $table->text('about')->nullable();
            $table->text('education')->nullable();
            $table->text('experience')->nullable();
            $table->text('achievements')->nullable();
            $table->json('languages')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_optimized')->default(false);
            $table->boolean('offers_online_consultation')->default(false);
            $table->boolean('offers_inhouse_consultation')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('law_firm_lawyers', function (Blueprint $table) {
            $table->dropColumn([
                'about',
                'education',
                'experience',
                'achievements',
                'languages',
                'photo_path',
                'is_optimized',
                'offers_online_consultation',
                'offers_inhouse_consultation'
            ]);
        });
    }
}; 