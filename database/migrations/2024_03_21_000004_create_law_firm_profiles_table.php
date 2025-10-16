<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('law_firm_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('firm_name');
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
            $table->enum('registration_type', [
                'SEC Registration Certificate',
                'DTI Registration Certificate'
            ]);
            $table->string('registration_certificate_file');
            $table->string('bir_certificate_file');
            $table->decimal('min_budget', 10, 2);
            $table->decimal('max_budget', 10, 2);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('total_ratings')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('law_firm_profiles');
    }
}; 