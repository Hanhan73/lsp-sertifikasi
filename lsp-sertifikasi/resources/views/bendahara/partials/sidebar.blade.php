@php $route = Route::currentRouteName(); @endphp

<a href="{{ route('bendahara.dashboard') }}"
   class="nav-link {{ $route === 'bendahara.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<a href="{{ route('bendahara.payments.index') }}"
   class="nav-link {{ str_starts_with($route, 'bendahara.payments') ? 'active' : '' }}">
    <i class="bi bi-credit-card"></i> Pembayaran Masuk
    @php $pendingCount = \App\Models\Payment::where('status','pending')->whereNotNull('proof_path')->count(); @endphp
    @if($pendingCount > 0)
    <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
    @endif
</a>