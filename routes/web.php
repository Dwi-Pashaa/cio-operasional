<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pages\DashboardController;
use App\Http\Controllers\Pages\ExpenseCategoryController;
use App\Http\Controllers\Pages\ItemController;
use App\Http\Controllers\Pages\OperationalExpenseController;
use App\Http\Controllers\Pages\ReportController;
use App\Http\Controllers\Pages\RoleController;
use App\Http\Controllers\Pages\SettingController;
use App\Http\Controllers\Pages\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('post.login');

// Reset Password Multi-Channel (Email & WhatsApp OTP)
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.email');
Route::get('/verify-otp', [ForgotPasswordController::class, 'showVerifyOtpForm'])->name('password.verify.form');
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify');
Route::post('/verify-otp/channel', [ForgotPasswordController::class, 'sendChannel'])->name('password.send.channel');
Route::post('/resend-otp', [ForgotPasswordController::class, 'resendOtp'])->name('password.resend');
Route::get('/reset-password', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    // Logout
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Data Role
    Route::prefix('role')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('role.index')->can('lihat role');
        Route::post('/store', [RoleController::class, 'store'])->name('role.store')->can('buat role');
        Route::get('/{id}/permission', [RoleController::class, 'permission'])->name('role.permission')->can('atur permission role');
        Route::put('/{id}/savePermission', [RoleController::class, 'savePermission'])->name('role.savePermission')->can('atur permission role');
        Route::get('/{id}/show', [RoleController::class, 'show'])->name('role.show')->can('lihat role');
        Route::put('/{id}/update', [RoleController::class, 'update'])->name('role.update')->can('ubah role');
        Route::delete('/{id}/destroy', [RoleController::class, 'destroy'])->name('role.destroy')->can('hapus role');
    });

    // Data Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user.index')->can('lihat pengguna');
        Route::get('/create', [UserController::class, 'create'])->name('user.create')->can('buat pengguna');
        Route::post('/store', [UserController::class, 'store'])->name('user.store')->can('buat pengguna');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->can('ubah pengguna');
        Route::put('/{id}/update', [UserController::class, 'update'])->name('user.update')->can('ubah pengguna');
        Route::delete('/{id}/destroy', [UserController::class, 'destroy'])->name('user.destroy')->can('hapus pengguna');
    });

    // Master Kategori Pengeluaran
    Route::prefix('expense-categories')->group(function () {
        Route::get('/', [ExpenseCategoryController::class, 'index'])->name('expense-category.index')->can('lihat kategori');
        Route::get('/create', [ExpenseCategoryController::class, 'create'])->name('expense-category.create')->can('buat kategori');
        Route::post('/store', [ExpenseCategoryController::class, 'store'])->name('expense-category.store')->can('buat kategori');
        Route::get('/{id}/show', [ExpenseCategoryController::class, 'show'])->name('expense-category.show')->can('lihat kategori');
        Route::get('/{id}/edit', [ExpenseCategoryController::class, 'edit'])->name('expense-category.edit')->can('ubah kategori');
        Route::put('/{id}/update', [ExpenseCategoryController::class, 'update'])->name('expense-category.update')->can('ubah kategori');
        Route::delete('/{id}/destroy', [ExpenseCategoryController::class, 'destroy'])->name('expense-category.destroy')->can('hapus kategori');
    });

    // Master Barang / Katalog Item
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('item.index')->can('lihat barang');
        Route::get('/create', [ItemController::class, 'create'])->name('item.create')->can('buat barang');
        Route::post('/store', [ItemController::class, 'store'])->name('item.store')->can('buat barang');
        Route::get('/{id}/show', [ItemController::class, 'show'])->name('item.show')->can('lihat barang');
        Route::get('/{id}/edit', [ItemController::class, 'edit'])->name('item.edit')->can('ubah barang');
        Route::put('/{id}/update', [ItemController::class, 'update'])->name('item.update')->can('ubah barang');
        Route::delete('/{id}/destroy', [ItemController::class, 'destroy'])->name('item.destroy')->can('hapus barang');
    });

    // Transaksi Pengeluaran Operasional
    Route::prefix('expenses')->group(function () {
        Route::get('/', [OperationalExpenseController::class, 'index'])->name('expense.index')->can('lihat pengeluaran');
        Route::get('/create', [OperationalExpenseController::class, 'create'])->name('expense.create')->can('buat pengeluaran');
        Route::post('/store', [OperationalExpenseController::class, 'store'])->name('expense.store')->can('buat pengeluaran');
        Route::get('/{id}', [OperationalExpenseController::class, 'show'])->name('expense.show')->can('lihat pengeluaran');
        Route::get('/{id}/print', [OperationalExpenseController::class, 'printVoucher'])->name('expense.print')->can('download pdf');
        Route::delete('/{id}/destroy', [OperationalExpenseController::class, 'destroy'])->name('expense.destroy')->can('hapus pengeluaran');
    });

    // Laporan & Rekapitulasi
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('report.index')->can('lihat laporan');
        Route::get('/export-csv', [ReportController::class, 'exportCsv'])->name('report.export.csv')->can('download excel');
    });

    // Pengaturan Sistem
    Route::prefix('setting')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('setting')->can('lihat pengaturan');
        Route::post('/store', [SettingController::class, 'store'])->name('setting.store')->can('ubah pengaturan');
        Route::post('/dashboard-columns', [SettingController::class, 'saveDashboardColumns'])->name('setting.dashboard-columns')->can('ubah pengaturan');
    });
});
