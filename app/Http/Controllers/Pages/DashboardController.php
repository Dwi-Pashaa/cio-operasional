<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Services\CioFinanceService;
use App\Services\OperationalExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected CioFinanceService $financeService;
    protected OperationalExpenseService $expenseService;

    public function __construct(
        CioFinanceService $financeService,
        OperationalExpenseService $expenseService
    ) {
        $this->financeService = $financeService;
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        $freshBalance = $request->has('refresh_balance');
        $financeBalance = Auth::user()->hasRole('Admin')
            ? $this->financeService->getBalance($freshBalance)
            : ['status' => 'not_permitted', 'message' => 'Hanya admin yang memiliki izin.', 'data' => []];

        $expenseStats = $this->expenseService->getDashboardStats();

        return view("pages.dashboard", compact('financeBalance', 'expenseStats'));
    }
}
