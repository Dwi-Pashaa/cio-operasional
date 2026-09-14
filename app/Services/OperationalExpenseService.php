<?php

namespace App\Services;

use App\Models\OperationalExpense;
use App\Models\OperationalExpenseItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class OperationalExpenseService
{
    protected CioFinanceService $financeService;
    protected XenditService $xenditService;

    public function __construct(CioFinanceService $financeService, XenditService $xenditService)
    {
        $this->financeService = $financeService;
        $this->xenditService  = $xenditService;
    }

    /**
     * Get paginated operational expenses with filters.
     */
    public function getPaginated(
        ?string $search = null,
        ?int $categoryId = null,
        ?string $channel = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = OperationalExpense::with(['user', 'category', 'items']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%");
            });
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($channel)) {
            $query->where('payment_channel', $channel);
        }

        if (!empty($startDate)) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        return $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Process creation of operational expense:
     * 1. Validate balance
     * 2. Deduct balance in CIO Finance API
     * 3. Insert records inside database transaction
     * 4. Auto-refund if database write fails
     */
    public function createExpense(array $data, array $items, $receiptFile = null): array
    {
        // 1. Calculate totals
        $subtotal = 0;
        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $subtotal += ($qty * $price);
        }

        $hasAdminFee = !empty($data['has_admin_fee']);
        $adminFee = $hasAdminFee ? (float) ($data['admin_fee'] ?? 0) : 0;
        $grandTotal = $subtotal + $adminFee;

        if ($grandTotal <= 0) {
            return [
                'success' => false,
                'message' => 'Total pengeluaran harus lebih besar dari Rp 0.',
            ];
        }

        $paymentChannel = $data['payment_channel'] ?? 'manual'; // 'manual' atau 'xendit'

        // 2. Validate balance with CIO Finance
        $balanceInfo = $this->financeService->getBalance(true);
        if (!$balanceInfo['is_connected']) {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke API Finance: ' . ($balanceInfo['message'] ?? 'Periksa koneksi/kredensial.'),
            ];
        }

        $balanceData = $balanceInfo['data'] ?? [];
        $channelStatus = $balanceData['channel_status'] ?? [];
        $isChannelActive = (bool) ($channelStatus[$paymentChannel] ?? false);
        $channelLabel = ($paymentChannel === 'xendit') ? 'Saldo Xendit' : 'Saldo Kas/Manual';

        if (!$isChannelActive) {
            return [
                'success' => false,
                'message' => "Saluran {$channelLabel} saat ini sedang dinonaktifkan oleh Web Finance. Silakan gunakan saluran saldo yang aktif.",
            ];
        }

        $availableBalance = ($paymentChannel === 'xendit')
            ? (float) ($balanceData['balance_xendit'] ?? 0)
            : (float) ($balanceData['balance_manual'] ?? 0);

        if ($availableBalance < $grandTotal) {
            return [
                'success' => false,
                'message' => "{$channelLabel} tidak mencukupi! Sisa saldo saat ini: Rp " . number_format($availableBalance, 0, ',', '.') . ", total yang dibutuhkan: Rp " . number_format($grandTotal, 0, ',', '.'),
            ];
        }

        // 3. Generate Reference Number
        $refNo = $this->generateReferenceNo();

        // 4. Handle attachment receipt upload
        $receiptPath = null;
        if ($receiptFile && $receiptFile->isValid()) {
            $receiptPath = $receiptFile->store('receipts', 'public');
        }

        // 5. Deduct Balance via API
        $description = $data['title'] . ($hasAdminFee ? " (Termasuk Biaya Admin Rp " . number_format($adminFee, 0, ',', '.') . ")" : "");
        $deductResult = $this->financeService->deductBalance(
            $grandTotal,
            $paymentChannel,
            $refNo,
            $description,
            'Operasional',
            $data['notes'] ?? 'Pengeluaran operasional perusahaan'
        );

        if (!$deductResult['success']) {
            // Delete uploaded file if API call failed
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }

            return [
                'success' => false,
                'message' => 'Gagal memotong saldo Finance: ' . ($deductResult['message'] ?? 'Terjadi kesalahan.'),
            ];
        }

        // 6. Jika menggunakan Saldo Xendit: Kirim Disbursement ke Xendit API
        $disbursementData = null;
        $initialStatus = 'success';

        if ($paymentChannel === 'xendit') {
            $bankCode          = $data['bank_code'] ?? '';
            $accountNumber     = $data['account_number'] ?? '';
            $accountHolderName = $data['account_holder_name'] ?? ($data['vendor_name'] ?? 'Vendor Operasional');

            if (empty($bankCode) || empty($accountNumber)) {
                $this->financeService->refundBalance(
                    $grandTotal,
                    $paymentChannel,
                    $refNo,
                    'Rollback pemotongan saldo: Rekening bank Xendit tidak lengkap',
                    'Validation error on bank_code or account_number'
                );

                if ($receiptPath) {
                    Storage::disk('public')->delete($receiptPath);
                }

                return [
                    'success' => false,
                    'message' => 'Bank tujuan dan nomor rekening wajib diisi untuk transaksi via Saldo Xendit.',
                ];
            }

            $disbResult = $this->xenditService->createDisbursement(
                $refNo,
                $bankCode,
                $accountHolderName,
                $accountNumber,
                $grandTotal,
                $description
            );

            if (!$disbResult['success']) {
                // Auto-Refund rollback saldo karena Xendit API gagal
                $this->financeService->refundBalance(
                    $grandTotal,
                    $paymentChannel,
                    $refNo,
                    'Auto-Refund kegagalan request disbursement Xendit',
                    $disbResult['message'] ?? 'Xendit API error'
                );

                if ($receiptPath) {
                    Storage::disk('public')->delete($receiptPath);
                }

                return [
                    'success' => false,
                    'message' => 'Gagal mengirim transfer ke Xendit: ' . ($disbResult['message'] ?? 'Periksa konfigurasi Xendit.') . ' (Saldo Anda otomatis dikembalikan).',
                ];
            }

            $disbursementData = $disbResult['data'] ?? [];
            $xenditStatus     = strtoupper($disbursementData['status'] ?? 'PENDING');
            $initialStatus    = in_array($xenditStatus, ['COMPLETED', 'SUCCESS', 'PAID', 'SETTLED']) ? 'success' : 'pending';
        }

        // 7. Save in Database
        DB::beginTransaction();
        try {
            $expense = OperationalExpense::create([
                'reference_no'        => $refNo,
                'user_id'             => auth()->id(),
                'category_id'         => $data['category_id'] ?? null,
                'title'               => $data['title'],
                'vendor_name'         => $data['vendor_name'] ?? null,
                'transaction_date'    => $data['transaction_date'] ?? now()->toDateString(),
                'subtotal_amount'     => $subtotal,
                'has_admin_fee'       => $hasAdminFee,
                'admin_fee'           => $adminFee,
                'grand_total'         => $grandTotal,
                'payment_channel'     => $paymentChannel,
                'bank_code'           => $data['bank_code'] ?? null,
                'bank_name'           => $data['bank_name'] ?? null,
                'account_number'      => $data['account_number'] ?? null,
                'account_holder_name' => $data['account_holder_name'] ?? ($data['vendor_name'] ?? null),
                'finance_reference_id'=> $refNo,
                'finance_response'    => array_merge(
                    $deductResult['data'] ?? [],
                    ['xendit_disbursement' => $disbursementData]
                ),
                'status'              => $initialStatus,
                'notes'               => $data['notes'] ?? null,
                'attachment_receipt'  => $receiptPath,
            ]);

            foreach ($items as $item) {
                $qty = (float) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);

                OperationalExpenseItem::create([
                    'expense_id' => $expense->id,
                    'item_id'    => !empty($item['item_id']) ? $item['item_id'] : null,
                    'item_name'  => $item['item_name'],
                    'quantity'   => $qty,
                    'unit'       => $item['unit'] ?? 'pcs',
                    'unit_price' => $price,
                    'subtotal'   => ($qty * $price),
                ]);
            }

            DB::commit();

            // 7. Catat riwayat aktivitas lengkap ke Finance API (/api/v1/history)
            try {
                $this->financeService->recordOperationalExpenseHistory($expense);
            } catch (\Throwable $th) {
                Log::warning('Gagal mencatat log aktivitas pengeluaran ke Finance: ' . $th->getMessage());
            }

            return [
                'success' => true,
                'message' => "Pengeluaran {$refNo} berhasil dicatat dan {$channelLabel} telah terpotong sebesar Rp " . number_format($grandTotal, 0, ',', '.'),
                'data'    => $expense,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DB Exception saat simpan pengeluaran, mengeksekusi Auto-Refund: ' . $e->getMessage());

            // 7. AUTO-REFUND GARANSI: Kembalikan saldo karena database gagal simpan
            $this->financeService->refundBalance(
                $grandTotal,
                $paymentChannel,
                $refNo,
                'Auto-Refund gagal simpan data operasional',
                'System Rollback: ' . $e->getMessage()
            );

            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menyimpan transaksi. Saldo Anda telah otomatis dikembalikan (Auto-Refund). Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete expense and optionally refund balance.
     */
    public function deleteExpense(OperationalExpense $expense, bool $withRefund = true): array
    {
        if ($withRefund && $expense->status === 'success' && $expense->grand_total > 0) {
            $refundRes = $this->financeService->refundBalance(
                $expense->grand_total,
                $expense->payment_channel,
                $expense->reference_no,
                "Pembatalan pengeluaran {$expense->reference_no}",
                "Dihapus oleh user " . (auth()->user()->name ?? 'System')
            );

            if (!$refundRes['success']) {
                Log::warning('Gagal auto-refund saat hapus pengeluaran: ' . ($refundRes['message'] ?? ''));
            }
        }

        if ($expense->attachment_receipt) {
            Storage::disk('public')->delete($expense->attachment_receipt);
        }

        $expense->delete();

        return [
            'success' => true,
            'message' => 'Data pengeluaran berhasil dihapus' . ($withRefund ? ' dan saldo telah dikembalikan ke Finance.' : '.'),
        ];
    }

    /**
     * Generate unique reference number with OPS- prefix for Xendit Central Router.
     */
    protected function generateReferenceNo(): string
    {
        $datePrefix = 'OPS-' . date('Ymd') . '-';
        $latest = OperationalExpense::where('reference_no', 'like', $datePrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $dbSeq = 0;
        if ($latest) {
            $parts = explode('-', $latest->reference_no);
            $dbSeq = (int) end($parts);
        }

        $cacheKey = 'ops_ref_seq_' . date('Ymd');
        $cachedSeq = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);

        $nextSeq = max($dbSeq, $cachedSeq) + 1;
        \Illuminate\Support\Facades\Cache::put($cacheKey, $nextSeq, 86400);

        return $datePrefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get statistics summary for dashboard & reports.
     */
    public function getDashboardStats(): array
    {
        $now = now();

        $totalThisMonth = OperationalExpense::where('status', 'success')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->sum('grand_total');

        $totalLastMonth = OperationalExpense::where('status', 'success')
            ->whereMonth('transaction_date', $now->copy()->subMonth()->month)
            ->whereYear('transaction_date', $now->copy()->subMonth()->year)
            ->sum('grand_total');

        $transactionCountThisMonth = OperationalExpense::where('status', 'success')
            ->whereMonth('transaction_date', $now->month)
            ->whereYear('transaction_date', $now->year)
            ->count();

        $categoryBreakdown = OperationalExpense::where('operational_expenses.status', 'success')
            ->whereMonth('operational_expenses.transaction_date', $now->month)
            ->whereYear('operational_expenses.transaction_date', $now->year)
            ->leftJoin('expense_categories', 'operational_expenses.category_id', '=', 'expense_categories.id')
            ->selectRaw('COALESCE(expense_categories.name, "Lain-lain") as category_name, SUM(operational_expenses.grand_total) as total_amount')
            ->groupBy('category_name')
            ->orderBy('total_amount', 'desc')
            ->get();

        $recentTransactions = OperationalExpense::with(['category', 'user'])
            ->where('status', 'success')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return [
            'total_this_month'         => (float) $totalThisMonth,
            'total_last_month'         => (float) $totalLastMonth,
            'transaction_count'        => $transactionCountThisMonth,
            'category_breakdown'       => $categoryBreakdown,
            'recent_transactions'      => $recentTransactions,
        ];
    }
}
