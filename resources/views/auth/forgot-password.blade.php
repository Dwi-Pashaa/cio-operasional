@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
    <div class="text-center mb-4">
        <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle mb-3 mx-auto" style="width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>
        </div>
        <h2 class="card-title fw-bold">Atur Ulang Password</h2>
        <p class="card-subtitle text-muted">
            Masukkan Username atau Email Anda. Kami akan mengirimkan kode verifikasi OTP ke kontak terdaftar Anda.
        </p>
    </div>

    <form action="{{ route('password.email') }}" method="POST" autocomplete="off" novalidate id="form-forgot">
        @csrf
        <div class="mb-4">
            <label class="form-label fw-semibold" for="username_or_email">
                Username atau Alamat Email <span class="text-danger">*</span>
            </label>
            <div class="input-icon">
                <span class="input-icon-addon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                </span>
                <input type="text" name="username_or_email" id="username_or_email" 
                       class="form-control @error('username_or_email') is-invalid @enderror" 
                       placeholder="Contoh: user_cio atau email@domain.com" 
                       value="{{ old('username_or_email') }}" required autofocus autocomplete="off" />
            </div>
            @error('username_or_email')
                <span class="invalid-feedback d-block">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-footer mb-3">
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 14l11 -11" /><path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" /></svg>
                <span>Kirim Kode OTP</span>
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="btn btn-link text-muted small d-inline-flex align-items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                <span>Kembali ke Halaman Login</span>
            </a>
        </div>
    </form>
@endsection
