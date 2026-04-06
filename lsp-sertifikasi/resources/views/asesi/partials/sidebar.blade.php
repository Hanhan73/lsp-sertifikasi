{{-- Sidebar untuk Asesi --}}

@php
$currentRoute = Route::currentRouteName();
$asesmen = auth()->user()->asesmen;
@endphp

{{-- Dashboard --}}
<a href="{{ route('asesi.dashboard') }}" class="nav-link {{ $currentRoute == 'asesi.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

@if(!$asesmen)
<a href="{{ route('asesi.complete-data') }}"
    class="nav-link {{ $currentRoute == 'asesi.complete-data' ? 'active' : '' }}">
    <i class="bi bi-person-plus"></i> Daftar Asesi
</a>

@else

{{-- ── Data Pribadi ── --}}
@if(in_array($asesmen->status, ['registered', 'data_completed', 'pra_asesmen_started', 'scheduled',
'pre_assessment_completed', 'assessed', 'certified']))
<a href="{{ route('asesi.complete-data') }}"
    class="nav-link {{ $currentRoute == 'asesi.complete-data' ? 'active' : '' }}">
    <i class="bi bi-person-fill"></i>
    Data Pribadi
    @if($asesmen->status === 'registered')
    <span class="badge bg-warning text-dark ms-1">!</span>
    @elseif($asesmen->biodata_needs_revision)
    <span class="badge bg-danger ms-1">!</span>
    @endif
</a>
@endif

{{-- ── Pembayaran ── --}}
@if(!$asesmen->is_collective)

@if(in_array($asesmen->status, ['data_completed', 'payment_pending']))
<a href="{{ route('asesi.payment') }}" class="nav-link {{ $currentRoute === 'asesi.payment' ? 'active' : '' }}">
    <i class="bi bi-credit-card"></i> Pembayaran
    @if(!$asesmen->payment || $asesmen->payment->status === 'rejected')
    <span class="badge bg-danger ms-1">!</span>
    @elseif($asesmen->payment->status === 'pending')
    <span class="badge bg-warning text-dark ms-1">Review</span>
    @endif
</a>
@endif

@if($asesmen->payment)
<a href="{{ route('asesi.payment.status') }}"
    class="nav-link {{ $currentRoute === 'asesi.payment.status' ? 'active' : '' }}">
    <i class="bi bi-receipt"></i> Status Pembayaran
    @if($asesmen->payment->status === 'pending')
    <span class="badge bg-warning text-dark ms-1">Menunggu</span>
    @elseif($asesmen->payment->status === 'verified')
    <span class="badge bg-success ms-1">✓</span>
    @elseif($asesmen->payment->status === 'rejected')
    <span class="badge bg-danger ms-1">Ditolak</span>
    @endif
</a>
@endif

@endif

<hr class="my-2 mx-3" style="border-color: rgba(255,255,255,0.2);">

{{-- ── Fase Pra-Asesmen: sebelum dijadwalkan ── --}}
{{-- Akses langsung ke dokumen karena belum ada hub jadwal --}}
@if(in_array($asesmen->status, ['pra_asesmen_started']) && !$asesmen->schedule_id)

@php
$aplStatus = $asesmen->aplsatu?->status;
$apldStatus = $asesmen->apldua?->status;
$frak01Status = $asesmen->frak01?->status;
$doneDocs = collect([
in_array($aplStatus, ['submitted', 'verified', 'approved']),
in_array($apldStatus, ['submitted', 'verified', 'approved']),
in_array($frak01Status, ['submitted', 'verified', 'approved']),
])->filter()->count();
@endphp

<div class="px-3 mb-1">
    <small class="text-white-50 text-uppercase" style="font-size:.65rem;letter-spacing:.05em;">
        Dokumen Pra-Asesmen
    </small>
</div>

<a href="{{ route('asesi.apl01') }}" class="nav-link {{ $currentRoute === 'asesi.apl01' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-text"></i> APL-01
    @if($aplStatus === 'draft') <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Draft</span>
    @elseif($aplStatus === 'submitted') <span class="badge bg-info ms-1" style="font-size:.65rem;">Review</span>
    @elseif ($aplStatus === 'returned') <span class="badge bg-danger ms-1" style="font-size:.65rem;">Perbaiki</span>
    @elseif(in_array($aplStatus, ['verified','approved'])) <span class="badge bg-success ms-1"
        style="font-size:.65rem;">✓</span>
    @else <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Belum</span>
    @endif
