@extends('layouts.app')
@section('title', 'Detail Asesi - ' . $asesmen->full_name)
@section('page-title', 'Detail Asesi')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@push('styles')
<style>
.doc-section-header {
    background: #f8fafc;
    border-left: 4px solid #627be9;
    padding: 10px 16px;
    border-radius: 0 6px 6px 0;
    margin-bottom: 12px;
}
.kuk-list li { font-size: .78rem; color: #6b7280; line-height: 1.6; }
.jawaban-badge-K  { background: #d1fae5; color: #065f46; }
.jawaban-badge-BK { background: #fee2e2; color: #991b1b; }
.jawaban-badge    { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: .75rem; font-weight: 700; }

#sig-wrapper-asesor {
    border: 2px dashed #94a3b8;
    border-radius: 8px;
    background: #f0f4f8;
    overflow: hidden;
}
#sig-canvas-asesor {
    display: block;
    width: 100%;
    height: 180px;
    touch-action: none;
    cursor: crosshair;
}
.hint-box {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: .78rem;
    color: #92400e;
}
</style>
@endpush

@section('content')

{{-- Breadcrumb ─────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('asesor.schedule') }}">Jadwal</a></li>
        <li class="breadcrumb-item"><a href="{{ route('asesor.schedule.detail', $schedule) }}">{{ $schedule->assessment_date->format('d M Y') }}</a></li>
        <li class="breadcrumb-item active">{{ $asesmen->full_name }}</li>
    </ol>
</nav>

{{-- Header asesi ────────────────────────────────────────── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex align-items-start gap-4 flex-wrap">
            {{-- Foto ── --}}
            @if($asesmen->photo_path)
            <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                 class="rounded border" style="width:90px;height:110px;object-fit:cover;" alt="foto">
            @else
            <div class="rounded bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:90px;height:110px;font-size:2rem;">
                {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
            </div>
            @endif

            {{-- Info ── --}}
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

{{-- ══════════ TABS ══════════ --}}
<ul class="nav nav-tabs mb-4" id="asesiTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-apl01">
            <i class="bi bi-file-earmark-person me-1"></i>APL-01
            @if($asesmen->aplsatu)
            <span class="badge bg-{{ $asesmen->aplsatu->status === 'verified' ? 'success' : 'info' }} ms-1" style="font-size:.65rem;">
                {{ ucfirst($asesmen->aplsatu->status) }}
            </span>
            @endif
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-apl02">
            <i class="bi bi-clipboard-check me-1"></i>APL-02 (Asesmen Mandiri)
            @if($asesmen->apldua)
            <span class="badge bg-{{ $asesmen->apldua->status_badge }} ms-1" style="font-size:.65rem;">
                {{ $asesmen->apldua->status_label }}
            </span>
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

    {{-- ─── TAB APL-01 ─── --}}
    <div class="tab-pane fade show active" id="tab-apl01">
        @if($asesmen->aplsatu)
        @php $aplsatu = $asesmen->aplsatu; @endphp
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-file-earmark-person text-primary me-2"></i>FR.APL.01 — Permohonan Sertifikasi</h6>
                <div class="d-flex gap-2">
                    <span class="badge bg-{{ $aplsatu->status === 'verified' ? 'success' : 'info' }}">
                        {{ ucfirst($aplsatu->status) }}
                    </span>
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

                {{-- Bukti kelengkapan ── --}}
                @if($aplsatu->buktiKelengkapan->isNotEmpty())
                <hr class="my-3">
                <h6 class="fw-semibold mb-3 small text-muted text-uppercase">Bukti Kelengkapan Dokumen</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40%">Dokumen</th>
                                <th>Status</th>
                                <th>Link / File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($aplsatu->buktiKelengkapan as $bukti)
                            <tr>
                                <td class="small">{{ $bukti->nama_dokumen }}</td>
                                <td>
                                    <span class="badge bg-{{ match($bukti->status) {
                                        'Ada Memenuhi Syarat' => 'success',
                                        'Ada Tidak Memenuhi Syarat' => 'warning',
                                        default => 'secondary'
                                    } }}" style="font-size:.68rem;">
                                        {{ $bukti->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($bukti->gdrive_file_url)
                                    <a href="{{ $bukti->gdrive_file_url }}" target="_blank" class="btn btn-xs btn-outline-primary" style="font-size:.72rem; padding:.15rem .4rem;">
                                        <i class="bi bi-google me-1"></i>Drive
                                    </a>
                                    @else
                                    <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- TTD asesi ── --}}
                @if($aplsatu->ttd_asesi ?? false)
                <hr class="my-3">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <div class="text-muted small mb-1">TTD Pemohon:</div>
                        <img src="{{ 'data:image/png;base64,' . $aplsatu->ttd_asesi }}"
                             style="max-height:50px; border:1px solid #e2e8f0; border-radius:4px; padding:2px;" alt="TTD">
                    </div>
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
            <i class="bi bi-file-earmark-x" style="font-size:2.5rem;"></i>
            <p class="mt-2">APL-01 belum disubmit oleh asesi.</p>
        </div>
        @endif
    </div>

    {{-- ─── TAB APL-02 ─── --}}
    <div class="tab-pane fade" id="tab-apl02">
        @if($asesmen->apldua)
        @php
            $apldua  = $asesmen->apldua;
            $progress = $apldua->progress;
            $jawMap  = $apldua->jawabans->keyBy('elemen_id');
        @endphp

        {{-- Status banner ── --}}
        @if($apldua && in_array($apldua->status, ['verified', 'approved']))
        <div class="alert alert-success d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-check-circle-fill fs-4"></i>
            <div>
                <strong>APL-02 sudah Anda verifikasi</strong> pada {{ $apldua->verified_at?->format('d M Y H:i') }}.
                Rekomendasi: <span class="fw-bold">{{ $apldua->rekomendasi_asesor === 'lanjut' ? 'Lanjut Asesmen' : 'Tidak Lanjut' }}</span>
            </div>
        </div>
        <div class="mb-3 d-flex justify-content-end">
            <a href="{{ route('asesor.asesi.apl02.preview', [$schedule, $asesmen]) }}" target="_blank"
            class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-pdf me-1"></i> Preview PDF APL-02
            </a>
        </div>
        @elseif($apldua->status === 'submitted')
        <div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
            <i class="bi bi-exclamation-circle-fill fs-4"></i>
            <div><strong>APL-02 menunggu verifikasi Anda.</strong> Periksa jawaban asesi dan tanda tangani di bawah.</div>
        </div>
        @else
        <div class="alert alert-secondary mb-4">
            <i class="bi bi-info-circle me-2"></i>APL-02 masih dalam status <strong>{{ $apldua->status_label }}</strong>.
            Belum bisa diverifikasi.
        </div>
        @endif

        {{-- Progress summary ── --}}
        <div class="row g-3 mb-4 text-center">
            <div class="col-4">
                <div class="bg-light rounded p-3">
                    <div class="fw-bold fs-4">{{ $progress['total'] }}</div>
                    <div class="small text-muted">Total Elemen</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-success bg-opacity-10 rounded p-3">
                    <div class="fw-bold fs-4 text-success">{{ $progress['k'] }}</div>
                    <div class="small text-muted">Kompeten (K)</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-danger bg-opacity-10 rounded p-3">
                    <div class="fw-bold fs-4 text-danger">{{ $progress['bk'] }}</div>
                    <div class="small text-muted">Blm Kompeten (BK)</div>
                </div>
            </div>
        </div>

        {{-- Unit accordion — read-only ── --}}
        @foreach($asesmen->skema->unitKompetensis as $unitIdx => $unit)
        <div class="card border shadow-sm mb-3">
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
                <div class="d-flex gap-1">
                    <span class="badge bg-success" style="font-size:.65rem;">K: {{ $unitK }}</span>
                    <span class="badge bg-danger" style="font-size:.65rem;">BK: {{ $unitBK }}</span>
                </div>
            </div>
            <div class="collapse show" id="apl02-unit-{{ $unit->id }}">
                <div class="card-body p-0">
                    @foreach($unit->elemens as $elemen)
                    @php $jaw = $jawMap[$elemen->id] ?? null; @endphp
                    <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom
                        {{ $jaw?->jawaban === 'K' ? 'border-start border-success border-3' : ($jaw?->jawaban === 'BK' ? 'border-start border-danger border-3' : '') }}">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small mb-1">{{ $elemen->judul }}</div>

                            {{-- KUK ── --}}
                            @if($elemen->kuks->isNotEmpty())
                            <ul class="kuk-list mb-2 ps-3">
                                @foreach($elemen->kuks as $kuk)
                                <li>{{ $kuk->kode }} — {{ $kuk->deskripsi }}</li>
                                @endforeach
                            </ul>
                            @endif

                            {{-- Hint ── --}}
                            @if($elemen->hint_bukti)
                            <div class="hint-box mb-2 d-flex align-items-start gap-1">
                                <i class="bi bi-lightbulb-fill text-warning mt-1" style="font-size:.75rem; flex-shrink:0;"></i>
                                <span>{{ $elemen->hint_bukti }}</span>
                            </div>
                            @endif

                            {{-- Bukti dari asesi ── --}}
                            @if($jaw?->bukti)
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="bi bi-chat-left-dots me-1"></i>{{ $jaw->bukti }}
                                @if(str_starts_with($jaw->bukti, 'http'))
                                <a href="{{ $jaw->bukti }}" target="_blank" class="ms-1 btn btn-xs btn-outline-primary" style="font-size:.68rem;padding:.1rem .3rem;">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Jawaban badge ── --}}
                        <div class="flex-shrink-0 text-end" style="min-width:90px;">
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

        {{-- TTD Asesi ── --}}
        @if($apldua->ttd_asesi)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 small fw-semibold text-muted text-uppercase">Tanda Tangan Asesi</h6>
            </div>
            <div class="card-body d-flex align-items-center gap-4">
                <img src="{{ $apldua->ttd_asesi_image }}"
                     style="max-height:60px; border:1px solid #e2e8f0; border-radius:6px; padding:4px; background:#fff;" alt="TTD Asesi">
                <div>
                    <div class="fw-semibold">{{ $apldua->nama_ttd_asesi }}</div>
                    <div class="text-muted small">{{ $apldua->tanggal_ttd_asesi?->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══ FORM VERIFIKASI ASESOR ══ --}}
        @if($apldua->status === 'submitted')
        <div class="card border-0 shadow-sm mt-4" id="verify-section">
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
                    <label class="form-label fw-semibold">Nama Asesor <span class="text-danger">*</span></label>
                    <input type="text" id="nama-asesor" class="form-control"
                           value="{{ $asesor->nama }}" placeholder="Nama lengkap asesor">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Tanda Tangan Asesor <span class="text-danger">*</span></label>
                    <div id="sig-wrapper-asesor">
                        <canvas id="sig-canvas-asesor"></canvas>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">Gunakan mouse atau layar sentuh untuk menandatangani.</small>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSig()">
                            <i class="bi bi-eraser me-1"></i>Hapus TTD
                        </button>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="asesor-agree">
                    <label class="form-check-label" for="asesor-agree">
                        Saya menyatakan bahwa saya telah memeriksa APL-02 asesi ini dan memberikan rekomendasi
                        berdasarkan penilaian yang <strong>objektif dan profesional</strong>.
                    </label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-success px-4" onclick="submitVerifikasi()">
                        <i class="bi bi-check2-circle me-1"></i>Verifikasi & Tanda Tangan
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Tampilkan TTD Asesor jika sudah verified ── --}}
        @if($apldua->status === 'verified' && $apldua->ttd_asesor)
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-person-check-fill me-2"></i>Tanda Tangan & Rekomendasi Asesor
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <img src="{{ $apldua->ttd_asesor_image }}"
                         style="max-height:60px; border:1px solid #e2e8f0; border-radius:6px; padding:4px; background:#fff;" alt="TTD Asesor">
                    <div>
                        <div class="fw-semibold">{{ $apldua->nama_ttd_asesor }}</div>
                        <div class="text-muted small">{{ $apldua->tanggal_ttd_asesor?->format('d M Y H:i') }}</div>
                        <div class="mt-1">
                            <span class="badge bg-{{ $apldua->rekomendasi_asesor === 'lanjut' ? 'success' : 'danger' }}">
                                {{ $apldua->rekomendasi_asesor === 'lanjut' ? '✅ Lanjut Asesmen' : '❌ Tidak Lanjut' }}
                            </span>
                        </div>
                        @if($apldua->catatan_asesor)
                        <div class="text-muted small mt-1">{{ $apldua->catatan_asesor }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-clipboard-x" style="font-size:2.5rem;"></i>
            <p class="mt-2">APL-02 belum disubmit oleh asesi.</p>
        </div>
        @endif
    </div>

    {{-- ─── TAB DOKUMEN ─── --}}
    <div class="tab-pane fade" id="tab-docs">
        <div class="row g-3">

            {{-- Foto ── --}}
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small fw-semibold text-uppercase mb-2">Foto</div>
                        @if($asesmen->photo_path)
                        <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                             class="img-fluid rounded" style="max-height:200px;" alt="foto">
                        @else
                        <div class="text-muted py-4"><i class="bi bi-image" style="font-size:2rem;"></i><br>Belum ada foto</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- KTP ── --}}
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
                        <div class="text-muted py-4"><i class="bi bi-file-earmark-x" style="font-size:2rem;"></i><br>Belum ada</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Dokumen Pendukung ── --}}
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
                        <div class="text-muted py-4"><i class="bi bi-file-earmark-x" style="font-size:2rem;"></i><br>Belum ada</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sertifikat ── --}}
            @if($asesmen->certificate)
            <div class="col-12">
                <div class="card border-0 shadow-sm border-start border-success border-4">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold text-success"><i class="bi bi-award-fill me-2"></i>Sertifikat Kompetensi</div>
                            <div class="text-muted small">
                                No: {{ $asesmen->certificate->certificate_number }} &bull;
                                Berlaku s/d: {{ $asesmen->certificate->valid_until?->format('d M Y') }}
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $asesmen->certificate->pdf_path) }}" target="_blank"
                           class="btn btn-success">
                            <i class="bi bi-download me-1"></i>Download Sertifikat
                        </a>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>{{-- end tab-content --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const VERIFY_URL = '{{ route("asesor.apl02.verify", [$schedule, $asesmen]) }}';

let sigPad = null;

// Init signature pad saat tab APL-02 dibuka
document.querySelector('[data-bs-target="#tab-apl02"]')?.addEventListener('shown.bs.tab', initSig);
window.addEventListener('DOMContentLoaded', () => {
    // Jika langsung di tab APL-02 saat load (misal ada hash)
    if (document.getElementById('tab-apl02')?.classList.contains('show')) initSig();
});

function initSig() {
    const canvas = document.getElementById('sig-canvas-asesor');
    if (!canvas || sigPad) return;

    const ratio = Math.max(window.devicePixelRatio ?? 1, 1);
    canvas.width  = canvas.offsetWidth * ratio;
    canvas.height = 180 * ratio;
    canvas.style.height = '180px';
    canvas.getContext('2d').scale(ratio, ratio);

    sigPad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255,255,255)',
        penColor: 'rgb(0,0,180)',
        minWidth: 1.5, maxWidth: 3.5,
    });
    ['touchstart','touchmove'].forEach(ev =>
        canvas.addEventListener(ev, e => e.preventDefault(), { passive: false })
    );
}

