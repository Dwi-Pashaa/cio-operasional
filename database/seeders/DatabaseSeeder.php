<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Permissions
        $this->call(PermissionSeeder::class);

        // 2. Seed Roles & Sync to Admin
        $this->call(RoleSeeder::class);

        // 3. Seed Default Master Data Kategori & Barang Operasional
        $this->call(ExpenseCategorySeeder::class);

        // 4. Create Default Administrator
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'username' => 'admin',
                'name'     => 'Administrator',
                'password' => Hash::make('password')
            ]
        );

        $user->assignRole('Admin');
    }
}
