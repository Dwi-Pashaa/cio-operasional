<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 30px 0 40px 0;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 560px;
            border-spacing: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #334155;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .otp-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 24px 0;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #1e40af;
            margin: 0;
        }
        @media only screen and (max-width: 600px) {
            .content-padding {
                padding: 24px 20px !important;
            }
            .otp-code {
                font-size: 30px !important;
                letter-spacing: 6px !important;
            }
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="main" width="100%">
            <!-- Header -->
            <tr>
                <td style="padding: 32px 40px 20px 40px; text-align: center; background-color: #ffffff; border-bottom: 1px solid #f1f5f9;">
                    <h2 style="margin: 0; color: #1e293b; font-size: 22px; font-weight: 800; letter-spacing: -0.02em;">
                        {{ $appName }}
                    </h2>
                    <p style="margin: 4px 0 0 0; color: #64748b; font-size: 13px;">
                        Layanan Keamanan & Autentikasi Pengguna
                    </p>
                </td>
            </tr>

            <!-- Body -->
            <tr>
                <td class="content-padding" style="padding: 36px 40px;">
                    <p style="margin: 0 0 14px 0; font-size: 16px; line-height: 1.6; color: #1e293b;">
                        Halo, <strong>{{ $user->name ?: $user->username }}</strong>
                    </p>
                    <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                        Kami menerima permintaan untuk mereset password akun <strong>{{ $appName }}</strong> Anda. Silakan gunakan kode OTP (One-Time Password) berikut untuk memverifikasi akun Anda:
                    </p>

                    <!-- OTP Code Box -->
                    <div class="otp-box">
                        <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #2563eb; margin-bottom: 8px;">
                            Kode Verifikasi OTP Anda
                        </div>
                        <div class="otp-code">
                            {{ $otpCode }}
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 10px;">
                            ⏰ Berlaku selama <strong>{{ $expiryMinutes }} menit</strong>
                        </div>
                    </div>

                    <!-- Security Alert -->
                    <table width="100%" style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; margin-top: 20px;">
                        <tr>
                            <td style="padding: 14px 16px;">
                                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #991b1b;">
                                    <strong>PENTING:</strong> Jangan berikan kode OTP ini kepada siapa pun, termasuk pihak {{ $appName }}. Kami tidak pernah meminta kode OTP Anda.
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p style="margin: 24px 0 0 0; font-size: 13px; line-height: 1.6; color: #64748b;">
                        Jika Anda tidak merasa melakukan permintaan reset password, akun Anda tetap aman dan Anda dapat mengabaikan email ini.
                    </p>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="padding: 24px 40px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                    <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                        Email ini dikirimkan secara otomatis oleh sistem keamanan {{ $appName }}.<br>
                        &copy; {{ date('Y') }} {{ $setting->company_name ?? 'CIO Network Solution' }}. Seluruh hak cipta dilindungi.
                    </p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
