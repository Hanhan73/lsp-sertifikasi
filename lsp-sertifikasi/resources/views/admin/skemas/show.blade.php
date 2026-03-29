@extends('layouts.app')
@section('title', 'Detail Skema - ' . $skema->name)
@section('page-title', 'Detail Skema')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.unit-card { border-left: 4px solid #627be9; transition: box-shadow .2s; }
.unit-card:hover { box-shadow: 0 4px 15px rgba(0,0,0,.1); }
.elemen-item { background: #f8f9ff; border-radius: 6px; padding: 10px 14px; margin-bottom: 6px; }
.kuk-item { background: white; border: 1px solid #e9ecef; border-radius: 4px;
            padding: 7px 12px; margin-bottom: 4px; display: flex; align-items: flex-start; gap: 10px; }
.kuk-kode { color: #627be9; font-weight: 600; font-size: .8rem; min-width: 32px; padding-top: 1px; }
.kuk-text { font-size: .875rem; flex: 1; }
.btn-icon { padding: .2rem .4rem; line-height: 1; }
.collapse-toggle { cursor: pointer; }
.collapse-toggle:hover { background: rgba(98,123,233,.05); border-radius: 4px; }
</style>
@endpush

@section('content')

{{-- ── Header Info Skema ── --}}
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-{{ $skema->jenis_badge }} fs-6">{{ $skema->jenis_label }}</span>
                            @if(!$skema->is_active)
                            <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </div>
                        <h4 class="fw-bold mb-1">{{ $skema->name }}</h4>
                        <p class="text-muted mb-2">
                            <code>{{ $skema->code }}</code>
                            @if($skema->nomor_skema)
                            &bull; <small>{{ $skema->nomor_skema }}</small>
                            @endif
                        </p>
                        @if($skema->description)
                        <p class="text-muted small mb-0">{{ $skema->description }}</p>
                        @endif
                    </div>
                    <div class="text-end">
                        <a href="{{ route('admin.skemas.edit', $skema) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit Skema
                        </a>
                    </div>
                </div>
                <hr class="my-2">
                <div class="row text-center">
                    <div class="col-3">
                        <div class="fw-bold text-primary fs-5">{{ $skema->unitKompetensis->count() }}</div>
                        <small class="text-muted">Unit</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-success fs-5">
                            {{ $skema->unitKompetensis->sum(fn($u) => $u->elemens->count()) }}
                        </div>
                        <small class="text-muted">Elemen</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-info fs-5">
                            {{ $skema->unitKompetensis->sum(fn($u) => $u->elemens->sum(fn($e) => $e->kuks->count())) }}
                        </div>
                        <small class="text-muted">KUK</small>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-warning fs-5">{{ $skema->asesmens->count() }}</div>
                        <small class="text-muted">Asesi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle"></i> Info Biaya & Dokumen</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted small">Biaya</td>
                        <td class="fw-bold">Rp {{ number_format($skema->fee, 0, ',', '.') }}</td>
                    </tr>
                    @if($skema->tanggal_pengesahan)
                    <tr>
                        <td class="text-muted small">Tgl. Pengesahan</td>
                        <td>{{ $skema->tanggal_pengesahan->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($skema->dokumen_pengesahan_path)
                    <tr>
                        <td class="text-muted small">Dokumen</td>
                        <td>
                            <a href="{{ $skema->dokumen_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark-pdf"></i> Lihat
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ── Toolbar Unit Kompetensi ── --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><i class="bi bi-list-check"></i> Unit Kompetensi & KUK</h5>
        <div class="d-flex gap-2">
            {{-- Import MUK --}}
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importMukModal">
                <i class="bi bi-file-earmark-excel"></i> Import dari MUK
            </button>
            {{-- Tambah Unit Manual --}}
            <button class="btn btn-primary btn-sm" onclick="showAddUnit()">
                <i class="bi bi-plus-circle"></i> Tambah Unit
            </button>
        </div>
    </div>

    <div class="card-body" id="unit-list">
        @forelse($skema->unitKompetensis as $unit)
        @include('admin.skemas.partials.unit-card', ['unit' => $unit])
        @empty
        <div class="text-center py-5" id="empty-state">
            <i class="bi bi-list-check" style="font-size:3rem;color:#ccc;"></i>
            <h5 class="mt-3 text-muted">Belum Ada Unit Kompetensi</h5>
            <p class="text-muted">Import dari file MUK atau tambah manual</p>
        </div>
        @endforelse
    </div>
</div>

{{-- ── Modal Import MUK ── --}}
<div class="modal fade" id="importMukModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel"></i> Import dari File MUK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.skemas.import-muk', $skema) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Import akan <strong>menghapus dan mengganti</strong>
                        semua Unit Kompetensi yang ada sekarang dengan data dari file MUK.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Master MUK (.xlsm / .xlsx)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsm,.xlsx,.xls" required>
                        <small class="text-muted">Sheet yang dibaca: <code>FR.APL.02</code></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Tambah Unit ── --}}
<div class="modal fade" id="addUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addUnitTitle">Tambah Unit Kompetensi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="unit-edit-id">
                <div class="mb-3">
                    <label class="form-label">Kode Unit</label>
                    <input type="text" id="unit-kode" class="form-control" placeholder="N.82ADM00.001.3">
                </div>
                <div class="mb-3">
                    <label class="form-label">Judul Unit <span class="text-danger">*</span></label>
                    <input type="text" id="unit-judul" class="form-control" placeholder="Judul unit kompetensi">
                </div>
                <div class="mb-3">
                    <label class="form-label">Standar Kompetensi</label>
                    <input type="text" id="unit-standar" class="form-control" placeholder="SKKNI, dll">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveUnit()">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tambah/Edit Elemen ── --}}
<div class="modal fade" id="elemenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="elemenModalTitle">Tambah Elemen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="elemen-unit-id">
                <input type="hidden" id="elemen-edit-id">
            <div class="mb-3">
                <label class="form-label">Judul Elemen <span class="text-danger">*</span></label>
                <input type="text" id="elemen-judul" class="form-control" placeholder="Judul elemen">
            </div>
            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-lightbulb text-warning me-1"></i>
                    Hint / Panduan Bukti
                    <small class="text-muted fw-normal ms-1">(panduan yang ditampilkan ke asesi)</small>
                </label>
                <textarea id="elemen-hint" class="form-control" rows="3"
                    placeholder="Contoh: Upload foto/scan ijazah ke Google Drive, lalu paste link-nya di kolom bukti."></textarea>
                <div class="form-text">Opsional. Membantu asesi memahami bukti apa yang perlu disiapkan.</div>
            </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="saveElemen()">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Modal Tambah/Edit KUK ── --}}
<div class="modal fade" id="kukModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="kukModalTitle">Tambah KUK</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="kuk-elemen-id">
                <input type="hidden" id="kuk-edit-id">
                <div class="mb-3">
                    <label class="form-label">Deskripsi KUK <span class="text-danger">*</span></label>
                    <textarea id="kuk-deskripsi" class="form-control" rows="3"
                        placeholder="Kriteria unjuk kerja..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info text-white" onclick="saveKuk()">
                    <i class="bi bi-save"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF   = '{{ csrf_token() }}';
const BASE   = '{{ url("admin") }}';
const SKEMA_ID = {{ $skema->id }};

// ══════════════════ UNIT ══════════════════
function showAddUnit() {
    document.getElementById('addUnitTitle').textContent = 'Tambah Unit Kompetensi';
    document.getElementById('unit-edit-id').value = '';
    document.getElementById('unit-kode').value   = '';
    document.getElementById('unit-judul').value  = '';
    document.getElementById('unit-standar').value= '';
    new bootstrap.Modal(document.getElementById('addUnitModal')).show();
}

function editUnit(id, kode, judul, standar) {
    document.getElementById('addUnitTitle').textContent = 'Edit Unit Kompetensi';
    document.getElementById('unit-edit-id').value  = id;
    document.getElementById('unit-kode').value     = kode;
    document.getElementById('unit-judul').value    = judul;
    document.getElementById('unit-standar').value  = standar;
    new bootstrap.Modal(document.getElementById('addUnitModal')).show();
}

async function saveUnit() {
    const id     = document.getElementById('unit-edit-id').value;
    const kode   = document.getElementById('unit-kode').value.trim();
    const judul  = document.getElementById('unit-judul').value.trim();
    const standar= document.getElementById('unit-standar').value.trim();
    if (!judul) { alert('Judul unit wajib diisi'); return; }

    const url    = id ? `${BASE}/units/${id}` : `${BASE}/skemas/${SKEMA_ID}/units`;
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ kode_unit: kode, judul_unit: judul, standar_kompetensi: standar }),
    });
    const data = await res.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('addUnitModal')).hide();
        location.reload();
    }
}

