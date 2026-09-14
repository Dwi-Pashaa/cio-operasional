<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings';
    protected $fillable = [
        'telp', 
        'notification_channel', 
        'admin_fee', 
        'xendit_secret_key', 
        'xendit_webhook_token', 
        'dashboard_columns'
    ];
    protected $casts = [
        'dashboard_columns' => 'array',
        'admin_fee' => 'double',
    ];

    /**
     * Return the list of dashboard columns with their visibility.
     * Falls back to all columns visible if not configured.
     */
    public static function dashboardColumns(): array
    {
        $setting = self::first();
        $saved = $setting?->dashboard_columns ?? [];

        $defaults = [
            'dana_investasi'     => ['label' => 'Dana Investasi',     'visible' => true],
            'persentase'         => ['label' => 'Persentase',         'visible' => true],
            'nominal_pendapatan' => ['label' => 'Nominal Pendapatan', 'visible' => true],
            'status_pembayaran'  => ['label' => 'Status Pembayaran',  'visible' => true],
        ];

        foreach ($defaults as $key => &$col) {
            if (isset($saved[$key])) {
                $col['visible'] = (bool) $saved[$key]['visible'];
            }
        }

        return $defaults;
    }
}
