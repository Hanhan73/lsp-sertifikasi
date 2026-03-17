<a href="{{ route('tuk.dashboard') }}" class="nav-link {{ request()->routeIs('tuk.dashboard') ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<a href="{{ route('tuk.collective') }}"
    class="nav-link {{ request()->routeIs('tuk.collective') && !request()->routeIs('tuk.collective.payment*') ? 'active' : '' }}">
    <i class="bi bi-person-plus"></i> Pendaftaran Kolektif
</a>

<!-- ✅ NEW: Menu Pembayaran Kolektif -->
<a href="{{ route('tuk.collective.payments') }}"
    class="nav-link {{ request()->routeIs('tuk.collective.payment*') ? 'active' : '' }}">
    <i class="bi bi-credit-card"></i> Pembayaran Kolektif
    @php
    $pendingBatches = \App\Models\Asesmen::whereNotNull('collective_batch_id')
    ->where('is_collective', true)
    ->where('status', 'verified')
    ->where('collective_paid_by_tuk', true)
    ->get()
    ->groupBy('collective_batch_id')
    ->filter(function($batch) {
    // Check if batch needs payment
    $firstAsesmen = $batch->first();
    if ($firstAsesmen->payment_phases === 'single') {
    return $batch->every(fn($a) => !$a->payments()->where('payment_phase', 'full')->where('status',
    'verified')->exists());
    } else {
    // Check phase 1
    $phase1Unpaid = $batch->every(fn($a) => !$a->payments()->where('payment_phase', 'phase_1')->where('status',
    'verified')->exists());
    if ($phase1Unpaid) return true;

    // Check phase 2
    $allAssessed = $batch->every(fn($a) => in_array($a->status, ['assessed', 'certified']));
    $phase2Unpaid = $batch->every(fn($a) => !$a->payments()->where('payment_phase', 'phase_2')->where('status',
    'verified')->exists());
    return $allAssessed && $phase2Unpaid;
    }
    return false;
    })
    ->count();
    @endphp
    @if($pendingBatches > 0)
    <span class="badge bg-danger ms-2">{{ $pendingBatches }}</span>
    @endif
</a>

<a href="{{ route('tuk.asesi') }}"
    class="nav-link {{ request()->routeIs('tuk.asesi') || request()->routeIs('tuk.batch.detail') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Daftar Asesi
</a>

<a href="{{ route('tuk.schedules') }}" class="nav-link {{ request()->routeIs('tuk.schedules*') ? 'active' : '' }}">
    <i class="bi bi-calendar-event"></i> Penjadwalan
</a>

<a href="{{ route('tuk.verifications') }}"
    class="nav-link {{ request()->routeIs('tuk.verifications*') ? 'active' : '' }}">
    <i class="bi bi-check-circle"></i> Verifikasi Asesi
    @if(($stats['pending_verification'] ?? 0) > 0)
    <span class="badge bg-warning ms-2">{{ $stats['pending_verification'] }}</span>
    @endif
</a>