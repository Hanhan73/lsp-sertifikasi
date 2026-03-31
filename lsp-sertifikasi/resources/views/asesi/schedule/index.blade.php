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
                        <div class="fw-semibold fs-5">{{ $schedule->assessment_date->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Waktu</div>
                        <div class="fw-semibold"><i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Lokasi</div>
                        <div class="fw-semibold"><i class="bi bi-geo-alt me-1"></i>{{ $schedule->location ?? $asesmen->tuk->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Asesor</div>
                        <div class="fw-semibold">
                            @if($schedule->asesor)
                            <i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor->nama }}
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
                @php $daysLeft = now()->startOfDay()->diffInDays($schedule->assessment_date->startOfDay(), false); @endphp
                <div class="mt-3">
                    @if($daysLeft > 0)
                    <span class="badge bg-info fs-6 px-3 py-2"><i class="bi bi-hourglass-split me-1"></i>{{ $daysLeft }} hari lagi</span>
                    @elseif($daysLeft == 0)
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-alarm me-1"></i>Asesmen hari ini!</span>
                    @else
                    <span class="badge bg-secondary fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i>Asesmen telah berlalu</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Alert APL-01 dikembalikan --}}
@if($aplsatu && $aplsatu->status === 'returned')
<div class="alert alert-danger border-0 shadow-sm d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-3 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        <h6 class="fw-bold mb-1">APL-01 Dikembalikan oleh Admin</h6>
        <p class="small mb-2">Admin meminta Anda memperbaiki APL-01. Perbaiki sesuai catatan berikut, lalu submit ulang.</p>
 
        @if($aplsatu->rejection_notes)
        <div class="bg-white border border-danger rounded p-2 mb-2 small">
            <strong>Catatan Admin:</strong><br>
            {{ $aplsatu->rejection_notes }}
        </div>
        @endif
 
        <a href="{{ route('asesi.apl01') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-pencil me-1"></i>Perbaiki APL-01 Sekarang
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

                    {{-- ══ FR.AK.01 ══ --}}
                    @php
                        $frak01 = $asesmen->frak01 ?? null;
                    @endphp
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                {{-- Ikon status --}}
                                @if($frak01 && $frak01->status === 'verified')
                                <div class="text-primary fs-4"><i class="bi bi-patch-check-fill"></i></div>
                                @elseif($frak01 && $frak01->status === 'submitted')
                                <div class="text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                                @elseif($frak01 && $frak01->status === 'draft')
                                <div class="text-warning fs-4"><i class="bi bi-pen"></i></div>
                                @else
                                <div class="text-secondary fs-4"><i class="bi bi-circle"></i></div>
                                @endif

                                <div>
                                    <div class="fw-semibold">FR.AK.01 — Persetujuan Asesmen dan Kerahasiaan</div>
                                    <div class="text-muted small mt-1">
                                        Dokumen persetujuan dan kerahasiaan antara asesi dan asesor.
                                    </div>
                                    @if($frak01)
                                    <span class="badge bg-{{ $frak01->status_badge }} mt-1">{{ $frak01->status_label }}</span>
                                    @if($frak01->submitted_at)
                                    <span class="text-muted small ms-2">Ditandatangani: {{ $frak01->submitted_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary mt-1">Belum Tersedia</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                @if($frak01 && in_array($frak01->status, ['verified', 'approved']))
                                {{-- PDF aktif setelah kedua pihak TTD --}}
                                <a href="{{ route('asesi.frak01.pdf', ['preview' => 1]) }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>Preview PDF
                                </a>
                                <a href="{{ route('asesi.frak01.pdf') }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-download me-1"></i>Download PDF
                                </a>

                                @elseif($frak01 && $frak01->status === 'submitted')
                                {{-- Sudah TTD asesi, tunggu asesor --}}
                                <span class="badge bg-info px-3 py-2">
                                    <i class="bi bi-hourglass-split me-1"></i>Menunggu tanda tangan asesor
                                </span>

                                @elseif($frak01 && $frak01->status === 'draft')
                                {{-- Draft: asesi bisa TTD --}}
                                <a href="{{ route('asesi.frak01') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pen me-1"></i>Baca & Tanda Tangan
                                </a>

                                @else
                                {{-- Belum ada --}}
                                <span class="badge bg-secondary px-3 py-2">
                                    <i class="bi bi-lock me-1"></i>Menunggu asesor menyiapkan dokumen
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ══ APL-01 ══ --}}
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
                                    <div class="text-muted small mt-1">Formulir permohonan berisi data pribadi, data pekerjaan, dan tujuan asesmen.</div>
                                    @if($aplsatu)
                                    <span class="badge bg-{{ $aplsatu->status_badge }} mt-1">{{ $aplsatu->status_label }}</span>
                                    @if($aplsatu->submitted_at)
                                    <span class="text-muted small ms-2">Disubmit: {{ $aplsatu->submitted_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary mt-1">Belum Diisi</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                @if($aplsatu && in_array($aplsatu->status, ['verified', 'approved']))
                                <a href="{{ route('asesi.apl01.pdf', ['preview' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>Preview PDF
                                </a>
                                <a href="{{ route('asesi.apl01.pdf') }}" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-download me-1"></i>Download PDF
                                </a>
                                @elseif($aplsatu && $aplsatu->status === 'submitted')
                                <span class="badge bg-warning text-dark px-3 py-2">
                                    <i class="bi bi-lock me-1"></i>PDF tersedia setelah admin verifikasi
                                </span>
                                @elseif($aplsatu && $aplsatu->status === 'returned')
                                <a href="{{ route('asesi.apl01') }}" class="btn btn-sm btn-danger">
                                    <i class="bi bi-pencil me-1"></i>Perbaiki & Submit Ulang
                                </a>
                                @else
                                <a href="{{ route('asesi.apl01') }}" class="btn btn-sm btn-primary">
                                    @if($aplsatu && $aplsatu->status === 'draft')
                                    <i class="bi bi-pencil me-1"></i>Lanjutkan Isi
                                    @else
                                    <i class="bi bi-plus-circle me-1"></i>Isi Sekarang
                                    @endif
                                </a>
                                @endif
                            </div>
                        </div>

                        @if($aplsatu && $aplsatu->buktiKelengkapan->isNotEmpty())
                        <div class="mt-3 ps-5">
                            <div class="text-muted small mb-2">Bukti Kelengkapan Dokumen:</div>
                            @php
                            $totalBukti    = $aplsatu->buktiKelengkapan->count();
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

                    {{-- ══ APL-02 ══ --}}
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
                                    <div class="text-muted small mt-1">Penilaian mandiri kompetensi per elemen unit kompetensi.</div>
                                    @if($apldua)
                                    <span class="badge bg-{{ $apldua->status_badge }} mt-1">{{ $apldua->status_label }}</span>
                                    @if($apldua->submitted_at)
                                    <span class="text-muted small ms-2">Disubmit: {{ $apldua->submitted_at->format('d/m/Y H:i') }}</span>
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
                                    <a href="{{ route('asesi.apldua.pdf', ['preview' => 1]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>Preview PDF
                                    </a>
                                    <a href="{{ route('asesi.apldua.pdf') }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-download me-1"></i>Download PDF
                                    </a>
                                    @else
                                    <a href="{{ route('asesi.apldua') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                    @endif
                                </div>
                                @else
                                <a href="{{ route('asesi.apldua') }}" class="btn btn-sm btn-primary">
                                    @if($apldua && $apldua->status === 'draft')
                                    <i class="bi bi-pencil me-1"></i>Lanjutkan Isi
                                    @else
                                    <i class="bi bi-plus-circle me-1"></i>Isi Sekarang
                                    @endif
                                </a>
                                @endif
                            </div>
                        </div>

                        @if($apldua && $apldua->jawabans->isNotEmpty())
                        @php $prog = $apldua->progress; @endphp
                        <div class="mt-3 ps-5">
                            <div class="text-muted small mb-1">Progress Pengisian:</div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-success"
                                         style="width:{{ $prog['total'] > 0 ? round($prog['answered']/$prog['total']*100) : 0 }}%;"></div>
                                </div>
                                <small class="text-muted">{{ $prog['answered'] }}/{{ $prog['total'] }} elemen</small>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- ══ FR.AK.04 — Banding Asesmen (opsional) ══ --}}
                    @php
                        $frak04 = $asesmen->frak04 ?? null;
                    @endphp
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                    
                                {{-- Ikon status --}}
                                @if($frak04 && $frak04->status === 'submitted')
                                <div class="text-warning fs-4"><i class="bi bi-megaphone-fill"></i></div>
                                @else
                                <div class="text-secondary fs-4"><i class="bi bi-megaphone"></i></div>
                                @endif
                    
                                <div>
                                    <div class="fw-semibold">
                                        FR.AK.04 — Banding Asesmen
                                        <span class="badge bg-secondary ms-1" style="font-size:.65rem; vertical-align:middle;">Opsional</span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        Formulir pengajuan banding jika Anda menilai proses asesmen tidak sesuai SOP.
                                    </div>
                                    @if($frak04 && $frak04->status === 'submitted')
                                    <span class="badge bg-warning text-dark mt-1">Banding Diajukan</span>
                                    <span class="text-muted small ms-2">{{ $frak04->submitted_at?->format('d/m/Y H:i') }}</span>
                                    @else
                                    <span class="badge bg-light text-secondary border mt-1" style="font-size:.72rem;">Belum Diajukan</span>
                                    @endif
                                </div>
                            </div>
                    
                            <div class="d-flex gap-2">
                                @if($frak04 && $frak04->status === 'submitted')
                                <a href="{{ route('asesi.frak04.pdf', ['preview' => 1]) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>Preview PDF
                                </a>
                                <a href="{{ route('asesi.frak04.pdf') }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-download me-1"></i>Download PDF
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
    </div>
</div>


@endsection