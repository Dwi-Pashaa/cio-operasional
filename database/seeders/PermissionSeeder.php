<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Master Pengguna (Users)
            'lihat pengguna',
            'buat pengguna',
            'ubah pengguna',
            'hapus pengguna',

            // Master Role & Hak Akses
            'lihat role',
            'buat role',
            'ubah role',
            'hapus role',
            'atur permission role',

            // Master Kategori Pengeluaran
            'lihat kategori',
            'buat kategori',
            'ubah kategori',
            'hapus kategori',

            // Master Data Barang (Katalog Item)
            'lihat barang',
            'buat barang',
            'ubah barang',
            'hapus barang',

            // Transaksi Pengeluaran Operasional
            'lihat pengeluaran',
            'buat pengeluaran',
            'ubah pengeluaran',
            'hapus pengeluaran',

            // Laporan & Ekspor
            'lihat laporan',
            'download excel',
            'download pdf',

            // Pengaturan Sistem
            'lihat pengaturan',
            'ubah pengaturan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
