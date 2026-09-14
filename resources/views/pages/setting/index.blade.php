@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@push('css')
<style>
    .settings-nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 8px;
        color: #475569;
        font-weight: 500;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        text-decoration: none !important;
        margin-bottom: 0.35rem;
    }
    .settings-nav-link:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .settings-nav-link.active {
        background-color: #eff6ff;
        color: #2563eb;
        font-weight: 600;
        border-color: #bfdbfe;
    }
    .settings-nav-link.active svg {
        color: #2563eb;
    }
    .settings-nav-link svg {
        color: #94a3b8;
        transition: color 0.2s ease;
    }
    .settings-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .input-icon-btn {
        cursor: pointer;
        background: #f8fafc;
        border-left: none;
    }
    .input-icon-btn:hover {
        background: #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="row g-3">
    <!-- LEFT SIDEBAR: TAB NAVIGATION -->
    <div class="col-lg-3 col-md-4">
        <div class="settings-card p-3 mb-3">
            <div class="text-uppercase fw-bold text-muted small px-2 mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                Menu Pengaturan
            </div>
            <div class="nav flex-column nav-pills" id="settings-tabs" role="tablist">
                <a class="settings-nav-link active" id="tab-general-btn" data-bs-toggle="pill" href="#tab-general" role="tab" aria-controls="tab-general" aria-selected="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                    <span>Umum & Kontak</span>
                </a>
                <a class="settings-nav-link" id="tab-xendit-btn" data-bs-toggle="pill" href="#tab-xendit" role="tab" aria-controls="tab-xendit" aria-selected="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" /></svg>
                    <span class="flex-grow-1">Xendit Gateway</span>
                    <span class="badge bg-blue-lt rounded-pill" style="font-size: 0.68rem;">Disbursement</span>
                </a>
                <a class="settings-nav-link" id="tab-finance-btn" data-bs-toggle="pill" href="#tab-finance" role="tab" aria-controls="tab-finance" aria-selected="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /><path d="M4 10l0 11" /><path d="M20 10l0 11" /><path d="M8 14l0 3" /><path d="M12 14l0 3" /><path d="M16 14l0 3" /></svg>
                    <span class="flex-grow-1">Finance Core</span>
                    @if($financeConfigured)
                        <span class="badge bg-green-lt rounded-pill" style="font-size: 0.68rem;">Online</span>
                    @else
                        <span class="badge bg-danger-lt rounded-pill" style="font-size: 0.68rem;">Offline</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- SYSTEM INFO SUMMARY CARD -->
        <div class="settings-card p-3 bg-light-subtle">
            <div class="text-uppercase fw-bold text-muted small mb-2" style="font-size: 0.72rem;">
                Informasi Sistem
            </div>
            <div class="small text-muted mb-1 d-flex justify-content-between">
                <span>Versi Aplikasi:</span>
                <strong class="text-dark">v1.2.0 (Pro)</strong>
            </div>
            <div class="small text-muted mb-1 d-flex justify-content-between">
                <span>Environment:</span>
                <strong class="text-dark">{{ app()->environment() }}</strong>
            </div>
            <div class="small text-muted d-flex justify-content-between">
                <span>Zona Waktu:</span>
                <strong class="text-dark">{{ config('app.timezone', 'Asia/Jakarta') }}</strong>
            </div>
        </div>
    </div>

    <!-- RIGHT CONTENT: TAB PANES -->
    <div class="col-lg-9 col-md-8">
        <div class="tab-content">

            <!-- TAB 1: UMUM & KONTAK -->
            <div class="tab-pane fade show active" id="tab-general" role="tabpanel" aria-labelledby="tab-general-btn">
                <div class="settings-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title fw-bold text-dark mb-0 fs-4">Pengaturan Umum & Notifikasi</h3>
                            <div class="text-muted small">Kelola nomor kontak resmi dan preferensi saluran notifikasi sistem.</div>
                        </div>
                        <span class="badge bg-blue-lt p-1.5 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('setting.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $settings->id ?? 1 }}">
                            <input type="hidden" name="xendit_secret_key" value="{{ $settings->xendit_secret_key ?? '' }}">
                            <input type="hidden" name="xendit_webhook_token" value="{{ $settings->xendit_webhook_token ?? '' }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold text-dark">Nomor WhatsApp / Kontak Layanan</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted fw-semibold">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" /><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" /></svg>
                                        </span>
                                        <input type="text" name="telp" class="form-control @error('telp') is-invalid @enderror" value="{{ old('telp', $settings->telp ?? '') }}" placeholder="Contoh: 6281234567890" required>
                                    </div>
                                    <div class="form-hint text-muted small mt-1">Nomor resmi perusahaan untuk layanan informasi kuitansi & pesan notifikasi.</div>
                                    @error('telp')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold text-dark">Saluran Notifikasi OTP & Notifikasi</label>
                                    <select name="notification_channel" class="form-select @error('notification_channel') is-invalid @enderror" required>
                                        <option value="whatsapp" {{ old('notification_channel', $settings->notification_channel ?? 'whatsapp') === 'whatsapp' ? 'selected' : '' }}>WhatsApp Saja</option>
                                        <option value="email" {{ old('notification_channel', $settings->notification_channel ?? '') === 'email' ? 'selected' : '' }}>Email Saja</option>
                                        <option value="both" {{ old('notification_channel', $settings->notification_channel ?? '') === 'both' ? 'selected' : '' }}>WhatsApp & Email (Keduanya)</option>
                                        <option value="none" {{ old('notification_channel', $settings->notification_channel ?? '') === 'none' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                    <div class="form-hint text-muted small mt-1">Metode pengiriman kode autentikasi & alert pengeluaran.</div>
                                    @error('notification_channel')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                    Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 2: XENDIT PAYMENT GATEWAY -->
            <div class="tab-pane fade" id="tab-xendit" role="tabpanel" aria-labelledby="tab-xendit-btn">
                <div class="settings-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title fw-bold text-dark mb-0 fs-4">Konfigurasi Xendit Disbursement</h3>
                            <div class="text-muted small">Kelola kunci API Xendit dan webhook otomatis untuk transfer dana keluar.</div>
                        </div>
                        <span class="badge bg-blue-lt p-1.5 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" /></svg>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('setting.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $settings->id ?? 1 }}">
                            <input type="hidden" name="telp" value="{{ $settings->telp ?? '6281234567890' }}">
                            <input type="hidden" name="notification_channel" value="{{ $settings->notification_channel ?? 'whatsapp' }}">
                            <input type="hidden" name="admin_fee" value="{{ $settings->admin_fee ?? 0 }}">

                            <!-- WEBHOOK URL COPY BOX -->
                            <div class="p-3 mb-4 rounded-3 border bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold text-dark d-flex align-items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 15l6 -6" /><path d="M11 6l.463 -.536a5 5 0 0 1 7.071 7.072l-.534 .464" /><path d="M13 18l-.397 .534a5.068 5.068 0 0 1 -7.127 0a4.972 4.972 0 0 1 0 -7.071l.524 -.463" /></svg>
                                        Endpoint URL Callback / Webhook Resmi
                                    </div>
                                    <span class="badge bg-green-lt fw-bold">POST Callback Ready</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Salin URL di bawah ini ke <strong>Dashboard Xendit &rarr; Settings &rarr; Callbacks &rarr; Disbursements</strong>:
                                </p>
                                <div class="input-group">
                                    <input type="text" id="webhook-endpoint-url" class="form-control bg-white font-monospace text-primary fw-bold" value="{{ url('/api/xendit/callback') }}" readonly>
                                    <button class="btn btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1" type="button" onclick="copyWebhookUrl()">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 8m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /><path d="M16 8v-2a2 2 0 0 0 -2 -2h-8a2 2 0 0 0 -2 2v8a2 2 0 0 0 2 2h2" /></svg>
                                        Salin URL
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Xendit Secret API Key</label>
                                    <div class="input-group">
                                        <input type="password" name="xendit_secret_key" id="xendit-secret-key" class="form-control font-monospace" value="{{ old('xendit_secret_key', $settings->xendit_secret_key ?? '') }}" placeholder="xnd_development_... atau xnd_production_...">
                                        <button class="btn btn-outline-secondary input-icon-btn" type="button" onclick="togglePasswordVisibility('xendit-secret-key', this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        </button>
                                    </div>
                                    <div class="form-hint text-muted small mt-1">Kunci API rahasia dari akun Xendit Anda untuk otorisasi transfer uang keluar.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Xendit Webhook Verification Token</label>
                                    <div class="input-group">
                                        <input type="password" name="xendit_webhook_token" id="xendit-webhook-token" class="form-control font-monospace" value="{{ old('xendit_webhook_token', $settings->xendit_webhook_token ?? '') }}" placeholder="Token verifikasi callback Xendit">
                                        <button class="btn btn-outline-secondary input-icon-btn" type="button" onclick="togglePasswordVisibility('xendit-webhook-token', this)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        </button>
                                    </div>
                                    <div class="form-hint text-muted small mt-1">Digunakan untuk memvalidasi keaslian signature webhook dari server Xendit.</div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top text-end">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                                    Simpan Kunci Xendit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB 3: CIO FINANCE CENTRAL -->
            <div class="tab-pane fade" id="tab-finance" role="tabpanel" aria-labelledby="tab-finance-btn">
                <div class="settings-card">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title fw-bold text-dark mb-0 fs-4">Integrasi Finance Central (Core)</h3>
                            <div class="text-muted small">Status koneksi sinkronisasi saldo likuiditas terpusat CIO Network Solution.</div>
                        </div>
                        <span class="badge bg-green-lt p-1.5 rounded-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /></svg>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                    <div class="text-muted small fw-bold text-uppercase mb-1">Status Koneksi API</div>
                                    @if($financeConfigured)
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <span class="badge bg-success text-white px-2.5 py-1.5 rounded-pill fw-bold fs-6">
                                                <span class="status-dot status-dot-animated bg-white d-inline-block me-1" style="width: 8px; height: 8px; border-radius: 50%;"></span>
                                                Terhubung & Aktif
                                            </span>
                                        </div>
                                        <div class="small text-muted mt-2">Autentikasi HMAC-SHA256 valid dan siap sinkronisasi saldo.</div>
                                    @else
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <span class="badge bg-danger text-white px-2.5 py-1.5 rounded-pill fw-bold fs-6">
                                                Belum Terhubung
                                            </span>
                                        </div>
                                        <div class="small text-danger mt-2">Kredensial API Finance di berkas <code>.env</code> belum lengkap.</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light-subtle h-100">
                                    <div class="text-muted small fw-bold text-uppercase mb-1">Server Target Base URL</div>
                                    <div class="font-monospace fw-bold text-dark fs-6 mt-2 text-truncate" title="{{ $financeBaseUrl }}">
                                        {{ $financeBaseUrl }}
                                    </div>
                                    <div class="small text-muted mt-2">Dikonfigurasi melalui environment variable <code>CIO_FINANCE_BASE_URL</code>.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-white">
                                    <h4 class="fw-bold text-dark fs-6 mb-2">Protokol Keamanan Terpusat (HMAC-SHA256)</h4>
                                    <p class="text-muted small mb-0">
                                        Sistem operasional ini menggunakan tanda tangan kriptografi <code>X-CIO-SIGNATURE</code>, <code>X-CIO-TIMESTAMP</code>, dan <code>X-CIO-NONCE</code> untuk setiap request mutasi dana, menjamin transaksi tidak dapat dimanipulasi atau di-replay oleh pihak ketiga.
                                    </p>
                                </div>
                            </div>
                        </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" /><path d="M3 3l18 18" /></svg>`;
    } else {
        field.type = 'password';
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>`;
    }
}

function copyWebhookUrl() {
    const input = document.getElementById('webhook-endpoint-url');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'URL Webhook Disalin!',
                text: input.value,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            alert('URL Webhook berhasil disalin: ' + input.value);
        }
    });
}

// Check for flash messages
@if(session('success'))
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false
        });
    }
@endif
</script>
@endpush
