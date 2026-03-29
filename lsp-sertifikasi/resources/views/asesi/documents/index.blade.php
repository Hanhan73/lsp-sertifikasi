@extends('layouts.app')
@section('title', 'Dokumen Pra-Asesmen')
@section('page-title', 'Dokumen Pra-Asesmen')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')

{{-- Info jadwal ringkas — hanya jika sudah ada, tidak wajib --}}
@if($asesmen->schedule)
<div class="alert alert-info border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-calendar2-check fs-4 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>Jadwal Asesmen:</strong>
        {{ $asesmen->schedule->assessment_date->translatedFormat('d F Y') }},
        {{ $asesmen->schedule->start_time }} – {{ $asesmen->schedule->end_time }}
        @if($asesmen->schedule->location)
        &bull; {{ $asesmen->schedule->location }}
        @endif
        @if($asesmen->schedule->asesor)
        &bull; Asesor: <strong>{{ $asesmen->schedule->asesor->nama }}</strong>
        @endif
    </div>
    <a href="{{ route('asesi.schedule') }}" class="btn btn-sm btn-outline-info flex-shrink-0">
        <i class="bi bi-arrow-right me-1"></i>Detail Jadwal
    </a>
</div>
@else
<div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-clock-history fs-4 flex-shrink-0"></i>
    <div>
        <strong>Jadwal belum ditetapkan.</strong>
        Silakan lengkapi dokumen di bawah sambil menunggu penjadwalan dari Admin LSP.
    </div>
</div>
@endif

{{-- Alert APL-01 dikembalikan --}}
@if($aplsatu && $aplsatu->status === 'returned')
<div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 mt-1"></i>
    <div>
        <strong>APL-01 Dikembalikan oleh Admin</strong><br>
        <span class="small">Admin meminta Anda untuk memperbaiki data APL-01.</span>
        <div class="mt-2">
            <a href="{{ route('asesi.apl01') }}" class="btn btn-danger btn-sm">
                <i class="bi bi-pencil me-1"></i>Perbaiki APL-01 Sekarang
            </a>
        </div>
    </div>
</div>
@endif

{{-- Progress ringkas semua dokumen --}}
@php
$aplStatus = $aplsatu?->status;
$apldStatus = $apldua?->status;
$frak01Status = $frak01?->status;
$frak04Status = $frak04?->status;

$selesai = collect([
in_array($aplStatus, ['submitted', 'verified', 'approved']),
in_array($apldStatus, ['submitted', 'verified', 'approved']),
in_array($frak01Status, ['submitted', 'verified', 'approved']),
])->filter()->count();

$totalWajib = 3;
$pctWajib = round($selesai / $totalWajib * 100);
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold">Progress Dokumen Wajib</span>
            <span class="text-muted small">{{ $selesai }}/{{ $totalWajib }} selesai</span>
        </div>
        <div class="progress mb-2" style="height: 10px;">
            <div class="progress-bar {{ $selesai === $totalWajib ? 'bg-success' : 'bg-primary' }}"
                style="width: {{ $pctWajib }}%; transition: width .5s;"></div>
        </div>
        @if($selesai === $totalWajib)
        <div class="text-success small">
            <i class="bi bi-check-circle-fill me-1"></i>
            Semua dokumen wajib sudah disubmit. Pastikan hadir tepat waktu saat asesmen.
        </div>
        @else
        <div class="text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Lengkapi semua dokumen wajib sebelum tanggal asesmen.
        </div>
        @endif
    </div>
</div>

