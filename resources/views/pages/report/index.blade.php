@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi Pengeluaran')

@push('css')
<style>
    .num-currency {
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum";
    }
    .fintech-stat-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem 1.25rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .fintech-stat-card:hover {
        transform: translateY(-2px);
        border-color: #cbd5e1;
        box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.08);
    }
    .fintech-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .date-preset-btn {
        font-size: 0.76rem;
        padding: 0.25rem 0.65rem;
        border-radius: 50rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
        line-height: 1.2;
    }
    .date-preset-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .date-preset-btn.active {
        background: #206bc4;
        color: #ffffff;
        border-color: #206bc4;
    }
    @media print {
        .d-print-none, .page-header, .navbar, .sticky-top, footer {
            display: none !important;
        }
        .card {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
        }
        body {
            background: #ffffff !important;
            color: #0f172a !important;
            padding: 10px !important;
        }
        .print-only-header {
            display: block !important;
        }
    }
    .print-only-header {
        display: none;
    }
</style>
@endpush

@section('page_actions')
<div class="d-flex gap-2">
    <button type="button" onclick="window.print()" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
        Cetak Laporan
    </button>
    @can('download excel')
        <a href="{{ route('report.export.csv', request()->query()) }}" class="btn btn-success d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
            Ekspor ke Excel / CSV
        </a>
    @endcan
</div>
@endsection

@section('content')
<!-- Print Header (Only visible on paper print) -->
<div class="print-only-header mb-4">
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
        <div>
            <h2 class="fw-bold mb-0 text-dark">LAPORAN REKAPITULASI PENGELUARAN OPERASIONAL</h2>
            <div class="text-muted small">CIO Network Solution &bull; Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</div>
        </div>
        <div class="text-end">
            <div class="fw-bold text-primary">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</div>
            <div class="text-muted small">Total: {{ count($expenses) }} Transaksi</div>
        </div>
    </div>
</div>

