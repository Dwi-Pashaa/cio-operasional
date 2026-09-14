@extends('layouts.auth')

@section('title', 'Verifikasi OTP')

@push('css')
<style>
    .otp-input {
        letter-spacing: 12px;
        font-size: 28px;
        font-weight: 700;
        text-align: center;
        padding-left: 20px;
    }
    .otp-channel-badge {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.85rem;
    }
    .wa-switch-card {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 14px;
        transition: all 0.2s ease;
    }
    .wa-switch-card:hover {
        background: #ecfdf5;
        border-color: #86efac;
    }
</style>
@endpush

@section('content')
    <div class="text-center mb-4">
        <div class="avatar avatar-md bg-blue-subtle text-primary rounded-circle mb-3 mx-auto" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" /><path d="M9 11l3 3l6 -6" /></svg>
        </div>
        <h2 class="card-title fw-bold">Verifikasi Kode OTP</h2>
        <p class="card-subtitle text-muted mb-2">
            Masukkan 6 digit kode verifikasi yang telah dikirimkan ke:
        </p>
        <div class="otp-channel-badge text-dark font-monospace fw-semibold d-inline-block">
            {{ $sentTo }}
        </div>
    </div>

    <form action="{{ route('password.verify') }}" method="POST" autocomplete="off" novalidate id="form-verify-otp">
        @csrf
        <div class="mb-4">
            <label class="form-label text-center d-block fw-semibold" for="otp">
                Kode OTP (6 Digit Angka)
            </label>
            <div class="input-icon">
                <input type="text" name="otp" id="otp" 
                       class="form-control font-monospace otp-input @error('otp') is-invalid @enderror" 
                       placeholder="••••••" 
                       maxlength="6" 
                       inputmode="numeric" 
                       pattern="[0-9]*" 
                       value="{{ old('otp') }}" 
                       required autofocus autocomplete="one-time-code" />
            </div>
            @error('otp')
                <span class="invalid-feedback d-block text-center mt-2">
                    {{ $message }}
                </span>
            @enderror
            <div class="form-text text-center text-muted small mt-2">
                Kode OTP berlaku selama <strong>10 menit</strong>.
            </div>
        </div>

        <div class="form-footer mb-3">
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                <span>Verifikasi OTP</span>
            </button>
        </div>
    </form>

    <!-- Opsi Kirim ke WhatsApp jika WhatsApp Aktif di Pengaturan Sistem -->
    @if (!empty($canSendWhatsApp))
        <div class="wa-switch-card mb-3">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="avatar rounded-3 bg-success text-white flex-shrink-0" style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" /><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" /></svg>
                    </div>
                    <div>
                        <div class="fw-bold text-dark small" style="line-height: 1.2;">Email lambat masuk?</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Kirim ke WhatsApp ({{ $maskedPhone }})</div>
                    </div>
                </div>
                <form action="{{ route('password.send.channel') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="channel" value="whatsapp">
                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3 py-1.5 rounded-2 d-inline-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" /><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" /></svg>
                        <span>Kirim ke WA</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Resend OTP Form -->
    <div class="card p-3 bg-light border-0 rounded-3 text-center mb-3">
        <div class="small text-muted mb-2">Tidak menerima kode verifikasi?</div>
        <form action="{{ route('password.resend') }}" method="POST" id="form-resend">
            @csrf
            <button type="submit" id="btn-resend" class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-semibold" {{ $cooldown > 0 ? 'disabled' : '' }}>
                <span id="resend-spinner" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                <span id="resend-text">Kirim Ulang Kode</span>
                <span id="countdown-wrap" class="{{ $cooldown > 0 ? '' : 'd-none' }}">(<span id="countdown-timer">{{ $cooldown }}</span>s)</span>
            </button>
        </form>
    </div>

    <div class="text-center">
        <a href="{{ route('password.request') }}" class="btn btn-link text-muted small d-inline-flex align-items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
            <span>Ubah Username / Email</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.addEventListener('input', function() {
                    // Hanya izinkan angka
                    this.value = this.value.replace(/\D/g, '');
                    // Auto submit jika 6 digit
                    if (this.value.length === 6) {
                        document.getElementById('form-verify-otp').submit();
                    }
                });
            }

            // Countdown Timer Resend
            let remaining = {{ $cooldown }};
            const btnResend = document.getElementById('btn-resend');
            const countdownWrap = document.getElementById('countdown-wrap');
            const countdownTimer = document.getElementById('countdown-timer');

            if (remaining > 0) {
                const interval = setInterval(() => {
                    remaining--;
                    if (countdownTimer) countdownTimer.textContent = remaining;

                    if (remaining <= 0) {
                        clearInterval(interval);
                        if (btnResend) btnResend.disabled = false;
                        if (countdownWrap) countdownWrap.classList.add('d-none');
                    }
                }, 1000);
            }
        });
    </script>
@endsection
