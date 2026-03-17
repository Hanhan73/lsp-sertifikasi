@extends('layouts.app')
@section('title', 'Jadwal & Asesmen')
@section('page-title', 'Jadwal & Dokumen Asesmen')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')

{{-- Info Jadwal --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
                <i class="bi bi-calendar2-check fs-5"></i>
                <h5 class="mb-0">Jadwal Asesmen Anda</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Tanggal</div>
                        <div class="fw-semibold fs-5">
                            {{ $schedule->assessment_date->translatedFormat('d F Y') }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Waktu</div>
                        <div class="fw-semibold">
                            <i class="bi bi-clock me-1"></i>
                            {{ $schedule->start_time }} – {{ $schedule->end_time }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Lokasi</div>
                        <div class="fw-semibold">
                            <i class="bi bi-geo-alt me-1"></i>
                            {{ $schedule->location ?? $asesmen->tuk->name ?? '-' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Asesor</div>
                        <div class="fw-semibold">
                            @if($schedule->asesor)
                            <i class="bi bi-person-badge me-1"></i>
                            {{ $schedule->asesor->nama }}
                            @else
                            <span class="text-muted">Belum ditetapkan</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($schedule->notes)
                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Catatan:</small>
                    <div class="mt-1">{{ $schedule->notes }}</div>
                </div>
                @endif

                {{-- Countdown --}}
                @php
                $daysLeft = now()->startOfDay()->diffInDays($schedule->assessment_date->startOfDay(), false);
                @endphp
                <div class="mt-3">
                    @if($daysLeft > 0)
                    <span class="badge bg-info fs-6 px-3 py-2">
                        <i class="bi bi-hourglass-split me-1"></i>
                        {{ $daysLeft }} hari lagi
                    </span>
                    @elseif($daysLeft == 0)
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                        <i class="bi bi-alarm me-1"></i> Asesmen hari ini!
                    </span>
                    @else
                    <span class="badge bg-secondary fs-6 px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i> Asesmen telah berlalu
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alert jika APL-01 dikembalikan untuk diperbaiki --}}
@if($aplsatu && $aplsatu->status === 'returned')
<div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 mt-1"></i>
    <div>
        <strong>APL-01 Dikembalikan oleh Admin</strong><br>
        <span class="small">Admin meminta Anda untuk memperbaiki data APL-01. Silakan buka formulir dan perbaiki data
            sesuai catatan.</span>
        <a href="{{ route('asesi.apl01') }}" class="btn btn-danger btn-sm mt-2">
            <i class="bi bi-pencil me-1"></i> Perbaiki APL-01 Sekarang
        </a>
    </div>
</div>
@endif

{{-- Dokumen Pra-Asesmen --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-folder2-open text-primary fs-5"></i>
                    <h5 class="mb-0">Dokumen Pra-Asesmen</h5>
                </div>
                <span class="badge bg-light text-dark border">Harus dilengkapi sebelum asesmen</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">

                    {{-- APL-01 --}}
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                @if($aplsatu && $aplsatu->status === 'submitted')
                                <div class="text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                                @elseif($aplsatu && $aplsatu->status === 'verified')
                                <div class="text-primary fs-4"><i class="bi bi-patch-check-fill"></i></div>
                                @elseif($aplsatu && $aplsatu->status === 'approved')
                                <div class="text-success fs-4"><i class="bi bi-shield-check"></i></div>
                                @elseif($aplsatu && $aplsatu->status === 'returned')
                                <div class="text-danger fs-4"><i class="bi bi-arrow-return-left"></i></div>
                                @elseif($aplsatu && $aplsatu->status === 'draft')
                                <div class="text-warning fs-4"><i class="bi bi-pencil-square"></i></div>
                                @else
                                <div class="text-secondary fs-4"><i class="bi bi-circle"></i></div>
                                @endif

                                <div>
                                    <div class="fw-semibold">FR.APL.01 — Permohonan Sertifikasi Kompetensi</div>
                                    <div class="text-muted small mt-1">
                                        Formulir permohonan berisi data pribadi, data pekerjaan, dan tujuan asesmen.
                                    </div>
                                    @if($aplsatu)
                                    <span class="badge bg-{{ $aplsatu->status_badge }} mt-1">
                                        {{ $aplsatu->status_label }}
                                    </span>
                                    @if($aplsatu->submitted_at)
                                    <span class="text-muted small ms-2">
                                        Disubmit: {{ $aplsatu->submitted_at->format('d/m/Y H:i') }}
                                    </span>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary mt-1">Belum Diisi</span>
                                    @endif
                                </div>
                            </div>

                            {{-- 
                                ATURAN TOMBOL PDF:
                                - draft / submitted / returned  → tampilkan tombol "Isi/Perbaiki", TANPA PDF
                                - verified / approved           → tampilkan Preview + Download PDF
                            --}}
                            <div class="d-flex gap-2">
                                @if($aplsatu && in_array($aplsatu->status, ['verified', 'approved']))
                                {{-- PDF aktif hanya setelah admin TTD --}}
                                <a href="{{ route('asesi.apl01.pdf', ['preview' => 1]) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i> Preview PDF
                                </a>
                                <a href="{{ route('asesi.apl01.pdf') }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-download me-1"></i> Download PDF
                                </a>

                                @elseif($aplsatu && $aplsatu->status === 'submitted')
                                {{-- Sudah submit, tunggu verifikasi admin --}}
                                <div class="text-end">
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="bi bi-lock me-1"></i> PDF tersedia setelah admin verifikasi & TTD
                                    </span>
                                </div>

                                @elseif($aplsatu && $aplsatu->status === 'returned')
                                {{-- Dikembalikan, perlu diperbaiki --}}
                                <a href="{{ route('asesi.apl01') }}" class="btn btn-sm btn-danger">
                                    <i class="bi bi-pencil me-1"></i> Perbaiki & Submit Ulang
                                </a>

                                @else
                                {{-- Draft atau belum ada --}}
                                <a href="{{ route('asesi.apl01') }}" class="btn btn-sm btn-primary">
                                    @if($aplsatu && $aplsatu->status === 'draft')
                                    <i class="bi bi-pencil me-1"></i> Lanjutkan Isi
                                    @else
                                    <i class="bi bi-plus-circle me-1"></i> Isi Sekarang
                                    @endif
                                </a>
                                @endif
                            </div>
                        </div>

                        {{-- Bukti Kelengkapan Progress --}}
                        @if($aplsatu && $aplsatu->buktiKelengkapan->isNotEmpty())
                        <div class="mt-3 ps-5">
                            <div class="text-muted small mb-2">Bukti Kelengkapan Dokumen:</div>
                            @php
                            $totalBukti = $aplsatu->buktiKelengkapan->count();
                            $uploadedBukti = $aplsatu->buktiKelengkapan->whereNotNull('gdrive_file_url')->count();
                            $pct = $totalBukti > 0 ? round(($uploadedBukti / $totalBukti) * 100) : 0;
                            @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="text-muted">{{ $uploadedBukti }}/{{ $totalBukti }} dokumen</small>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="list-group-item px-4 py-3 {{ !$apldua || $apldua->is_editable ? '' : 'bg-light' }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                @if($apldua && $apldua->status === 'submitted')
                                <div class="text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                                @elseif($apldua && $apldua->status === 'verified')
                                <div class="text-primary fs-4"><i class="bi bi-patch-check-fill"></i></div>
                                @elseif($apldua && $apldua->status === 'draft')
                                <div class="text-warning fs-4"><i class="bi bi-pencil-square"></i></div>
                                @elseif(!$apldua)
                                <div class="text-secondary fs-4"><i class="bi bi-circle"></i></div>
                                @else
                                <div class="text-secondary fs-4"><i class="bi bi-lock"></i></div>
                                @endif
                                <div>
                                    <div class="fw-semibold">FR.APL.02 — Asesmen Mandiri</div>
                                    <div class="text-muted small mt-1">
                                        Penilaian mandiri kompetensi per elemen unit kompetensi.
                                    </div>
                                    @if($apldua)
                                    <span
                                        class="badge bg-{{ $apldua->status_badge }} mt-1">{{ $apldua->status_label }}</span>
                                    @if($apldua->submitted_at)
                                    <span class="text-muted small ms-2">Disubmit:
                                        {{ $apldua->submitted_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary mt-1">Belum Diisi</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if($apldua && !$apldua->is_editable)
                                <div class="d-flex gap-2">
                                    @if(in_array($apldua->status, ['verified', 'approved']))
                                    {{-- ✅ PDF tersedia setelah asesor verifikasi --}}
                                    <a href="{{ route('asesi.apldua.pdf', ['preview' => 1]) }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i> Preview PDF
                                    </a>
                                    <a href="{{ route('asesi.apldua.pdf') }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-download me-1"></i> Download PDF
                                    </a>
                                    @else
                                    <a href="{{ route('asesi.apldua') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </a>
                                    @endif
                                </div>
                                @else
                                <a href="{{ route('asesi.apldua') }}" class="btn btn-sm btn-primary">
                                    @if($apldua && $apldua->status === 'draft')
                                    <i class="bi bi-pencil me-1"></i> Lanjutkan Isi
                                    @else
                                    <i class="bi bi-plus-circle me-1"></i> Isi Sekarang
                                    @endif
                                </a>
                                @endif
                            </div>
                        </div>

                        @if($apldua && $apldua->jawabans->isNotEmpty())
                        @php
                        $prog = $apldua->progress;
                        @endphp
                        <div class="mt-3 ps-5">
                            <div class="text-muted small mb-1">Progress Pengisian:</div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-success"
                                        style="width:{{ $prog['total'] > 0 ? round($prog['answered']/$prog['total']*100) : 0 }}%;">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $prog['answered'] }}/{{ $prog['total'] }} elemen</small>
                            </div>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tips --}}
<div class="row">
    <div class="col-12">
        <div class="alert alert-info border-0 shadow-sm">
            <h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Tips Persiapan Asesmen</h6>
            <ul class="mb-0 small">
                <li>Pastikan semua formulir dokumen sudah diisi dan disubmit sebelum tanggal asesmen.</li>
                <li>Siapkan dokumen asli (KTP, ijazah, sertifikat pengalaman kerja) untuk ditunjukkan saat asesmen.</li>
                <li>Hadir minimal 15 menit sebelum waktu asesmen dimulai.</li>
                @if($schedule->location)
                <li>Lokasi asesmen: <strong>{{ $schedule->location }}</strong></li>
                @endif
            </ul>
        </div>
    </div>
</div>

@endsection