async function deleteUnit(id, judul) {
    const ok = await Swal.fire({
        title: 'Hapus Unit?', html: `<strong>${judul}</strong> dan semua elemen/KUK di dalamnya akan dihapus.`,
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Hapus', confirmButtonColor: '#dc3545', cancelButtonText: 'Batal',
    });
    if (!ok.isConfirmed) return;
    const res = await fetch(`${BASE}/units/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    if ((await res.json()).success) location.reload();
}

// ══════════════════ ELEMEN ══════════════════
function showAddElemen(unitId) {
    document.getElementById('elemenModalTitle').textContent = 'Tambah Elemen';
    document.getElementById('elemen-unit-id').value = unitId;
    document.getElementById('elemen-edit-id').value = '';
    document.getElementById('elemen-judul').value   = '';
    document.getElementById('elemen-hint').value    = '';
    new bootstrap.Modal(document.getElementById('elemenModal')).show();
}

function editElemen(id, judul, hint) {
    document.getElementById('elemenModalTitle').textContent = 'Edit Elemen';
    document.getElementById('elemen-edit-id').value = id;
    document.getElementById('elemen-judul').value   = judul;
    document.getElementById('elemen-hint').value    = hint ?? '';
    new bootstrap.Modal(document.getElementById('elemenModal')).show();
}

async function saveElemen() {
    const unitId    = document.getElementById('elemen-unit-id').value;
    const id        = document.getElementById('elemen-edit-id').value;
    const judul     = document.getElementById('elemen-judul').value.trim();
    const hint_bukti = document.getElementById('elemen-hint').value.trim();
    if (!judul) { alert('Judul elemen wajib diisi'); return; }

    const url    = id ? `${BASE}/elemens/${id}` : `${BASE}/units/${unitId}/elemens`;
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ judul, hint_bukti }),
    });
    if ((await res.json()).success) {
        bootstrap.Modal.getInstance(document.getElementById('elemenModal')).hide();
        location.reload();
    }
}

async function deleteElemen(id, judul) {
    const ok = await Swal.fire({
        title: 'Hapus Elemen?', html: `<strong>${judul}</strong> dan semua KUK-nya akan dihapus.`,
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Hapus', cancelButtonText: 'Batal',
    });
    if (!ok.isConfirmed) return;
    const res = await fetch(`${BASE}/elemens/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    if ((await res.json()).success) location.reload();
}

// ══════════════════ KUK ══════════════════
function showAddKuk(elemenId) {
    document.getElementById('kukModalTitle').textContent = 'Tambah KUK';
    document.getElementById('kuk-elemen-id').value = elemenId;
    document.getElementById('kuk-edit-id').value   = '';
    document.getElementById('kuk-deskripsi').value = '';
    new bootstrap.Modal(document.getElementById('kukModal')).show();
}

function editKuk(id, deskripsi) {
    document.getElementById('kukModalTitle').textContent = 'Edit KUK';
    document.getElementById('kuk-edit-id').value   = id;
    document.getElementById('kuk-deskripsi').value = deskripsi;
    new bootstrap.Modal(document.getElementById('kukModal')).show();
}

async function saveKuk() {
    const elemenId  = document.getElementById('kuk-elemen-id').value;
    const id        = document.getElementById('kuk-edit-id').value;
    const deskripsi = document.getElementById('kuk-deskripsi').value.trim();
    if (!deskripsi) { alert('Deskripsi KUK wajib diisi'); return; }

    const url    = id ? `${BASE}/kuks/${id}` : `${BASE}/elemens/${elemenId}/kuks`;
    const method = id ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ deskripsi }),
    });
    if ((await res.json()).success) {
        bootstrap.Modal.getInstance(document.getElementById('kukModal')).hide();
        location.reload();
    }
}

async function deleteKuk(id) {
    const ok = await Swal.fire({
        title: 'Hapus KUK ini?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', confirmButtonText: 'Hapus', cancelButtonText: 'Batal',
    });
    if (!ok.isConfirmed) return;
    const res = await fetch(`${BASE}/kuks/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    if ((await res.json()).success) location.reload();
}
</script>
@endpush