@extends('layouts.app')
@section('title', 'Detail Asesi - ' . $asesmen->full_name)
@section('page-title', 'Detail Asesi')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@push('styles')
<style>
.info-row td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.info-row td:first-child { color: #64748b; width: 38%; font-size: .88rem; }
.jawaban-badge-K  { background: #d1fae5; color: #065f46; }
.jawaban-badge-BK { background: #fee2e2; color: #991b1b; }
.jawaban-badge    { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: .75rem; font-weight: 700; }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('asesor.schedule') }}">Jadwal</a></li>
        <li class="breadcrumb-item"><a href="{{ route('asesor.schedule.detail', $schedule) }}">{{ $schedule->assessment_date->format('d M Y') }}</a></li>
        <li class="breadcrumb-item active">{{ $asesmen->full_name }}</li>
    </ol>
</nav>

{{-- Header asesi --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start gap-4 flex-wrap">
            @if($asesmen->photo_path)
            <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                 class="rounded border" style="width:90px;height:110px;object-fit:cover;" alt="foto">
            @else
            <div class="rounded bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:90px;height:110px;font-size:2rem;">
                {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
            </div>
            @endif
            <div class="flex-grow-1">
                <h4 class="fw-bold mb-1">{{ $asesmen->full_name }}</h4>
                <div class="row g-1 text-muted small">
                    <div class="col-md-6"><strong>NIK:</strong> {{ $asesmen->nik }}</div>
                    <div class="col-md-6"><strong>TTL:</strong> {{ $asesmen->birth_place }}, {{ $asesmen->birth_date?->format('d M Y') }}</div>
                    <div class="col-md-6"><strong>Telepon:</strong> {{ $asesmen->phone }}</div>
                    <div class="col-md-6"><strong>Pendidikan:</strong> {{ $asesmen->education }}</div>
                    <div class="col-md-6"><strong>Institusi:</strong> {{ $asesmen->institution }}</div>
                    <div class="col-md-6"><strong>Jabatan:</strong> {{ $asesmen->occupation }}</div>
                    <div class="col-md-6"><strong>TUK:</strong> {{ $asesmen->tuk->name ?? '-' }}</div>
                    <div class="col-md-6"><strong>Skema:</strong> {{ $asesmen->skema->name ?? '-' }}</div>
                </div>
            </div>
            <a href="{{ route('asesor.schedule.detail', $schedule) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>
</div>

{{-- TABS --}}
@php
    $frak01   = $asesmen->frak01 ?? null;
    $frak04   = $asesmen->frak04 ?? null;
    $aplsatu  = $asesmen->aplsatu ?? null;
    $apldua   = $asesmen->apldua ?? null;
@endphp

<ul class="nav nav-tabs mb-4" id="asesiTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-frak01">
            <i class="bi bi-file-earmark-check me-1"></i>FR.AK.01
            @if($frak01)
            <i class="bi ms-1 small
                {{ in_array($frak01->status, ['verified','approved']) ? 'bi-check-circle-fill text-success' :
                ($frak01->status === 'submitted' ? 'bi-pen-fill text-warning' : 'bi-clock text-muted') }}">
            </i>
            @endif
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-apl01">
            <i class="bi bi-file-earmark-person me-1"></i>APL-01
            @if($aplsatu)
            <i class="bi ms-1 small {{ $aplsatu->status === 'verified' ? 'bi-check-circle-fill text-success' : 'bi-clock text-muted' }}"></i>
            @endif
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-apl02">
            <i class="bi bi-clipboard-check me-1"></i>APL-02
            @if($apldua)
            <i class="bi ms-1 small
                {{ in_array($apldua->status, ['verified','approved']) ? 'bi-check-circle-fill text-success' :
                ($apldua->status === 'submitted' ? 'bi-exclamation-circle-fill text-warning' : 'bi-clock text-muted') }}">
            </i>
            @endif
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-frak04">
            <i class="bi bi-megaphone me-1"></i>FR.AK.04
            @if($frak04?->status === 'submitted')
            <i class="bi bi-megaphone-fill text-warning ms-1 small"></i>
            @endif
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-docs">
            <i class="bi bi-folder2-open me-1"></i>Dokumen
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ══════════════════════════════════════════════════
         TAB FR.AK.01 — READ ONLY, asesor hanya TTD
    ══════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="tab-frak01">

        @if(!$frak01)
        {{-- Belum ada sama sekali --}}
        <div class="text-center py-5 text-muted">
            <i class="bi bi-file-earmark-x" style="font-size:3rem;opacity:.3;"></i>
            <p class="mt-3 fw-semibold">FR.AK.01 Belum Dibuat</p>
            <p class="small">Asesi belum membuat dan menandatangani FR.AK.01.</p>
        </div>

        @else

        {{-- Status banner --}}
        @if(in_array($frak01->status, ['verified','approved']))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-check-circle-fill fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <strong>FR.AK.01 Selesai</strong> — Kedua pihak telah menandatangani.
            </div>
            <a href="{{ route('asesor.frak01.pdf', [$schedule, $asesmen]) }}" target="_blank"
               class="btn btn-sm btn-success flex-shrink-0">
                <i class="bi bi-file-pdf me-1"></i>Lihat PDF
            </a>
        </div>
        @elseif($frak01->status === 'submitted')
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-pen-fill fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <strong>Menunggu Tanda Tangan Anda</strong> — Asesi sudah menandatangani. Silakan review dan berikan tanda tangan.
            </div>
        </div>
        @else
        <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-hourglass-split fs-4 flex-shrink-0"></i>
            <div>
                <strong>Menunggu Tanda Tangan Asesi</strong> — Asesi belum menandatangani FR.AK.01.
            </div>
        </div>
        @endif

        {{-- Info Jadwal --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle text-primary me-2"></i>Informasi Asesmen
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="info-row"><td>Skema Sertifikasi</td><td><strong>{{ $frak01->skema_judul ?? $asesmen->skema?->name ?? '-' }}</strong></td></tr>
                        <tr class="info-row"><td>Nomor Skema</td><td>{{ $frak01->skema_nomor ?? $asesmen->skema?->nomor_skema ?? '-' }}</td></tr>
                        <tr class="info-row"><td>TUK</td><td>{{ $frak01->tuk_nama ?? $asesmen->tuk?->name ?? '-' }}</td></tr>
                        <tr class="info-row"><td>Nama Asesor</td><td>{{ $frak01->nama_asesor ?? $asesor->nama ?? '-' }}</td></tr>
                        <tr class="info-row"><td>Nama Asesi</td><td>{{ $frak01->nama_asesi ?? $asesmen->full_name }}</td></tr>
                        <tr class="info-row"><td>Hari / Tanggal</td><td>{{ $frak01->hari_tanggal ?? $schedule->assessment_date->translatedFormat('l, d F Y') }}</td></tr>
                        <tr class="info-row"><td>Waktu</td><td>{{ $frak01->waktu_asesmen ?? ($schedule->start_time . ' – ' . $schedule->end_time) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bukti yang dikumpulkan (read-only — diisi asesi) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-primary"></i>
                Bukti yang Akan Dikumpulkan
                <span class="badge bg-light border text-muted ms-auto" style="font-size:.7rem;">Diisi oleh Asesi</span>
            </div>
            <div class="card-body">
                @php
                $buktis = [
                    ['field' => 'bukti_verifikasi_portofolio',     'label' => 'Hasil Verifikasi Portofolio'],
                    ['field' => 'bukti_hasil_review_produk',        'label' => 'Hasil Review Produk'],
                    ['field' => 'bukti_observasi_langsung',         'label' => 'Hasil Observasi Langsung'],
                    ['field' => 'bukti_hasil_kegiatan_terstruktur', 'label' => 'Hasil Kegiatan Terstruktur'],
                    ['field' => 'bukti_pertanyaan_lisan',           'label' => 'Hasil Pertanyaan Lisan'],
                    ['field' => 'bukti_pertanyaan_tertulis',        'label' => 'Hasil Pertanyaan Tertulis'],
                    ['field' => 'bukti_pertanyaan_wawancara',       'label' => 'Hasil Pertanyaan Wawancara'],
                ];
                @endphp
                <div class="row g-2">
                    @foreach($buktis as $b)
                    <div class="col-md-6 d-flex align-items-center gap-2 py-1">
                        <i class="bi {{ $frak01->{$b['field']} ? 'bi-check-square-fill text-primary' : 'bi-square text-muted' }} fs-5"></i>
                        <span class="{{ $frak01->{$b['field']} ? '' : 'text-muted' }} small">{{ $b['label'] }}</span>
                    </div>
                    @endforeach
                    @if($frak01->bukti_lainnya)
                    <div class="col-md-6 d-flex align-items-center gap-2 py-1">
                        <i class="bi bi-check-square-fill text-primary fs-5"></i>
                        <span class="small">Lainnya{{ $frak01->bukti_lainnya_keterangan ? ': ' . $frak01->bukti_lainnya_keterangan : '' }}</span>
                    </div>
                    @endif
                </div>
                @php $checkedCount = collect($buktis)->filter(fn($b) => $frak01->{$b['field']})->count() + ($frak01->bukti_lainnya ? 1 : 0); @endphp
                @if($checkedCount === 0)
                <p class="text-muted small mb-0 mt-2"><i class="bi bi-info-circle me-1"></i>Asesi belum menentukan bukti yang akan dikumpulkan.</p>
                @endif
            </div>
        </div>

        {{-- Pernyataan Kerahasiaan (read-only info) --}}
        <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #e2e8f0 !important;">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-shield-check text-warning me-2"></i>Pernyataan Persetujuan & Kerahasiaan
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded bg-light">
                        <span class="badge bg-primary mb-1">Asesi</span>
                        <p class="mb-0 small text-muted">Telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.</p>
                    </div>
                    <div class="p-3 rounded bg-light">
                        <span class="badge bg-secondary mb-1">Asesor</span>
                        <p class="mb-0 small text-muted">Menyatakan tidak akan membuka hasil pekerjaan kepada siapapun selain pihak yang berwenang.</p>
                    </div>
                    <div class="p-3 rounded bg-light">
                        <span class="badge bg-primary mb-1">Asesi</span>
                        <p class="mb-0 small text-muted">Setuju mengikuti asesmen dengan pemahaman bahwa informasi hanya digunakan untuk pengembangan profesional.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tanda Tangan --}}
        <div class="row g-3 mb-4">

            {{-- TTD Asesi (selalu read-only) --}}
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">Tanda Tangan Asesi</span>
                        @if($frak01->ttd_asesi)
                        <span class="badge bg-success">Sudah TTD</span>
                        @else
                        <span class="badge bg-secondary">Belum TTD</span>
                        @endif
                    </div>
                    <div class="card-body text-center">
                        @if($frak01->ttd_asesi)
                        <img src="{{ $frak01->ttd_asesi_image }}" style="max-height:80px;max-width:100%;" alt="TTD Asesi">
                        <div class="small fw-semibold mt-2">{{ $frak01->nama_ttd_asesi }}</div>
                        <div class="text-muted small">{{ $frak01->tanggal_ttd_asesi?->format('d M Y, H:i') }}</div>
                        @else
                        <div class="py-4 text-muted">
                            <i class="bi bi-hourglass-split fs-2 d-block mb-2 opacity-40"></i>
                            <span class="small">Menunggu tanda tangan asesi</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TTD Asesor --}}
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm {{ $frak01->status === 'submitted' ? 'border-warning' : '' }}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">Tanda Tangan Asesor</span>
                        @if($frak01->ttd_asesor)
                        <span class="badge bg-success">Sudah TTD</span>
                        @else
                        <span class="badge bg-secondary">Belum TTD</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($frak01->ttd_asesor)
                        {{-- Sudah TTD → tampilkan --}}
                        <div class="text-center">
                            <img src="{{ $frak01->ttd_asesor_image }}" style="max-height:80px;max-width:100%;" alt="TTD Asesor">
                            <div class="small fw-semibold mt-2">{{ $frak01->nama_ttd_asesor }}</div>
                            <div class="text-muted small">{{ $frak01->tanggal_ttd_asesor?->format('d M Y, H:i') }}</div>
                        </div>

                        @elseif($frak01->status === 'submitted')
                        {{-- Giliran asesor TTD --}}
                        @include('partials._signature_pad', [
                            'padId'    => 'asesor-frak01',
                            'padLabel' => 'Tanda Tangan Asesor',
                            'padHeight' => 160,
                            'savedSig'  => auth()->user()->signature_image,
                        ])
                        <button class="btn btn-success btn-sm mt-3 w-100" onclick="signFrak01()">
                            <i class="bi bi-pen me-1"></i>Tanda Tangan & Verifikasi
                        </button>

                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-lock fs-2 d-block mb-2 opacity-40"></i>
                            <span class="small">Tersedia setelah asesi menandatangani</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @endif {{-- end if $frak01 --}}
    </div>

    {{-- ══════════════════════════════════════════════════
         TAB APL-01
    ══════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-apl01">
        @if($aplsatu)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-file-earmark-person text-primary me-2"></i>FR.APL.01 — Permohonan Sertifikasi</h6>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ $aplsatu->status === 'verified' ? 'success' : 'info' }}">{{ ucfirst($aplsatu->status) }}</span>
                    <a href="{{ route('asesor.asesi.apl01.preview', [$schedule, $asesmen]) }}" target="_blank"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Lihat PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted small" style="width:45%">Nama Lengkap</td><td class="fw-semibold small">{{ $aplsatu->nama_lengkap }}</td></tr>
                            <tr><td class="text-muted small">NIK</td><td class="small">{{ $aplsatu->nik }}</td></tr>
                            <tr><td class="text-muted small">TTL</td><td class="small">{{ $aplsatu->tempat_lahir }}, {{ \Carbon\Carbon::parse($aplsatu->tanggal_lahir)->format('d M Y') }}</td></tr>
                            <tr><td class="text-muted small">HP</td><td class="small">{{ $aplsatu->hp }}</td></tr>
                            <tr><td class="text-muted small">Email</td><td class="small">{{ $aplsatu->email }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td class="text-muted small" style="width:45%">Institusi</td><td class="small">{{ $aplsatu->nama_institusi }}</td></tr>
                            <tr><td class="text-muted small">Jabatan</td><td class="small">{{ $aplsatu->jabatan }}</td></tr>
                            <tr><td class="text-muted small">Tujuan Asesmen</td><td class="small">{{ $aplsatu->tujuan_asesmen }}</td></tr>
                            <tr><td class="text-muted small">Pendidikan</td><td class="small">{{ $aplsatu->kualifikasi_pendidikan }}</td></tr>
                        </table>
                    </div>
                </div>
                @if($aplsatu->ttd_asesi ?? false)
                <hr class="my-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ 'data:image/png;base64,' . $aplsatu->ttd_asesi }}"
                         style="max-height:50px;border:1px solid #e2e8f0;border-radius:4px;padding:2px;" alt="TTD">
                    <div>
                        <div class="text-muted small">Nama: <span class="fw-semibold">{{ $aplsatu->nama_ttd_asesi ?? $aplsatu->nama_lengkap }}</span></div>
                        @if($aplsatu->submitted_at ?? false)
                        <div class="text-muted small">Tanggal: {{ \Carbon\Carbon::parse($aplsatu->submitted_at)->format('d M Y') }}</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-file-earmark-x" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-2">APL-01 belum disubmit oleh asesi.</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════
         TAB APL-02
    ══════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-apl02">
        @if($apldua)
        @php
            $progress = $apldua->progress;
            $jawMap   = $apldua->jawabans->keyBy('elemen_id');
        @endphp

        {{-- Status banner --}}
        @if(in_array($apldua->status, ['verified','approved']))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-check-circle-fill fs-4"></i>
            <div class="flex-grow-1">
                <strong>APL-02 sudah diverifikasi</strong> — Rekomendasi:
                <span class="fw-bold">{{ $apldua->rekomendasi_asesor === 'lanjut' ? '✅ Lanjut Asesmen' : '❌ Tidak Lanjut' }}</span>
            </div>
            <a href="{{ route('asesor.asesi.apl02.preview', [$schedule, $asesmen]) }}" target="_blank"
               class="btn btn-sm btn-outline-success flex-shrink-0">
                <i class="bi bi-file-earmark-pdf me-1"></i>Preview PDF
            </a>
        </div>
        @elseif($apldua->status === 'submitted')
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-exclamation-circle-fill fs-4"></i>
            <div><strong>APL-02 menunggu verifikasi Anda.</strong></div>
        </div>
        @else
        <div class="alert alert-secondary border-0 shadow-sm mb-4">
            <i class="bi bi-info-circle me-2"></i>APL-02 status: <strong>{{ $apldua->status_label }}</strong>
        </div>
        @endif

        {{-- Progress stats --}}
        <div class="row g-3 mb-4 text-center">
            <div class="col-4">
                <div class="card border-0 shadow-sm py-3">
                    <div class="fw-bold fs-4">{{ $progress['total'] }}</div>
                    <div class="small text-muted">Total Elemen</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm py-3">
                    <div class="fw-bold fs-4 text-success">{{ $progress['k'] }}</div>
                    <div class="small text-muted">Kompeten (K)</div>
                </div>
            </div>
            <div class="col-4">
                <div class="card border-0 shadow-sm py-3">
                    <div class="fw-bold fs-4 text-danger">{{ $progress['bk'] }}</div>
                    <div class="small text-muted">Blm Kompeten</div>
                </div>
            </div>
        </div>

        {{-- Detail per unit kompetensi --}}
        @foreach($asesmen->skema->unitKompetensis as $unitIdx => $unit)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex align-items-center gap-2 py-2"
                 style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#apl02-unit-{{ $unit->id }}">
                <i class="bi bi-chevron-down text-muted" style="font-size:.8rem;"></i>
                <span class="badge bg-primary">{{ $unitIdx + 1 }}</span>
                <div class="flex-grow-1 small">
                    <span class="text-muted">{{ $unit->kode_unit }}</span>
                    <span class="ms-2 fw-semibold">{{ $unit->judul_unit }}</span>
                </div>
                @php
                    $unitElemIds = $unit->elemens->pluck('id');
                    $unitK  = $apldua->jawabans->whereIn('elemen_id', $unitElemIds)->where('jawaban','K')->count();
                    $unitBK = $apldua->jawabans->whereIn('elemen_id', $unitElemIds)->where('jawaban','BK')->count();
                @endphp
                <span class="badge bg-success" style="font-size:.65rem;">K: {{ $unitK }}</span>
                <span class="badge bg-danger"  style="font-size:.65rem;">BK: {{ $unitBK }}</span>
            </div>
            <div class="collapse show" id="apl02-unit-{{ $unit->id }}">
                <div class="card-body p-0">
                    @foreach($unit->elemens as $elemen)
                    @php $jaw = $jawMap[$elemen->id] ?? null; @endphp
                    <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small mb-1">{{ $elemen->judul }}</div>
                            @if($jaw?->bukti)
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="bi bi-chat-left-dots me-1"></i>{{ $jaw->bukti }}
                            </div>
                            @endif
                        </div>
                        <div class="flex-shrink-0 text-end" style="min-width:100px;">
                            @if($jaw?->jawaban)
                            <span class="jawaban-badge jawaban-badge-{{ $jaw->jawaban }}">
                                {{ $jaw->jawaban === 'K' ? 'Kompeten' : 'Blm Kompeten' }}
                            </span>
                            @else
                            <span class="badge bg-secondary" style="font-size:.7rem;">Belum diisi</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        {{-- TTD Asesi --}}
        @if($apldua->ttd_asesi)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white small fw-semibold text-muted text-uppercase">Tanda Tangan Asesi</div>
            <div class="card-body d-flex align-items-center gap-4">
                <img src="{{ $apldua->ttd_asesi_image }}"
                     style="max-height:60px;border:1px solid #e2e8f0;border-radius:6px;padding:4px;background:#fff;" alt="TTD Asesi">
                <div>
                    <div class="fw-semibold">{{ $apldua->nama_ttd_asesi }}</div>
                    <div class="text-muted small">{{ $apldua->tanggal_ttd_asesi?->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Form verifikasi asesor --}}
        @if($apldua->status === 'submitted')
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-pen me-2"></i>Verifikasi & Tanda Tangan Asesor
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Rekomendasi <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rekomendasi" id="rek-lanjut" value="lanjut">
                            <label class="form-check-label text-success fw-semibold" for="rek-lanjut">
                                <i class="bi bi-check-circle-fill me-1"></i>Lanjut Asesmen
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="rekomendasi" id="rek-tidak" value="tidak_lanjut">
                            <label class="form-check-label text-danger fw-semibold" for="rek-tidak">
                                <i class="bi bi-x-circle-fill me-1"></i>Tidak Lanjut
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Catatan Asesor <small class="text-muted fw-normal">(opsional)</small></label>
                    <textarea id="catatan-asesor" class="form-control" rows="3"
                              placeholder="Tuliskan catatan atau komentar untuk asesi..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Nama Asesor</label>
                    <input type="text" id="nama-asesor" class="form-control bg-light" value="{{ $asesor->nama }}" readonly>
                </div>
                <div class="mb-4">
                    @include('partials._signature_pad', [
                        'padId'    => 'asesor-apl02',
                        'padLabel' => 'Tanda Tangan Asesor',
                        'padHeight' => 160,
                        'savedSig'  => auth()->user()->signature_image,
                    ])
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="asesor-agree">
                    <label class="form-check-label" for="asesor-agree">
                        Saya menyatakan telah memeriksa APL-02 ini dan memberikan rekomendasi berdasarkan penilaian yang <strong>objektif dan profesional</strong>.
                    </label>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-success px-4" onclick="submitVerifikasiApl02()">
                        <i class="bi bi-check2-circle me-1"></i>Verifikasi & Tanda Tangan
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- TTD & Rekomendasi asesor (sudah verified) --}}
        @if(in_array($apldua->status, ['verified','approved']) && $apldua->ttd_asesor)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-person-check-fill me-2"></i>Tanda Tangan & Rekomendasi Asesor
            </div>
            <div class="card-body d-flex align-items-center gap-4 flex-wrap">
                <img src="{{ $apldua->ttd_asesor_image }}"
                     style="max-height:60px;border:1px solid #e2e8f0;border-radius:6px;padding:4px;background:#fff;" alt="TTD Asesor">
                <div>
                    <div class="fw-semibold">{{ $apldua->nama_ttd_asesor }}</div>
                    <div class="text-muted small">{{ $apldua->tanggal_ttd_asesor?->format('d M Y H:i') }}</div>
                    <span class="badge bg-{{ $apldua->rekomendasi_asesor === 'lanjut' ? 'success' : 'danger' }} mt-1">
                        {{ $apldua->rekomendasi_asesor === 'lanjut' ? '✅ Lanjut Asesmen' : '❌ Tidak Lanjut' }}
                    </span>
                    @if($apldua->catatan_asesor)
                    <div class="text-muted small mt-1">{{ $apldua->catatan_asesor }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-clipboard-x" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-2">APL-02 belum disubmit oleh asesi.</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════
         TAB FR.AK.04
    ══════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-frak04">
        @if($frak04 && $frak04->status === 'submitted')
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-megaphone-fill fs-4"></i>
            <div class="flex-grow-1">
                <strong>Asesi mengajukan Banding</strong> pada {{ $frak04->submitted_at?->format('d M Y H:i') }}
            </div>
            <a href="{{ route('asesor.frak04.pdf', [$schedule, $asesmen]) }}" target="_blank"
               class="btn btn-sm btn-warning flex-shrink-0">
                <i class="bi bi-file-pdf me-1"></i>Lihat PDF
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-megaphone-fill me-2"></i>FR.AK.04 — Detail Banding</h6>
            </div>
            <div class="card-body">
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr class="info-row"><td>Nama Asesi</td><td class="fw-semibold">{{ $frak04->nama_asesi }}</td></tr>
                            <tr class="info-row"><td>Nama Asesor</td><td>{{ $frak04->nama_asesor }}</td></tr>
                            <tr class="info-row"><td>Tanggal Asesmen</td><td>{{ $frak04->tanggal_asesmen }}</td></tr>
                            <tr class="info-row"><td>Skema</td><td class="fw-semibold">{{ $frak04->skema_sertifikasi }}</td></tr>
                            <tr class="info-row"><td>No. Skema</td><td>{{ $frak04->no_skema_sertifikasi }}</td></tr>
                        </table>
                    </div>
                </div>

                <h6 class="fw-semibold mb-3">Jawaban Pertanyaan</h6>
                <div class="d-flex flex-column gap-2 mb-4">
                    @foreach([
                        ['label' => 'Proses Banding telah dijelaskan', 'val' => $frak04->proses_banding_dijelaskan],
                        ['label' => 'Sudah mendiskusikan Banding dengan Asesor', 'val' => $frak04->sudah_diskusi_dengan_asesor],
                        ['label' => 'Melibatkan orang lain dalam Proses Banding', 'val' => $frak04->melibatkan_orang_lain],
                    ] as $pq)
                    <div class="d-flex align-items-center gap-3 p-3 rounded border">
                        <span class="badge {{ $pq['val'] ? 'bg-success' : 'bg-danger' }} px-3">
                            {{ $pq['val'] ? 'YA' : 'TIDAK' }}
                        </span>
                        <span class="small">{{ $pq['label'] }}</span>
                    </div>
                    @endforeach
                </div>

                <h6 class="fw-semibold mb-2">Alasan Banding</h6>
                <div class="p-3 bg-light rounded border small mb-4" style="white-space:pre-wrap;line-height:1.7;">
                    {{ $frak04->alasan_banding }}
                </div>

                @if($frak04->ttd_asesi)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light small fw-bold">Tanda Tangan Pengaju</div>
                    <div class="card-body text-center">
                        <img src="{{ $frak04->ttd_asesi_image }}" style="max-height:70px;max-width:200px;" alt="TTD">
                        <div class="small text-muted mt-2">{{ $frak04->nama_ttd_asesi }}</div>
                        <div class="small text-muted">{{ $frak04->tanggal_ttd_asesi?->format('d M Y H:i') }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-megaphone" style="font-size:2.5rem;opacity:.4;"></i>
            <p class="mt-3 fw-semibold">Tidak Ada Banding</p>
            <p class="small">Asesi tidak mengajukan banding asesmen.</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════
         TAB DOKUMEN
    ══════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-docs">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-2">Foto</div>
                        @if($asesmen->photo_path)
                        <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                             class="img-fluid rounded" style="max-height:200px;" alt="foto">
                        @else
                        <div class="text-muted py-4">
                            <i class="bi bi-image" style="font-size:2rem;"></i><br>Belum ada foto
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-2">KTP</div>
                        @if($asesmen->ktp_path)
                        <a href="{{ asset('storage/' . $asesmen->ktp_path) }}" target="_blank"
                           class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Lihat KTP
                        </a>
                        @else
                        <div class="text-muted py-4">
                            <i class="bi bi-file-earmark-x" style="font-size:2rem;"></i><br>Belum ada
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-2">Dokumen Pendukung</div>
                        @if($asesmen->document_path)
                        <a href="{{ asset('storage/' . $asesmen->document_path) }}" target="_blank"
                           class="btn btn-outline-primary">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Lihat Dokumen
                        </a>
                        @else
                        <div class="text-muted py-4">
                            <i class="bi bi-file-earmark-x" style="font-size:2rem;"></i><br>Belum ada
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end tab-content --}}

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const SIGN_URL   = '{{ route("asesor.frak01.sign", [$schedule, $asesmen]) }}';
const VERIFY_URL = '{{ route("asesor.apl02.verify", [$schedule, $asesmen]) }}';

// Init sig pads saat tab aktif
document.querySelector('[data-bs-target="#tab-frak01"]')?.addEventListener('shown.bs.tab', () => {
    @if($frak01 && $frak01->status === 'submitted')
    SigPadManager.init('asesor-frak01', @json(auth()->user()->signature_image));
    @endif
});

document.querySelector('[data-bs-target="#tab-apl02"]')?.addEventListener('shown.bs.tab', () => {
    @if(isset($apldua) && $apldua->status === 'submitted')
    SigPadManager.init('asesor-apl02', @json(auth()->user()->signature_image));
    @endif
});

// ── TTD FR.AK.01 ─────────────────────────────────────────────
async function signFrak01() {
    if (SigPadManager.isEmpty('asesor-frak01')) {
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Diperlukan', text: 'Silakan tanda tangan di kotak yang tersedia.' });
        return;
    }
    const result = await Swal.fire({
        title: 'Verifikasi FR.AK.01?',
        text: 'Anda akan menandatangani dan memverifikasi FR.AK.01 untuk asesi ini.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
    });
    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('signature',   await SigPadManager.prepareAndGet('asesor-frak01'));
    formData.append('nama_asesor', '{{ $asesor->nama }}');

    try {
        const res  = await fetch(SIGN_URL, {
            method: 'POST', body: formData,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1800 })
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
}

// ── Verifikasi APL-02 ─────────────────────────────────────────
async function submitVerifikasiApl02() {
    const rekomendasi = document.querySelector('input[name="rekomendasi"]:checked')?.value;
    const catatan     = document.getElementById('catatan-asesor')?.value?.trim() ?? '';
    const namaAsesor  = document.getElementById('nama-asesor')?.value?.trim() ?? '';
    const agreed      = document.getElementById('asesor-agree')?.checked;

    if (!rekomendasi) { Swal.fire('Rekomendasi Diperlukan', 'Pilih rekomendasi terlebih dahulu.', 'warning'); return; }
    if (SigPadManager.isEmpty('asesor-apl02')) { Swal.fire('TTD Diperlukan', 'Tanda tangan di kotak yang tersedia.', 'warning'); return; }
    if (!agreed) { Swal.fire('Persetujuan Diperlukan', 'Centang pernyataan persetujuan terlebih dahulu.', 'warning'); return; }

    const confirm = await Swal.fire({
        title: 'Konfirmasi Verifikasi APL-02',
        html: `<p>Rekomendasi: <strong>${rekomendasi === 'lanjut' ? '✅ Lanjut Asesmen' : '❌ Tidak Lanjut'}</strong></p>
               <div class="alert alert-warning py-2 small mb-0">Setelah diverifikasi, tidak dapat diubah kembali.</div>`,
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi', cancelButtonText: 'Periksa Ulang',
        confirmButtonColor: '#0d6efd', reverseButtons: true,
    });
    if (!confirm.isConfirmed) return;

    const fd = new FormData();
    fd.append('rekomendasi', rekomendasi);
    fd.append('catatan', catatan);
    fd.append('nama_asesor', namaAsesor);
    fd.append('signature', await SigPadManager.prepareAndGet('asesor-apl02'));

    try {
        const res  = await fetch(VERIFY_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'APL-02 Berhasil Diverifikasi!', text: 'Rekomendasi dan tanda tangan tersimpan.' })
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
}
</script>
@endpush

@endsection