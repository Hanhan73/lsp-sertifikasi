{{-- Sidebar untuk Asesi --}}

@php
$currentRoute = Route::currentRouteName();
$asesmen = auth()->user()->asesmen;
@endphp

{{-- Dashboard --}}
<a href="{{ route('asesi.dashboard') }}"
    class="nav-link {{ $currentRoute == 'asesi.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

@if(!$asesmen)
{{-- Belum terdaftar --}}
<a href="{{ route('asesi.complete-data') }}"
    class="nav-link {{ $currentRoute == 'asesi.complete-data' ? 'active' : '' }}">
    <i class="bi bi-person-plus"></i> Daftar Asesi
</a>

@else

{{-- ── Data Pribadi ── --}}
{{-- Tampil saat registered (wajib lengkapi) atau data_completed (bisa lihat) --}}
@if(in_array($asesmen->status, ['registered', 'data_completed', 'pra_asesmen_started', 'scheduled', 'pre_assessment_completed', 'assessed', 'certified']))
<a href="{{ route('asesi.complete-data') }}"
    class="nav-link {{ $currentRoute == 'asesi.complete-data' ? 'active' : '' }}">
    <i class="bi bi-person-fill"></i>
    @if($asesmen->status === 'registered')
        Data Pribadi <span class="badge bg-warning text-dark ms-1">!</span>
    @else
        Data Pribadi
    @endif
</a>
@endif

{{-- ── Pembayaran ── --}}
{{-- Hanya mandiri, hanya setelah verified (flow lama) --}}
{{-- Kolektif: pembayaran diurus TUK, tidak tampil di sini --}}
@if(!$asesmen->is_collective && $asesmen->status === 'verified')
<a href="{{ route('asesi.payment') }}"
    class="nav-link {{ $currentRoute == 'asesi.payment' ? 'active' : '' }}">
    <i class="bi bi-credit-card"></i> Pembayaran
    <span class="badge bg-danger ms-1">!</span>
</a>
@endif

{{-- Status Pembayaran — hanya mandiri yang sudah punya payment record --}}
@if(!$asesmen->is_collective && $asesmen->payment)
<a href="{{ route('asesi.payment.status') }}"
    class="nav-link {{ $currentRoute == 'asesi.payment.status' ? 'active' : '' }}">
    <i class="bi bi-receipt"></i> Status Pembayaran
    @if($asesmen->payment->status === 'pending')
        <span class="badge bg-warning text-dark ms-1">Pending</span>
    @elseif($asesmen->payment->status === 'verified')
        <span class="badge bg-success ms-1">✓</span>
    @endif
</a>
@endif

<hr class="my-2 mx-3" style="border-color: rgba(255,255,255,0.2);">

{{-- ── Dokumen Pra-Asesmen ── --}}
@if(in_array($asesmen->status, ['pra_asesmen_started', 'scheduled', 'pre_assessment_completed', 'assessed', 'certified']))

@php
    $aplStatus    = $asesmen->aplsatu?->status;
    $apldStatus   = $asesmen->apldua?->status;
    $frak01Status = $asesmen->frak01?->status;

    // Auto-expand jika sedang di halaman dokumen manapun
    $isOnDokumen = in_array($currentRoute, [
        'asesi.documents',
        'asesi.apl01',
        'asesi.apldua',
        'asesi.frak01',
    ]);

    // Hitung berapa dokumen sudah selesai untuk badge
    $doneDocs = collect([
        in_array($aplStatus,    ['submitted', 'verified', 'approved']),
        in_array($apldStatus,   ['submitted', 'verified', 'approved']),
        in_array($frak01Status, ['submitted', 'verified', 'approved']),
    ])->filter()->count();
@endphp

{{-- Parent menu --}}
<a href="{{ route('asesi.documents') }}"
    class="nav-link {{ $currentRoute === 'asesi.documents' ? 'active' : '' }} d-flex align-items-center justify-content-between"
    data-bs-toggle="collapse"
    data-bs-target="#submenu-dokumen"
    aria-expanded="{{ $isOnDokumen ? 'true' : 'false' }}"
    onclick="if(event.target === this || event.target.classList.contains('bi-chevron')) { event.preventDefault(); this.querySelector('.bi-chevron-down, .bi-chevron-up') && null; } else { window.location='{{ route('asesi.documents') }}'; event.stopPropagation(); }"
>
    <span>
        <i class="bi bi-folder2-open me-1"></i> Dokumen Pra-Asesmen
        @if($doneDocs > 0)
            <span class="badge bg-success ms-1">{{ $doneDocs }}/3</span>
        @endif
    </span>
    <i class="bi {{ $isOnDokumen ? 'bi-chevron-up' : 'bi-chevron-down' }} small"></i>