function clearSig() { sigPad?.clear(); }

async function submitVerifikasi() {
    const rekomendasi = document.querySelector('input[name="rekomendasi"]:checked')?.value;
    const catatan     = document.getElementById('catatan-asesor')?.value?.trim() ?? '';
    const namaAsesor  = document.getElementById('nama-asesor')?.value?.trim() ?? '';
    const agreed      = document.getElementById('asesor-agree')?.checked;

    if (!rekomendasi) {
        Swal.fire('Rekomendasi Diperlukan', 'Pilih rekomendasi terlebih dahulu.', 'warning');
        return;
    }
    if (!namaAsesor) {
        Swal.fire('Nama Diperlukan', 'Isi nama asesor terlebih dahulu.', 'warning');
        return;
    }
    if (!sigPad || sigPad.isEmpty()) {
        Swal.fire('TTD Diperlukan', 'Tanda tangani di kotak yang tersedia.', 'warning');
        return;
    }
    if (!agreed) {
        Swal.fire('Persetujuan Diperlukan', 'Centang pernyataan persetujuan terlebih dahulu.', 'warning');
        return;
    }

    const labelRek = rekomendasi === 'lanjut' ? '✅ Lanjut Asesmen' : '❌ Tidak Lanjut';

    const confirm = await Swal.fire({
        title: 'Konfirmasi Verifikasi APL-02',
        html: `
            <p>Rekomendasi Anda: <strong>${labelRek}</strong></p>
            ${catatan ? `<p class="text-muted small">"${catatan}"</p>` : ''}
            <div class="alert alert-warning py-2 small mb-0">
                Setelah diverifikasi, tidak dapat diubah kembali.
            </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Verifikasi',
        cancelButtonText: 'Periksa Ulang',
        confirmButtonColor: '#0d6efd',
        reverseButtons: true,
    });

    if (!confirm.isConfirmed) return;

    try {
        const res = await fetch(VERIFY_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: (() => {
                const fd = new FormData();
                fd.append('rekomendasi', rekomendasi);
                fd.append('catatan', catatan);
                fd.append('nama_asesor', namaAsesor);
                fd.append('signature', sigPad.toDataURL('image/png'));
                return fd;
            })(),
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'APL-02 Berhasil Diverifikasi!',
                text: 'Rekomendasi dan tanda tangan Anda telah tersimpan.',
            });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
        }
    } catch (e) {
        console.error('[VERIFY-APL02]', e);
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
}
</script>
@endpush