{{-- List Dokumen --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-folder2-open text-primary fs-5"></i>
            <h5 class="mb-0">Daftar Dokumen</h5>
        </div>
        <span class="badge bg-light text-dark border">Harus dilengkapi sebelum asesmen</span>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">

            {{-- ══ APL-01 ══ --}}
            <div class="list-group-item px-4 py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        @if(in_array($aplStatus, ['verified', 'approved']))
                        <div class="text-primary fs-4"><i class="bi bi-patch-check-fill"></i></div>
                        @elseif($aplStatus === 'submitted')
                        <div class="text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                        @elseif($aplStatus === 'returned')
                        <div class="text-danger fs-4"><i class="bi bi-arrow-return-left"></i></div>
                        @elseif($aplStatus === 'draft')
                        <div class="text-warning fs-4"><i class="bi bi-pencil-square"></i></div>
                        @else
                        <div class="text-secondary fs-4"><i class="bi bi-circle"></i></div>
                        @endif
                        <div>
                            <div class="fw-semibold">
                                FR.APL.01 — Permohonan Sertifikasi Kompetensi
                                <span class="badge bg-danger ms-1" style="font-size:.65rem;">Wajib</span>
                            </div>
                            <div class="text-muted small mt-1">
                                Formulir permohonan berisi data pribadi, data pekerjaan, dan tujuan asesmen.
                            </div>
                            <div class="mt-1">
                                @if($aplsatu)
                                <span class="badge bg-{{ $aplsatu->status_badge }}">{{ $aplsatu->status_label }}</span>
                                @if($aplsatu->submitted_at)
                                <span class="text-muted small ms-2">
                                    Disubmit: {{ $aplsatu->submitted_at->format('d/m/Y H:i') }}
                                </span>
                                @endif
                                @else
                                <span class="badge bg-secondary">Belum Diisi</span>
                                @endif
                            </div>
                            {{-- Progress bukti kelengkapan --}}
                            @if($aplsatu && $aplsatu->buktiKelengkapan->isNotEmpty())
                            @php
                            $totalBukti = $aplsatu->buktiKelengkapan->count();
                            $uploadedBukti = $aplsatu->buktiKelengkapan->whereNotNull('gdrive_file_url')->count();
                            $pctBukti = $totalBukti > 0 ? round(($uploadedBukti / $totalBukti) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center gap-2 mt-2" style="max-width:200px;">
                                <div class="progress flex-grow-1" style="height:5px;">
                                    <div class="progress-bar bg-success" style="width:{{ $pctBukti }}%"></div>
                                </div>
                                <small class="text-muted">{{ $uploadedBukti }}/{{ $totalBukti }} bukti</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        @if(in_array($aplStatus, ['verified', 'approved']))
                        <a href="{{ route('asesi.apl01.pdf', ['preview' => 1]) }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Preview
                        </a>
                        <a href="{{ route('asesi.apl01.pdf') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                        @elseif($aplStatus === 'submitted')
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="bi bi-lock me-1"></i>Menunggu verifikasi admin
                        </span>
                        @elseif($aplStatus === 'returned')
                        <a href="{{ route('asesi.apl01') }}" class="btn btn-sm btn-danger">
                            <i class="bi bi-pencil me-1"></i>Perbaiki & Submit Ulang
                        </a>
                        @else
                        <a href="{{ route('asesi.apl01') }}" class="btn btn-sm btn-primary">
                            @if($aplStatus === 'draft')
                            <i class="bi bi-pencil me-1"></i>Lanjutkan Isi
                            @else
                            <i class="bi bi-plus-circle me-1"></i>Isi Sekarang
                            @endif
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ══ APL-02 ══ --}}
            <div class="list-group-item px-4 py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        @if(in_array($apldStatus, ['verified', 'approved']))
                        <div class="text-primary fs-4"><i class="bi bi-patch-check-fill"></i></div>
                        @elseif($apldStatus === 'submitted')
                        <div class="text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                        @elseif($apldStatus === 'draft')
                        <div class="text-warning fs-4"><i class="bi bi-pencil-square"></i></div>
                        @else
                        <div class="text-secondary fs-4"><i class="bi bi-circle"></i></div>
                        @endif
                        <div>
                            <div class="fw-semibold">
                                FR.APL.02 — Asesmen Mandiri
                                <span class="badge bg-danger ms-1" style="font-size:.65rem;">Wajib</span>
                            </div>
                            <div class="text-muted small mt-1">
                                Penilaian mandiri kompetensi per elemen unit kompetensi.
                            </div>
                            <div class="mt-1">
                                @if($apldua)
                                <span class="badge bg-{{ $apldua->status_badge }}">{{ $apldua->status_label }}</span>
                                @if($apldua->submitted_at)
                                <span class="text-muted small ms-2">
                                    Disubmit: {{ $apldua->submitted_at->format('d/m/Y H:i') }}
                                </span>
                                @endif
                                {{-- Progress jawaban --}}
                                @if($apldua->jawabans->isNotEmpty())
                                @php $prog = $apldua->progress; @endphp
                                <div class="d-flex align-items-center gap-2 mt-2" style="max-width:200px;">
                                    <div class="progress flex-grow-1" style="height:5px;">
                                        <div class="progress-bar bg-success"
                                            style="width:{{ $prog['total'] > 0 ? round($prog['answered']/$prog['total']*100) : 0 }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $prog['answered'] }}/{{ $prog['total'] }}
                                        elemen</small>
                                </div>
                                @endif
                                @else
                                <span class="badge bg-secondary">Belum Diisi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        @if(in_array($apldStatus, ['verified', 'approved']))
                        <a href="{{ route('asesi.apldua.pdf', ['preview' => 1]) }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Preview
                        </a>
                        <a href="{{ route('asesi.apldua.pdf') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                        @elseif($apldua && $apldua->status === 'submitted')
                        <span class="badge bg-info px-3 py-2">
                            <i class="bi bi-hourglass-split me-1"></i>Menunggu verifikasi asesor
                        </span>
                        @else
                        <a href="{{ route('asesi.apldua') }}" class="btn btn-sm btn-primary">
                            @if($apldStatus === 'draft')
                            <i class="bi bi-pencil me-1"></i>Lanjutkan Isi
                            @else
                            <i class="bi bi-plus-circle me-1"></i>Isi Sekarang
                            @endif
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ══ FR.AK.01 ══ --}}
            <div class="list-group-item px-4 py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        @if(in_array($frak01Status, ['verified', 'approved']))
                        <div class="text-primary fs-4"><i class="bi bi-patch-check-fill"></i></div>
                        @elseif($frak01Status === 'submitted')
                        <div class="text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                        @elseif($frak01Status === 'draft')
                        <div class="text-warning fs-4"><i class="bi bi-pen"></i></div>
                        @else
                        <div class="text-secondary fs-4"><i class="bi bi-circle"></i></div>
                        @endif
                        <div>
                            <div class="fw-semibold">
                                FR.AK.01 — Persetujuan Asesmen dan Kerahasiaan
                                <span class="badge bg-danger ms-1" style="font-size:.65rem;">Wajib</span>
                            </div>
                            <div class="text-muted small mt-1">
                                Dokumen persetujuan dan kerahasiaan antara asesi dan asesor.
                            </div>
                            <div class="mt-1">
                                @if($frak01)
                                <span class="badge bg-{{ $frak01->status_badge }}">{{ $frak01->status_label }}</span>
                                @if($frak01->submitted_at)
                                <span class="text-muted small ms-2">
                                    Ditandatangani: {{ $frak01->submitted_at->format('d/m/Y H:i') }}
                                </span>
                                @endif
                                @else
                                <span class="badge bg-secondary">Belum Ada</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        @if(in_array($frak01Status, ['verified', 'approved']))
                        <a href="{{ route('asesi.frak01.pdf', ['preview' => 1]) }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Preview
                        </a>
                        <a href="{{ route('asesi.frak01.pdf') }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                        @elseif($frak01Status === 'submitted')
                        <span class="badge bg-info px-3 py-2">
                            <i class="bi bi-hourglass-split me-1"></i>Menunggu tanda tangan asesor
                        </span>
                        @elseif($frak01Status === 'draft')
                        <a href="{{ route('asesi.frak01') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-pen me-1"></i>Baca & Tanda Tangan
                        </a>
                        @else
                        <a href="{{ route('asesi.frak01') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i>Buat Sekarang
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ══ FR.AK.04 — Banding (opsional) ══ --}}
            <div class="list-group-item px-4 py-3 bg-light">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($frak04Status === 'submitted')
                        <div class="text-warning fs-4"><i class="bi bi-megaphone-fill"></i></div>
                        @else
                        <div class="text-secondary fs-4"><i class="bi bi-megaphone"></i></div>
                        @endif
                        <div>
                            <div class="fw-semibold">
                                FR.AK.04 — Banding Asesmen
                                <span class="badge bg-secondary ms-1" style="font-size:.65rem;">Opsional</span>
                            </div>
                            <div class="text-muted small mt-1">
                                Ajukan banding jika Anda menilai proses asesmen tidak sesuai SOP.
                            </div>
                            <div class="mt-1">
                                @if($frak04Status === 'submitted')
                                <span class="badge bg-warning text-dark">Banding Diajukan</span>
                                <span class="text-muted small ms-2">
                                    {{ $frak04->submitted_at?->format('d/m/Y H:i') }}
                                </span>
                                @else
                                <span class="badge bg-light text-secondary border">Belum Diajukan</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        @if($frak04Status === 'submitted')
                        <a href="{{ route('asesi.frak04.pdf', ['preview' => 1]) }}" target="_blank"
                            class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Preview
                        </a>
                        <a href="{{ route('asesi.frak04.pdf') }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                        @else
                        <a href="{{ route('asesi.frak04') }}" class="btn btn-sm btn-outline-warning">
                            <i class="bi bi-megaphone me-1"></i>Ajukan Banding
                        </a>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Tips --}}
<div class="alert alert-info border-0 shadow-sm mt-4">
    <h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Tips Persiapan Asesmen</h6>
    <ul class="mb-0 small">
        <li>Isi dan submit semua dokumen wajib (APL-01, APL-02, FR.AK.01) sebelum tanggal asesmen.</li>
        <li>Siapkan dokumen asli — KTP, ijazah, dan sertifikat pengalaman kerja — untuk ditunjukkan saat asesmen.</li>
        <li>Hadir minimal 15 menit sebelum waktu asesmen dimulai.</li>
        @if($asesmen->schedule?->location)
        <li>Lokasi asesmen: <strong>{{ $asesmen->schedule->location }}</strong></li>
        @endif
    </ul>
</div>

@endsection