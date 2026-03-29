<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Reset Password - LSP System')
                ->greeting('Halo, ' . $notifiable->name . '!')
                ->line('Kami menerima permintaan reset password untuk akun Anda.')
                ->action('Reset Password', url(route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false)))
                ->line('Link ini akan kadaluarsa dalam ' . config('auth.passwords.users.expire') . ' menit.')
                ->line('Jika Anda tidak merasa meminta reset password, abaikan email ini.')
                ->salutation('Salam, ' . config('app.name'));
        });
    }
}