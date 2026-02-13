<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class VerifyEmail extends VerifyEmailBase
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email - ' . config('app.name'))
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Terima kasih telah mendaftar di ' . config('app.name') . ' - Sistem Sertifikasi Kompetensi.')
            ->line('Untuk melanjutkan, silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini:')
            ->action('Verifikasi Email Saya', $verificationUrl)
            ->line('Link verifikasi ini akan kadaluarsa dalam 60 menit.')
            ->line('Jika Anda tidak mendaftar, abaikan email ini.')
            ->salutation('Salam hangat, ' . config('app.name'));
    }

    /**
     * ✅ Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60), 
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}