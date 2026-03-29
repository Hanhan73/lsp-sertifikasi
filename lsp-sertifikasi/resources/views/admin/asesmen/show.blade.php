@extends('layouts.app')
@section('title', 'Detail Asesi — ' . $asesmen->full_name)
@section('page-title', 'Detail Asesi')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
/* ── Design system ──────────────────────────────────────── */
.section-heading {
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 12px;
}
.info-row { display: flex; gap: 8px; padding: 6px 0; border-bottom: 1px solid #f1f5f9; }
.info-row:last-child { border-bottom: none; }
.info-label { color: #94a3b8; font-size: .82rem; min-width: 160px; flex-shrink: 0; }
.info-value { font-weight: 600; font-size: .9rem; }

/* ── TTD ────────────────────────────────────────────────── */
.ttd-thumb {
    max-height: 70px; max-width: 180px; object-fit: contain;
    border: 1px solid #e2e8f0; border-radius: 4px; background: #fff; padding: 4px;
}

/* ── Bukti cards ────────────────────────────────────────── */
.bukti-card { border-left: 4px solid #e2e8f0; border-radius: 0 6px 6px 0; }
.bukti-card.ok   { border-left-color: #22c55e; }
.bukti-card.warn { border-left-color: #f59e0b; }

/* ── Batch table ────────────────────────────────────────── */
.batch-member-row { cursor: pointer; }
.batch-member-row:hover { background: #f8faff; }

/* ── APL-02 elemen rows ─────────────────────────────────── */
.elemen-row { border-bottom: 1px solid #f1f5f9; padding: 10px 16px; }
.elemen-row:last-child { border-bottom: none; }
.elemen-row.answered-K  { border-left: 3px solid #10b981; }
.elemen-row.answered-BK { border-left: 3px solid #ef4444; }
.elemen-row.unanswered  { border-left: 3px solid #e2e8f0; }
.ro-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: .78rem; font-weight: 700; }
.ro-badge-K  { background: #d1fae5; color: #065f46; }
.ro-badge-BK { background: #fee2e2; color: #991b1b; }

/* ── Unit accordion (APL-02 & AK.01) ───────────────────── */
.unit-card { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 10px; }
.unit-header {
    background: #f8fafc; padding: 10px 14px; cursor: pointer;
    user-select: none; border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; gap: 10px;
}
.unit-header:hover { background: #f1f5f9; }

/* ── FR.AK.04 banding ───────────────────────────────────── */
.pertanyaan-card {
    border: 1px solid #e2e8f0; border-radius: 8px;
    padding: 12px 16px; margin-bottom: 8px; background: #fff;
}
.pertanyaan-card.answered { border-color: #3b82f6; background: #f0f7ff; }

/* ── Status bar helper ──────────────────────────────────── */
.status-bar-submitted { background: #eff6ff; border: 1px solid #bfdbfe; }
.status-bar-verified  { background: #f0fdf4; border: 1px solid #bbf7d0; }
.status-bar-default   { background: #f8fafc; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')

{{-- ── Breadcrumb ─────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.asesi') }}">Semua Asesi</a></li>
        @if($asesmen->is_collective && $asesmen->collective_batch_id)
        <li class="breadcrumb-item text-muted">Batch {{ $asesmen->collective_batch_id }}</li>
        @endif
        <li class="breadcrumb-item active">{{ $asesmen->full_name }}</li>
    </ol>
</nav>

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — Header Cards
══════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Identitas Asesi --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex gap-3 align-items-start">
                @if($asesmen->photo_path)
                <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                     class="rounded" style="width:72px;height:90px;object-fit:cover;flex-shrink:0;">
                @else
                <div class="rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:72px;height:90px;">
                    <i class="bi bi-person-fill text-muted fs-2"></i>
                </div>
                @endif
                <div class="flex-grow-1">
                    <h5 class="mb-1 fw-bold">{{ $asesmen->full_name }}</h5>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-{{ $asesmen->status_badge }} fs-6">{{ $asesmen->status_label }}</span>
                        @if($asesmen->is_collective)
                        <span class="badge bg-primary"><i class="bi bi-people me-1"></i>Kolektif</span>
                        @else
                        <span class="badge bg-success"><i class="bi bi-person me-1"></i>Mandiri</span>
                        @endif
                    </div>
                    <div class="small text-muted">
                        <div><i class="bi bi-credit-card me-1"></i><span class="font-monospace">{{ $asesmen->nik ?? '-' }}</span></div>
                        <div><i class="bi bi-telephone me-1"></i>{{ $asesmen->phone ?? '-' }}</div>
                        <div><i class="bi bi-envelope me-1"></i>{{ $asesmen->user->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Asesmen --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-award me-1"></i>Info Asesmen</div>
                <div class="info-row"><span class="info-label">Skema</span><span class="info-value">{{ $asesmen->skema->name ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">TUK</span><span class="info-value">{{ $asesmen->tuk->name ?? '-' }}</span></div>
                <div class="info-row">
                    <span class="info-label">Jadwal</span>
                    <span class="info-value">
                        {{ $asesmen->schedule?->assessment_date?->translatedFormat('d F Y') ?? '-' }}
                        @if($asesmen->schedule?->start_time)
                        <span class="text-muted small ms-1">{{ $asesmen->schedule->start_time }}</span>
                        @endif
                    </span>
                </div>
                <div class="info-row"><span class="info-label">Asesor</span><span class="info-value">{{ $asesmen->schedule?->asesor?->nama ?? '-' }}</span></div>
                <div class="info-row"><span class="info-label">Tgl Daftar</span><span class="info-value">{{ $asesmen->registration_date->format('d M Y') }}</span></div>
                @if($asesmen->is_collective)
                <div class="info-row"><span class="info-label">Didaftarkan oleh</span><span class="info-value">{{ $asesmen->registrar->name ?? '-' }}</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Status Dokumen --}}
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="section-heading"><i class="bi bi-files me-1"></i>Status Dokumen</div>
                @foreach([
                    ['label' => 'APL-01',   'model' => $asesmen->aplsatu],
                    ['label' => 'APL-02',   'model' => $asesmen->apldua],
                    ['label' => 'FR.AK.01', 'model' => $asesmen->frak01],
                    ['label' => 'FR.AK.04', 'model' => $asesmen->frak04],
                ] as $doc)
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span class="small fw-semibold">{{ $doc['label'] }}</span>
                    <span class="badge bg-{{ $doc['model']?->status_badge ?? 'light border' }}">
                        {{ $doc['model']?->status_label ?? '-' }}
                    </span>
                </div>
                @endforeach
                @if($asesmen->result)
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">Hasil Asesmen</span>
                    <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                        {{ ucfirst($asesmen->result) }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     BATCH INFO
══════════════════════════════════════════════════════════ --}}
@if($asesmen->is_collective && $batchMembers?->count() > 1)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">
            <i class="bi bi-people-fill me-2 text-primary"></i>
            Batch Kolektif — <code>{{ $asesmen->collective_batch_id }}</code>
            <span class="badge bg-primary ms-2">{{ $batchMembers->count() }} peserta</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary" type="button"
                data-bs-toggle="collapse" data-bs-target="#batchTable">
            <i class="bi bi-chevron-down"></i> Tampilkan
        </button>
    </div>
    <div class="collapse" id="batchTable">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light" style="font-size:.78rem;">
                    <tr>
                        <th class="ps-3">#</th><th>Nama</th><th>Status</th>
                        <th class="text-center">APL-01</th><th class="text-center">APL-02</th>
                        <th class="text-center">AK.01</th><th class="text-center">AK.04</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batchMembers as $i => $member)
                    <tr class="batch-member-row {{ $member->id === $asesmen->id ? 'table-primary' : '' }}"
                        onclick="window.location='{{ route('admin.asesi.show', $member) }}'">
                        <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                        <td>
                            <span class="fw-semibold">{{ $member->full_name }}</span>
                            @if($member->id === $asesmen->id)
                            <span class="badge bg-primary ms-1" style="font-size:.65rem;">Sedang dilihat</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $member->status_badge }}">{{ $member->status_label }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->aplsatu?->status_badge ?? 'light border' }}">{{ $member->aplsatu?->status_label ?? '-' }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->apldua?->status_badge ?? 'light border' }}">{{ $member->apldua?->status_label ?? '-' }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->frak01?->status_badge ?? 'light border' }}">{{ $member->frak01?->status_label ?? '-' }}</span></td>
                        <td class="text-center"><span class="badge bg-{{ $member->frak04?->status_badge ?? 'light border' }}">{{ $member->frak04?->status_label ?? '-' }}</span></td>
                        <td class="pe-2">@if($member->id !== $asesmen->id)<i class="bi bi-arrow-right text-muted small"></i>@endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════
     TABS UTAMA
══════════════════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom pt-0 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="docTabs">
            <li class="nav-item">
                <button class="nav-link active py-3" data-bs-toggle="tab" data-bs-target="#tab-biodata">
                    <i class="bi bi-person me-1"></i>Biodata
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-apl01">
                    <i class="bi bi-file-earmark-person me-1"></i>APL-01
                    @if($asesmen->aplsatu?->status === 'submitted')
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Perlu Verif</span>
                    @elseif($asesmen->aplsatu?->status === 'verified')
                    <span class="badge bg-success ms-1" style="font-size:.6rem;">✓</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-apl02">
                    <i class="bi bi-clipboard-check me-1"></i>APL-02
                    @if($asesmen->apldua?->status === 'submitted')
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Perlu Verif</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-frak01">
                    <i class="bi bi-file-earmark-check me-1"></i>FR.AK.01
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3" data-bs-toggle="tab" data-bs-target="#tab-frak04">
                    <i class="bi bi-megaphone me-1"></i>FR.AK.04
                    @if($asesmen->frak04?->status === 'submitted')
                    <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Banding!</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">

            {{-- ════════════════════════════════════════════
                 TAB 1 — BIODATA (lengkap, dari pasted-2/doc-4)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade show active" id="tab-biodata">
                <div class="row g-4 pt-2">

                    {{-- Kolom kiri: Data pribadi lengkap --}}
                    <div class="col-md-7">

                        {{-- Informasi Pendaftaran --}}
                        <div class="section-heading">Informasi Pendaftaran</div>
                        <div class="info-row"><span class="info-label">No. Registrasi</span><span class="info-value font-monospace">#{{ $asesmen->id }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Daftar</span><span class="info-value">{{ $asesmen->registration_date->format('d F Y') }}</span></div>
                        <div class="info-row">
                            <span class="info-label">Tipe Pendaftaran</span>
                            <span class="info-value">
                                @if($asesmen->is_collective)
                                <span class="badge bg-primary">Kolektif</span>
                                @else
                                <span class="badge bg-secondary">Mandiri</span>
                                @endif
                            </span>
                        </div>
                        @if($asesmen->is_collective)
                        <div class="info-row"><span class="info-label">Batch ID</span><span class="info-value font-monospace">{{ $asesmen->collective_batch_id }}</span></div>
                        <div class="info-row">
                            <span class="info-label">Waktu Bayar</span>
                            <span class="info-value">
                                <span class="badge bg-{{ $asesmen->collective_payment_timing === 'before' ? 'warning text-dark' : 'success' }}">
                                    {{ $asesmen->collective_payment_timing === 'before' ? 'Sebelum Asesmen' : 'Setelah Asesmen' }}
                                </span>
                            </span>
                        </div>
                        <div class="info-row"><span class="info-label">Didaftarkan Oleh</span><span class="info-value">{{ $asesmen->registrar->name ?? '-' }}</span></div>
                        @endif
                        <div class="info-row"><span class="info-label">Skema</span><span class="info-value">{{ $asesmen->skema->name ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Skema</span><span class="info-value font-monospace small">{{ $asesmen->skema->code ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Biaya Skema</span><span class="info-value">Rp {{ number_format($asesmen->skema->fee ?? 0, 0, ',', '.') }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Pilihan</span><span class="info-value">{{ $asesmen->preferred_date ? $asesmen->preferred_date->format('d F Y') : '-' }}</span></div>

                        {{-- Data Pribadi --}}
                        <div class="section-heading mt-4">Data Pribadi</div>
                        <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value">{{ $asesmen->full_name }}</span></div>
                        <div class="info-row"><span class="info-label">NIK</span><span class="info-value font-monospace">{{ $asesmen->nik }}</span></div>
                        <div class="info-row"><span class="info-label">Tempat Lahir</span><span class="info-value">{{ $asesmen->birth_place }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Lahir</span><span class="info-value">{{ $asesmen->birth_date->format('d F Y') }}</span></div>
                        <div class="info-row"><span class="info-label">Jenis Kelamin</span><span class="info-value">{{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                        <div class="info-row"><span class="info-label">Email</span><span class="info-value">{{ $asesmen->email ?? $asesmen->user->email ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Telepon</span><span class="info-value">{{ $asesmen->phone }}</span></div>
                        <div class="info-row"><span class="info-label">Alamat</span><span class="info-value">{{ $asesmen->address }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Provinsi</span><span class="info-value">{{ $asesmen->province_code ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Kota</span><span class="info-value">{{ $asesmen->city_code ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Pendidikan</span><span class="info-value">{{ $asesmen->education ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Pekerjaan</span><span class="info-value">{{ $asesmen->occupation ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Asal Lembaga</span><span class="info-value">{{ $asesmen->institution ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Sumber Anggaran</span><span class="info-value">{{ $asesmen->budget_source ?? '-' }}</span></div>
                    </div>

                    {{-- Kolom kanan: Dokumen pendaftaran --}}
                    <div class="col-md-5">
                        <div class="section-heading">Dokumen Pendaftaran</div>

                        {{-- Pas Foto --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 small fw-semibold">Pas Foto</div>
                            <div class="card-body text-center p-2">
                                @if($asesmen->photo_path)
                                <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                                     alt="Foto" class="img-thumbnail mb-2"
                                     style="max-height:200px; object-fit:cover;">
                                <div>
                                    <a href="{{ asset('storage/' . $asesmen->photo_path) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </a>
                                </div>
                                @else
                                <div class="py-3 text-muted small">
                                    <i class="bi bi-x-circle text-danger d-block fs-3 mb-1"></i>Belum upload
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- KTP --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 small fw-semibold">KTP</div>
                            <div class="card-body text-center p-2">
                                @if($asesmen->ktp_path)
                                <iframe src="{{ asset('storage/' . $asesmen->ktp_path) }}"
                                        style="width:100%; height:180px; border:1px solid #ddd;"
                                        class="mb-2"></iframe>
                                <div>
                                    <a href="{{ asset('storage/' . $asesmen->ktp_path) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                                @else
                                <div class="py-3 text-muted small">
                                    <i class="bi bi-x-circle text-danger d-block fs-3 mb-1"></i>Belum upload
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Ijazah --}}
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 small fw-semibold">Ijazah / Transkrip</div>
                            <div class="card-body text-center p-2">
                                @if($asesmen->document_path)
                                <iframe src="{{ asset('storage/' . $asesmen->document_path) }}"
                                        style="width:100%; height:180px; border:1px solid #ddd;"
                                        class="mb-2"></iframe>
                                <div>
                                    <a href="{{ asset('storage/' . $asesmen->document_path) }}"
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                </div>
                                @else
                                <div class="py-3 text-muted small">
                                    <i class="bi bi-x-circle text-danger d-block fs-3 mb-1"></i>Belum upload
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 2 — APL-01 (read-only dari doc-5)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-apl01">
                @if(!$asesmen->aplsatu)
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                    <p class="mt-2">APL-01 belum diisi oleh asesi.</p>
                </div>
                @else
                @php $apl = $asesmen->aplsatu; @endphp

                {{-- Status bar --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded
                    {{ $apl->status === 'verified' ? 'status-bar-verified' : ($apl->status === 'submitted' ? 'status-bar-submitted' : 'status-bar-default') }}">
                    <span class="badge bg-{{ $apl->status_badge }} fs-6">{{ $apl->status_label }}</span>
                    @if($apl->submitted_at)
                    <span class="small text-muted">Submit: {{ $apl->submitted_at->format('d M Y H:i') }}</span>
                    @endif
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('admin.apl01.pdf', [$apl, 'preview' => 1]) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-pdf me-1"></i>Preview PDF
                        </a>
                        <a href="{{ route('admin.apl01.pdf', $apl) }}"
                           class="btn btn-sm btn-success">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                        @if($apl->status === 'submitted')
                        <a href="{{ route('admin.apl01.show', $apl) }}"
                           class="btn btn-sm btn-warning text-dark">
                            <i class="bi bi-pen me-1"></i>Verifikasi
                        </a>
                        @endif
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Data Pribadi --}}
                    <div class="col-md-6">
                        <div class="section-heading">a. Data Pribadi</div>
                        <div class="info-row"><span class="info-label">Nama Lengkap</span><span class="info-value">{{ $apl->nama_lengkap }}</span></div>
                        <div class="info-row"><span class="info-label">NIK</span><span class="info-value font-monospace">{{ $apl->nik }}</span></div>
                        <div class="info-row"><span class="info-label">Tempat Lahir</span><span class="info-value">{{ $apl->tempat_lahir }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Lahir</span><span class="info-value">{{ $apl->tanggal_lahir?->format('d M Y') ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Jenis Kelamin</span><span class="info-value">{{ $apl->jenis_kelamin }}</span></div>
                        <div class="info-row"><span class="info-label">Kebangsaan</span><span class="info-value">{{ $apl->kebangsaan ?? 'Indonesia' }}</span></div>
                        <div class="info-row"><span class="info-label">Kualifikasi Pendidikan</span><span class="info-value">{{ $apl->kualifikasi_pendidikan ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Alamat Rumah</span><span class="info-value">{{ $apl->alamat_rumah }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Pos</span><span class="info-value">{{ $apl->kode_pos ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Telepon Rumah</span><span class="info-value">{{ $apl->telp_rumah ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">No. HP</span><span class="info-value">{{ $apl->hp }}</span></div>
                        <div class="info-row"><span class="info-label">Email</span><span class="info-value">{{ $apl->email }}</span></div>

                        <div class="section-heading mt-4">b. Data Pekerjaan</div>
                        <div class="info-row"><span class="info-label">Institusi / Perusahaan</span><span class="info-value">{{ $apl->nama_institusi ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Jabatan</span><span class="info-value">{{ $apl->jabatan ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Alamat Kantor</span><span class="info-value">{{ $apl->alamat_kantor ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Kode Pos Kantor</span><span class="info-value">{{ $apl->kode_pos_kantor ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Telepon Kantor</span><span class="info-value">{{ $apl->telp_kantor_detail ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Fax</span><span class="info-value">{{ $apl->fax_kantor ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Email Kantor</span><span class="info-value">{{ $apl->email_kantor ?? '-' }}</span></div>

                        <div class="section-heading mt-4">c. Data Sertifikasi</div>
                        <div class="info-row"><span class="info-label">Skema</span><span class="info-value fw-bold">{{ $apl->asesmen?->skema?->name ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">Nomor Skema</span><span class="info-value font-monospace small">{{ $apl->asesmen?->skema?->nomor_skema ?? ($apl->asesmen?->skema?->code ?? '-') }}</span></div>
                        <div class="info-row">
                            <span class="info-label">Tujuan Asesmen</span>
                            <span class="info-value">
                                {{ $apl->tujuan_asesmen ?? '-' }}
                                @if($apl->tujuan_asesmen === 'Lainnya' && $apl->tujuan_asesmen_lainnya)
                                — {{ $apl->tujuan_asesmen_lainnya }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Unit Kompetensi + TTD + Bukti --}}
                    <div class="col-md-6">

                        {{-- Unit Kompetensi --}}
                        @if($apl->asesmen?->skema?->unitKompetensis?->isNotEmpty())
                        <div class="section-heading">Unit Kompetensi</div>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered" style="font-size:.82rem;">
                                <thead class="table-light">
                                    <tr><th width="30" class="text-center">No</th><th width="140">Kode Unit</th><th>Judul Unit</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($apl->asesmen->skema->unitKompetensis as $i => $unit)
                                    <tr>
                                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                                        <td><small class="font-monospace">{{ $unit->kode_unit }}</small></td>
                                        <td>{{ $unit->judul_unit }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif

                        {{-- Bukti Kelengkapan --}}
                        <div class="section-heading">Bukti Kelengkapan</div>
                        @php $gdriveLink = $apl->buktiKelengkapan->whereNotNull('gdrive_file_url')->first()?->gdrive_file_url; @endphp
                        @if($gdriveLink)
                        <div class="alert alert-info d-flex align-items-center gap-3 mb-3 py-2">
                            <i class="bi bi-google fs-4 flex-shrink-0"></i>
                            <div class="flex-grow-1 small overflow-hidden">
                                <strong>Google Drive:</strong><br>
                                <a href="{{ $gdriveLink }}" target="_blank" class="text-break small">{{ $gdriveLink }}</a>
                            </div>
                            <a href="{{ $gdriveLink }}" target="_blank" class="btn btn-sm btn-primary flex-shrink-0">Buka</a>
                        </div>
                        @endif

                        @foreach(['persyaratan_dasar' => 'Persyaratan Dasar', 'administratif' => 'Administratif'] as $kat => $katLabel)
                        @php $items = $apl->buktiKelengkapan->where('kategori', $kat); @endphp
                        @if($items->isNotEmpty())
                        <div class="fw-semibold small text-muted mb-2 mt-3">{{ $katLabel }}</div>
                        @foreach($items as $bukti)
                        @php
                            $bc = match($bukti->status) {
                                'Ada Memenuhi Syarat'       => 'ok',
                                'Ada Tidak Memenuhi Syarat' => 'warn',
                                default                     => '',
                            };
                            $badgeColor = match($bukti->status) {
                                'Ada Memenuhi Syarat'       => 'success',
                                'Ada Tidak Memenuhi Syarat' => 'warning',
                                default                     => 'secondary',
                            };
                        @endphp
                        <div class="card bukti-card {{ $bc }} mb-2 shadow-sm">
                            <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center gap-2">
                                <div class="small flex-grow-1">
                                    <span class="fw-semibold">{{ $bukti->nama_dokumen }}</span>
                                    @if($bukti->catatan)
                                    <div class="text-muted" style="font-size:.78rem;"><i class="bi bi-chat-left-dots me-1"></i>{{ $bukti->catatan }}</div>
                                    @endif
                                </div>
                                <span class="badge bg-{{ $badgeColor }} text-nowrap flex-shrink-0">{{ $bukti->status }}</span>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        @endforeach

                        {{-- TTD Pemohon --}}
                        @if($apl->ttd_pemohon)
                        <div class="section-heading mt-4">Tanda Tangan Pemohon</div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <img src="{{ $apl->ttd_pemohon_image }}" class="ttd-thumb" alt="TTD Pemohon">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $apl->nama_ttd_pemohon }}</div>
                                {{ $apl->tanggal_ttd_pemohon ? \Carbon\Carbon::parse($apl->tanggal_ttd_pemohon)->format('d M Y') : '-' }}
                            </div>
                        </div>
                        @endif

                        {{-- TTD Admin --}}
                        @if($apl->ttd_admin)
                        <div class="section-heading">Tanda Tangan Admin LSP</div>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $apl->ttd_admin_image }}" class="ttd-thumb" style="border-color:#bbf7d0;" alt="TTD Admin">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $apl->nama_ttd_admin }}</div>
                                {{ $apl->tanggal_ttd_admin ? \Carbon\Carbon::parse($apl->tanggal_ttd_admin)->format('d M Y') : '-' }}
                                @if($apl->verified_at)
                                <div class="mt-1"><i class="bi bi-clock me-1"></i>{{ $apl->verified_at->format('d M Y H:i') }}
                                    @if($apl->verifier) — <strong>{{ $apl->verifier->name }}</strong>@endif
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
                @endif
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 3 — APL-02 (read-only dari doc-6)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-apl02">
                @if(!$asesmen->apldua)
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                    <p class="mt-2">APL-02 belum diisi oleh asesi.</p>
                </div>
                @else
                @php $apl02 = $asesmen->apldua; $prog = $apl02->progress; @endphp

                {{-- Status bar --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded status-bar-default">
                    <span class="badge bg-{{ $apl02->status_badge }} fs-6">{{ $apl02->status_label }}</span>
                    <div class="small text-muted">
                        Progress: <strong>{{ $prog['answered'] }}/{{ $prog['total'] }}</strong> elemen dijawab
                        (<span class="text-success">K: {{ $prog['k'] }}</span> /
                        <span class="text-danger">BK: {{ $prog['bk'] }}</span>)
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        @if(in_array($apl02->status, ['verified', 'approved']))
                        <a href="{{ route('admin.apl02.pdf', [$apl02, 'preview' => 1]) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-pdf me-1"></i>Preview PDF
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Progress summary --}}
                <div class="row g-3 mb-4 text-center">
                    <div class="col-4">
                        <div class="bg-light rounded p-3">
                            <div class="fw-bold fs-4">{{ $prog['total'] }}</div>
                            <div class="small text-muted">Total Elemen</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-success bg-opacity-10 rounded p-3">
                            <div class="fw-bold fs-4 text-success">{{ $prog['k'] }}</div>
                            <div class="small text-muted">Kompeten (K)</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-danger bg-opacity-10 rounded p-3">
                            <div class="fw-bold fs-4 text-danger">{{ $prog['bk'] }}</div>
                            <div class="small text-muted">Blm Kompeten (BK)</div>
                        </div>
                    </div>
                </div>

                {{-- Jawaban per unit (accordion) --}}
                @foreach($asesmen->skema->unitKompetensis as $unitIdx => $unit)
                <div class="unit-card" id="apl02-unit-{{ $unit->id }}">
                    <div class="unit-header" onclick="toggleUnit('apl02-{{ $unit->id }}')">
                        <i class="bi bi-chevron-right" id="arrow-apl02-{{ $unit->id }}"
                           style="transition:transform .2s; font-size:1rem; color:#94a3b8;"></i>
                        <span class="badge bg-secondary me-1">{{ $unitIdx + 1 }}</span>
                        <div class="flex-grow-1">
                            <div class="small text-muted">{{ $unit->kode_unit }}</div>
                            <div class="fw-semibold" style="font-size:.9rem;">{{ $unit->judul_unit }}</div>
                        </div>
                    </div>
                    <div id="body-apl02-{{ $unit->id }}" style="{{ $unitIdx === 0 ? '' : 'display:none;' }}">
                        @foreach($unit->elemens as $elemen)
                        @php $jaw = $jawabanMap[$elemen->id] ?? null; @endphp
                        <div class="elemen-row {{ $jaw?->jawaban ? 'answered-'.$jaw->jawaban : 'unanswered' }} d-flex align-items-start gap-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small mb-1">{{ $elemen->judul }}</div>
                                @if($elemen->kuks->isNotEmpty())
                                <ul class="mb-1" style="padding-left:14px;">
                                    @foreach($elemen->kuks as $kuk)
                                    <li style="font-size:.75rem; color:#6b7280;">{{ $kuk->kode }} — {{ $kuk->deskripsi }}</li>
                                    @endforeach
                                </ul>
                                @endif
                                @if($jaw?->bukti)
                                <div class="text-muted mt-1" style="font-size:.78rem;">
                                    <i class="bi bi-chat-left-dots me-1"></i>{{ $jaw->bukti }}
                                </div>
                                @endif
                            </div>
                            <div class="flex-shrink-0 text-end" style="min-width:100px;">
                                @if($jaw?->jawaban)
                                <span class="ro-badge ro-badge-{{ $jaw->jawaban }}">
                                    {{ $jaw->jawaban === 'K' ? 'Kompeten' : 'Blm Kompeten' }}
                                </span>
                                @else
                                <span class="badge bg-secondary">Belum diisi</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                {{-- TTD --}}
                @if($apl02->ttd_asesi || $apl02->ttd_asesor)
                <div class="row g-3 mt-3">
                    @if($apl02->ttd_asesi)
                    <div class="col-md-4">
                        <div class="section-heading">TTD Asesi</div>
                        <img src="{{ $apl02->ttd_asesi_image }}" class="ttd-thumb" alt="TTD Asesi">
                        <div class="text-muted small mt-1">{{ $apl02->nama_ttd_asesi }}</div>
                    </div>
                    @endif
                    @if($apl02->ttd_asesor)
                    <div class="col-md-4">
                        <div class="section-heading">TTD Asesor</div>
                        <img src="{{ $apl02->ttd_asesor_image }}" class="ttd-thumb" alt="TTD Asesor">
                        <div class="text-muted small mt-1">{{ $apl02->nama_ttd_asesor }}</div>
                        @if($apl02->rekomendasi_asesor)
                        <span class="badge bg-{{ $apl02->rekomendasi_asesor === 'lanjut' ? 'success' : 'danger' }} mt-1 d-block" style="width:fit-content;">
                            {{ $apl02->rekomendasi_asesor === 'lanjut' ? 'Lanjut Asesmen' : 'Tidak Lanjut' }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
                @endif
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 4 — FR.AK.01 (read-only dari doc-7)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-frak01">
                @if(!$asesmen->frak01)
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
                    <p class="mt-2">FR.AK.01 belum dibuat.</p>
                </div>
                @else
                @php $ak01 = $asesmen->frak01; @endphp

                {{-- Status bar --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded status-bar-default">
                    <span class="badge bg-{{ $ak01->status_badge }} fs-6">{{ $ak01->status_label }}</span>
                    @if($ak01->submitted_at)
                    <span class="small text-muted">Submit: {{ $ak01->submitted_at->format('d M Y H:i') }}</span>
                    @endif
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('admin.frak01.pdf', [$ak01, 'preview' => 1]) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-pdf me-1"></i>Preview PDF
                        </a>
                        <a href="{{ route('admin.frak01.pdf', $ak01) }}"
                           class="btn btn-sm btn-success">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="section-heading">Info Dokumen</div>
                        <div class="info-row"><span class="info-label">Skema</span><span class="info-value">{{ $ak01->skema_judul }}</span></div>
                        <div class="info-row"><span class="info-label">TUK</span><span class="info-value">{{ $ak01->tuk_nama }}</span></div>
                        <div class="info-row"><span class="info-label">Asesor</span><span class="info-value">{{ $ak01->nama_asesor }}</span></div>
                        <div class="info-row"><span class="info-label">Hari / Tanggal</span><span class="info-value">{{ $ak01->hari_tanggal }}</span></div>
                        <div class="info-row"><span class="info-label">Waktu</span><span class="info-value">{{ $ak01->waktu_asesmen ?? '-' }}</span></div>

                        <div class="section-heading mt-4">Bukti yang Dikumpulkan</div>
                        @foreach([
                            'bukti_verifikasi_portofolio'     => 'Verifikasi Portofolio',
                            'bukti_hasil_review_produk'        => 'Review Produk',
                            'bukti_observasi_langsung'         => 'Observasi Langsung',
                            'bukti_pertanyaan_lisan'           => 'Pertanyaan Lisan',
                            'bukti_pertanyaan_tertulis'        => 'Pertanyaan Tertulis',
                            'bukti_pertanyaan_wawancara'       => 'Wawancara',
                            'bukti_hasil_kegiatan_terstruktur' => 'Kegiatan Terstruktur',
                        ] as $field => $lbl)
                        <div class="d-flex align-items-center gap-2 py-1 border-bottom">
                            <i class="bi bi-{{ $ak01->$field ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
                            <span class="small {{ $ak01->$field ? '' : 'text-muted' }}">{{ $lbl }}</span>
                        </div>
                        @endforeach
                        @if($ak01->bukti_lainnya)
                        <div class="d-flex align-items-center gap-2 py-1">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span class="small">Lainnya: {{ $ak01->bukti_lainnya_keterangan }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        {{-- TTD Asesi --}}
                        @if($ak01->ttd_asesi)
                        <div class="section-heading">TTD Asesi</div>
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="{{ $ak01->ttd_asesi_image }}" class="ttd-thumb">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $ak01->nama_ttd_asesi }}</div>
                                {{ $ak01->tanggal_ttd_asesi?->format('d M Y') }}
                            </div>
                        </div>
                        @endif

                        {{-- TTD Asesor --}}
                        @if($ak01->ttd_asesor)
                        <div class="section-heading">TTD Asesor</div>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $ak01->ttd_asesor_image }}" class="ttd-thumb">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $ak01->nama_ttd_asesor }}</div>
                                {{ $ak01->tanggal_ttd_asesor?->format('d M Y') }}
                            </div>
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-lock fs-2 opacity-25"></i>
                            <p class="small mt-1">Menunggu tanda tangan asesor.</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            {{-- ════════════════════════════════════════════
                 TAB 5 — FR.AK.04 / BANDING (read-only dari doc-8)
            ════════════════════════════════════════════ --}}
            <div class="tab-pane fade" id="tab-frak04">
                @if(!$asesmen->frak04 || $asesmen->frak04->status === 'draft')
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-megaphone fs-1 opacity-25"></i>
                    <p class="mt-2">Tidak ada banding yang diajukan.</p>
                </div>
                @else
                @php $ak04 = $asesmen->frak04; @endphp

                {{-- Status bar --}}
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded"
                     style="background:#fffbeb; border:1px solid #fde68a;">
                    <span class="badge bg-{{ $ak04->status_badge }} fs-6">{{ $ak04->status_label }}</span>
                    @if($ak04->submitted_at)
                    <span class="small text-muted">Diajukan: {{ $ak04->submitted_at->format('d M Y H:i') }}</span>
                    @endif
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('admin.frak04.pdf', [$ak04, 'preview' => 1]) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-pdf me-1"></i>Preview PDF
                        </a>
                        <a href="{{ route('admin.frak04.pdf', $ak04) }}"
                           class="btn btn-sm btn-success">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-7">
                        <div class="section-heading">Detail Banding</div>
                        <div class="info-row"><span class="info-label">Asesi</span><span class="info-value">{{ $ak04->nama_asesi }}</span></div>
                        <div class="info-row"><span class="info-label">Asesor</span><span class="info-value">{{ $ak04->nama_asesor }}</span></div>
                        <div class="info-row"><span class="info-label">Tanggal Asesmen</span><span class="info-value">{{ $ak04->tanggal_asesmen }}</span></div>
                        <div class="info-row"><span class="info-label">Skema Sertifikasi</span><span class="info-value">{{ $ak04->skema_sertifikasi ?? $asesmen->skema?->name ?? '-' }}</span></div>
                        <div class="info-row"><span class="info-label">No. Skema</span><span class="info-value font-monospace small">{{ $ak04->no_skema_sertifikasi ?? $asesmen->skema?->nomor_skema ?? '-' }}</span></div>

                        <div class="section-heading mt-4">Pernyataan</div>
                        @foreach([
                            ['label' => 'Proses banding sudah dijelaskan', 'val' => $ak04->proses_banding_dijelaskan],
                            ['label' => 'Sudah diskusi dengan asesor',      'val' => $ak04->sudah_diskusi_dengan_asesor],
                            ['label' => 'Melibatkan pihak lain',            'val' => $ak04->melibatkan_orang_lain],
                        ] as $item)
                        <div class="pertanyaan-card {{ $item['val'] !== null ? 'answered' : '' }}">
                            <div class="small fw-semibold mb-1">{{ $item['label'] }}</div>
                            <span class="badge bg-{{ $item['val'] ? 'success' : 'secondary' }} px-3">
                                {{ $item['val'] ? 'YA' : 'TIDAK' }}
                            </span>
                        </div>
                        @endforeach

                        <div class="section-heading mt-3">Alasan Banding</div>
                        <div class="p-3 bg-light rounded small" style="white-space:pre-wrap;">{{ $ak04->alasan_banding }}</div>
                    </div>

                    <div class="col-md-5">
                        @if($ak04->ttd_asesi)
                        <div class="section-heading">TTD Pengaju Banding</div>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $ak04->ttd_asesi_image }}" class="ttd-thumb">
                            <div class="small text-muted">
                                <div class="fw-semibold text-dark">{{ $ak04->nama_ttd_asesi }}</div>
                                {{ $ak04->tanggal_ttd_asesi?->format('d M Y H:i') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

        </div>{{-- end tab-content --}}
    </div>{{-- end card-body --}}
</div>{{-- end card --}}

@endsection

@push('scripts')
<script>
// ── Toggle accordion unit (APL-02 & AK.01) ─────────────────
function toggleUnit(key) {
    const body  = document.getElementById(`body-${key}`);
    const arrow = document.getElementById(`arrow-${key}`);
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (arrow) arrow.style.transform = open ? '' : 'rotate(90deg)';
}

// ── Auto-buka tab via ?tab=... ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const tabParam = new URLSearchParams(window.location.search).get('tab');
    if (tabParam) {
        const tabEl = document.querySelector(`[data-bs-target="#tab-${tabParam}"]`);
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
});
</script>
@endpush