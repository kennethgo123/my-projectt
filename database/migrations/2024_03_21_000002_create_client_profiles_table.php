<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('contact_number');
            $table->text('address');
            $table->enum('city', [
                'Cavite City',
                'Dasmarinas',
                'General Trias',
                'Imus',
                'Tagaytay',
                'Trece Martires',
                'Bacoor'
            ]);
            $table->enum('valid_id_type', [
                'Philippine Passport',
                'PhilSys National ID',
                'SSS ID',
                'GSIS ID',
                'UMID',
                'Drivers License',
                'PRC ID',
                'Postal ID',
                'Voters ID',
                'PhilHealth ID',
                'NBI Clearance'
            ]);
            $table->string('valid_id_file');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
}; 