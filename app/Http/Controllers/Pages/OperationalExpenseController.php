<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\OperationalExpense;
use App\Services\CioFinanceService;
use App\Services\ExpenseCategoryService;
use App\Services\ItemService;
use App\Services\OperationalExpenseService;
use App\Services\XenditService;
use Illuminate\Http\Request;

class OperationalExpenseController extends Controller
{
    protected OperationalExpenseService $expenseService;
    protected ExpenseCategoryService $categoryService;
    protected ItemService $itemService;
    protected CioFinanceService $financeService;
    protected XenditService $xenditService;

    public function __construct(
        OperationalExpenseService $expenseService,
        ExpenseCategoryService $categoryService,
        ItemService $itemService,
        CioFinanceService $financeService,
        XenditService $xenditService
    ) {
        $this->expenseService = $expenseService;
        $this->categoryService = $categoryService;
        $this->itemService = $itemService;
        $this->financeService = $financeService;
        $this->xenditService = $xenditService;
    }

    public function index(Request $request)
    {
        $this->authorize('lihat pengeluaran');

        $search     = $request->get('search');
        $categoryId = $request->get('category_id');
        $channel    = $request->get('channel');
        $startDate  = $request->get('start_date');
        $endDate    = $request->get('end_date');

        $expenses   = $this->expenseService->getPaginated(
            $search,
            $categoryId ? (int)$categoryId : null,
            $channel,
            $startDate,
            $endDate,
            10
        );

        $categories = $this->categoryService->getAllActive();

        return view('pages.expense.index', compact('expenses', 'categories', 'search', 'categoryId', 'channel', 'startDate', 'endDate'));
    }

    public function create()
    {
        $this->authorize('buat pengeluaran');

        $categories = $this->categoryService->getAllActive();
        $items = $this->itemService->getAllActive();
        $balanceInfo = $this->financeService->getBalance(true);
        $bankList = $this->xenditService->getAvailableBanks();

        return view('pages.expense.create', compact('categories', 'items', 'balanceInfo', 'bankList'));
    }

    public function store(Request $request)
    {
        $this->authorize('buat pengeluaran');

        $request->validate([
            'title'               => 'required|string|max:255',
            'category_id'         => 'nullable|exists:expense_categories,id',
            'vendor_name'         => 'nullable|string|max:255',
            'transaction_date'    => 'required|date',
            'payment_channel'     => 'required|in:manual,xendit',
            'bank_code'           => 'nullable|string|max:50',
            'bank_name'           => 'nullable|string|max:100',
            'account_number'      => 'nullable|string|max:100',
            'account_holder_name' => 'nullable|string|max:150',
            'has_admin_fee'       => 'nullable|boolean',
            'admin_fee'           => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string',
            'attachment'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'items'               => 'required|array|min:1',
            'items.*.item_name'   => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit'        => 'required|string|max:50',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        if ($request->input('payment_channel') === 'xendit') {
            $request->validate([
                'bank_code'      => 'required|string',
                'account_number' => 'required|string|max:100',
            ]);
        }

        $result = $this->expenseService->createExpense(
            $request->all(),
            $request->input('items', []),
            $request->file('attachment')
        );

        if (!$result['success']) {
            return back()->withInput()->with('error', $result['message']);
        }

        return redirect()->route('expense.index')->with('success', $result['message']);
    }

    public function show($id)
    {
        $this->authorize('lihat pengeluaran');

        $expense = OperationalExpense::with(['user', 'category', 'items'])->findOrFail($id);

        return view('pages.expense.show', compact('expense'));
    }

    public function printVoucher($id)
    {
        $this->authorize('download pdf');

        $expense = OperationalExpense::with(['user', 'category', 'items'])->findOrFail($id);

        $attachmentBase64 = null;
        if ($expense->attachment_receipt) {
            $filePath = storage_path('app/public/' . $expense->attachment_receipt);
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $fileData = file_get_contents($filePath);
                    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'webp') ? 'image/webp' : 'image/jpeg');
                    $attachmentBase64 = 'data:' . $mime . ';base64,' . base64_encode($fileData);
                }
            }
        }

        $filename = ($expense->payment_channel === 'xendit' ? 'Resi_Xendit_' : 'Bukti_Kas_') . $expense->reference_no . '.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.expense.pdf_receipt', compact('expense', 'attachmentBase64'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'defaultFont'          => 'sans-serif',
            ]);

        return $pdf->stream($filename);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('hapus pengeluaran');

        $expense = OperationalExpense::findOrFail($id);
        $result = $this->expenseService->deleteExpense($expense, true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
            ], $result['success'] ? 200 : 400);
        }

        return redirect()->route('expense.index')->with('success', $result['message']);
    }
}
