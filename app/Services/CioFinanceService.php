<?php

namespace App\Services;

use App\Models\OperationalExpense;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class CioFinanceService
{
    protected ?string $baseUrl;
    protected ?string $clientId;
    protected ?string $keyId;
    protected ?string $secretKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl   = rtrim(config('services.cio_finance.base_url') ?? '', '/');
        $this->clientId  = config('services.cio_finance.client_id');
        $this->keyId     = config('services.cio_finance.key_id');
        $this->secretKey = config('services.cio_finance.secret_key');
        $this->timeout   = (int) (config('services.cio_finance.timeout') ?? 5);
    }

    /**
     * Memeriksa apakah konfigurasi API Finance sudah terisi lengkap.
     */
    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) &&
               !empty($this->clientId) &&
               !empty($this->keyId) &&
               !empty($this->secretKey);
    }

    /**
     * Membuat HMAC-SHA256 Headers sesuai spesifikasi API Finance CIO.
     */
    public function generateHeaders(string $method, string $path, array $body = []): array
    {
        $timestamp = (string) time();
        $nonce     = bin2hex(random_bytes(16));
        $bodyJson  = !empty($body) ? json_encode($body) : '';
        $bodyHash  = hash('sha256', $bodyJson);
        $cleanPath = '/' . ltrim($path, '/');

        // Canonical String sesuai HmacSignatureService server:
        // [METHOD, PATH_WITH_QUERY, CLIENT_ID, KEY_ID, TIMESTAMP, NONCE, SHA256_BODY_HASH]
        $canonical = implode("\n", [
            strtoupper($method),
            $cleanPath,
            (string) $this->clientId,
            (string) $this->keyId,
            $timestamp,
            $nonce,
            $bodyHash,
        ]);

        $signature = base64_encode(hash_hmac('sha256', $canonical, (string) ($this->secretKey ?? ''), true));

        return [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'X-Client-ID'  => (string) $this->clientId,
            'X-Key-ID'     => (string) $this->keyId,
            'X-Timestamp'  => $timestamp,
            'X-Nonce'      => $nonce,
            'X-Signature'  => $signature,
        ];
    }

    /**
     * Mengambil data Saldo (Manual & Xendit) serta status channel dari API Finance CIO.
     *
     * @param bool $fresh Jika true, bypass cache.
     * @return array
     */
    public function getBalance(bool $fresh = false): array
    {
        $defaultFallback = [
            'is_connected'   => false,
            'status'         => 'not_configured',
            'message'        => 'API Finance belum dikonfigurasi',
            'data'           => [
                'client_code'    => '-',
                'client_name'    => '-',
                'balance'        => '0.00',
                'balance_manual' => '0.00',
                'balance_xendit' => '0.00',
                'total_balance'  => '0.00',
                'channel_status' => [
                    'manual' => false,
                    'xendit' => false,
                ],
                'retrieved_at'   => null,
            ],
            'error_detail'   => null,
        ];

        if (!$this->isConfigured()) {
            return $defaultFallback;
        }

        $cacheKey = 'cio_finance_balance_data';

        if (!$fresh) {
            try {
                if (Cache::has($cacheKey)) {
                    return Cache::get($cacheKey);
                }
            } catch (\Throwable $e) {
                // Ignore cache read failures and fallback to live fetch
            }
        }

        $path = '/api/v1/balance';
        $url  = $this->baseUrl . $path;

        try {
            $headers = $this->generateHeaders('GET', $path);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->get($url);

            if ($response->successful()) {
                $resData = $response->json();
                $data = $resData['data'] ?? [];

                $result = [
                    'is_connected' => true,
                    'status'       => 'success',
                    'message'      => $resData['message'] ?? 'Berhasil mengambil data saldo',
                    'data'         => [
                        'client_code'    => $data['client_code'] ?? '-',
                        'client_name'    => $data['client_name'] ?? '-',
                        'balance'        => (string) ($data['balance'] ?? '0.00'),
                        'balance_manual' => (string) ($data['balance_manual'] ?? '0.00'),
                        'balance_xendit' => (string) ($data['balance_xendit'] ?? '0.00'),
                        'total_balance'  => (string) ($data['total_balance'] ?? ($data['balance'] ?? '0.00')),
                        'channel_status' => [
                            'manual' => (bool) ($data['channel_status']['manual'] ?? false),
                            'xendit' => (bool) ($data['channel_status']['xendit'] ?? false),
                        ],
                        'retrieved_at'   => $data['retrieved_at'] ?? now()->toIso8601String(),
                    ],
                    'error_detail' => null,
                ];

                // Cache selama 30 detik untuk respon cepat
                try {
                    Cache::put($cacheKey, $result, 30);
                } catch (\Throwable $e) {
                    // Ignore cache write failures
                }

                return $result;
            }

            Log::warning('CIO Finance API responded with error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'is_connected' => false,
                'status'       => 'error',
                'message'      => 'Gagal memuat saldo dari server Finance (HTTP ' . $response->status() . ')',
                'data'         => $defaultFallback['data'],
                'error_detail' => $response->json() ?? $response->body(),
            ];

        } catch (Exception $e) {
            Log::error('Error connecting to CIO Finance API', [
                'error' => $e->getMessage(),
            ]);

            return [
                'is_connected' => false,
                'status'       => 'offline',
                'message'      => 'Server API Finance tidak dapat dihubungi',
                'data'         => $defaultFallback['data'],
                'error_detail' => $e->getMessage(),
            ];
        }
    }

    /**
     * Memotong saldo di API Finance CIO (Saldo Manual atau Saldo Xendit).
     *
     * @param float       $amount       Nominal yang akan dipotong
     * @param string      $balanceType  'manual' atau 'xendit'
     * @param string      $referenceId  ID Referensi ber-prefix INV-
     * @param string      $description  Deskripsi pemotongan saldo
     * @param string|null $category     Kategori pengeluaran (default: 'Dividen')
     * @param string|null $note         Catatan tambahan
     * @return array{success: bool, message: string, data?: mixed, error_code?: string}
     */
    public function deductBalance(
        float $amount,
        string $balanceType,
        string $referenceId,
        string $description,
        ?string $category = 'Operasional',
        ?string $note = null
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'API Finance belum dikonfigurasi pada .env.',
            ];
        }

        $path = '/api/v1/balance/deduct';
        $url  = $this->baseUrl . $path;

        // Pastikan reference ID memiliki prefix OPS- sesuai standar Router Multi-Website
        if (!str_starts_with($referenceId, 'OPS-')) {
            $referenceId = 'OPS-' . $referenceId;
        }

        $body = [
            'amount'       => (float) $amount,
            'balance_type' => strtolower($balanceType),
            'reference_id' => $referenceId,
            'description'  => $description,
            'category'     => $category ?? 'Operasional',
            'note'         => $note ?? 'Pengeluaran operasional perusahaan',
        ];

        try {
            $headers = $this->generateHeaders('POST', $path, $body);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $body);

            $resJson = $response->json();

            if ($response->successful() && (($resJson['status'] ?? '') === 'success' || ($resJson['success'] ?? false) === true)) {
                // Invalidate balance cache agar pembacaan saldo berikutnya akurat
                try {
                    Cache::forget('cio_finance_balance_data');
                } catch (\Throwable $e) {}

                Log::info('Berhasil memotong saldo Finance CIO', [
                    'reference_id' => $referenceId,
                    'balance_type' => $balanceType,
                    'amount'       => $amount,
                    'data'         => $resJson['data'] ?? [],
                ]);

                return [
                    'success' => true,
                    'message' => $resJson['message'] ?? 'Saldo berhasil dipotong',
                    'data'    => $resJson['data'] ?? [],
                ];
            }

            $errorMsg = $resJson['message'] ?? ('Gagal memotong saldo (HTTP ' . $response->status() . ')');
            Log::warning('Gagal memotong saldo Finance CIO', [
                'status'   => $response->status(),
                'response' => $resJson ?? $response->body(),
            ]);

            return [
                'success'    => false,
                'message'    => $errorMsg,
                'error_code' => $resJson['error_code'] ?? 'DEDUCT_FAILED',
                'data'       => $resJson['data'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error('Exception saat memotong saldo Finance CIO: ' . $e->getMessage(), [
                'reference_id' => $referenceId,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memotong saldo Finance: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengembalikan (Refund) saldo ke API Finance CIO jika transfer/transaksi gagal.
     *
     * @param float  $amount       Nominal yang dikembalikan
     * @param string $balanceType  'manual' atau 'xendit'
     * @param string $referenceId  ID Referensi Refund ber-prefix INV-
     * @param string $description  Deskripsi pengembalian dana
     * @param string $reason       Alasan rollback
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function refundBalance(
        float $amount,
        string $balanceType,
        string $referenceId,
        string $description,
        string $reason
    ): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'API Finance belum dikonfigurasi.',
            ];
        }

        $path = '/api/v1/balance/refund';
        $url  = $this->baseUrl . $path;

        if (!str_starts_with($referenceId, 'OPS-')) {
            $referenceId = 'OPS-' . $referenceId;
        }

        $body = [
            'amount'       => (float) $amount,
            'balance_type' => strtolower($balanceType),
            'reference_id' => $referenceId,
            'description'  => $description,
            'reason'       => $reason,
        ];

        try {
            $headers = $this->generateHeaders('POST', $path, $body);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $body);

            $resJson = $response->json();

            if ($response->successful() && (($resJson['status'] ?? '') === 'success' || ($resJson['success'] ?? false) === true)) {
                try {
                    Cache::forget('cio_finance_balance_data');
                } catch (\Throwable $e) {}

                Log::info('Berhasil mengembalikan saldo Finance CIO (Auto-Refund)', [
                    'reference_id' => $referenceId,
                    'balance_type' => $balanceType,
                    'amount'       => $amount,
                ]);

                return [
                    'success' => true,
                    'message' => $resJson['message'] ?? 'Saldo berhasil dikembalikan',
                    'data'    => $resJson['data'] ?? [],
                ];
            }

            Log::error('Gagal refund saldo Finance CIO', [
                'status'   => $response->status(),
                'response' => $resJson ?? $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $resJson['message'] ?? ('Gagal refund saldo (HTTP ' . $response->status() . ')'),
            ];

        } catch (Exception $e) {
            Log::error('Exception saat refund saldo Finance CIO: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat refund saldo: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengirim log riwayat aktivitas transaksi ke endpoint /api/v1/history di Server Finance CIO.
     *
     * @param array $payload
     * @return array{success: bool, message: string, data?: mixed}
     */
    public function recordHistory(array $payload): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'API Finance belum dikonfigurasi pada .env.',
            ];
        }

        $path = '/api/v1/history';
        $url  = $this->baseUrl . $path;

        try {
            $headers = $this->generateHeaders('POST', $path, $payload);

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $payload);

            $resJson = $response->json();

            if ($response->successful() && (($resJson['status'] ?? '') === 'success' || ($resJson['success'] ?? false) === true)) {
                Log::info('Berhasil mencatat log aktivitas transaksi ke Finance CIO (/api/v1/history)', [
                    'subject_external_id' => $payload['subject_external_id'] ?? null,
                    'event'               => $payload['event'] ?? 'created',
                    'data'                => $resJson['data'] ?? [],
                ]);

                return [
                    'success' => true,
                    'message' => $resJson['message'] ?? 'Activity log created successfully',
                    'data'    => $resJson['data'] ?? [],
                ];
            }

            Log::warning('Gagal mencatat log aktivitas ke Finance CIO', [
                'status'   => $response->status(),
                'response' => $resJson ?? $response->body(),
            ]);

            return [
                'success' => false,
                'message' => $resJson['message'] ?? ('Gagal mencatat log aktivitas (HTTP ' . $response->status() . ')'),
            ];

        } catch (Exception $e) {
            Log::error('Exception saat mencatat log aktivitas ke Finance CIO: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat mencatat log aktivitas: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mencatat log riwayat transaksi pengeluaran operasional lengkap ke endpoint /api/v1/history Server Finance CIO.
     * Format deskripsi terperinci: Siap untuk apa, daftar item barang (qty, satuan, harga per unit, subtotal),
     * total pengeluaran, biaya admin, sumber dana saldo, dicatat oleh siapa, vendor, dan catatan.
     *
     * @param OperationalExpense $expense
     * @return array
     */
    public function recordOperationalExpenseHistory(OperationalExpense $expense): array
    {
        $expense->loadMissing(['user', 'category', 'items']);

        $categoryName        = $expense->category?->name ?? 'Operasional Umum';
        $vendorName          = $expense->vendor_name ?: ($expense->account_holder_name ?: 'Tidak Disebutkan');
        $userName            = $expense->user?->name ?? 'Staf Operasional';
        $channelFormatted    = ($expense->payment_channel === 'xendit') ? 'Saldo Xendit' : 'Saldo Kas/Manual';
        $bankDetailsStr      = '';
        if ($expense->payment_channel === 'xendit' && ($expense->bank_name || $expense->bank_code || $expense->account_number)) {
            $bName = $expense->bank_name ?: ($expense->bank_code ?: 'Bank Transfer');
            $accNo = $expense->account_number ?: '-';
            $accHolder = $expense->account_holder_name ?: $vendorName;
            $bankDetailsStr = " [Transfer: {$bName} Rek. {$accNo} a.n {$accHolder}]";
        }
        $subtotalFormatted   = 'Rp ' . number_format($expense->subtotal_amount, 0, ',', '.');
        $adminFeeFormatted   = $expense->has_admin_fee ? ('Rp ' . number_format($expense->admin_fee, 0, ',', '.')) : 'Rp 0';
        $grandTotalFormatted = 'Rp ' . number_format($expense->grand_total, 0, ',', '.');
        $trxDateFormatted    = $expense->transaction_date ? \Carbon\Carbon::parse($expense->transaction_date)->translatedFormat('d F Y') : now()->translatedFormat('d F Y');
        $notes               = $expense->notes ?: '-';

        // Susun rincian item barang secara detail
        $itemsList = [];
        $itemsDescriptionParts = [];
        $no = 1;
        foreach ($expense->items as $item) {
            $qty                   = (float) $item->quantity;
            $unit                  = $item->unit ?: 'pcs';
            $unitPriceFormatted    = 'Rp ' . number_format($item->unit_price, 0, ',', '.');
            $subtotalItemFormatted = 'Rp ' . number_format($item->subtotal, 0, ',', '.');

            $itemsDescriptionParts[] = sprintf(
                "%d. %s (%s %s @ %s = %s)",
                $no++,
                $item->item_name,
                $qty,
                $unit,
                $unitPriceFormatted,
                $subtotalItemFormatted
            );

            $itemsList[] = [
                'item_name'   => $item->item_name,
                'quantity'    => $qty,
                'unit'        => $unit,
                'unit_price'  => (float) $item->unit_price,
                'subtotal'    => (float) $item->subtotal,
            ];
        }

        $itemsSummaryStr = !empty($itemsDescriptionParts) ? implode('; ', $itemsDescriptionParts) : 'Tidak ada item rincian';

        // Deskripsi terperinci, informatif, dan jelas untuk apa serta berapa jumlah dan harganya
        $description = sprintf(
            "Tambah Pengeluaran Operasional [%s] \"%s\" (Kategori: %s, Vendor: %s). Rincian Barang: [%s]. Subtotal Barang: %s, Biaya Admin: %s, Total Pengeluaran: %s. Sumber Dana: %s%s. Dicatat oleh: %s pada %s. Catatan: %s",
            $expense->reference_no,
            $expense->title,
            $categoryName,
            $vendorName,
            $itemsSummaryStr,
            $subtotalFormatted,
            $adminFeeFormatted,
            $grandTotalFormatted,
            $channelFormatted,
            $bankDetailsStr,
            $userName,
            $trxDateFormatted,
            $notes
        );

        $payload = [
            'event'               => 'created',
            'subject_type'        => 'Expense',
            'subject_external_id' => $expense->reference_no,
            'description'         => \Illuminate\Support\Str::limit($description, 250, '...'),
            'properties'          => [
                'full_description'       => $description,
                'client_name'            => 'Web Operasional',
                'reference_no'           => $expense->reference_no,
                'title'                  => $expense->title,
                'category'               => $categoryName,
                'vendor_name'            => $vendorName,
                'bank_code'              => $expense->bank_code,
                'bank_name'              => $expense->bank_name,
                'account_number'         => $expense->account_number,
                'account_holder_name'    => $expense->account_holder_name,
                'transaction_date'       => $expense->transaction_date ? \Carbon\Carbon::parse($expense->transaction_date)->format('Y-m-d') : now()->format('Y-m-d'),
                'subtotal_amount'        => (float) $expense->subtotal_amount,
                'has_admin_fee'          => (bool) $expense->has_admin_fee,
                'admin_fee'              => (float) $expense->admin_fee,
                'grand_total'            => (float) $expense->grand_total,
                'payment_channel'        => $expense->payment_channel,
                'status'                 => $expense->status ?: 'success',
                'items_count'            => count($itemsList),
                'items'                  => $itemsList,
                'notes'                  => $notes,
                'recorded_by'            => $userName,
            ],
        ];

        return $this->recordHistory($payload);
    }
}
