@php $route = request()->route()->getName(); @endphp

<a href="{{ route('tuk.dashboard') }}" class="nav-link {{ $route === 'tuk.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<a href="{{ route('tuk.collective') }}" class="nav-link {{ $route === 'tuk.collective' ? 'active' : '' }}">
    <i class="bi bi-person-plus"></i> Pendaftaran Kolektif
</a>

<a href="{{ route('tuk.invoice-kolektif.index') }}"
    class="nav-link {{ str_starts_with($route, 'tuk.invoice-kolektif') ? 'active' : '' }}">
    <i class="bi bi-receipt"></i> Pembayaran Kolektif
    @php
    $tukId = auth()->user()->tuk?->id;
    $badgeCount = $tukId
    ? \App\Models\CollectivePayment::whereHas('invoice', fn($q) => $q->where('tuk_id', $tukId))
    ->where('status', 'pending')
    ->whereNull('proof_path') // belum upload sama sekali
    ->count()
    : 0;
    @endphp
    @if($badgeCount > 0)
    <span class="badge bg-danger ms-1">{{ $badgeCount }}</span>
    @endif
</a>

<a href="{{ route('tuk.asesi') }}"
    class="nav-link {{ $route === 'tuk.asesi' || $route === 'tuk.batch.detail' ? 'active' : '' }}">
    <i class="bi bi-people"></i> Daftar Asesi
</a>