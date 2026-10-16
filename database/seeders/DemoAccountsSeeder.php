<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['Client',      'client@lexcav.com',     'Client123!!!',     'client',   []],
            ['Lawyer',      'lawyer@lexcav.com',     'Lawyer123!!!',     'lawyer',   []],
            ['Law Firm',    'lawfirm@lexcav.com',    'Lawfirm123!!!',    'law_firm', []],
            ['Super Admin', 'superadmin@lexcav.com', 'Superadmin123!!!', 'admin',    ['is_super_admin' => true, 'is_staff' => true]],
        ];

        foreach ($accounts as [$name, $email, $password, $role, $extra]) {
            $roleModel = Role::firstOrCreate(['name' => $role]);

            $user = User::updateOrCreate(
                ['email' => $email],
                array_merge([
                    'name' => $name,
                    'password' => Hash::make($password),
                    'role_id' => $roleModel->id,
                    'status' => 'approved',
                    'profile_completed' => true,
                ], $extra)
            );

            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
