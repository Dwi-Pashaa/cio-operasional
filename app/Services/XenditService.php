<?php

namespace App\Services;

use App\Models\OperationalExpense;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    /**
     * Dapatkan Secret Key Xendit dari Database Settings atau file .env
     */
    public function getSecretKey(): ?string
    {
        $setting = Setting::first();
        return $setting?->xendit_secret_key ?: config('services.xendit.secret_key') ?: env('XENDIT_SECRET_KEY');
    }

    /**
     * Dapatkan Webhook Verification Token Xendit
     */
    public function getWebhookToken(): ?string
    {
        $setting = Setting::first();
        return $setting?->xendit_webhook_token ?: config('services.xendit.webhook_token') ?: env('XENDIT_WEBHOOK_TOKEN');
    }

    /**
     * Kirim permintaan Payout / Disbursement ke Xendit API.
     * Endpoint: POST https://api.xendit.co/disbursements
     *
     * @param string $referenceNo        Reference number (e.g. OPS-20260913-0001)
     * @param string $bankCode           Bank code (e.g. BCA, MANDIRI, BRI, BNI, GOPAY, OVO)
     * @param string $accountHolderName  Nama pemilik rekening
     * @param string $accountNumber      Nomor rekening tujuan
     * @param float  $amount             Nominal transfer
     * @param string $description        Deskripsi transfer
     * @return array{success: bool, message: string, data?: mixed, error_code?: string}
     */
    public function createDisbursement(
        string $referenceNo,
        string $bankCode,
        string $accountHolderName,
        string $accountNumber,
        float  $amount,
        string $description
    ): array {
        $secretKey = $this->getSecretKey();

        if (empty($secretKey)) {
            return [
                'success' => false,
                'message' => 'Xendit Secret Key belum dikonfigurasi di Pengaturan Sistem atau .env.',
            ];
        }

        $endpoint = 'https://api.xendit.co/disbursements';

        $payload = [
            'external_id'          => $referenceNo,
            'amount'               => (int) $amount,
            'bank_code'            => strtoupper($bankCode),
            'account_holder_name'  => $accountHolderName,
            'account_number'       => $accountNumber,
            'description'          => substr($description, 0, 255),
        ];

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->withHeaders([
                    'X-IDEMPOTENCY-KEY' => $referenceNo . '-' . time(),
                ])
                ->timeout(15)
                ->post($endpoint, $payload);

            $data = $response->json();

            if ($response->successful()) {
                Log::info("[XenditService] Disbursement created successfully for {$referenceNo}", [
                    'disbursement_id' => $data['id'] ?? null,
                    'status'          => $data['status'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message' => 'Disbursement Xendit berhasil dibuat.',
                    'data'    => $data,
                ];
            }

            $errorMsg = $data['message'] ?? ('Gagal membuat disbursement Xendit (HTTP ' . $response->status() . ')');
            Log::error("[XenditService] Gagal create disbursement untuk {$referenceNo}: " . $response->body());

            return [
                'success'    => false,
                'message'    => $errorMsg,
                'error_code' => $data['error_code'] ?? 'DISBURSEMENT_FAILED',
                'data'       => $data,
            ];
        } catch (\Throwable $e) {
            Log::error("[XenditService] Exception saat create disbursement {$referenceNo}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi Xendit API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Webhook Callback dari Xendit / Central Router.
     * Standar Central Router Multi-Website Prefix untuk Operasional: OPS-
     */
    public function handleWebhook(array $payload, ?string $incomingToken): array
    {
        $configuredToken = $this->getWebhookToken();

        if (!empty($configuredToken) && !empty($incomingToken) && $incomingToken !== $configuredToken) {
            Log::warning('[XenditService] Webhook token mismatch. Incoming: ' . $incomingToken . ' vs Configured: ' . $configuredToken);
            return ['status' => false, 'message' => 'Invalid webhook verification token.'];
        }

        $externalId = $payload['external_id'] ?? $payload['data']['external_id'] ?? null;
        $status     = strtoupper($payload['status'] ?? $payload['data']['status'] ?? '');
        $disbId     = $payload['id'] ?? $payload['data']['id'] ?? null;

        Log::info('[XenditService] Webhook payload parsed', [
            'external_id' => $externalId,
            'status'      => $status,
            'id'          => $disbId,
        ]);

        // Validasi prefix router multi-website untuk Web Operasional (OPS-)
        if ($externalId && !str_starts_with((string)$externalId, 'OPS-')) {
            Log::info("[XenditService] Webhook event skipped: external_id {$externalId} bukan untuk modul Operasional (prefix OPS-).");
            return ['status' => true, 'message' => 'Event ignored: non-operational prefix'];
        }

        // Cari transaksi OperationalExpense terkait
        $expense = null;
        if ($externalId) {
            $expense = OperationalExpense::where('reference_no', $externalId)
                ->orWhere('finance_reference_id', $externalId)
                ->first();

            if (!$expense && preg_match('/(?:OPS-)?(?:TRX-)?([A-Za-z0-9\-]+?)(?:-\d+)?$/i', $externalId, $matches)) {
                $rawCode = $matches[1];
                $expense = OperationalExpense::where('reference_no', $rawCode)
                    ->orWhere('reference_no', 'OPS-' . $rawCode)
                    ->first();
            }
        }

        if (!$expense) {
            Log::warning("[XenditService] Expense record not found for external_id: {$externalId}, disbursement_id: {$disbId}");
            return ['status' => true, 'message' => 'Expense record not found in Operasional system'];
        }

        // Cek status sukses payout
        $isSuccess = in_array($status, ['COMPLETED', 'SUCCESS', 'PAID', 'SETTLED']);
        $isFailed  = in_array($status, ['FAILED', 'REJECTED', 'CANCELLED']);

        if ($isSuccess) {
            $expense->update([
                'status' => 'success',
            ]);

            Log::info("[XenditService] Pengeluaran #{$expense->id} ({$expense->reference_no}) berhasil dilunasi via Xendit Webhook.");
            return ['status' => true, 'message' => 'Operational expense marked as success via webhook'];
        }

        if ($isFailed) {
            $expense->update([
                'status' => 'failed',
            ]);

            // Auto-Refund ke Saldo Xendit di Finance API jika transfer gagal di callback
            try {
                if ($expense->payment_channel === 'xendit' && $expense->grand_total > 0) {
                    $refId = 'OPS-REFUND-' . $expense->id . '-' . time();
                    app(CioFinanceService::class)->refundBalance(
                        $expense->grand_total,
                        'xendit',
                        $refId,
                        "Rollback pengeluaran gagal Xendit: {$expense->reference_no}",
                        $payload['failure_code'] ?? 'Xendit payout failed via webhook'
                    );
                    Log::info("[XenditService] Auto-refund saldo Xendit berhasil dieksekusi untuk Pengeluaran #{$expense->id}");
                }
            } catch (Exception $e) {
                Log::error('[XenditService] Gagal auto-refund saat webhook failed: ' . $e->getMessage());
            }

            Log::warning("[XenditService] Pengeluaran #{$expense->id} dinyatakan GAGAL oleh Xendit Webhook.");
            return ['status' => true, 'message' => 'Operational expense marked as failed via webhook'];
        }

        return ['status' => true, 'message' => 'Webhook received and processed'];
    }

    /**
     * Dapatkan daftar bank resmi yang tersedia dari Xendit API atau fallback ke daftar standar.
     *
     * @return array<string, array<int, array{code: string, name: string, type: string}>>
     */
    public function getAvailableBanks(): array
    {
        $cacheKey = 'xendit_disbursement_banks_list';

        return Cache::remember($cacheKey, 86400, function () {
            $secretKey = $this->getSecretKey();

            if (!empty($secretKey)) {
                try {
                    $response = Http::withBasicAuth($secretKey, '')
                        ->timeout(8)
                        ->get('https://api.xendit.co/available_disbursements_banks');

                    if ($response->successful()) {
                        $banks = $response->json();
                        if (is_array($banks) && !empty($banks)) {
                            // Filter and format
                            $popularCodes = ['BCA', 'MANDIRI', 'BRI', 'BNI', 'BSI', 'CIMB', 'PERMATA', 'DANAMON', 'BTN'];
                            $digitalCodes = ['JAGO', 'SEABANK', 'BTPN', 'NEO', 'ALLO', 'SAQU', 'SUPERBANK', 'KROM', 'ALADIN'];

                            $grouped = [
                                'Bank Terpopuler' => [],
                                'Bank Digital & Fintech' => [],
                                'Bank Lainnya & Daerah' => [],
                            ];

                            foreach ($banks as $b) {
                                $code = strtoupper($b['code'] ?? '');
                                $name = $b['name'] ?? $code;
                                if (!$code) continue;

                                $item = ['code' => $code, 'name' => $name, 'type' => 'bank'];

                                if (in_array($code, $popularCodes)) {
                                    $grouped['Bank Terpopuler'][] = $item;
                                } elseif (in_array($code, $digitalCodes)) {
                                    $grouped['Bank Digital & Fintech'][] = $item;
                                } else {
                                    $grouped['Bank Lainnya & Daerah'][] = $item;
                                }
                            }

                            // Append standard e-wallets
                            $standard = self::getSupportedBanks();
                            if (!empty($standard['E-Wallet (Dompet Digital)'])) {
                                $grouped['E-Wallet (Dompet Digital)'] = $standard['E-Wallet (Dompet Digital)'];
                            }

                            return array_filter($grouped, fn($arr) => !empty($arr));
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('[XenditService] Gagal fetch available banks dari Xendit API: ' . $e->getMessage());
                }
            }

            return self::getSupportedBanks();
        });
    }

    /**
     * Daftar Standar Bank & E-Wallet Resmi yang Didukung oleh Xendit Disbursement Indonesia.
     *
     * @return array<string, array<int, array{code: string, name: string, type: string}>>
     */
    public static function getSupportedBanks(): array
    {
        return [
            'Bank Terpopuler' => [
                ['code' => 'BCA', 'name' => 'Bank Central Asia (BCA)', 'type' => 'bank'],
                ['code' => 'MANDIRI', 'name' => 'Bank Mandiri', 'type' => 'bank'],
                ['code' => 'BRI', 'name' => 'Bank Rakyat Indonesia (BRI)', 'type' => 'bank'],
                ['code' => 'BNI', 'name' => 'Bank Negara Indonesia (BNI)', 'type' => 'bank'],
                ['code' => 'BSI', 'name' => 'Bank Syariah Indonesia (BSI)', 'type' => 'bank'],
                ['code' => 'CIMB', 'name' => 'Bank CIMB Niaga', 'type' => 'bank'],
                ['code' => 'PERMATA', 'name' => 'Bank Permata', 'type' => 'bank'],
                ['code' => 'DANAMON', 'name' => 'Bank Danamon', 'type' => 'bank'],
                ['code' => 'BTN', 'name' => 'Bank Tabungan Negara (BTN)', 'type' => 'bank'],
            ],
            'Bank Digital & Fintech' => [
                ['code' => 'JAGO', 'name' => 'Bank Jago', 'type' => 'bank'],
                ['code' => 'SEABANK', 'name' => 'SeaBank Indonesia', 'type' => 'bank'],
                ['code' => 'BTPN', 'name' => 'Bank BTPN / Jenius', 'type' => 'bank'],
                ['code' => 'NEO', 'name' => 'Bank Neo Commerce (BNC)', 'type' => 'bank'],
                ['code' => 'ALLO', 'name' => 'Allo Bank Indonesia', 'type' => 'bank'],
                ['code' => 'SAQU', 'name' => 'Bank Saqu (Bank Jasa Jakarta)', 'type' => 'bank'],
                ['code' => 'SUPERBANK', 'name' => 'Superbank', 'type' => 'bank'],
                ['code' => 'KROM', 'name' => 'Krom Bank', 'type' => 'bank'],
                ['code' => 'ALADIN', 'name' => 'Bank Aladin Syariah', 'type' => 'bank'],
            ],
            'E-Wallet (Dompet Digital)' => [
                ['code' => 'GOPAY', 'name' => 'GoPay', 'type' => 'ewallet'],
                ['code' => 'OVO', 'name' => 'OVO', 'type' => 'ewallet'],
                ['code' => 'DANA', 'name' => 'DANA', 'type' => 'ewallet'],
                ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'type' => 'ewallet'],
                ['code' => 'LINKAJA', 'name' => 'LinkAja', 'type' => 'ewallet'],
                ['code' => 'ASTRA', 'name' => 'AstraPay', 'type' => 'ewallet'],
            ],
        ];
    }
}