</a>

<a href="{{ route('asesi.apldua') }}" class="nav-link {{ $currentRoute === 'asesi.apldua' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-check"></i> APL-02
    @if($apldStatus === 'draft') <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Draft</span>
    @elseif($apldStatus === 'submitted') <span class="badge bg-info ms-1" style="font-size:.65rem;">Review</span>
    @elseif ($apldStatus === 'returned') <span class="badge bg-danger ms-1" style="font-size:.65rem;">Perbaiki</span>
    @elseif(in_array($apldStatus, ['verified','approved'])) <span class="badge bg-success ms-1"
        style="font-size:.65rem;">✓</span>
    @else <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Belum</span>
    @endif
</a>

<a href="{{ route('asesi.frak01') }}" class="nav-link {{ $currentRoute === 'asesi.frak01' ? 'active' : '' }}">
    <i class="bi bi-file-earmark-person"></i> FR.AK.01
    @if($frak01Status === 'returned')
    <span class="badge bg-danger ms-1" style="font-size:.65rem;">Perbaiki</span>
    @elseif($frak01Status === 'draft')
    <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">TTD</span>
    @elseif($frak01Status === 'submitted')
    <span class="badge bg-info ms-1" style="font-size:.65rem;">Review</span>
    @elseif(in_array($frak01Status, ['verified','approved']))
    <span class="badge bg-success ms-1" style="font-size:.65rem;">✓</span>
    @else
    <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Belum</span>
    @endif
</a>

<hr class="my-2 mx-3" style="border-color: rgba(255,255,255,0.2);;">
@endif

{{-- ── Jadwal Asesmen — jadi hub utama setelah dijadwalkan ── --}}
@if($asesmen->schedule_id)

@php
$teoriAda = ($asesmen->soalTeoriAsesi ?? collect())->isNotEmpty();
$teoriSubmit = ($asesmen->soalTeoriAsesi ?? collect())->whereNotNull('submitted_at')->isNotEmpty();

// Alert badge: ada dokumen yang perlu perhatian
$needsAttention = ($asesmen->aplsatu?->status === 'returned') || ($asesmen->frak01?->status === 'returned')
|| ($asesmen->soalTeoriAsesi ?? collect())->whereNull('submitted_at')->count() > 0;
@endphp

<a href="{{ route('asesi.schedule') }}" class="nav-link {{ in_array($currentRoute, [
        'asesi.schedule',
        'asesi.apl01', 'asesi.apldua', 'asesi.frak01', 'asesi.frak04', 'asesi.documents',
        'asesi.soal.teori.intro', 'asesi.soal.teori.index',
        'asesi.soal.observasi.index',
    ]) ? 'active' : '' }}">
    <i class="bi bi-calendar2-check"></i> Jadwal & Asesmen
    @if($needsAttention)
    <span class="badge bg-danger ms-1">!</span>
    @elseif($teoriAda && !$teoriSubmit)
    <span class="badge bg-warning text-dark ms-1">!</span>
    @else
    <span class="badge bg-info ms-1" style="font-size:.65rem;">
        {{ $asesmen->schedule->assessment_date->translatedFormat('d/m') }}
    </span>
    @endif
</a>

@endif

{{-- ── Hasil Asesmen ── --}}
@if(in_array($asesmen->status, ['assessed', 'certified']))
<a href="{{ route('asesi.tracking') }}" class="nav-link {{ $currentRoute === 'asesi.tracking' ? 'active' : '' }}">
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
<a href="{{ route('asesi.certificate') }}" class="nav-link {{ $currentRoute === 'asesi.certificate' ? 'active' : '' }}">
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
            'registered' => 10,
            'data_completed' => 25,
            'pra_asesmen_started' => 40,
            'scheduled' => 55,
            'pre_assessment_completed' => 70,
            'asesmen_started' => 75,
            'assessed' => 85,
            'certified' => 100,
            default => 0,
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