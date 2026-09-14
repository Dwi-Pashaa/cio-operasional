<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Role list
        $roles = [
            "Admin",
            "Staff"
        ];

        foreach ($roles as $value) {
            Role::firstOrCreate(['name' => $value]);
        }

        // Sinkronkan SEMUA Permission ke Role Admin
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->syncPermissions($allPermissions);
        }

        // Berikan izin operasional dasar ke Role Staff
        $staffRole = Role::where('name', 'Staff')->first();
        if ($staffRole) {
            $staffRole->syncPermissions([
                'lihat barang',
                'lihat kategori',
                'lihat pengeluaran',
                'buat pengeluaran',
            ]);
        }
    }
}
