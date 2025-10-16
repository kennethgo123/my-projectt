<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get admin role ID
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            return;
        }
        
        // Create the superadmin user
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
            'role_id' => $adminRole->id,
            'status' => 'approved',
            'profile_completed' => true,
            'is_super_admin' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the superadmin user
        User::where('email', 'superadmin@gmail.com')->delete();
    }
}; 