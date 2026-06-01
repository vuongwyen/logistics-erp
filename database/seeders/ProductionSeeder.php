<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production data: Roles + first Admin account only.
     * Admin credentials are passed via INSTALL_ADMIN_* environment variables
     * set by the install.ps1 script during first-time setup.
     */
    public function run(): void
    {
        // Seed 7 roles
        $this->call(RoleSeeder::class);

        $adminRole = Role::where('role_code', 'ADMIN')->first();

        $adminName = env('INSTALL_ADMIN_NAME', 'Quản trị viên');
        $adminEmail = env('INSTALL_ADMIN_EMAIL', 'admin@ntlogistics.local');
        $adminPassword = env('INSTALL_ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'username' => 'admin',
                'full_name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'role_id' => $adminRole->id,
                'status' => 1,
                'employee_code' => 'NV-'.now()->format('ym').'-001',
                'position' => 'Quản trị viên hệ thống',
                'department' => 'Ban điều hành',
                'theme_color' => '#1a237e',
                'is_dark_mode' => false,
                'joined_at' => now()->toDateString(),
            ]
        );

        $this->command->info('✅ Đã tạo tài khoản Admin: '.$adminEmail);
    }
}
