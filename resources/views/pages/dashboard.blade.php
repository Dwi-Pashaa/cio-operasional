@extends('layouts.app')

@section('title', 'Dashboard Operasional')

@push('css')
<style>
    .dash-header-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .dash-header-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #2563eb 0%, #3b82f6 100%);
    }

    .fintech-stat-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem 1.35rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        height: 100%;
    }
    .fintech-stat-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
    }
    .fintech-stat-card.card-manual { border-top: 3px solid #2563eb; }
    .fintech-stat-card.card-xendit { border-top: 3px solid #10b981; }
    .fintech-stat-card.card-total {
        border-top: 3px solid #7c3aed;
        background: linear-gradient(180deg, #ffffff 0%, #faf8ff 100%);
    }
    .fintech-stat-card.card-expense-now { border-top: 3px solid #ef4444; }
    .fintech-stat-card.card-expense-prev { border-top: 3px solid #f59e0b; }
    .fintech-stat-card.card-expense-count { border-top: 3px solid #06b6d4; }

    .fintech-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .status-dot-pulse {
        position: relative;
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 5px;
    }
    .status-dot-pulse.active {
        background-color: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
        animation: pulse-green 2s infinite;
    }
    .status-dot-pulse.inactive { background-color: #ef4444; }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .badge-soft-success { background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .badge-soft-danger { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .badge-soft-primary { background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-soft-purple { background-color: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe; }

    .num-currency {
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum";
        letter-spacing: -0.02em;
    }
</style>
@endpush

@section('content')
@php
    $balanceData = $financeBalance['data'] ?? [];
    $channelStatus = $balanceData['channel_status'] ?? ['manual' => false, 'xendit' => false];
    $isApiConnected = $financeBalance['is_connected'] ?? false;
@endphp

<!-- 1. Executive Welcome Header -->
<div class="dash-header-card p-4 mb-4 shadow-sm">
    <div class="row align-items-center g-3">
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge badge-soft-primary px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                    {{ Auth::user()->roles->pluck('name')->implode(', ') ?: 'Administrator' }}
                </span>
                <span class="text-muted small">&bull;</span>
                <span class="text-muted small fw-medium">{{ now()->translatedFormat('l, d F Y') }}</span>
            </div>
            <h1 class="h2 fw-bold text-dark mb-1" style="letter-spacing: -0.02em;">
                Selamat Datang, {{ Auth::user()->name }}! 👋
            </h1>
            <p class="text-muted mb-0 small">
                Portal Operasional CIO Network Solution & Pemantauan Likuiditas Saldo Finansial secara real-time.
            </p>
        </div>
        <div class="col-lg-5 text-lg-end">
            <div class="d-inline-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                @can('buat pengeluaran')
                    <a href="{{ route('expense.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm px-3 py-2 fw-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        Tambah Pengeluaran / Beli Barang
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>

@role('Admin')
<!-- 2. Section Header: Saldo Finance CIO -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
    <div class="d-flex align-items-center gap-2">
        <div class="fintech-icon-box" style="background-color: #eff6ff; color: #2563eb; width: 32px; height: 32px; border-radius: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
        </div>
        <div>
            <h3 class="fw-bold text-dark mb-0 fs-5">Likuiditas Saldo Finance CIO</h3>
            <div class="text-muted" style="font-size: 0.78rem;">
                Klien: <strong class="text-dark">{{ $balanceData['client_name'] ?? 'Web_Operasional' }}</strong>
                @if(!empty($balanceData['retrieved_at']))
                    &bull; Update: {{ \Carbon\Carbon::parse($balanceData['retrieved_at'])->translatedFormat('d M Y, H:i:s') }}
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($isApiConnected)
            <span class="badge badge-soft-success d-inline-flex align-items-center px-2.5 py-1.5 rounded-pill fw-bold" style="font-size: 0.75rem;">
                <span class="status-dot-pulse active"></span> API Terhubung
            </span>
        @else
            <span class="badge badge-soft-danger d-inline-flex align-items-center px-2.5 py-1.5 rounded-pill fw-bold" style="font-size: 0.75rem;">
                <span class="status-dot-pulse inactive"></span> API Terputus
            </span>
        @endif
        <a href="{{ route('dashboard', ['refresh_balance' => 1]) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1 shadow-sm px-2.5 py-1" title="Sinkronkan & Perbarui Saldo">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
            <span>Sinkronkan</span>
        </a>
    </div>
</div>

<!-- Balance 3-Cards Row -->
<div class="row g-3 mb-4">
    <!-- Saldo Manual -->
    <div class="col-sm-6 col-lg-4">
        <div class="fintech-stat-card card-manual">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="fintech-icon-box" style="background-color: #eff6ff; color: #2563eb;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                    </div>
                    <div>
                        <div class="text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em; color: #64748b;">Saldo Kas & Manual</div>
                        <div class="text-muted" style="font-size: 0.76rem;">Kas Internal Perusahaan</div>
                    </div>
                </div>
                @if(!empty($channelStatus['manual']))
                    <span class="badge badge-soft-success px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Aktif</span>
                @else
                    <span class="badge badge-soft-danger px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Nonaktif (Finance)</span>
                @endif
            </div>
            <div class="fs-2 fw-bold text-dark my-2 num-currency">
                Rp {{ number_format((float) ($balanceData['balance_manual'] ?? 0), 0, ',', '.') }}
            </div>
            <div class="small text-muted">Tersedia untuk pengeluaran tunai/kas internal</div>
        </div>
    </div>

    <!-- Saldo Xendit -->
    <div class="col-sm-6 col-lg-4">
        <div class="fintech-stat-card card-xendit">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="fintech-icon-box" style="background-color: #ecfdf5; color: #059669;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z" /><path d="M3 10l18 0" /><path d="M7 15l.01 0" /><path d="M11 15l2 0" /></svg>
                    </div>
                    <div>
                        <div class="text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em; color: #64748b;">Saldo Xendit</div>
                        <div class="text-muted" style="font-size: 0.76rem;">Payment Gateway & VA</div>
                    </div>
                </div>
                @if(!empty($channelStatus['xendit']))
                    <span class="badge badge-soft-success px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Aktif</span>
                @else
                    <span class="badge badge-soft-danger px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Nonaktif (Finance)</span>
                @endif
            </div>
            <div class="fs-2 fw-bold text-success my-2 num-currency">
                Rp {{ number_format((float) ($balanceData['balance_xendit'] ?? 0), 0, ',', '.') }}
            </div>
            <div class="small text-muted">Tersedia untuk auto-disbursement online</div>
        </div>
    </div>

    <!-- Total Saldo Gabungan -->
    <div class="col-sm-12 col-lg-4">
        <div class="fintech-stat-card card-total">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="fintech-icon-box" style="background-color: #f5f3ff; color: #7c3aed;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3v18" /><path d="M16 7l-8 10" /></svg>
                    </div>
                    <div>
                        <div class="text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em; color: #6d28d9;">Total Saldo Gabungan</div>
                        <div class="text-muted" style="font-size: 0.76rem;">Akumulasi Semua Saluran</div>
                    </div>
                </div>
                <span class="badge badge-soft-purple px-2 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">Semua Saluran</span>
            </div>
            <div class="fs-2 fw-bold my-2 num-currency" style="color: #6d28d9 !important;">
                Rp {{ number_format((float) ($balanceData['total_balance'] ?? ($balanceData['balance'] ?? 0)), 0, ',', '.') }}
            </div>
            <div class="small text-muted">Total likuiditas finansial CIO</div>
        </div>
    </div>
</div>
@endrole

<!-- 3. Section Header: Ringkasan Pengeluaran Operasional -->
<div class="mb-3">
    <h3 class="fw-bold text-dark mb-0 fs-5">Ringkasan Pengeluaran Operasional</h3>
    <div class="text-muted" style="font-size: 0.78rem;">Statistik belanja dan pengeluaran operasional perusahaan</div>
</div>

<div class="row g-3 mb-4">
    <!-- Pengeluaran Bulan Ini -->
    <div class="col-sm-6 col-lg-4">
        <div class="fintech-stat-card card-expense-now">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Pengeluaran Bulan Ini</div>
                    <div class="fs-2 fw-bold text-danger num-currency">
                        Rp {{ number_format($expenseStats['total_this_month'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="fintech-icon-box" style="background-color: #fef2f2; color: #ef4444; width: 44px; height: 44px; border-radius: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                </div>
            </div>
            <div class="small text-muted d-flex align-items-center gap-1.5 mt-2">
                <span class="badge bg-danger-subtle text-danger px-2 py-0.5 rounded-pill fw-bold">{{ now()->translatedFormat('F Y') }}</span>
                <span>{{ $expenseStats['transaction_count'] ?? 0 }} transaksi tercatat</span>
            </div>
        </div>
    </div>

    <!-- Pengeluaran Bulan Lalu -->
    <div class="col-sm-6 col-lg-4">
        <div class="fintech-stat-card card-expense-prev">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Pengeluaran Bulan Lalu</div>
                    <div class="fs-2 fw-bold text-dark num-currency">
                        Rp {{ number_format($expenseStats['total_last_month'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="fintech-icon-box" style="background-color: #fffbeb; color: #f59e0b; width: 44px; height: 44px; border-radius: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>
                </div>
            </div>
            <div class="small text-muted d-flex align-items-center gap-1.5 mt-2">
                <span class="badge bg-warning-subtle text-warning px-2 py-0.5 rounded-pill fw-bold">{{ now()->subMonth()->translatedFormat('F Y') }}</span>
                <span>Periode sebelumnya</span>
            </div>
        </div>
    </div>

    <!-- Total Transaksi Bulan Ini -->
    <div class="col-sm-12 col-lg-4">
        <div class="fintech-stat-card card-expense-count">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-uppercase fw-bold text-muted mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Transaksi Bulan Ini</div>
                    <div class="fs-2 fw-bold text-info num-currency">
                        {{ $expenseStats['transaction_count'] ?? 0 }} <span class="fs-4 text-muted fw-normal">Transaksi</span>
                    </div>
                </div>
                <div class="fintech-icon-box" style="background-color: #ecfeff; color: #06b6d4; width: 44px; height: 44px; border-radius: 12px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h10l2 2l-2 2h-10a2 2 0 0 1 -2 -2v0a2 2 0 0 1 2 -2z" /></svg>
                </div>
            </div>
            <div class="small text-muted mt-2">
                <a href="{{ route('expense.index') }}" class="text-decoration-none fw-semibold">Lihat Semua Transaksi &rarr;</a>
            </div>
        </div>
    </div>
</div>

<!-- 4. Section: Sebaran Kategori & Transaksi Terbaru -->
<div class="row g-3">
    <!-- Sebaran Kategori -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h4 class="card-title fw-bold text-dark mb-0">Sebaran Pengeluaran Bulan Ini</h4>
            </div>
            <div class="card-body p-3">
                @php
                    $breakdown = $expenseStats['category_breakdown'] ?? collect();
                @endphp
                @if($breakdown->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>
                        <div class="fw-semibold">Belum ada transaksi bulan ini</div>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($breakdown as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                                <span class="fw-semibold text-dark">{{ $item->category_name }}</span>
                                <span class="fw-bold text-danger num-currency">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Transaksi Pengeluaran Terbaru -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-bold text-dark mb-0">Transaksi Pengeluaran Terkini</h4>
                <a href="{{ route('expense.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>No. Ref</th>
                            <th>Judul Pengeluaran</th>
                            <th>Kategori</th>
                            <th class="text-end">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenseStats['recent_transactions'] ?? [] as $rec)
                            <tr>
                                <td>
                                    <a href="{{ route('expense.show', $rec->id) }}" class="badge bg-blue-lt fw-bold font-monospace text-decoration-none">
                                        {{ $rec->reference_no }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ Str::limit($rec->title, 35) }}</div>
                                    <div class="text-muted small">{{ $rec->transaction_date->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark">{{ $rec->category->name ?? 'Umum' }}</span>
                                </td>
                                <td class="text-end fw-bold text-dark num-currency">
                                    Rp {{ number_format($rec->grand_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Belum ada transaksi pengeluaran tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection