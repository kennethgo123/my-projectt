<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add default legal services
        $defaultServices = [
            [
                'name' => 'Criminal Law',
                'description' => 'Legal representation for criminal cases',
                'category' => 'Criminal',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Family Law',
                'description' => 'Legal services for family-related matters',
                'category' => 'Family',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Corporate Law',
                'description' => 'Legal services for business and corporate matters',
                'category' => 'Business',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Civil Litigation',
                'description' => 'Legal representation for civil cases',
                'category' => 'Civil',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Real Estate Law',
                'description' => 'Legal services for property matters',
                'category' => 'Property',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultServices as $service) {
            DB::table('legal_services')->insert($service);
        }

        // Removed logic to migrate data from old tables as they don't exist with migrate:fresh
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('legal_services')->truncate();
        Schema::enableForeignKeyConstraints();
    }
}; 