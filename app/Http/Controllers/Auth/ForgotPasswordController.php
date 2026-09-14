<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\Setting;
use App\Models\User;
use App\Services\MekariQontakService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    protected MekariQontakService $qontakService;

    public function __construct(MekariQontakService $qontakService)
    {
        $this->qontakService = $qontakService;
    }

    /**
     * Tampilkan form input Username / Email untuk permintaan reset password.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Proses pengiriman kode OTP ke Email dan/atau WhatsApp.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'username_or_email' => 'required|string',
        ], [
            'username_or_email.required' => 'Silakan masukkan Username atau Email Anda.',
        ]);

        $identifier = trim($request->username_or_email);

        // Cari user berdasarkan username atau email
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'username_or_email' => 'Akun dengan Username atau Email tersebut tidak ditemukan.',
            ])->withInput();
        }

        // Cek pengaturan saluran notifikasi
        $setting = Setting::first();
        $channel = $setting?->notification_channel ?? 'both';

        if ($channel === 'none') {
            return back()->with('error', 'Layanan reset password mandiri sedang dinonaktifkan oleh sistem. Silakan hubungi administrator.')->withInput();
        }

        // Validasi ketersediaan kontak user
        $hasEmail = !empty($user->email);
        $hasPhone = !empty($user->phone);

        if ($channel === 'email' && !$hasEmail) {
            return back()->withErrors(['username_or_email' => 'Akun Anda belum memiliki alamat email yang terdaftar.'])->withInput();
        }

        if ($channel === 'whatsapp' && !$hasPhone) {
            return back()->withErrors(['username_or_email' => 'Akun Anda belum memiliki nomor WhatsApp yang terdaftar.'])->withInput();
        }

        if ($channel === 'both' && !$hasEmail && !$hasPhone) {
            return back()->withErrors(['username_or_email' => 'Akun Anda belum memiliki email maupun nomor WhatsApp yang terdaftar.'])->withInput();
        }

        // Generate 1 Kode OTP yang Sama (6 digit angka)
        $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);
        $expiryMinutes = 10;
        $expiresAt = Carbon::now()->addMinutes($expiryMinutes);

        // Simpan ke tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => $token,
                'otp'        => $otpCode,
                'created_at' => Carbon::now(),
                'expires_at' => $expiresAt,
            ]
        );

        $sentChannels = [];

        // Prioritas Pengiriman Default:
        // 1. Kirim ke Email sebagai default jika akun memiliki email dan pengaturan mengizinkan (email / both)
        if (in_array($channel, ['email', 'both']) && $hasEmail) {
            try {
                Mail::to($user->email)->send(new ResetPasswordOtpMail($user, $otpCode, $expiryMinutes));
                $sentChannels[] = 'Email (' . $this->maskEmail($user->email) . ')';
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim OTP Email: ' . $e->getMessage());
            }
        } 
        // 2. Jika pengaturan hanya WhatsApp atau user tidak memiliki email, kirim langsung ke WhatsApp
        elseif (in_array($channel, ['whatsapp', 'both']) && $hasPhone) {
            try {
                $this->qontakService->sendOtpNotification($user, $otpCode);
                $sentChannels[] = 'WhatsApp (' . $this->maskPhone($user->phone) . ')';
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim OTP WhatsApp: ' . $e->getMessage());
            }
        }

        if (empty($sentChannels)) {
            return back()->with('error', 'Gagal mengirimkan kode OTP ke saluran komunikasi Anda. Silakan hubungi admin.')->withInput();
        }

        $sentText = implode(' & ', $sentChannels);

        // Simpan ke session untuk langkah verifikasi
        session([
            'reset_password_email'   => $user->email,
            'reset_password_sent_to' => $sentText,
            'reset_otp_created_at'   => Carbon::now()->timestamp,
        ]);

        return redirect()->route('password.verify.form')
            ->with('success', "Kode OTP berhasil dikirimkan ke {$sentText}. Berlaku selama {$expiryMinutes} menit.");
    }

    /**
     * Tampilkan form input 6-digit kode OTP.
     */
    public function showVerifyOtpForm()
    {
        $email = session('reset_password_email');
        if (!$email) {
            return redirect()->route('password.request')->with('warning', 'Silakan masukkan username/email Anda terlebih dahulu.');
        }

        $user = User::where('email', $email)->first();
        $setting = Setting::first();
        $channel = $setting?->notification_channel ?? 'both';

        // Cek apakah opsi kirim via WhatsApp diizinkan (WA aktif di setting & user punya nomor HP)
        $canSendWhatsApp = in_array($channel, ['whatsapp', 'both']) && !empty($user?->phone);
        $maskedPhone = $canSendWhatsApp ? $this->maskPhone($user->phone) : '';

        $sentTo = session('reset_password_sent_to', 'Email Anda');
        $createdAt = session('reset_otp_created_at', time());
        $cooldown = max(0, 60 - (time() - $createdAt));

        return view('auth.verify-otp', compact('email', 'sentTo', 'cooldown', 'canSendWhatsApp', 'maskedPhone'));
    }

    /**
     * Kirim kode OTP secara khusus melalui saluran alternatif (misal: WhatsApp).
     */
    public function sendChannel(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:whatsapp,email',
        ]);

        $email = session('reset_password_email');
        if (!$email) {
            return redirect()->route('password.request')->with('warning', 'Sesi telah kedaluwarsa. Silakan masukkan username/email Anda.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Data pengguna tidak ditemukan.');
        }

        $setting = Setting::first();
        $systemChannel = $setting?->notification_channel ?? 'both';
        $targetChannel = $request->input('channel');

        // Validasi izin saluran dari pengaturan sistem
        if ($targetChannel === 'whatsapp' && !in_array($systemChannel, ['whatsapp', 'both'])) {
            return back()->with('error', 'Pengiriman via WhatsApp sedang tidak aktif di pengaturan sistem.');
        }

        // Ambil data OTP yang ada atau buat baru jika expired
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        $expiryMinutes = 10;

        if (!$record || empty($record->otp) || ($record->expires_at && Carbon::parse($record->expires_at)->isPast())) {
            $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $token = Str::random(64);
            $expiresAt = Carbon::now()->addMinutes($expiryMinutes);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token'      => $token,
                    'otp'        => $otpCode,
                    'created_at' => Carbon::now(),
                    'expires_at' => $expiresAt,
                ]
            );
        } else {
            $otpCode = $record->otp;
        }

        if ($targetChannel === 'whatsapp') {
            if (empty($user->phone)) {
                return back()->with('error', 'Nomor WhatsApp belum terdaftar pada profil akun Anda.');
            }

            try {
                $waResult = $this->qontakService->sendOtpNotification($user, $otpCode);
                $maskedPhone = $this->maskPhone($user->phone);

                session([
                    'reset_password_sent_to' => 'WhatsApp (' . $maskedPhone . ')',
                    'reset_otp_created_at'   => Carbon::now()->timestamp,
                ]);

                if ($waResult['success'] ?? false) {
                    return back()->with('success', "Kode OTP berhasil dikirimkan ke WhatsApp Anda ({$maskedPhone}).");
                }

                return back()->with('success', "Permintaan kode OTP dikirimkan ke WhatsApp ({$maskedPhone}).");
            } catch (\Throwable $e) {
                Log::error('Gagal kirim OTP WhatsApp: ' . $e->getMessage());
                return back()->with('error', 'Terjadi kendala saat menghubungi gateway WhatsApp: ' . $e->getMessage());
            }
        }

        if ($targetChannel === 'email') {
            try {
                Mail::to($user->email)->send(new ResetPasswordOtpMail($user, $otpCode, $expiryMinutes));
                $maskedEmail = $this->maskEmail($user->email);

                session([
                    'reset_password_sent_to' => 'Email (' . $maskedEmail . ')',
                    'reset_otp_created_at'   => Carbon::now()->timestamp,
                ]);

                return back()->with('success', "Kode OTP berhasil dikirimkan kembali ke Email Anda ({$maskedEmail}).");
            } catch (\Throwable $e) {
                Log::error('Gagal kirim OTP Email: ' . $e->getMessage());
                return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
            }
        }

        return back();
    }

    /**
     * Validasi kode OTP yang diinput oleh user.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Silakan masukkan 6 digit kode OTP.',
            'otp.size'     => 'Kode OTP harus terdiri dari 6 digit angka.',
        ]);

        $email = session('reset_password_email');
        if (!$email) {
            return redirect()->route('password.request')->with('warning', 'Sesi verifikasi telah berakhir. Silakan minta kode OTP baru.');
        }

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || empty($record->otp)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak ditemukan. Silakan minta kode baru.'])->withInput();
        }

        // Cek kedaluwarsa
        if ($record->expires_at && Carbon::parse($record->expires_at)->isPast()) {
            return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa. Silakan klik "Kirim Ulang Kode".'])->withInput();
        }

        // Cek kecocokan OTP
        if (trim($record->otp) !== trim($request->otp)) {
            return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah. Silakan periksa kembali.'])->withInput();
        }

        // OTP Valid -> Buat sesi verifikasi sukses
        session([
            'reset_password_verified' => true,
            'reset_password_token'    => $record->token,
        ]);

        return redirect()->route('password.reset')
            ->with('success', 'Kode OTP berhasil diverifikasi. Silakan buat password baru Anda.');
    }

    /**
     * Kirim ulang kode OTP (Rate limited).
     */
    public function resendOtp(Request $request)
    {
        $email = session('reset_password_email');
        if (!$email) {
            return redirect()->route('password.request')->with('warning', 'Sesi telah kedaluwarsa.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Data pengguna tidak ditemukan.');
        }

        // Cek rate limiting (minimal 60 detik)
        $createdAt = session('reset_otp_created_at', 0);
        if (time() - $createdAt < 60) {
            $remaining = 60 - (time() - $createdAt);
            return back()->with('warning', "Harap tunggu {$remaining} detik sebelum meminta kode OTP kembali.");
        }

        $setting = Setting::first();
        $channel = $setting?->notification_channel ?? 'both';

        $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);
        $expiryMinutes = 10;
        $expiresAt = Carbon::now()->addMinutes($expiryMinutes);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => $token,
                'otp'        => $otpCode,
                'created_at' => Carbon::now(),
                'expires_at' => $expiresAt,
            ]
        );

        $sentChannels = [];

        if (in_array($channel, ['email', 'both']) && !empty($user->email)) {
            try {
                Mail::to($user->email)->send(new ResetPasswordOtpMail($user, $otpCode, $expiryMinutes));
                $sentChannels[] = 'Email (' . $this->maskEmail($user->email) . ')';
            } catch (\Throwable $e) {
                Log::error('Gagal kirim ulang OTP Email: ' . $e->getMessage());
            }
        }

        if (in_array($channel, ['whatsapp', 'both']) && !empty($user->phone)) {
            try {
                $this->qontakService->sendOtpNotification($user, $otpCode);
                $sentChannels[] = 'WhatsApp (' . $this->maskPhone($user->phone) . ')';
            } catch (\Throwable $e) {
                Log::error('Gagal kirim ulang OTP WhatsApp: ' . $e->getMessage());
            }
        }

        $sentText = implode(' & ', $sentChannels);

        session([
            'reset_password_sent_to' => $sentText,
            'reset_otp_created_at'   => Carbon::now()->timestamp,
        ]);

        return back()->with('success', "Kode OTP baru berhasil dikirim ulang ke {$sentText}.");
    }

    /**
     * Tampilkan form pembuatan password baru.
     */
    public function showResetPasswordForm()
    {
        $email = session('reset_password_email');
        $verified = session('reset_password_verified');

        if (!$email || !$verified) {
            return redirect()->route('password.request')->with('warning', 'Silakan verifikasi kode OTP terlebih dahulu.');
        }

        return view('auth.reset-password', compact('email'));
    }

    /**
     * Update password user setelah OTP berhasil diverifikasi.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'Silakan masukkan password baru.',
            'password.min'       => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $email = session('reset_password_email');
        $verified = session('reset_password_verified');
        $sessionToken = session('reset_password_token');

        if (!$email || !$verified) {
            return redirect()->route('password.request')->with('error', 'Sesi reset password tidak sah atau telah berakhir.');
        }

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (!$record || $record->token !== $sessionToken) {
            return redirect()->route('password.request')->with('error', 'Token verifikasi tidak valid.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Pengguna tidak ditemukan.');
        }

        // Update password pengguna
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus token OTP dari basis data
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Bersihkan session
        session()->forget([
            'reset_password_email',
            'reset_password_sent_to',
            'reset_password_verified',
            'reset_password_token',
            'reset_otp_created_at',
        ]);

        return redirect()->route('login')->with('success', 'Password Anda berhasil diperbarui! Silakan masuk menggunakan password baru.');
    }

    // -------------------------------------------------------------------------
    // Helper Masking
    // -------------------------------------------------------------------------

    private function maskEmail(?string $email): string
    {
        if (empty($email)) return '';
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        $len = strlen($name);
        if ($len <= 2) {
            $masked = $name . '***';
        } else {
            $masked = substr($name, 0, 2) . str_repeat('*', max(3, $len - 3)) . substr($name, -1);
        }

        return $masked . '@' . $domain;
    }

    private function maskPhone(?string $phone): string
    {
        if (empty($phone)) return '';
        $len = strlen($phone);
        if ($len <= 4) return '****';
        return substr($phone, 0, 4) . '****' . substr($phone, -3);
    }
}