<div class="row g-3">

    <!-- FILTER TOOLBAR -->
    <div class="col-12 d-print-none">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <form action="{{ route('report.index') }}" method="GET" id="report-filter-form">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <div class="fw-bold text-dark d-flex align-items-center gap-2">
                            <span class="badge bg-blue-lt p-1 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.828 4.828a2 2 0 0 0 -.586 1.414v3.172l-4 2v-5.172a2 2 0 0 0 -.586 -1.414l-4.828 -4.828a2 2 0 0 1 -.586 -1.414v-2.172z" /></svg>
                            </span>
                            <span class="fs-4">Filter Periode & Saluran</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="text-muted small fw-medium me-1">Pilihan Cepat:</span>
                            <button type="button" class="date-preset-btn" onclick="applyDatePreset('this_month')">Bulan Ini</button>
                            <button type="button" class="date-preset-btn" onclick="applyDatePreset('last_month')">Bulan Lalu</button>
                            <button type="button" class="date-preset-btn" onclick="applyDatePreset('last_7')">7 Hari</button>
                            <button type="button" class="date-preset-btn" onclick="applyDatePreset('last_30')">30 Hari</button>
                            <button type="button" class="date-preset-btn" onclick="applyDatePreset('this_year')">Tahun Ini</button>
                        </div>
                    </div>

                    <div class="row g-2 align-items-end">
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                            <input type="date" name="start_date" id="filter-start-date" value="{{ $startDate }}" class="form-control" required>
                        </div>

                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="filter-end-date" value="{{ $endDate }}" class="form-control" required>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Kategori Pengeluaran</label>
                            <select name="category_id" class="form-select">
                                <option value="">Semua Kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-6 col-lg-3">
                            <label class="form-label small fw-semibold text-muted mb-1">Saluran Saldo</label>
                            <select name="channel" class="form-select">
                                <option value="">Semua Saluran</option>
                                <option value="manual" {{ $channel === 'manual' ? 'selected' : '' }}>Saldo Kas / Manual</option>
                                <option value="xendit" {{ $channel === 'xendit' ? 'selected' : '' }}>Saldo Xendit (PG)</option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-2">
                            <div class="d-flex gap-1">
                                <button type="submit" class="btn btn-primary flex-grow-1 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" title="Terapkan Filter">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.828 4.828a2 2 0 0 0 -.586 1.414v3.172l-4 2v-5.172a2 2 0 0 0 -.586 -1.414l-4.828 -4.828a2 2 0 0 1 -.586 -1.414v-2.172z" /></svg>
                                    <span>Terapkan</span>
                                </button>
                                <a href="{{ route('report.index') }}" class="btn btn-outline-secondary px-2.5 shadow-sm" title="Reset Filter">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EXECUTIVE KPI SUMMARY CARDS -->
    <div class="col-sm-6 col-lg-3">
        <div class="fintech-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.04em;">Total Belanja Murni</span>
                <div class="fintech-icon-box" style="background-color: #eff6ff; color: #2563eb;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 17h-11v-14h-2" /><path d="M6 5l14 1l-1 7h-13" /></svg>
                </div>
            </div>
            <div class="fs-2 fw-bold text-dark num-currency my-1">
                Rp {{ number_format($totalSubtotal, 0, ',', '.') }}
            </div>
            <div class="small text-muted">Subtotal murni barang/jasa</div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="fintech-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.04em;">Total Biaya Admin</span>
                <div class="fintech-icon-box" style="background-color: #fff7ed; color: #ea580c;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12h1m8 -9v1m8 8h1m-15.4 -6.4l.7 .7m12.1 -.7l-.7 .7" /><path d="M9 16a5 5 0 1 1 6 0a3.5 3.5 0 0 0 -1 3a2 2 0 0 1 -4 0a3.5 3.5 0 0 0 -1 -3" /><path d="M9.7 17l4.6 0" /></svg>
                </div>
            </div>
            <div class="fs-2 fw-bold text-dark num-currency my-1">
                Rp {{ number_format($totalAdminFee, 0, ',', '.') }}
            </div>
            <div class="small text-muted">Akumulasi biaya transfer & admin</div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="fintech-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.04em;">Grand Total Pengeluaran</span>
                <div class="fintech-icon-box" style="background-color: #f5f3ff; color: #7c3aed;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                </div>
            </div>
            <div class="fs-2 fw-bold text-dark num-currency my-1">
                Rp {{ number_format($totalGrand, 0, ',', '.') }}
            </div>
            <div class="small text-muted">{{ $totalCount }} transaksi pada periode ini</div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="fintech-stat-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.04em;">Sebaran Saluran Dana</span>
                <div class="fintech-icon-box" style="background-color: #ecfdf5; color: #059669;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /></svg>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center small mt-1">
                <span class="text-muted">Kas Manual:</span>
                <span class="text-dark fw-bold num-currency">Rp {{ number_format($manualTotal, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="text-muted">Xendit Online:</span>
                <span class="text-dark fw-bold num-currency">Rp {{ number_format($xenditTotal, 0, ',', '.') }}</span>
            </div>
            @php
                $manualPercent = $totalGrand > 0 ? round(($manualTotal / $totalGrand) * 100) : 0;
                $xenditPercent = $totalGrand > 0 ? (100 - $manualPercent) : 0;
            @endphp
            <div class="progress progress-xs mt-1" style="height: 5px;">
                <div class="progress-bar bg-primary" style="width: {{ $manualPercent }}%" title="Manual: {{ $manualPercent }}%"></div>
                <div class="progress-bar bg-success" style="width: {{ $xenditPercent }}%" title="Xendit: {{ $xenditPercent }}%"></div>
            </div>
        </div>
    </div>

    <!-- CATEGORY BREAKDOWN LIST (Visual Pills) -->
    @if(count($categoryBreakdown) > 0)
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-2 d-flex align-items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /><path d="M4 4l0 15" /></svg>
                        Distribusi Pengeluaran Per Kategori
                    </div>
                    <div class="row g-2">
                        @foreach ($categoryBreakdown as $cat)
                            <div class="col-md-4 col-lg-3">
                                <div class="p-2.5 rounded-3 border bg-light-subtle d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-dark text-truncate" title="{{ $cat['name'] }}">{{ $cat['name'] }}</span>
                                        <span class="badge bg-blue-lt fw-bold">{{ $cat['percentage'] }}%</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center small text-muted">
                                        <span>{{ $cat['count'] }} transaksi</span>
                                        <strong class="text-dark num-currency">Rp {{ number_format($cat['total'], 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- TABLE REPORT -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="card-title fw-bold text-dark mb-0">Rincian Transaksi Pengeluaran</h3>
                    <div class="text-muted small">Periode {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</div>
                </div>
                <div class="badge bg-secondary-lt text-dark fs-6 px-3 py-1.5">
                    {{ count($expenses) }} Data Transaksi
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover table-bordered align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 45px;" class="text-center">No</th>
                            <th style="min-width: 150px;">No. Referensi</th>
                            <th style="min-width: 110px;">Tanggal</th>
                            <th style="min-width: 260px;">Judul & Keperluan</th>
                            <th style="min-width: 140px;">Kategori</th>
                            <th style="min-width: 150px;">Vendor / Penerima</th>
                            <th style="min-width: 130px;" class="text-center">Saluran Dana</th>
                            <th style="min-width: 140px;" class="text-end">Subtotal (Rp)</th>
                            <th style="min-width: 130px;" class="text-end">Biaya Admin</th>
                            <th style="min-width: 150px;" class="text-end">Grand Total</th>
                            <th style="width: 80px;" class="text-center d-print-none">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $idx => $row)
                            <tr>
                                <td class="text-muted text-center fw-semibold">{{ $idx + 1 }}</td>
                                <td>
                                    <span class="badge bg-blue-lt fw-bold font-monospace px-2 py-1">{{ $row->reference_no }}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-medium">{{ $row->transaction_date->translatedFormat('d M Y') }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row->title }}</div>
                                    @if($row->notes)
                                        <div class="text-muted small text-truncate" style="max-width: 280px;" title="{{ $row->notes }}">{{ $row->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark border">{{ $row->category->name ?? 'Umum' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $row->vendor_name ?: ($row->account_holder_name ?: '-') }}</div>
                                    @if($row->payment_channel === 'xendit' && ($row->bank_name || $row->bank_code))
                                        <div class="text-muted small">{{ $row->bank_name ?: $row->bank_code }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row->payment_channel === 'xendit')
                                        <span class="badge bg-success text-white px-2 py-1 d-inline-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" /></svg>
                                            Xendit PG
                                        </span>
                                    @else
                                        <span class="badge bg-primary text-white px-2 py-1 d-inline-flex align-items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /></svg>
                                            Kas Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end num-currency">Rp {{ number_format($row->subtotal_amount, 0, ',', '.') }}</td>
                                <td class="text-end num-currency {{ $row->admin_fee > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ $row->admin_fee > 0 ? '+Rp ' . number_format($row->admin_fee, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end fw-bold text-dark num-currency fs-6">
                                    Rp {{ number_format($row->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center d-print-none">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('expense.show', $row->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Lihat Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        </a>
                                        <a href="{{ route('expense.print', $row->id) }}" target="_blank" class="btn btn-sm btn-icon btn-outline-secondary" title="Cetak Voucher PDF">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /><path d="M14 11h-4" /><path d="M12 15h-2" /></svg>
                                        <div class="fw-bold fs-4 text-dark mb-1">Tidak Ada Transaksi Ditemukan</div>
                                        <div class="small text-muted mb-3">Tidak ada catatan pengeluaran operasional pada rentang tanggal atau filter yang dipilih.</div>
                                        <a href="{{ route('report.index') }}" class="btn btn-sm btn-outline-secondary">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($expenses) > 0)
                        <tfoot class="bg-light border-top-2">
                            <tr>
                                <th colspan="7" class="text-end fw-bold text-dark fs-6 py-3">TOTAL KESELURUHAN PENGELUARAN:</th>
                                <th class="text-end fw-bold num-currency text-dark py-3">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</th>
                                <th class="text-end fw-bold num-currency text-danger py-3">+Rp {{ number_format($totalAdminFee, 0, ',', '.') }}</th>
                                <th class="text-end fw-bold num-currency fs-4 text-primary py-3">Rp {{ number_format($totalGrand, 0, ',', '.') }}</th>
                                <th class="d-print-none"></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function applyDatePreset(preset) {
    const today = new Date();
    let startDate, endDate;

    const formatDate = (d) => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (preset === 'this_month') {
        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    } else if (preset === 'last_month') {
        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        endDate = new Date(today.getFullYear(), today.getMonth(), 0);
    } else if (preset === 'last_7') {
        startDate = new Date(today);
        startDate.setDate(today.getDate() - 6);
        endDate = today;
    } else if (preset === 'last_30') {
        startDate = new Date(today);
        startDate.setDate(today.getDate() - 29);
        endDate = today;
    } else if (preset === 'this_year') {
        startDate = new Date(today.getFullYear(), 0, 1);
        endDate = new Date(today.getFullYear(), 11, 31);
    }

    if (startDate && endDate) {
        document.getElementById('filter-start-date').value = formatDate(startDate);
        document.getElementById('filter-end-date').value = formatDate(endDate);
        document.getElementById('report-filter-form').submit();
    }
}
</script>
@endpush
