<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use App\Models\Asesmen;

class ViewServiceProvider extends ServiceProvider
{


public function boot(): void
{
    View::composer('tuk.partials.tuk-sidebar', function ($view) {
        $user = auth()->user();

        if (!$user || !$user->tuk) {
            return;
        }

        $tuk = $user->tuk;

        $stats = [
            'pending_payment' => Asesmen::where('tuk_id', $tuk->id)
                ->where('is_collective', true)
                ->where(function($q) {
                    $q->where(function($subq) {
                        // Before timing: verified but not paid
                        $subq->where('payment_phases', 'single')
                            ->where('status', 'verified');
                    })->orWhere(function($subq) {
                        // After timing: assessed but not paid
                        $subq->where('payment_phases', 'two_phase')
                            ->whereIn('status', ['assessed', 'certified']);
                    });
                })
                ->whereDoesntHave('payment', fn ($q) => $q->where('status', 'verified'))
                ->distinct('collective_batch_id')
                ->count(),

            'pending_verification' => Asesmen::where('tuk_id', $tuk->id)
                ->where('status', 'data_completed')
                ->whereNull('tuk_verified_at')
                ->count(),
        ];

        $view->with('stats', $stats);
    });

}

}