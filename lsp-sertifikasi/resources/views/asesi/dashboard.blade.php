@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@push('styles')
<style>
.tl { position:relative; padding:4px 0; }
.tl::before { content:''; position:absolute; left:15px; top:0; bottom:0; width:2px; background:#e2e8f0; }
.tl-item { position:relative; padding-left:44px; padding-bottom:20px; }
.tl-item:last-child { padding-bottom:0; }
.tl-dot {
    position:absolute; left:6px; top:2px;
    width:20px; height:20px; border-radius:50%;
    background:#fff; border:2px solid #cbd5e1;
    display:flex; align-items:center; justify-content:center;
    font-size:.65rem; color:#94a3b8;
    z-index:1;
}
.tl-item.done .tl-dot { background:#22c55e; border-color:#22c55e; color:#fff; }
.tl-item.now  .tl-dot { background:#3b82f6; border-color:#3b82f6; color:#fff; }
.tl-item.now  .tl-label { font-weight:700; color:#1e40af; }
.tl-label { font-size:.875rem; margin-bottom:1px; }
.tl-sub   { font-size:.78rem; color:#64748b; }
</style>
@endpush

@section('content')

@if(session('verified'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
    <i class="bi bi-shield-check me-2"></i>
    <strong>Email berhasil diverifikasi!</strong> Anda sekarang bisa mengakses semua fitur.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(!$asesmen)
{{-- ── BELUM DAFTAR ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-person-plus text-primary" style="font-size:4rem;opacity:.4;"></i>
        <h5 class="mt-3">Mulai Pendaftaran Sertifikasi</h5>
        <p class="text-muted">Anda belum terdaftar. Lengkapi data pribadi untuk memulai proses sertifikasi.</p>
        <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary mt-2">
            <i class="bi bi-pencil me-1"></i>Daftar Sekarang
        </a>
    </div>
</div>

@else

@php
    $statusOrder = ['registered','data_completed','pra_asesmen_started','scheduled','pre_assessment_completed','assessed','certified'];
    $currentIdx  = array_search($asesmen->status, $statusOrder) ?: 0;

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

{{-- ── HEADER STRIP ── --}}
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="d-flex flex-wrap">
            {{-- Warna status --}}
            <div class="d-flex flex-column justify-content-center px-4 py-4 text-white flex-shrink-0"
                 style="min-width:140px; background:{{ $asesmen->status === 'certified' ? '#16a34a' : ($asesmen->status === 'assessed' ? '#0284c7' : '#1e40af') }};">
                <div style="font-size:.7rem;opacity:.75;text-transform:uppercase;letter-spacing:.06em;">Status</div>
                <div class="fw-bold" style="font-size:1rem;line-height:1.3;">{{ $asesmen->status_label }}</div>
                <div class="mt-2" style="font-size:.75rem;opacity:.8;">{{ $progress }}% selesai</div>
                <div class="progress mt-1" style="height:4px;background:rgba(255,255,255,.3);">
                    <div class="progress-bar bg-white" style="width:{{ $progress }}%;"></div>
                </div>
            </div>

            {{-- Info utama --}}
            <div class="flex-grow-1 p-4">
                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                    <div>
                        <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                        <div class="text-muted small d-flex flex-wrap gap-3">
                            <span><i class="bi bi-hash me-1"></i>#{{ $asesmen->id }}</span>
                            <span><i class="bi bi-building me-1"></i>{{ $asesmen->tuk->name ?? '-' }}</span>
                            <span><i class="bi bi-patch-check me-1"></i>{{ $asesmen->skema->name ?? '-' }}</span>
                            <span>
                                <i class="bi bi-person-badge me-1"></i>
                                {{ $asesmen->is_collective ? 'Kolektif' : 'Mandiri' }}
                            </span>
                        </div>
                        @if($asesmen->next_action)
                        <div class="mt-2 small">
                            <i class="bi bi-arrow-right-circle text-primary me-1"></i>
                            <span class="text-primary fw-semibold">{{ $asesmen->next_action }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- CTA utama --}}
                    @if($asesmen->status === 'registered')
                    <a href="{{ route('asesi.complete-data') }}" class="btn btn-primary btn-sm flex-shrink-0">
                        <i class="bi bi-pencil me-1"></i>Lengkapi Data
                    </a>
                    @elseif($asesmen->status === 'verified' && !$asesmen->is_collective)
                    <a href="{{ route('asesi.payment') }}" class="btn btn-success btn-sm flex-shrink-0">
                        <i class="bi bi-credit-card me-1"></i>Bayar Sekarang
                    </a>
                    @elseif($asesmen->status === 'pra_asesmen_started')
                    <a href="{{ route('asesi.apl01') }}" class="btn btn-primary btn-sm flex-shrink-0">
                        <i class="bi bi-file-earmark-text me-1"></i>Isi Dokumen
                    </a>
                    @elseif($asesmen->schedule_id)
                    <a href="{{ route('asesi.schedule') }}" class="btn btn-warning btn-sm flex-shrink-0">
                        <i class="bi bi-calendar2-check me-1"></i>Lihat Jadwal
                    </a>
                    @elseif($asesmen->status === 'certified')
                    <a href="{{ route('asesi.certificate') }}" class="btn btn-success btn-sm flex-shrink-0">
                        <i class="bi bi-award me-1"></i>Download Sertifikat
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── KIRI: Timeline ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clock-history text-primary me-2"></i>Timeline Proses
            </div>
            <div class="card-body">
                <div class="tl">

                    @php
                    $steps = [
                        [
                            'label'  => 'Pendaftaran',
                            'status' => 'registered',
                            'icon'   => 'bi-person-plus',
                            'sub'    => $asesmen->registration_date->translatedFormat('d M Y'),
                        ],
                        [
                            'label'  => 'Kelengkapan Data',
                            'status' => 'data_completed',
                            'icon'   => 'bi-clipboard-check',
                            'sub'    => $currentIdx > 1 ? 'Selesai' : 'Belum dilengkapi',
                        ],
                        [
                            'label'  => 'Proses Dimulai Admin',
                            'status' => 'pra_asesmen_started',
                            'icon'   => 'bi-play-circle',
                            'sub'    => $asesmen->admin_started_at
                                            ? $asesmen->admin_started_at->translatedFormat('d M Y')
                                            : 'Menunggu Admin LSP',
                        ],
                        [
                            'label'  => 'Pengisian Dokumen',
                            'status' => 'pra_asesmen_started',
                            'icon'   => 'bi-file-earmark-text',
                            'sub'    => $currentIdx > 2 ? 'Selesai' : ($asesmen->status === 'pra_asesmen_started' ? 'Sedang diisi' : 'Menunggu'),
                            'indent' => true,
                        ],
                        [
                            'label'  => 'Penjadwalan Asesmen',
                            'status' => 'scheduled',
                            'icon'   => 'bi-calendar-event',
                            'sub'    => $asesmen->schedule
                                            ? $asesmen->schedule->assessment_date->translatedFormat('l, d F Y')
                                            : 'Belum dijadwalkan',
                        ],
                        [
                            'label'  => 'Pelaksanaan Asesmen',
                            'status' => 'asesmen_started',
                            'icon'   => 'bi-person-check',
                            'sub'    => $asesmen->status === 'asesmen_started' && $asesmen->schedule?->assessment_date?->isPast()
                                            ? 'Selesai dilaksanakan, menunggu hasil dari asesor'
                                            : ($asesmen->status === 'asesmen_started'
                                                ? 'Sedang berjalan'
                                                : ($asesmen->assessed_at
                                                    ? $asesmen->assessed_at->translatedFormat('d M Y')
                                                    : 'Belum dilakukan')),
                        ],
                        [
                            'label'  => 'Hasil Asesmen',
                            'status' => 'assessed',
                            'icon'   => 'bi-clipboard-check',
                            'sub'    => $asesmen->result
                                            ? ($asesmen->result === 'kompeten' ? '✓ Kompeten' : '✗ Belum Kompeten')
                                            : ($currentIdx >= array_search('assessed', $statusOrder)
                                                ? 'Menunggu keputusan asesor'
                                                : 'Belum dilakukan'),
                        ],
                        [
                            'label'  => 'Penerbitan Sertifikat',
                            'status' => 'certified',
                            'icon'   => 'bi-award',
                            'sub'    => $asesmen->certificate
                                            ? 'No. ' . $asesmen->certificate->certificate_number
                                            : 'Belum terbit',
                        ],
                    ];
                    @endphp

                    @foreach($steps as $step)
                    @php
                        $idx       = array_search($step['status'], $statusOrder);
                        $isDone    = $idx !== false && $currentIdx > $idx;
                        $isNow     = $asesmen->status === $step['status'] && !($step['indent'] ?? false && $currentIdx > 2);
                        $indent    = $step['indent'] ?? false;
                    @endphp
                    <div class="tl-item {{ $isDone ? 'done' : ($isNow ? 'now' : '') }}"
                         style="{{ $indent ? 'padding-left:64px;' : '' }}">
                        <div class="tl-dot" style="{{ $indent ? 'left:26px;width:16px;height:16px;' : '' }}">
                            @if($isDone)
                            <i class="bi bi-check"></i>
                            @elseif($isNow)
                            <i class="{{ $step['icon'] }}" style="font-size:.6rem;"></i>
                            @endif
                        </div>
                        <div class="tl-label">{{ $step['label'] }}</div>
                        <div class="tl-sub">{{ $step['sub'] }}</div>
                        @if($step['status'] === 'assessed' && $asesmen->result)
                        <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }} mt-1">
                            {{ strtoupper($asesmen->result) }}
                        </span>
                        @endif
                        {{-- Detail tambahan untuk step yang aktif --}}
                        @if($isNow && $asesmen->status === 'scheduled' && $asesmen->schedule)
                        <div class="mt-1 d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                <i class="bi bi-clock me-1"></i>{{ $asesmen->schedule->start_time }} – {{ $asesmen->schedule->end_time }}
                            </span>
                            @if($asesmen->schedule->location)
                            <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                <i class="bi bi-geo-alt me-1"></i>{{ $asesmen->schedule->location }}
                            </span>
                            @endif
                            @if($asesmen->schedule->asesor)
                            <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                <i class="bi bi-person-badge me-1"></i>{{ $asesmen->schedule->asesor->nama }}
                            </span>
                            @endif
                        </div>
                        @endif
                        @if($isDone && $step['status'] === 'asesmen_started' && $asesmen->result)
                        <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }} mt-1">
                            {{ strtoupper($asesmen->result) }}
                        </span>
                        @endif
                    </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>

    {{-- ── KANAN: Info + Aksi ── --}}
    <div class="col-lg-5">

        {{-- Info Registrasi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle text-primary me-2"></i>Info Registrasi
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-3" style="width:130px;">No. Registrasi</td>
                        <td class="fw-semibold">#{{ $asesmen->id }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">TUK</td>
                        <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Skema</td>
                        <td>{{ $asesmen->skema->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Jenis</td>
                        <td>
                            <span class="badge bg-{{ $asesmen->is_collective ? 'primary' : 'success' }}">
                                {{ $asesmen->is_collective ? 'Kolektif' : 'Mandiri' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Tgl Daftar</td>
                        <td>{{ $asesmen->registration_date->translatedFormat('d M Y') }}</td>
                    </tr>
                    @if($asesmen->training_flag)
                    <tr>
                        <td class="text-muted ps-3">Pelatihan</td>
                        <td><span class="badge bg-warning text-dark">Terdaftar</span></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Pembayaran (mandiri) --}}
        @if(!$asesmen->is_collective && $asesmen->payment)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt text-primary me-2"></i>Pembayaran
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-muted">Jumlah</div>
                        <div class="fw-bold">Rp {{ number_format($asesmen->payment->amount, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge bg-{{ $asesmen->payment->status_badge }} px-3 py-2">
                        {{ $asesmen->payment->status_label }}
                    </span>
                </div>
                @if($asesmen->payment->status === 'verified')
                <a href="{{ route('asesi.payment.invoice') }}" class="btn btn-sm btn-outline-success w-100 mt-3">
                    <i class="bi bi-download me-1"></i>Download Invoice
                </a>
                @elseif($asesmen->payment->status === 'pending')
                <a href="{{ route('asesi.payment') }}" class="btn btn-sm btn-warning w-100 mt-3">
                    <i class="bi bi-credit-card me-1"></i>Selesaikan Pembayaran
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Hasil asesmen --}}
        @if($asesmen->result)
        <div class="card border-0 shadow-sm mb-4
            {{ $asesmen->result === 'kompeten' ? 'border-success' : 'border-danger' }}"
             style="border-left:4px solid {{ $asesmen->result === 'kompeten' ? '#22c55e' : '#ef4444' }} !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <i class="bi {{ $asesmen->result === 'kompeten' ? 'bi-award-fill text-success' : 'bi-x-circle-fill text-danger' }} fs-3"></i>
                <div>
                    <div class="fw-bold">Hasil Asesmen</div>
                    <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }} mt-1">
                        {{ strtoupper($asesmen->result) }}
                    </span>
                </div>
                @if($asesmen->status === 'certified')
                <a href="{{ route('asesi.certificate') }}" class="btn btn-sm btn-success ms-auto">
                    <i class="bi bi-award me-1"></i>Sertifikat
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Aksi cepat --}}
        @if(in_array($asesmen->status, ['pra_asesmen_started']))
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-lightning-fill text-warning me-2"></i>Aksi Cepat
            </div>
            <div class="card-body d-grid gap-2">
                @php
                    $aplStatus    = $asesmen->aplsatu?->status;
                    $apldStatus   = $asesmen->apldua?->status;
                    $frak01Status = $asesmen->frak01?->status;
                @endphp
                <a href="{{ route('asesi.apl01') }}"
                   class="btn btn-sm {{ in_array($aplStatus, ['verified','approved']) ? 'btn-outline-success' : 'btn-primary' }}">
                    <i class="bi bi-file-earmark-text me-1"></i>APL-01
                    @if(in_array($aplStatus, ['verified','approved']))
                    <i class="bi bi-check-circle ms-1"></i>
                    @elseif($aplStatus === 'returned')
                    <span class="badge bg-danger ms-1">Perbaiki</span>
                    @endif
                </a>
                <a href="{{ route('asesi.apldua') }}"
                   class="btn btn-sm {{ in_array($apldStatus, ['verified','approved']) ? 'btn-outline-success' : 'btn-outline-primary' }}">
                    <i class="bi bi-file-earmark-check me-1"></i>APL-02
                    @if(in_array($apldStatus, ['verified','approved']))
                    <i class="bi bi-check-circle ms-1"></i>
                    @endif
                </a>
                <a href="{{ route('asesi.frak01') }}"
                   class="btn btn-sm {{ in_array($frak01Status, ['verified','approved']) ? 'btn-outline-success' : 'btn-outline-primary' }}">
                    <i class="bi bi-journal-text me-1"></i>FR.AK.01
                    @if(in_array($frak01Status, ['verified','approved']))
                    <i class="bi bi-check-circle ms-1"></i>
                    @elseif($frak01Status === 'submitted')
                    <span class="badge bg-info ms-1" style="font-size:.65rem;">Tunggu Asesor</span>
                    @endif
                </a>
            </div>
        </div>
        @endif

    </div>

</div>
@endif

@endsection