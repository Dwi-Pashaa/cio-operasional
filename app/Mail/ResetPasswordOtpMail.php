<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $otpCode;
    public int $expiryMinutes;
    public ?Setting $setting;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $otpCode, int $expiryMinutes = 10)
    {
        $this->user = $user;
        $this->otpCode = $otpCode;
        $this->expiryMinutes = $expiryMinutes;
        $this->setting = Setting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'CIO Operasional');
        $fromAddress = config('mail.from.address', 'security@cionetworksolution.com');
        $fromName = config('mail.from.name', $appName);

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "Kode OTP Reset Password [{$this->otpCode}] - {$appName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password_otp',
            with: [
                'user'          => $this->user,
                'otpCode'       => $this->otpCode,
                'expiryMinutes' => $this->expiryMinutes,
                'setting'       => $this->setting,
                'appName'       => config('app.name', 'CIO Operasional'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
