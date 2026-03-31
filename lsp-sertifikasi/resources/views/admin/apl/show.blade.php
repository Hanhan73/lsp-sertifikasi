@extends('layouts.app')
@section('title', 'Detail APL-01 - ' . $aplsatu->nama_lengkap)
@section('page-title', 'Verifikasi FR.APL.01')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.ttd-box {
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #f8fafc;
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
}
.ttd-box img { max-width: 220px; max-height: 90px; display: block; }
.bukti-item { border-left: 4px solid #e5e7eb; transition: border-color .2s; }
.bukti-item.ok   { border-left-color: #10b981; }
.bukti-item.warn { border-left-color: #f59e0b; }
.bukti-item.no   { border-left-color: #e5e7eb; }
.info-label { color: #6b7280; font-size: .82rem; margin-bottom: 1px; }
.info-value { font-weight: 600; }
</style>
@endpush

@section('content')

<div class="row g-4">

    {{-- KOLOM KIRI --}}
    <div class="col-lg-8">

        {{-- Bagian 1: Data Pribadi --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 bg-primary text-white">
                <i class="bi bi-person-fill"></i>
                <strong>Bagian 1 — Rincian Data Pemohon</strong>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold text-primary border-bottom pb-1 mb-3">a. Data Pribadi</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-12"><div class="info-label">Nama Lengkap</div><div class="info-value fs-5">{{ $aplsatu->nama_lengkap }}</div></div>
                    <div class="col-md-6"><div class="info-label">No. KTP / NIK</div><div class="info-value font-monospace">{{ $aplsatu->nik }}</div></div>
                    <div class="col-md-3"><div class="info-label">Tempat Lahir</div><div class="info-value">{{ $aplsatu->tempat_lahir }}</div></div>
                    <div class="col-md-3"><div class="info-label">Tanggal Lahir</div><div class="info-value">{{ $aplsatu->tanggal_lahir?->format('d M Y') ?? '-' }}</div></div>
                    <div class="col-md-3"><div class="info-label">Jenis Kelamin</div><div class="info-value">{{ $aplsatu->jenis_kelamin }}</div></div>
                    <div class="col-md-3"><div class="info-label">Kebangsaan</div><div class="info-value">{{ $aplsatu->kebangsaan ?? 'Indonesia' }}</div></div>
                    <div class="col-md-6"><div class="info-label">Kualifikasi Pendidikan</div><div class="info-value">{{ $aplsatu->kualifikasi_pendidikan ?? '-' }}</div></div>
                    <div class="col-md-9"><div class="info-label">Alamat Rumah</div><div class="info-value">{{ $aplsatu->alamat_rumah }}</div></div>
                    <div class="col-md-3"><div class="info-label">Kode Pos</div><div class="info-value">{{ $aplsatu->kode_pos ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="info-label">No. Telepon Rumah</div><div class="info-value">{{ $aplsatu->telp_rumah ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="info-label">No. HP</div><div class="info-value">{{ $aplsatu->hp }}</div></div>
                    <div class="col-md-4"><div class="info-label">Email</div><div class="info-value">{{ $aplsatu->email }}</div></div>
                </div>
                <h6 class="fw-semibold text-primary border-bottom pb-1 mb-3">b. Data Pekerjaan Sekarang</h6>
                <div class="row g-3">
                    <div class="col-md-6"><div class="info-label">Nama Institusi / Perusahaan</div><div class="info-value">{{ $aplsatu->nama_institusi ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="info-label">Jabatan</div><div class="info-value">{{ $aplsatu->jabatan ?? '-' }}</div></div>
                    <div class="col-md-9"><div class="info-label">Alamat Kantor</div><div class="info-value">{{ $aplsatu->alamat_kantor ?? '-' }}</div></div>
                    <div class="col-md-3"><div class="info-label">Kode Pos Kantor</div><div class="info-value">{{ $aplsatu->kode_pos_kantor ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="info-label">Telepon Kantor</div><div class="info-value">{{ $aplsatu->telp_kantor_detail ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="info-label">Fax</div><div class="info-value">{{ $aplsatu->fax_kantor ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="info-label">Email Kantor</div><div class="info-value">{{ $aplsatu->email_kantor ?? '-' }}</div></div>
                </div>
            </div>
        </div>

        {{-- Bagian 2: Data Sertifikasi --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 bg-info text-white">
                <i class="bi bi-award"></i><strong>Bagian 2 — Data Sertifikasi</strong>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-sm mb-4">
                    <tbody>
                        <tr><td width="220" class="fw-semibold text-muted">Skema Sertifikasi</td><td class="fw-bold">{{ $aplsatu->asesmen?->skema?->name ?? '-' }}</td></tr>
                        <tr><td class="fw-semibold text-muted">Nomor Skema</td><td>{{ $aplsatu->asesmen?->skema?->nomor_skema ?? ($aplsatu->asesmen?->skema?->code ?? '-') }}</td></tr>
                        <tr><td class="fw-semibold text-muted">Tujuan Asesmen</td><td><strong>{{ $aplsatu->tujuan_asesmen ?? '-' }}</strong>@if($aplsatu->tujuan_asesmen === 'Lainnya' && $aplsatu->tujuan_asesmen_lainnya): {{ $aplsatu->tujuan_asesmen_lainnya }}@endif</td></tr>
                    </tbody>
                </table>
                @if($aplsatu->asesmen?->skema?->unitKompetensis?->isNotEmpty())
                <h6 class="fw-semibold mb-2">Daftar Unit Kompetensi</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light"><tr><th width="40" class="text-center">No</th><th width="160">Kode Unit</th><th>Judul Unit Kompetensi</th></tr></thead>
                        <tbody>
                            @foreach($aplsatu->asesmen->skema->unitKompetensis as $i => $unit)
                            <tr><td class="text-center">{{ $i + 1 }}</td><td><small class="font-monospace">{{ $unit->kode_unit }}</small></td><td>{{ $unit->judul_unit }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Bagian 3: Bukti Kelengkapan --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 bg-warning text-white">
                <i class="bi bi-folder-check"></i><strong>Bagian 3 — Bukti Kelengkapan Pemohon</strong>
            </div>
            <div class="card-body">
                @php $gdriveLink = $aplsatu->buktiKelengkapan->whereNotNull('gdrive_file_url')->first()?->gdrive_file_url; @endphp
                @if($gdriveLink)
                <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
                    <i class="bi bi-google fs-3 flex-shrink-0"></i>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold">Link Google Drive dari Asesi</div>
                        <a href="{{ $gdriveLink }}" target="_blank" class="small text-break">{{ $gdriveLink }}</a>
                    </div>
                    <a href="{{ $gdriveLink }}" target="_blank" class="btn btn-primary btn-sm flex-shrink-0"><i class="bi bi-box-arrow-up-right me-1"></i>Buka</a>
                </div>
                @else
                <div class="alert alert-secondary mb-4"><i class="bi bi-exclamation-circle me-2"></i>Asesi belum mengisi link Google Drive.</div>
                @endif

                @foreach(['persyaratan_dasar' => 'Persyaratan Dasar', 'administratif' => 'Administratif'] as $kat => $label)
                @php $items = $aplsatu->buktiKelengkapan->where('kategori', $kat); @endphp
                @if($items->isNotEmpty())
                <h6 class="fw-semibold mb-2 mt-3">{{ $label }}</h6>
                @foreach($items as $bukti)
                @php
                    $cls = match($bukti->status) { 'Ada Memenuhi Syarat' => 'ok', 'Ada Tidak Memenuhi Syarat' => 'warn', default => 'no' };
                    $badgeColor = match($bukti->status) { 'Ada Memenuhi Syarat' => 'success', 'Ada Tidak Memenuhi Syarat' => 'warning', default => 'secondary' };
                @endphp
                <div class="card bukti-item {{ $cls }} mb-2 shadow-sm">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">{{ $bukti->nama_dokumen }}</div>
                                @if($bukti->catatan)<div class="text-muted" style="font-size:.8rem;"><i class="bi bi-chat-left-dots me-1"></i>{{ $bukti->catatan }}</div>@endif
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <select class="form-select form-select-sm" style="min-width:210px;"
                                    data-bukti-id="{{ $bukti->id }}"
                                    onchange="updateStatusBukti({{ $bukti->id }}, this.value, this)">
                                    <option value="Tidak Ada" {{ $bukti->status === 'Tidak Ada' ? 'selected' : '' }}>Tidak Ada</option>
                                    <option value="Ada Memenuhi Syarat" {{ $bukti->status === 'Ada Memenuhi Syarat' ? 'selected' : '' }}>Ada — Memenuhi Syarat</option>
                                    <option value="Ada Tidak Memenuhi Syarat" {{ $bukti->status === 'Ada Tidak Memenuhi Syarat' ? 'selected' : '' }}>Ada — Tidak Memenuhi</option>
                                </select>
                                <span class="badge bg-{{ $badgeColor }} text-nowrap" id="badge-bukti-{{ $bukti->id }}">{{ $bukti->status }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
                @endforeach
            </div>
        </div>

        {{-- TTD Pemohon --}}
        @if($aplsatu->ttd_pemohon)
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pen me-2 text-primary"></i>Tanda Tangan Pemohon</div>
            <div class="card-body d-flex gap-4 align-items-start flex-wrap">
                <div class="ttd-box"><img src="{{ $aplsatu->ttd_pemohon_image }}" alt="TTD Pemohon"></div>
                <div class="small">
                    <div class="info-label">Nama</div><div class="info-value mb-2">{{ $aplsatu->nama_ttd_pemohon ?? $aplsatu->nama_lengkap }}</div>
                    <div class="info-label">Tanggal TTD</div>
                    <div class="info-value">{{ $aplsatu->tanggal_ttd_pemohon ? \Carbon\Carbon::parse($aplsatu->tanggal_ttd_pemohon)->format('d M Y') : '-' }}</div>
                    <div class="mt-2 text-muted small"><i class="bi bi-clock me-1"></i>Submit: {{ $aplsatu->submitted_at?->format('d M Y H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- TTD Admin (jika sudah verified) --}}
        @if($aplsatu->ttd_admin)
        <div class="card mb-3 shadow-sm border-success">
            <div class="card-header bg-success text-white fw-semibold"><i class="bi bi-patch-check-fill me-2"></i>Tanda Tangan Admin LSP</div>
            <div class="card-body d-flex gap-4 align-items-start flex-wrap">
                <div class="ttd-box"><img src="{{ $aplsatu->ttd_admin_image }}" alt="TTD Admin"></div>
                <div class="small">
                    <div class="info-label">Nama Admin</div><div class="info-value mb-2">{{ $aplsatu->nama_ttd_admin ?? '-' }}</div>
                    <div class="info-label">Tanggal Verifikasi</div>
                    <div class="info-value">{{ $aplsatu->tanggal_ttd_admin ? \Carbon\Carbon::parse($aplsatu->tanggal_ttd_admin)->format('d M Y') : '-' }}</div>
                    @if($aplsatu->verified_at)
                    <div class="mt-2 text-muted small"><i class="bi bi-clock me-1"></i>Diverifikasi: {{ $aplsatu->verified_at->format('d M Y H:i') }}@if($aplsatu->verifier) oleh <strong>{{ $aplsatu->verifier->name }}</strong>@endif</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- PDF --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Dokumen PDF</div>
            <div class="card-body d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.apl01.pdf', [$aplsatu, 'preview' => 1]) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i>Preview PDF</a>
                <a href="{{ route('admin.apl01.pdf', $aplsatu) }}" class="btn btn-success"><i class="bi bi-download me-1"></i>Download PDF</a>
            </div>
        </div>

    </div>

    {{-- KOLOM KANAN --}}
    <div class="col-lg-4">

        <div class="card mb-3 shadow-sm">
            <div class="card-body text-center py-4">
                <span class="badge bg-{{ $aplsatu->status_badge }} fs-6 px-3 py-2">{{ $aplsatu->status_label }}</span>
                @if($aplsatu->submitted_at)<div class="text-muted small mt-2"><i class="bi bi-send me-1"></i>Submit: {{ $aplsatu->submitted_at->format('d M Y H:i') }}</div>@endif
                @if($aplsatu->verified_at)
                <div class="text-muted small"><i class="bi bi-check2-circle me-1"></i>Verified: {{ $aplsatu->verified_at->format('d M Y H:i') }}</div>
                @if($aplsatu->verifier)<div class="text-muted small">oleh <strong>{{ $aplsatu->verifier->name }}</strong></div>@endif
                @endif

                {{-- Rejection notes jika status returned --}}
                @if($aplsatu->status === 'returned' && $aplsatu->rejection_notes)
                <div class="alert alert-danger text-start mt-3 py-2 mb-0">
                    <div class="fw-semibold small mb-1"><i class="bi bi-arrow-return-left me-1"></i>Catatan Pengembalian:</div>
                    <div class="small">{{ $aplsatu->rejection_notes }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white fw-semibold small"><i class="bi bi-person-circle me-2 text-secondary"></i>Info Asesi</div>
            <div class="card-body small">
                <div class="info-label">Nama User</div><div class="info-value mb-2">{{ $aplsatu->asesmen?->user?->name ?? $aplsatu->nama_lengkap }}</div>
                <div class="info-label">Email User</div><div class="info-value mb-2">{{ $aplsatu->asesmen?->user?->email ?? '-' }}</div>
                <div class="info-label">Skema</div><div class="info-value mb-2">{{ $aplsatu->asesmen?->skema?->name ?? '-' }}</div>
                <div class="info-label">TUK</div><div class="info-value">{{ $aplsatu->asesmen?->tuk?->name ?? '-' }}</div>
            </div>
        </div>

        @php
            $totalBukti = $aplsatu->buktiKelengkapan->count();
            $isiCount   = $aplsatu->buktiKelengkapan->where('status', '!=', 'Tidak Ada')->count();
            $okCount    = $aplsatu->buktiKelengkapan->where('status', 'Ada Memenuhi Syarat')->count();
            $pctIsi     = $totalBukti > 0 ? round($isiCount / $totalBukti * 100) : 0;
        @endphp
        @if($totalBukti > 0)
        <div class="card mb-3 shadow-sm">
            <div class="card-body small">
                <div class="fw-semibold mb-2"><i class="bi bi-clipboard-data me-1"></i>Progress Bukti</div>
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Sudah diperiksa</span><strong>{{ $isiCount }}/{{ $totalBukti }}</strong></div>
                <div class="progress mb-2" style="height:6px;"><div class="progress-bar bg-{{ $isiCount === $totalBukti ? 'success' : 'warning' }}" style="width:{{ $pctIsi }}%;"></div></div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-success">{{ $okCount }} Memenuhi</span>
                    <span class="badge bg-warning text-dark">{{ $isiCount - $okCount }} Tdk Memenuhi</span>
                    <span class="badge bg-secondary">{{ $totalBukti - $isiCount }} Belum</span>
                </div>
            </div>
        </div>
        @endif

        @if($aplsatu->status === 'submitted')
        <div class="card mb-3 shadow-sm border-success">
            <div class="card-header bg-success text-white"><i class="bi bi-check-circle-fill me-2"></i><strong>Verifikasi APL-01</strong></div>
            <div class="card-body">
                <p class="small text-muted mb-3">Periksa semua data dan bukti kelengkapan, lalu tanda tangani untuk memverifikasi.</p>
                @if($gdriveLink ?? false)
                <a href="{{ $gdriveLink }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 mb-2"><i class="bi bi-google me-1"></i>Buka Google Drive Asesi</a>
                @endif
                <button class="btn btn-success w-100 mb-2" onclick="bukaModalVerifikasi()">
                    <i class="bi bi-pen me-1"></i>Isi Nama &amp; Tanda Tangan
                </button>
                <button class="btn btn-outline-danger w-100" onclick="openRejectApl01Modal()">
                    <i class="bi bi-file-earmark-x me-1"></i> Kembalikan untuk Direvisi
                </button>
            </div>
        </div>
        @elseif(in_array($aplsatu->status, ['verified', 'approved']))
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-patch-check-fill me-2"></i><strong>APL-01 sudah diverifikasi.</strong>
            @if($aplsatu->nama_ttd_admin)<div class="mt-1 small">Admin: <strong>{{ $aplsatu->nama_ttd_admin }}</strong></div>@endif
        </div>
        @elseif($aplsatu->status === 'returned')
        <div class="alert alert-warning shadow-sm">
            <i class="bi bi-arrow-return-left me-2"></i><strong>APL-01 dikembalikan ke asesi.</strong>
            <div class="mt-1 small text-muted">Menunggu asesi memperbaiki dan submit ulang.</div>
        </div>
        @endif

        <a href="{{ route('admin.asesi.show', $aplsatu->asesmen->id) }}" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>
</div>

{{-- ══════════════════════════════
     MODAL VERIFIKASI ADMIN
══════════════════════════════ --}}
<div class="modal fade" id="modalVerifikasi" tabindex="-1" aria-labelledby="modalVerifikasiLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalVerifikasiLabel"><i class="bi bi-pen me-2"></i>Verifikasi APL-01</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-4">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Dengan menandatangani, Anda menyatakan APL-01 ini telah diperiksa dan semua bukti kelengkapan telah diverifikasi.
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold" for="admin-nama-input">
                        Nama Lengkap Admin <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="admin-nama-input" class="form-control form-control-lg"
                        value="{{ auth()->user()->name }}" readonly>
                    <div class="form-text">Nama ini akan tercetak di dokumen PDF APL-01.</div>
                </div>
                @include('partials._signature_pad', [
                    'padId'    => 'admin',
                    'padLabel' => 'Tanda Tangan Admin LSP',
                    'padHeight' => 180,
                    'savedSig' => auth()->user()->signature_image,
                ])
                <div class="card bg-light border-0 mt-3">
                    <div class="card-body py-2 small">
                        <strong>Ringkasan yang akan diverifikasi:</strong>
                        Pemohon: <strong>{{ $aplsatu->nama_lengkap }}</strong> |
                        Skema: <strong>{{ $aplsatu->asesmen?->skema?->name ?? '-' }}</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4" id="btn-simpan-verifikasi" onclick="submitVerifikasi()">
                    <i class="bi bi-check-circle me-1"></i>Verifikasi &amp; Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL REJECT APL-01 ══ --}}
<div class="modal fade" id="modalRejectApl01" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-x me-2"></i>Kembalikan APL-01
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <small>APL-01 akan dikembalikan ke status <strong>returned</strong>.
                    Asesi dapat mengedit dan submit ulang.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Alasan Pengembalian <span class="text-danger">*</span>
                    </label>
                    <textarea id="apl01-rejection-notes" class="form-control" rows="4"
                        placeholder="Jelaskan apa yang perlu diperbaiki di APL-01. Contoh: Tanda tangan tidak jelas, data pekerjaan tidak lengkap, dsb."
                        maxlength="1000"></textarea>
                    <div class="form-text">Min. 10 karakter.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-reject-apl01"
                    onclick="submitRejectApl01()">
                    <i class="bi bi-send me-1"></i> Kembalikan APL-01
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const APL_ID = '{{ $aplsatu->id }}';

// ── Update status bukti (AJAX) ──────────────────────────────
async function updateStatusBukti(buktiId, status, selectEl) {
    if (!selectEl.dataset.semula) selectEl.dataset.semula = selectEl.value;
    const semula = selectEl.dataset.semula;

    const { isConfirmed, value: catatan } = await Swal.fire({
        title: 'Update Status Bukti',
        html: `<p class="text-start mb-2">Status baru: <span class="badge bg-primary">${status}</span></p>
               <div class="text-start"><label class="form-label small">Catatan (opsional):</label>
               <textarea class="form-control form-control-sm" id="swal-catatan" rows="2" placeholder="Tambahkan catatan..."></textarea></div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Batal',
        preConfirm: () => document.getElementById('swal-catatan')?.value?.trim() ?? '',
    });

    if (!isConfirmed) { selectEl.value = semula; return; }

    try {
        const res  = await fetch(`/admin/apl01-bukti/${buktiId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ status, catatan }),
        });
        const data = await res.json();
        if (data.success) {
            const badge = document.getElementById(`badge-bukti-${buktiId}`);
            if (badge) {
                const warna = { 'Ada Memenuhi Syarat': 'success', 'Ada Tidak Memenuhi Syarat': 'warning', 'Tidak Ada': 'secondary' };
                badge.className = `badge bg-${warna[status] ?? 'secondary'} text-nowrap`;
                badge.textContent = status;
            }
            const card = selectEl.closest('.bukti-item');
            if (card) { card.classList.remove('ok','warn','no'); card.classList.add(status === 'Ada Memenuhi Syarat' ? 'ok' : status === 'Ada Tidak Memenuhi Syarat' ? 'warn' : 'no'); }
            selectEl.dataset.semula = status;
            Swal.fire({ icon: 'success', title: 'Berhasil!', timer: 1400, showConfirmButton: false });
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
            selectEl.value = semula;
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        selectEl.value = semula;
    }
}

// ── Modal verifikasi ────────────────────────────────────────
function bukaModalVerifikasi() {
    const modalEl = document.getElementById('modalVerifikasi');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
    modalEl.addEventListener('shown.bs.modal', () => {
        SigPadManager.init('admin', @json(auth()->user()->signature_image));
    }, { once: true });
}

// ── Submit verifikasi ───────────────────────────────────────
async function submitVerifikasi() {
    const namaAdmin = document.getElementById('admin-nama-input')?.value?.trim() ?? '';
    if (!namaAdmin) {
        Swal.fire('Perhatian', 'Mohon isi nama lengkap Anda terlebih dahulu.', 'warning');
        document.getElementById('admin-nama-input')?.focus();
        return;
    }
    if (SigPadManager.isEmpty('admin')) {
        Swal.fire('Perhatian', 'Mohon buat atau upload tanda tangan terlebih dahulu.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-simpan-verifikasi');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...'; }

    try {
        const signature = await SigPadManager.prepareAndGet('admin');
        const res  = await fetch(`/admin/apl01/${APL_ID}/verify`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ signature, nama_admin: namaAdmin }),
        });
        const data = await res.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalVerifikasi'))?.hide();
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, confirmButtonText: 'OK' });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message ?? 'Gagal memverifikasi APL-01.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Verifikasi & Simpan'; }
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Verifikasi & Simpan'; }
    }
}

// ── Reject APL-01 ───────────────────────────────────────────
function openRejectApl01Modal() {
    document.getElementById('apl01-rejection-notes').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRejectApl01')).show();
}

async function submitRejectApl01() {
    const notes = document.getElementById('apl01-rejection-notes').value.trim();
    if (notes.length < 10) {
        Swal.fire('Perhatian', 'Catatan penolakan minimal 10 karakter.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-confirm-reject-apl01');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    try {
        const res = await fetch(`/admin/apl01/${APL_ID}/reject`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body   : JSON.stringify({ catatan: notes }),
        });
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalRejectApl01'))?.hide();
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1800, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i> Kembalikan APL-01';
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Kembalikan APL-01';
    }
}
</script>
@endpush