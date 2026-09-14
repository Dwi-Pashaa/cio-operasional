<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index() 
    {
        $settings = Setting::first();
        $dashboardColumns = Setting::dashboardColumns();
        $financeConfigured = app(\App\Services\CioFinanceService::class)->isConfigured();
        $financeBaseUrl = config('services.cio_finance.base_url', 'http://127.0.0.1:8001');
        
        return view("pages.setting.index", compact("settings", "dashboardColumns", "financeConfigured", "financeBaseUrl"));    
    }

    public function store(Request $request) 
    {
        $request->validate([
            "telp"                 => "required|string",
            "notification_channel" => "required|in:whatsapp,email,both,none",
            "admin_fee"            => "nullable",
            "xendit_secret_key"    => "nullable|string",
            "xendit_webhook_token" => "nullable|string",
        ]);

        $adminFee = (float) str_replace('.', '', $request->admin_fee ?? 0);

        Setting::updateOrCreate(
            ['id' => $request->id ?? 1],
            [
                "telp"                 => $request->telp,
                "notification_channel" => $request->notification_channel,
                "admin_fee"            => $adminFee,
                "xendit_secret_key"    => $request->xendit_secret_key,
                "xendit_webhook_token" => $request->xendit_webhook_token,
            ]
        );

        return back()->with('success', 'Berhasil memperbarui pengaturan sistem.');
    }

    public function saveDashboardColumns(Request $request)
    {
        $columns = [];
        $allowedKeys = ['dana_investasi', 'persentase', 'nominal_pendapatan', 'status_pembayaran'];

        foreach ($allowedKeys as $key) {
            $columns[$key] = [
                'visible' => $request->has("columns.$key") ? true : false
            ];
        }

        Setting::updateOrCreate([], ['dashboard_columns' => $columns]);

        return back()->with('success', 'Pengaturan kolom dashboard berhasil disimpan.');
    }
}
