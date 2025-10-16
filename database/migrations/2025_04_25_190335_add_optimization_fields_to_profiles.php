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
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->text('about')->nullable();
            $table->text('education')->nullable();
            $table->text('experience')->nullable();
            $table->text('achievements')->nullable();
            $table->text('specializations')->nullable();
            $table->text('languages')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
        });

        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->text('about')->nullable();
            $table->text('experience')->nullable();
            $table->text('achievements')->nullable();
            $table->text('specializations')->nullable();
            $table->text('languages')->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'about', 'education', 'experience', 'achievements', 
                'specializations', 'languages', 'website', 'linkedin'
            ]);
        });

        Schema::table('law_firm_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'about', 'experience', 'achievements', 
                'specializations', 'languages', 'website', 'linkedin'
            ]);
        });
    }
};
