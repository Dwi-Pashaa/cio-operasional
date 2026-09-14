@extends('layouts.auth')

@section('title', 'Buat Password Baru')

@section('content')
    <div class="text-center mb-4">
        <div class="avatar avatar-md bg-success-subtle text-success rounded-circle mb-3 mx-auto" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
        </div>
        <h2 class="card-title fw-bold">Buat Password Baru</h2>
        <p class="card-subtitle text-muted">
            Silakan masukkan password baru untuk akun <strong>{{ $email }}</strong>
        </p>
    </div>

    <form action="{{ route('password.update') }}" method="POST" autocomplete="off" novalidate id="form-reset-password">
        @csrf
        <!-- Password Baru -->
        <div class="mb-3">
            <label class="form-label fw-semibold" for="password">
                Password Baru <span class="text-danger">*</span>
            </label>
            <div class="input-group input-group-flat">
                <input type="password" name="password" id="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       placeholder="Minimal 8 karakter" 
                       required autofocus autocomplete="new-password" />
                <span class="input-group-text bg-white">
                    <a href="javascript:void(0)" class="link-secondary" id="toggle-password" title="Tampilkan password">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                    </a>
                </span>
            </div>
            @error('password')
                <span class="invalid-feedback d-block">
                    {{ $message }}
                </span>
            @enderror
            <div class="form-text text-muted small mt-1">
                Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal.
            </div>
        </div>

        <!-- Konfirmasi Password Baru -->
        <div class="mb-4">
            <label class="form-label fw-semibold" for="password_confirmation">
                Konfirmasi Password Baru <span class="text-danger">*</span>
            </label>
            <div class="input-group input-group-flat">
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="form-control" 
                       placeholder="Ulangi password baru" 
                       required autocomplete="new-password" />
                <span class="input-group-text bg-white">
                    <a href="javascript:void(0)" class="link-secondary" id="toggle-password-conf" title="Tampilkan password">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                    </a>
                </span>
            </div>
        </div>

        <div class="form-footer mb-3">
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                <span>Simpan Password Baru</span>
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="btn btn-link text-muted small d-inline-flex align-items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                <span>Batalkan & Kembali ke Login</span>
            </a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupToggle(btnId, inputId) {
                const btn = document.getElementById(btnId);
                const input = document.getElementById(inputId);
                if (btn && input) {
                    btn.addEventListener('click', function() {
                        const isPassword = input.type === 'password';
                        input.type = isPassword ? 'text' : 'password';
                    });
                }
            }
            setupToggle('toggle-password', 'password');
            setupToggle('toggle-password-conf', 'password_confirmation');
        });
    </script>
@endsection
