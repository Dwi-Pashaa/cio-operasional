@extends('layouts.app')

@section('title', 'Tambah Pengeluaran Operasional')

@push('css')
<style>
    /* Wizard Step Indicator */
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 2rem;
    }
    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 24px;
        left: 6%;
        right: 6%;
        height: 3px;
        background: #e2e8f0;
        z-index: 1;
    }
    .wizard-step-progress-bar {
        position: absolute;
        top: 24px;
        left: 6%;
        height: 3px;
        background: #206bc4;
        z-index: 2;
        transition: width 0.35s ease;
        width: 0%;
    }
    .wizard-step-item {
        position: relative;
        z-index: 3;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        cursor: default;
    }
    .wizard-step-item.completed {
        cursor: pointer;
    }
    .wizard-step-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #cbd5e1;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }
    .wizard-step-item.active .wizard-step-circle {
        border-color: #206bc4;
        background: #206bc4;
        color: #ffffff;
        box-shadow: 0 0 0 5px rgba(32, 107, 196, 0.2);
        transform: scale(1.08);
    }
    .wizard-step-item.completed .wizard-step-circle {
        border-color: #2fb344;
        background: #2fb344;
        color: #ffffff;
    }
    .wizard-step-label {
        margin-top: 0.6rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
    }
    .wizard-step-item.active .wizard-step-label {
        color: #1e293b;
        font-weight: 700;
    }
    .wizard-step-item.completed .wizard-step-label {
        color: #2fb344;
    }
    .wizard-step-desc {
        font-size: 0.75rem;
        color: #94a3b8;
        display: none;
    }
    @media (min-width: 768px) {
        .wizard-step-desc {
            display: block;
        }
    }

    /* Balance Selection Cards */
    .balance-card-radio {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px;
        cursor: pointer;
        transition: all 0.25s ease-in-out;
        background: #ffffff;
    }
    .balance-card-radio:hover {
        border-color: #94a3b8;
        background-color: #f8fafc;
    }
    .balance-card-radio.active {
        border-color: #206bc4;
        background-color: #f0f6ff;
        box-shadow: 0 4px 14px rgba(32, 107, 196, 0.12);
    }
    .balance-card-radio.disabled-channel {
        background-color: #f8fafc !important;
        border: 2px dashed #cbd5e1 !important;
        opacity: 0.7;
        cursor: not-allowed !important;
    }
    .balance-card-radio.disabled-channel:hover {
        border-color: #cbd5e1 !important;
        background-color: #f8fafc !important;
    }
    .balance-card-radio input[type="radio"] {
        display: none;
    }
    .num-currency {
        font-variant-numeric: tabular-nums;
        font-feature-settings: "tnum";
    }

    /* Animation Step Transition */
    .wizard-pane {
        display: none;
    }
    .wizard-pane.active {
        display: block;
        animation: fadeInPane 0.3s ease-in-out;
    }
    @keyframes fadeInPane {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Select2 customization inside tabler card */
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
@php
    $balanceData = $balanceInfo['data'] ?? [];
    $balManual = (float) ($balanceData['balance_manual'] ?? 0);
    $balXendit = (float) ($balanceData['balance_xendit'] ?? 0);
    $channelStatus = $balanceData['channel_status'] ?? ['manual' => false, 'xendit' => false];
    $isManualActive = (bool) ($channelStatus['manual'] ?? false);
    $isXenditActive = (bool) ($channelStatus['xendit'] ?? false);
    $isApiOk = $balanceInfo['is_connected'] ?? false;
@endphp

<div class="row">
    <div class="col-12 col-xl-11 mx-auto">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" role="alert">
                <strong>Gagal!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(!$isApiOk)
            <div class="alert alert-warning border-0 shadow-sm mb-3 d-flex align-items-center gap-2" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning flex-shrink-0"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v2m0 4v.01" /><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75" /></svg>
                <div>
                    <strong>Peringatan Koneksi Finance:</strong> {{ $balanceInfo['message'] ?? 'API Finance belum terhubung.' }}
                </div>
            </div>
        @endif

        <!-- STEP WIZARD PROGRESS HEADER (4 STEPS) -->
        <div class="wizard-steps my-3">
            <div class="wizard-step-progress-bar" id="wizard-progress-bar"></div>

            <!-- Step 1 Indicator -->
            <div class="wizard-step-item active" id="step-indicator-1" onclick="jumpToStep(1)">
                <div class="wizard-step-circle">
                    <span class="step-num">1</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="step-check d-none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                </div>
                <div class="wizard-step-label">Informasi Transaksi</div>
                <div class="wizard-step-desc">Keperluan & Tanggal</div>
            </div>

            <!-- Step 2 Indicator -->
            <div class="wizard-step-item" id="step-indicator-2" onclick="jumpToStep(2)">
                <div class="wizard-step-circle">
                    <span class="step-num">2</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="step-check d-none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                </div>
                <div class="wizard-step-label">Rincian Barang</div>
                <div class="wizard-step-desc">Item & Harga Satuan</div>
            </div>

            <!-- Step 3 Indicator -->
            <div class="wizard-step-item" id="step-indicator-3" onclick="jumpToStep(3)">
                <div class="wizard-step-circle">
                    <span class="step-num">3</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="step-check d-none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                </div>
                <div class="wizard-step-label">Metode & Saldo</div>
                <div class="wizard-step-desc">Saluran Saldo & Rekening</div>
            </div>

            <!-- Step 4 Indicator (Review Final) -->
            <div class="wizard-step-item" id="step-indicator-4" onclick="jumpToStep(4)">
                <div class="wizard-step-circle">
                    <span class="step-num">4</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="step-check d-none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                </div>
                <div class="wizard-step-label">Review & Simpan</div>
                <div class="wizard-step-desc">Ringkasan & Konfirmasi</div>
            </div>
        </div>

        <!-- MAIN FORM -->
        <form action="{{ route('expense.store') }}" method="POST" enctype="multipart/form-data" id="form-expense">
            @csrf

            <!-- ============================================================= -->
            <!-- PANE 1: INFORMASI TRANSAKSI -->
            <!-- ============================================================= -->
            <div class="card shadow-sm border-0 mb-3 wizard-pane active" id="wizard-pane-1">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-circle p-1" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                            Informasi Pengeluaran
                        </h3>
                        <div class="text-muted small">Lengkapi data keperluan operasional, tanggal transaksi, kategori, dan lampiran</div>
                    </div>
                    <a href="{{ route('expense.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required fw-semibold">Judul / Keperluan Pengeluaran</label>
                            <input type="text" name="title" id="input-title" class="form-control form-control-lg @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Belanja ATK & Kabel LAN Server" required autofocus>
                            <div class="text-muted small mt-1">Sebutkan nama keperluan pengeluaran dengan jelas.</div>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label required fw-semibold">Tanggal Transaksi</label>
                            <input type="date" name="transaction_date" id="input-transaction-date" class="form-control form-control-lg @error('transaction_date') is-invalid @enderror" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                            @error('transaction_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Kategori Pengeluaran</label>
                            <select name="category_id" id="input-category-id" class="form-select select2-init @error('category_id') is-invalid @enderror" data-placeholder="-- Pilih Kategori (Opsional) --">
                                <option value="">-- Pilih Kategori (Opsional) --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan Tambahan</label>
                            <textarea name="notes" id="input-notes" class="form-control" rows="2" placeholder="Catatan opsional untuk pengeluaran ini...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('expense.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-4 fw-semibold" onclick="goToStep(2)">
                        Lanjut ke Rincian Barang
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                    </button>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- PANE 2: RINCIAN BARANG / JASA -->
            <!-- ============================================================= -->
            <div class="card shadow-sm border-0 mb-3 wizard-pane" id="wizard-pane-2">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-circle p-1" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                            Rincian Barang & Biaya Belanja
                        </h3>
                        <div class="text-muted small">Tambahkan item barang yang dibeli (pilih dari katalog atau ketik manual)</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1" id="btn-add-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        Tambah Baris Barang
                    </button>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle" id="table-items">
                            <thead class="bg-light">
                                <tr>
                                    <th style="min-width: 300px;">Pilih dari Katalog / Nama Barang</th>
                                    <th style="width: 110px;" class="text-center">Qty</th>
                                    <th style="width: 110px;" class="text-center">Satuan</th>
                                    <th style="width: 180px;" class="text-end">Harga Satuan (Rp)</th>
                                    <th style="width: 180px;" class="text-end">Subtotal (Rp)</th>
                                    <th style="width: 50px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                <!-- Default row 0 -->
                                <tr class="item-row" data-index="0">
                                    <td>
                                        <div class="mb-1">
                                            <select class="form-select select-catalog-item" data-index="0">
                                                <option value="">-- Pilih dari Katalog Barang (Opsional) --</option>
                                                @foreach ($items as $item)
                                                    <option value="{{ $item->id }}" 
                                                        data-name="{{ $item->name }}" 
                                                        data-unit="{{ $item->unit }}" 
                                                        data-price="{{ $item->default_price }}">
                                                        {{ $item->name }} ({{ $item->unit }}) - Rp {{ number_format($item->default_price, 0, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="hidden" name="items[0][item_id]" class="input-item-id" value="">
                                        <input type="text" name="items[0][item_name]" class="form-control input-item-name mt-1" placeholder="Atau ketik nama barang manual..." required>
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0.01" name="items[0][quantity]" class="form-control input-qty text-center fw-bold" value="1" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][unit]" class="form-control input-unit text-center" value="pcs" required>
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0" name="items[0][unit_price]" class="form-control input-price text-end fw-bold" value="0" required>
                                    </td>
                                    <td class="text-end fw-bold text-dark num-currency row-subtotal fs-6">
                                        Rp 0
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" disabled title="Hapus Baris">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="4" class="text-end fw-bold fs-6">Total Belanja Barang (Subtotal):</th>
                                    <th class="text-end fw-bold fs-5 text-primary num-currency" id="label-subtotal">Rp 0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" onclick="goToStep(1)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19 12l-14 0" /><path d="M11 18l-6 -6" /><path d="M11 6l6 6" /></svg>
                        Kembali ke Informasi
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-4 fw-semibold" onclick="goToStep(3)">
                        Lanjut ke Metode Pembayaran
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                    </button>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- PANE 3: METODE PEMBAYARAN & SALURAN SALDO -->
            <!-- ============================================================= -->
            <div class="card shadow-sm border-0 mb-3 wizard-pane" id="wizard-pane-3">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-circle p-1" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                        Metode Pembayaran & Saluran Saldo
                    </h3>
                    <div class="text-muted small">Pilih saldo yang akan dipotong dan lengkapi rincian rekening tujuan transfer</div>
                </div>

                <div class="card-body p-4">
                    <!-- Pilihan Saluran Saldo -->
                    @php
                        $defaultChannel = $isManualActive ? 'manual' : ($isXenditActive ? 'xendit' : '');
                    @endphp

                    <div class="mb-4">
                        <label class="form-label required fw-bold text-dark mb-2">Pilih Saldo Pembayaran (Terhubung ke Central Finance)</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="balance-card-radio d-block {{ $isManualActive ? ($defaultChannel === 'manual' ? 'active' : '') : 'disabled-channel' }}" id="card-channel-manual">
                                    <input type="radio" name="payment_channel" value="manual" {{ $defaultChannel === 'manual' ? 'checked' : '' }} {{ !$isManualActive ? 'disabled' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold {{ $isManualActive ? 'text-dark' : 'text-muted' }} d-flex align-items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $isManualActive ? 'text-primary' : 'text-muted' }}"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /><path d="M4 10l0 11" /><path d="M20 10l0 11" /><path d="M8 14l0 3" /><path d="M12 14l0 3" /><path d="M16 14l0 3" /></svg>
                                            <span class="fs-5">Saldo Kas / Manual Web</span>
                                        </div>
                                        @if($isManualActive)
                                            <span class="badge bg-primary text-white px-2 py-1">Kas Internal</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold d-inline-flex align-items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="5.7" y1="5.7" x2="18.3" y2="18.3"/></svg>
                                                Nonaktif di Finance
                                            </span>
                                        @endif
                                    </div>
                                    <div class="fs-2 fw-bold {{ $isManualActive ? 'text-primary' : 'text-muted' }} num-currency my-1">
                                        Rp {{ number_format($balManual, 0, ',', '.') }}
                                    </div>
                                    @if($isManualActive)
                                        <div class="small text-muted">Dipakai untuk pengeluaran tunai / operasional harian kantor</div>
                                    @else
                                        <div class="small text-danger fw-semibold d-flex align-items-center gap-1 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4v.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
                                            Saluran saldo ini sedang dinonaktifkan oleh Web Finance (tidak dapat dipilih).
                                        </div>
                                    @endif
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="balance-card-radio d-block {{ $isXenditActive ? ($defaultChannel === 'xendit' ? 'active' : '') : 'disabled-channel' }}" id="card-channel-xendit">
                                    <input type="radio" name="payment_channel" value="xendit" {{ $defaultChannel === 'xendit' ? 'checked' : '' }} {{ !$isXenditActive ? 'disabled' : '' }}>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold {{ $isXenditActive ? 'text-dark' : 'text-muted' }} d-flex align-items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $isXenditActive ? 'text-success' : 'text-muted' }}"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" /></svg>
                                            <span class="fs-5">Saldo Xendit (Payment Gateway)</span>
                                        </div>
                                        @if($isXenditActive)
                                            <span class="badge bg-success text-white px-2 py-1">Disbursement Online</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fw-bold d-inline-flex align-items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><line x1="5.7" y1="5.7" x2="18.3" y2="18.3"/></svg>
                                                Nonaktif di Finance
                                            </span>
                                        @endif
                                    </div>
                                    <div class="fs-2 fw-bold {{ $isXenditActive ? 'text-success' : 'text-muted' }} num-currency my-1">
                                        Rp {{ number_format($balXendit, 0, ',', '.') }}
                                    </div>
                                    @if($isXenditActive)
                                        <div class="small text-muted">Dipakai untuk transfer online antar bank / disbursement vendor</div>
                                    @else
                                        <div class="small text-danger fw-semibold d-flex align-items-center gap-1 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4v.01"/><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/></svg>
                                            Saluran saldo ini sedang dinonaktifkan oleh Web Finance (tidak dapat dipilih).
                                        </div>
                                    @endif
                                </label>
                            </div>
                        </div>

                        @if(!$isManualActive && !$isXenditActive)
                            <div class="alert alert-danger border-0 shadow-sm mt-3 d-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-danger flex-shrink-0"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <div>
                                    <strong>Semua Saluran Saldo Dinonaktifkan:</strong> Kedua saluran saldo (Saldo Kas Manual & Saldo Xendit) sedang dinonaktifkan oleh Administrator Web Finance. Anda belum dapat mencatat transaksi baru saat ini.
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- FORM KHUSUS XENDIT DISBURSEMENT (Kondisional) -->
                    <div id="section-xendit-details" class="p-3 mb-4 rounded-3 border bg-light-subtle" style="display: none;">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-success text-white p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" /></svg>
                            </span>
                            <h4 class="mb-0 fw-bold text-dark">Data Rekening Tujuan Transfer (Xendit)</h4>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label required fw-semibold">Bank / E-Wallet Tujuan Transfer</label>
                                <select name="bank_code" id="select-bank-code" class="form-select select2-init" data-placeholder="-- Pilih Bank / E-Wallet Tujuan --">
                                    <option value="">-- Pilih Bank / E-Wallet Tujuan --</option>
                                    @foreach ($bankList as $groupName => $banks)
                                        <optgroup label="{{ $groupName }}">
                                            @foreach ($banks as $b)
                                                <option value="{{ $b['code'] }}" data-bankname="{{ $b['name'] }}">
                                                    {{ $b['name'] }} ({{ $b['code'] }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <input type="hidden" name="bank_name" id="input-bank-name" value="">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label required fw-semibold">Nomor Rekening / No E-Wallet</label>
                                <input type="text" name="account_number" id="input-account-number" class="form-control" placeholder="Contoh: 1234567890">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Nama Pemilik Rekening / Vendor</label>
                                <input type="text" name="account_holder_name" id="input-account-holder" class="form-control" placeholder="Nama pemilik rekening">
                            </div>
                        </div>
                    </div>

                    <!-- FORM VENDOR & BUKTI NOTA (Khusus Kas Manual) -->
                    <div id="section-vendor-manual" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Toko / Vendor / Supplier <span class="text-muted fw-normal">(Opsional)</span></label>
                                <input type="text" name="vendor_name" id="input-vendor-name" class="form-control" value="{{ old('vendor_name') }}" placeholder="Contoh: CV Media Prima Komputer">
                                <div class="text-muted small mt-1">Nama toko/vendor tempat pembelian barang dilakukan.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Upload Bukti Nota / Kuitansi <span class="text-muted fw-normal">(Foto/PDF max 5MB)</span></label>
                                <input type="file" name="attachment" id="input-attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                <div class="text-muted small mt-1">Lampirkan foto nota, kuitansi fisik, atau PDF pembelian.</div>
                                @error('attachment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Opsi Biaya Admin -->
                    <div class="mb-3">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="form-check form-switch mb-0 cursor-pointer">
                                <input class="form-check-input" type="checkbox" name="has_admin_fee" id="switch-admin-fee" value="1">
                                <label class="form-check-label fw-bold text-dark" for="switch-admin-fee">
                                    Apakah ada biaya admin transfer / transaksi? (Contoh: Biaya transfer antar bank Rp 2.500)
                                </label>
                            </div>
                            
                            <div id="container-admin-fee" style="display: none;" class="mt-3 pt-2 border-top">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold mb-0">Nominal Biaya Admin (Rp):</label>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white fw-bold">Rp</span>
                                            <input type="number" step="any" min="0" name="admin_fee" id="input-admin-fee" class="form-control text-end fw-bold" value="0" placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" onclick="goToStep(2)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19 12l-14 0" /><path d="M11 18l-6 -6" /><path d="M11 6l6 6" /></svg>
                        Kembali ke Rincian Barang
                    </button>
                    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1.5 px-4 fw-semibold" onclick="goToStep(4)">
                        Lanjut ke Review & Konfirmasi
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" /></svg>
                    </button>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- PANE 4: EXECUTIVE REVIEW & KONFIRMASI FINAL -->
            <!-- ============================================================= -->
            <div class="card shadow-sm border-0 mb-3 wizard-pane" id="wizard-pane-4">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-circle p-1" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">4</span>
                        Executive Review & Konfirmasi Final
                    </h3>
                    <div class="text-muted small">Periksa seluruh data pengeluaran dan rincian transaksi sebelum memotong saldo</div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <!-- Card Ringkasan Informasi -->
                        <div class="col-md-6">
                            <div class="card h-100 border shadow-none bg-light-subtle">
                                <div class="card-header bg-white py-2.5">
                                    <h4 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
                                        Informasi Transaksi
                                    </h4>
                                </div>
                                <div class="card-body p-3">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 140px;">Keperluan:</td>
                                            <td class="fw-bold text-dark" id="review-title">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Tanggal:</td>
                                            <td class="fw-semibold text-dark" id="review-date">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Kategori:</td>
                                            <td class="fw-semibold text-dark" id="review-category">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Catatan:</td>
                                            <td class="text-muted" id="review-notes">-</td>
                                        </tr>
                                        <tr id="review-row-receipt">
                                            <td class="text-muted">Bukti Nota:</td>
                                            <td class="fw-semibold text-dark" id="review-receipt">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Card Ringkasan Pembayaran & Bank -->
                        <div class="col-md-6">
                            <div class="card h-100 border shadow-none bg-light-subtle">
                                <div class="card-header bg-white py-2.5">
                                    <h4 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /></svg>
                                        Sumber Dana & Tujuan
                                    </h4>
                                </div>
                                <div class="card-body p-3">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 140px;">Sumber Saldo:</td>
                                            <td class="fw-bold text-primary" id="review-channel-name">Saldo Kas / Manual Web</td>
                                        </tr>
                                        <tr id="review-row-bank" style="display: none;">
                                            <td class="text-muted">Bank Tujuan:</td>
                                            <td class="fw-bold text-dark" id="review-bank-name">-</td>
                                        </tr>
                                        <tr id="review-row-account" style="display: none;">
                                            <td class="text-muted">No Rekening:</td>
                                            <td class="fw-bold text-dark" id="review-account-no">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Vendor / Penerima:</td>
                                            <td class="fw-bold text-dark" id="review-vendor">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Tabel Rincian Barang -->
                    <div class="card border shadow-none mb-4">
                        <div class="card-header bg-light py-2.5">
                            <h4 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M12 19a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M17 17h-11v-14h-2" /><path d="M6 5l14 1l-1 7h-13" /></svg>
                                Rincian Barang & Biaya Belanja
                            </h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0" id="table-review-items">
                                    <thead class="bg-white">
                                        <tr>
                                            <th style="width: 40px;" class="text-center">No</th>
                                            <th>Nama Barang / Jasa</th>
                                            <th style="width: 100px;" class="text-center">Qty</th>
                                            <th style="width: 100px;" class="text-center">Satuan</th>
                                            <th style="width: 160px;" class="text-end">Harga Satuan</th>
                                            <th style="width: 180px;" class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="review-items-tbody">
                                        <!-- Populated via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- CARD KALKULASI FINANSIAL -->
                    <div class="card border-primary-subtle shadow-none" style="background: #f8faff;">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-3">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Subtotal Belanja Barang:</span>
                                        <strong class="text-dark num-currency" id="review-subtotal">Rp 0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Biaya Admin / Transfer:</span>
                                        <strong class="text-dark num-currency" id="review-admin-fee">Rp 0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Sisa Saldo Terkini (<span id="review-current-bal-channel">Kas</span>):</span>
                                        <strong class="text-muted num-currency" id="review-current-bal">Rp 0</strong>
                                    </div>
                                </div>

                                <div class="col-md-6 text-md-end">
                                    <div class="text-uppercase fw-bold text-muted small">Total Pemotongan Saldo:</div>
                                    <div class="fs-1 fw-bold text-primary num-currency my-1" id="review-grand-total">Rp 0</div>
                                    <div id="balance-warning-step4" class="text-danger fw-bold small mt-1" style="display: none;">
                                        ⚠️ Saldo tidak mencukupi untuk transaksi ini!
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light py-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5" onclick="goToStep(3)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M19 12l-14 0" /><path d="M11 18l-6 -6" /><path d="M11 6l6 6" /></svg>
                        Kembali ke Metode Pembayaran
                    </button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4 py-2 d-inline-flex align-items-center gap-1.5 shadow-sm" id="btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        Simpan & Potong Saldo Sekarang
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Template baris item barang untuk ditambahkan via JS -->
<template id="row-template">
    <tr class="item-row" data-index="{INDEX}">
        <td>
            <div class="mb-1">
                <select class="form-select select-catalog-item" data-index="{INDEX}">
                    <option value="">-- Pilih dari Katalog Barang (Opsional) --</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" 
                            data-name="{{ $item->name }}" 
                            data-unit="{{ $item->unit }}" 
                            data-price="{{ $item->default_price }}">
                            {{ $item->name }} ({{ $item->unit }}) - Rp {{ number_format($item->default_price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="items[{INDEX}][item_id]" class="input-item-id" value="">
            <input type="text" name="items[{INDEX}][item_name]" class="form-control input-item-name mt-1" placeholder="Atau ketik nama barang manual..." required>
        </td>
        <td>
            <input type="number" step="any" min="0.01" name="items[{INDEX}][quantity]" class="form-control input-qty text-center fw-bold" value="1" required>
        </td>
        <td>
            <input type="text" name="items[{INDEX}][unit]" class="form-control input-unit text-center" value="pcs" required>
        </td>
        <td>
            <input type="number" step="any" min="0" name="items[{INDEX}][unit_price]" class="form-control input-price text-end fw-bold" value="0" required>
        </td>
        <td class="text-end fw-bold text-dark num-currency row-subtotal fs-6">
            Rp 0
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus Baris">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let currentStep = 1;
let rowIndex = 1;
const balanceManual = {{ $balManual }};
const balanceXendit = {{ $balXendit }};

function formatRupiah(number) {
    const num = Math.round(Number(number) || 0);
    return "Rp " + new Intl.NumberFormat("id-ID").format(num);
}

function updateProgressBar(step) {
    const progressBar = document.getElementById('wizard-progress-bar');
    if (!progressBar) return;

    if (step === 1) {
        progressBar.style.width = '0%';
    } else if (step === 2) {
        progressBar.style.width = '33%';
    } else if (step === 3) {
        progressBar.style.width = '66%';
    } else if (step === 4) {
        progressBar.style.width = '100%';
    }

    for (let i = 1; i <= 4; i++) {
        const item = document.getElementById(`step-indicator-${i}`);
        if (!item) continue;
        const num = item.querySelector('.step-num');
        const check = item.querySelector('.step-check');

        item.classList.remove('active', 'completed');
        if (i < step) {
            item.classList.add('completed');
            if (num) num.classList.add('d-none');
            if (check) check.classList.remove('d-none');
        } else if (i === step) {
            item.classList.add('active');
            if (num) num.classList.remove('d-none');
            if (check) check.classList.add('d-none');
        } else {
            if (num) num.classList.remove('d-none');
            if (check) check.classList.add('d-none');
        }
    }
}

function recalculateTotals() {
    let subtotal = 0;
    $('#items-container .item-row').each(function() {
        const qty = parseFloat($(this).find('.input-qty').val()) || 0;
        const price = parseFloat($(this).find('.input-price').val()) || 0;
        const rowTotal = qty * price;
        $(this).find('.row-subtotal').text(formatRupiah(rowTotal));
        subtotal += rowTotal;
    });

    $('#label-subtotal').text(formatRupiah(subtotal));
    $('#review-subtotal').text(formatRupiah(subtotal));

    const switchAdminFee = document.getElementById('switch-admin-fee');
    const inputAdminFee = document.getElementById('input-admin-fee');
    const adminFee = (switchAdminFee && switchAdminFee.checked) ? (parseFloat(inputAdminFee ? inputAdminFee.value : 0) || 0) : 0;
    const grandTotal = subtotal + adminFee;

    $('#review-admin-fee').text(formatRupiah(adminFee));
    $('#review-grand-total').text(formatRupiah(grandTotal));

    // Balance check
    const radioXendit = document.querySelector('#card-channel-xendit input[type="radio"]');
    const isXendit = radioXendit ? radioXendit.checked : false;
    const currentBal = isXendit ? balanceXendit : balanceManual;

    $('#review-current-bal-channel').text(isXendit ? 'Xendit' : 'Kas/Manual');
    $('#review-current-bal').text(formatRupiah(currentBal));

    const warningBox = document.getElementById('balance-warning-step4');
    const submitBtn = document.getElementById('btn-submit');

    if (grandTotal > currentBal && grandTotal > 0) {
        if (warningBox) {
            warningBox.style.display = 'block';
            warningBox.textContent = `⚠️ Saldo ${isXendit ? 'Xendit' : 'Kas'} tidak mencukupi! Sisa saldo: ${formatRupiah(currentBal)}, Dibutuhkan: ${formatRupiah(grandTotal)}`;
        }
        if (submitBtn) submitBtn.classList.add('disabled');
    } else {
        if (warningBox) warningBox.style.display = 'none';
        if (submitBtn) submitBtn.classList.remove('disabled');
    }
}

function updateRemoveButtons() {
    const rows = $('#items-container .item-row');
    rows.each(function() {
        $(this).find('.btn-remove-row').prop('disabled', rows.length <= 1);
    });
}

function validateStep(step) {
    if (step === 1) {
        const title = document.getElementById('input-title');
        const date = document.getElementById('input-transaction-date');

        if (!title || !title.value.trim()) {
            if (title) title.focus();
            Swal.fire({
                icon: 'warning',
                title: 'Judul Wajib Diisi',
                text: 'Silakan masukkan judul atau keperluan pengeluaran terlebih dahulu.',
                confirmButtonColor: '#206bc4'
            });
            return false;
        }

        if (!date || !date.value) {
            if (date) date.focus();
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Wajib Diisi',
                text: 'Silakan pilih tanggal transaksi pengeluaran.',
                confirmButtonColor: '#206bc4'
            });
            return false;
        }
        return true;
    }

    if (step === 2) {
        let hasValidItem = false;
        $('#items-container .item-row').each(function() {
            const name = $(this).find('.input-item-name').val().trim();
            const qty = parseFloat($(this).find('.input-qty').val()) || 0;
            const price = parseFloat($(this).find('.input-price').val()) || 0;

            if (name && qty > 0 && price >= 0) {
                hasValidItem = true;
            }
        });

        if (!hasValidItem) {
            Swal.fire({
                icon: 'warning',
                title: 'Rincian Barang Belum Lengkap',
                text: 'Pastikan minimal ada 1 barang dengan nama dan harga yang valid.',
                confirmButtonColor: '#206bc4'
            });
            return false;
        }
        return true;
    }

    if (step === 3) {
        const selectedRadio = document.querySelector('input[name="payment_channel"]:checked:not(:disabled)');
        if (!selectedRadio) {
            Swal.fire({
                icon: 'error',
                title: 'Saluran Saldo Tidak Dapat Dipilih',
                text: 'Silakan pilih saluran saldo yang aktif untuk melanjutkan transaksi.',
                confirmButtonColor: '#206bc4'
            });
            return false;
        }

        const isXendit = (selectedRadio.value === 'xendit');
        if (isXendit) {
            const bankCode = $('#select-bank-code').val();
            const accountNo = ($('#input-account-number').val() || '').trim();

            if (!bankCode) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Bank Tujuan',
                    text: 'Silakan pilih Bank / E-Wallet tujuan transfer untuk pembayaran Xendit.',
                    confirmButtonColor: '#206bc4'
                });
                return false;
            }

            if (!accountNo) {
                $('#input-account-number').focus();
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor Rekening Wajib Diisi',
                    text: 'Silakan masukkan nomor rekening atau nomor e-wallet penerima.',
                    confirmButtonColor: '#206bc4'
                });
                return false;
            }
        }
        return true;
    }

    return true;
}

function goToStep(step) {
    if (step > currentStep) {
        for (let i = currentStep; i < step; i++) {
            if (!validateStep(i)) {
                return;
            }
        }
    }

    $('.wizard-pane').removeClass('active');
    $(`#wizard-pane-${step}`).addClass('active');

    currentStep = step;
    updateProgressBar(step);
    if (step === 4) {
        renderReviewSummary();
    }
    recalculateTotals();

    window.scrollTo({ top: 100, behavior: 'smooth' });
}

function jumpToStep(step) {
    if (step < currentStep) {
        goToStep(step);
    } else if (step > currentStep) {
        goToStep(step);
    }
}

function renderReviewSummary() {
    const titleVal = $('#input-title').val() || '-';
    const dateVal = $('#input-transaction-date').val() || '-';
    const categorySelect = document.getElementById('input-category-id');
    const categoryText = (categorySelect && categorySelect.selectedIndex > 0) ? categorySelect.options[categorySelect.selectedIndex].text : 'Tanpa Kategori';
    const notesVal = $('#input-notes').val() || '-';

    const radioXendit = document.querySelector('#card-channel-xendit input[type="radio"]:checked');
    const isXendit = !!radioXendit;
    const vendorVal = $('#input-vendor-name').val() || (isXendit ? ($('#input-account-holder').val() || 'Tidak Disebutkan') : 'Tidak Disebutkan');

    $('#review-title').text(titleVal);
    $('#review-date').text(dateVal);
    $('#review-category').text(categoryText);
    $('#review-notes').text(notesVal);

    $('#review-channel-name').text(isXendit ? 'Saldo Xendit (Payment Gateway)' : 'Saldo Kas / Manual Web');
    $('#review-vendor').text(vendorVal);

    if (isXendit) {
        $('#review-row-bank').show();
        $('#review-row-account').show();

        const bankSelect = document.getElementById('select-bank-code');
        const bankName = (bankSelect && bankSelect.selectedIndex > 0) ? (bankSelect.options[bankSelect.selectedIndex].getAttribute('data-bankname') || bankSelect.options[bankSelect.selectedIndex].text) : '-';
        const accountNo = $('#input-account-number').val() || '-';
        const accountHolder = $('#input-account-holder').val();

        $('#review-bank-name').text(bankName);
        $('#review-account-no').text(accountNo + (accountHolder ? ` (a.n ${accountHolder})` : ''));
        $('#review-receipt').text('Otomatis via Payout Xendit');
    } else {
        $('#review-row-bank').hide();
        $('#review-row-account').hide();

        const fileInput = document.getElementById('input-attachment');
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            $('#review-receipt').text('📎 ' + fileInput.files[0].name);
        } else {
            $('#review-receipt').text('Tidak Dilampirkan');
        }
    }

    // Build Table Items
    const tbody = document.getElementById('review-items-tbody');
    if (tbody) {
        tbody.innerHTML = '';
        let no = 1;
        $('#items-container .item-row').each(function() {
            const name = $(this).find('.input-item-name').val().trim() || `Barang #${no}`;
            const qty = parseFloat($(this).find('.input-qty').val()) || 0;
            const unit = $(this).find('.input-unit').val() || 'pcs';
            const price = parseFloat($(this).find('.input-price').val()) || 0;
            const rowTotal = qty * price;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center fw-bold text-muted">${no}</td>
                <td class="fw-bold text-dark">${name}</td>
                <td class="text-center">${qty}</td>
                <td class="text-center text-muted">${unit}</td>
                <td class="text-end num-currency">${formatRupiah(price)}</td>
                <td class="text-end fw-bold text-dark num-currency">${formatRupiah(rowTotal)}</td>
            `;
            tbody.appendChild(tr);
            no++;
        });
    }

    recalculateTotals();
}

function initSelect2() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2-init').each(function() {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || 'Pilih...'
                });
            }
        });

        // Catalog Select2
        $('.select-catalog-item').each(function() {
            if (!$(this).hasClass("select2-hidden-accessible")) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: '-- Pilih dari Katalog Barang (Opsional) --'
                });
            }
        });
    }
}

$(document).ready(function() {
    initSelect2();

    // Initial channel UI visibility setup
    const initialRadio = document.querySelector('input[name="payment_channel"]:checked:not(:disabled)');
    if (initialRadio && initialRadio.value === 'xendit') {
        $('#section-xendit-details').show();
        $('#section-vendor-manual').hide();
    } else {
        $('#section-xendit-details').hide();
        $('#section-vendor-manual').show();
    }

    // Event Delegation for row inputs
    $(document).on('input keyup change', '.input-qty, .input-price, .input-item-name', function() {
        recalculateTotals();
    });

    // Event Delegation for Select Catalog Item
    $(document).on('change', '.select-catalog-item', function() {
        const row = $(this).closest('.item-row');
        const selected = $(this).find('option:selected');
        if ($(this).val()) {
            const itemId = $(this).val();
            const itemName = selected.attr('data-name') || selected.data('name') || '';
            const itemUnit = selected.attr('data-unit') || selected.data('unit') || 'pcs';
            const itemPrice = selected.attr('data-price') || selected.data('price') || 0;

            row.find('.input-item-id').val(itemId);
            row.find('.input-item-name').val(itemName);
            row.find('.input-unit').val(itemUnit);
            row.find('.input-price').val(itemPrice);
        }
        recalculateTotals();
    });

    // Event Delegation for Select Bank Code
    $(document).on('change', '#select-bank-code', function() {
        const selected = $(this).find('option:selected');
        const bankName = selected.attr('data-bankname') || selected.data('bankname') || selected.text();
        $('#input-bank-name').val(bankName);
    });

    // Admin Fee Toggle
    $('#switch-admin-fee').on('change', function() {
        if (this.checked) {
            $('#container-admin-fee').slideDown(200);
        } else {
            $('#container-admin-fee').slideUp(200);
            $('#input-admin-fee').val(0);
        }
        recalculateTotals();
    });

    $('#input-admin-fee').on('input keyup change', function() {
        recalculateTotals();
    });

    // Channel Selection Cards with Disabled Protection
    $('#card-channel-manual').on('click', function() {
        if ($(this).hasClass('disabled-channel') || $(this).find('input[type="radio"]').is(':disabled')) {
            Swal.fire({
                icon: 'warning',
                title: 'Saluran Dinonaktifkan',
                text: 'Saluran Saldo Kas / Manual sedang dinonaktifkan oleh Web Finance.',
                confirmButtonColor: '#206bc4'
            });
            return;
        }
        $(this).find('input[type="radio"]').prop('checked', true);
        $(this).addClass('active');
        $('#card-channel-xendit').removeClass('active');
        $('#section-xendit-details').hide();
        $('#section-vendor-manual').show();
        recalculateTotals();
    });

    $('#card-channel-xendit').on('click', function() {
        if ($(this).hasClass('disabled-channel') || $(this).find('input[type="radio"]').is(':disabled')) {
            Swal.fire({
                icon: 'warning',
                title: 'Saluran Dinonaktifkan',
                text: 'Saluran Saldo Xendit sedang dinonaktifkan oleh Web Finance.',
                confirmButtonColor: '#206bc4'
            });
            return;
        }
        $(this).find('input[type="radio"]').prop('checked', true);
        $(this).addClass('active');
        $('#card-channel-manual').removeClass('active');
        $('#section-xendit-details').show();
        $('#section-vendor-manual').hide();
        initSelect2();
        recalculateTotals();
    });

    // Add Row Item
    $('#btn-add-item').on('click', function() {
        const template = document.getElementById('row-template').innerHTML;
        const newHtml = template.replaceAll('{INDEX}', rowIndex);
        $('#items-container').append(newHtml);
        rowIndex++;
        initSelect2();
        updateRemoveButtons();
        recalculateTotals();
    });

    // Remove Row Item
    $(document).on('click', '.btn-remove-row', function() {
        const rows = $('#items-container .item-row');
        if (rows.length > 1) {
            $(this).closest('.item-row').remove();
            updateRemoveButtons();
            recalculateTotals();
        }
    });

    // Submit Loading State
    $('#form-expense').on('submit', function(e) {
        if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
            e.preventDefault();
            return;
        }

        const submitBtn = document.getElementById('btn-submit');
        if (submitBtn) {
            submitBtn.innerHTML = `
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                Memproses & Memotong Saldo...
            `;
            submitBtn.classList.add('disabled');
        }
    });

    updateRemoveButtons();
    recalculateTotals();
    updateProgressBar(1);
});
</script>
@endpush