</a>

{{-- Submenu --}}
<div class="collapse {{ $isOnDokumen ? 'show' : '' }}" id="submenu-dokumen">
    <div class="ps-3">

        {{-- APL-01 --}}
        <a href="{{ route('asesi.apl01') }}"
            class="nav-link py-1 {{ $currentRoute === 'asesi.apl01' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text me-1"></i> APL-01
            @if($aplStatus === 'draft')
                <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Draft</span>
            @elseif($aplStatus === 'submitted')
                <span class="badge bg-info ms-1" style="font-size:.65rem;">Review</span>
            @elseif(in_array($aplStatus, ['verified', 'approved']))
                <span class="badge bg-success ms-1" style="font-size:.65rem;">✓</span>
            @else
                <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Belum</span>
            @endif
        </a>

        {{-- APL-02 --}}
        <a href="{{ route('asesi.apldua') }}"
            class="nav-link py-1 {{ $currentRoute === 'asesi.apldua' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-check me-1"></i> APL-02
            @if($apldStatus === 'draft')
                <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Draft</span>
            @elseif($apldStatus === 'submitted')
                <span class="badge bg-info ms-1" style="font-size:.65rem;">Review</span>
            @elseif(in_array($apldStatus, ['verified', 'approved']))
                <span class="badge bg-success ms-1" style="font-size:.65rem;">✓</span>
            @else
                <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Belum</span>
            @endif
        </a>

        {{-- FR.AK.01 --}}
        <a href="{{ route('asesi.frak01') }}"
            class="nav-link py-1 {{ $currentRoute === 'asesi.frak01' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-person me-1"></i> FR.AK.01
            @if($frak01Status === 'draft')
                <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Belum TTD</span>
            @elseif($frak01Status === 'submitted')
                <span class="badge bg-info ms-1" style="font-size:.65rem;">Tunggu Asesor</span>
            @elseif(in_array($frak01Status, ['verified', 'approved']))
                <span class="badge bg-success ms-1" style="font-size:.65rem;">✓</span>
            @else
                <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Belum</span>
            @endif
        </a>


    </div>
</div>

<hr class="my-2 mx-3" style="border-color: rgba(255,255,255,0.2);">
@endif

{{-- ── Jadwal ── --}}
{{-- Tampil setelah dijadwalkan --}}
@if($asesmen->schedule)
<a href="{{ route('asesi.schedule') }}"
    class="nav-link {{ $currentRoute === 'asesi.schedule' ? 'active' : '' }}">
    <i class="bi bi-calendar2-check"></i> Jadwal Asesmen
    <span class="badge bg-info ms-1">
        {{ $asesmen->schedule->assessment_date->format('d/m') }}
    </span>
</a>
@endif

{{-- ── Hasil Asesmen ── --}}
@if(in_array($asesmen->status, ['assessed', 'certified']))
<a href="{{ route('asesi.tracking') }}"
    class="nav-link {{ $currentRoute === 'asesi.tracking' ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> Hasil Asesmen
    @if($asesmen->result === 'kompeten')
        <span class="badge bg-success ms-1">Kompeten</span>
    @elseif($asesmen->result === 'belum_kompeten')
        <span class="badge bg-danger ms-1">BK</span>
    @endif
</a>
@endif

{{-- ── Sertifikat ── --}}
@if($asesmen->status === 'certified')
<a href="{{ route('asesi.certificate') }}"
    class="nav-link {{ $currentRoute === 'asesi.certificate' ? 'active' : '' }}">
    <i class="bi bi-award"></i> Sertifikat
    <span class="badge bg-success ms-1">✓</span>
</a>
@endif

@endif {{-- end if $asesmen --}}

{{-- ── Progress Bar ── --}}
@if($asesmen && $asesmen->status !== 'certified')
<div class="px-3 mt-4">
    <div class="card bg-dark bg-opacity-25 border-0 text-white">
        <div class="card-body py-2 px-3">
            <small class="text-white-50 d-block mb-1">Progress:</small>
            @php
                $progress = match($asesmen->status) {
                    'registered'               => 10,
                    'data_completed'           => 25,
                    'pra_asesmen_started'          => 40,
                    'scheduled'                => 55,
                    'pre_assessment_completed' => 70,
                    'assessed'                 => 85,
                    'certified'                => 100,
                    default                    => 0,
                };
            @endphp
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-warning" style="width: {{ $progress }}%"></div>
            </div>
            <small class="text-white-50 d-block mt-1">{{ $progress }}% Selesai</small>
        </div>
    </div>
</div>
@endif