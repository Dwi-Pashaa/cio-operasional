<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Perlengkapan Kantor & ATK',
                'code' => 'OPR-ATK',
                'description' => 'Kertas, pulpen, tinta printer, map, amplop, dll.',
                'items' => [
                    ['name' => 'Kertas HVS A4 70gr', 'code' => 'ATK-HVS-A4', 'unit' => 'rim', 'default_price' => 48000],
                    ['name' => 'Kertas HVS F4 70gr', 'code' => 'ATK-HVS-F4', 'unit' => 'rim', 'default_price' => 55000],
                    ['name' => 'Tinta Printer Epson Black 003', 'code' => 'ATK-TINTA-003BK', 'unit' => 'botol', 'default_price' => 85000],
                    ['name' => 'Pulpen Gel Hitam 0.5mm', 'code' => 'ATK-PULPEN-GEL', 'unit' => 'box', 'default_price' => 35000],
                    ['name' => 'Map Folder Plastik', 'code' => 'ATK-MAP-PLASTIK', 'unit' => 'pack', 'default_price' => 25000],
                ]
            ],
            [
                'name' => 'Hardware & Infrastruktur Jaringan',
                'code' => 'OPR-HW',
                'description' => 'Kabel LAN, konektor, router, switch hub, adaptor, dll.',
                'items' => [
                    ['name' => 'Kabel LAN UTP Cat6 305M Belden', 'code' => 'HW-UTP-CAT6', 'unit' => 'box', 'default_price' => 1250000],
                    ['name' => 'Konektor RJ45 Cat6 (Isi 50 pcs)', 'code' => 'HW-RJ45-CAT6', 'unit' => 'pack', 'default_price' => 75000],
                    ['name' => 'Gigabit Switch 8-Port TP-Link', 'code' => 'HW-SW-8P', 'unit' => 'unit', 'default_price' => 245000],
                    ['name' => 'Mouse Wireless Logitech M170', 'code' => 'HW-MOUSE-M170', 'unit' => 'unit', 'default_price' => 135000],
                    ['name' => 'Stop Kontak 5 Lubang Uticon 3M', 'code' => 'HW-STP-5L', 'unit' => 'unit', 'default_price' => 85000],
                ]
            ],
            [
                'name' => 'Konsumsi & Logistik Kantor',
                'code' => 'OPR-KNS',
                'description' => 'Air galon, kopi, teh, gula, snack rapat, dll.',
                'items' => [
                    ['name' => 'Air Mineral Galon Aqua', 'code' => 'KNS-GALON-AQ', 'unit' => 'galon', 'default_price' => 20000],
                    ['name' => 'Kopi Kapal Api Special Mix (1 Renceng)', 'code' => 'KNS-KOPI-RC', 'unit' => 'pack', 'default_price' => 18000],
                    ['name' => 'Gula Pasir Gulaku 1 Kg', 'code' => 'KNS-GULA-1KG', 'unit' => 'pack', 'default_price' => 19000],
                    ['name' => 'Teh Celup Sariwangi (Isi 25)', 'code' => 'KNS-TEH-25', 'unit' => 'box', 'default_price' => 12000],
                ]
            ],
            [
                'name' => 'Operasional & Utilitas',
                'code' => 'OPR-UTL',
                'description' => 'Listrik PLN, air PAM, internet, kebersihan, dll.',
                'items' => [
                    ['name' => 'Token Listrik Kantor', 'code' => 'UTL-PLN-TOKEN', 'unit' => 'voucher', 'default_price' => 500000],
                    ['name' => 'Tagihan Internet Backup', 'code' => 'UTL-INET-BCK', 'unit' => 'bulan', 'default_price' => 350000],
                ]
            ],
            [
                'name' => 'Beban Administrasi & Lain-lain',
                'code' => 'OPR-ADM',
                'description' => 'Biaya materai, kurir/ekspedisi, transportasi dinas, dll.',
                'items' => [
                    ['name' => 'Materai Elektronik / Fisik 10.000', 'code' => 'ADM-MTR-10K', 'unit' => 'pcs', 'default_price' => 12000],
                    ['name' => 'Biaya Kurir & Pengiriman Dokumen', 'code' => 'ADM-EXP-DOC', 'unit' => 'paket', 'default_price' => 25000],
                ]
            ],
        ];

        foreach ($categories as $catData) {
            $items = $catData['items'] ?? [];
            unset($catData['items']);

            $cat = ExpenseCategory::firstOrCreate(
                ['code' => $catData['code']],
                $catData
            );

            foreach ($items as $itemData) {
                Item::firstOrCreate(
                    ['code' => $itemData['code']],
                    array_merge($itemData, ['category_id' => $cat->id])
                );
            }
        }
    }
}
