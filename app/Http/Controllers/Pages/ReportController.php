<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ExpenseCategoryService $categoryService;

    public function __construct(ExpenseCategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $this->authorize('lihat laporan');

        $startDate  = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate    = $request->get('end_date', now()->endOfMonth()->toDateString());
        $categoryId = $request->get('category_id');
        $channel    = $request->get('channel');

        $query = OperationalExpense::with(['user', 'category', 'items'])
            ->where('status', 'success')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($channel)) {
            $query->where('payment_channel', $channel);
        }

        $expenses = $query->orderBy('transaction_date', 'asc')->get();

        $totalSubtotal = (float) $expenses->sum('subtotal_amount');
        $totalAdminFee = (float) $expenses->sum('admin_fee');
        $totalGrand    = (float) $expenses->sum('grand_total');
        $manualTotal   = (float) $expenses->where('payment_channel', 'manual')->sum('grand_total');
        $xenditTotal   = (float) $expenses->where('payment_channel', 'xendit')->sum('grand_total');
        $totalCount    = $expenses->count();

        // Breakdown per category
        $categoryBreakdown = $expenses->groupBy(function($item) {
            return $item->category?->name ?? 'Lain-lain / Tanpa Kategori';
        })->map(function($items, $catName) use ($totalGrand) {
            $sum = (float) $items->sum('grand_total');
            $percentage = $totalGrand > 0 ? round(($sum / $totalGrand) * 100, 1) : 0;
            return [
                'name'       => $catName,
                'count'      => $items->count(),
                'total'      => $sum,
                'percentage' => $percentage,
            ];
        })->sortByDesc('total');

        $categories = $this->categoryService->getAllActive();

        return view('pages.report.index', compact(
            'expenses',
            'categories',
            'startDate',
            'endDate',
            'categoryId',
            'channel',
            'totalSubtotal',
            'totalAdminFee',
            'totalGrand',
            'manualTotal',
            'xenditTotal',
            'totalCount',
            'categoryBreakdown'
        ));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('download excel');

        $startDate  = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate    = $request->get('end_date', now()->endOfMonth()->toDateString());
        $categoryId = $request->get('category_id');
        $channel    = $request->get('channel');

        $query = OperationalExpense::with(['user', 'category'])
            ->where('status', 'success')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate);

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($channel)) {
            $query->where('payment_channel', $channel);
        }

        $expenses = $query->orderBy('transaction_date', 'asc')->get();

        $filename = "Laporan_Pengeluaran_Operasional_{$startDate}_sd_{$endDate}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for proper Excel viewing
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'No. Referensi',
                'Tanggal',
                'Judul Pengeluaran',
                'Kategori',
                'Vendor / Toko',
                'Saluran Saldo',
                'Subtotal (Rp)',
                'Biaya Admin (Rp)',
                'Grand Total (Rp)',
                'Dibuat Oleh',
                'Catatan'
            ]);

            foreach ($expenses as $row) {
                fputcsv($file, [
                    $row->reference_no,
                    $row->transaction_date->format('d/m/Y'),
                    $row->title,
                    $row->category->name ?? 'Lain-lain',
                    $row->vendor_name ?? '-',
                    strtoupper($row->payment_channel),
                    $row->subtotal_amount,
                    $row->admin_fee,
                    $row->grand_total,
                    $row->user->name ?? '-',
                    $row->notes ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
