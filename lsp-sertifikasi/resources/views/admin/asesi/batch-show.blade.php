@extends('layouts.app')
@section('title', 'Detail Batch - ' . $batchId)
@section('page-title', 'Detail Batch Kolektif')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.asesi') }}">Semua Asesi</a></li>
        <li class="breadcrumb-item active" id="breadcrumb-batch">Batch {{ $batchId }}</li>
    </ol>
</nav>

<div class="row g-4">

    {{-- KIRI --}}
    <div class="col-lg-8">

        {{-- Info Batch --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Batch</span>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#renameBatchModal">
                    <i class="bi bi-pencil me-1"></i> Ubah Nama
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="130">Batch ID</td>
                                <td>: <code id="current-batch-id">{{ $batchId }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">TUK</td>
                                <td>: {{ $firstBatch->tuk->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Skema</td>
                                <td>: {{ $firstBatch->skema->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="130">Total Peserta</td>
                                <td>: <strong>{{ $asesmens->count() }} orang</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tgl Daftar</td>
                                <td>: {{ $firstBatch->registration_date->translatedFormat('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Didaftarkan oleh</td>
                                <td>: {{ $firstBatch->registrar->name ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daftar Peserta --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2 text-primary"></i>Daftar Peserta</span>
                <span class="badge bg-primary">{{ $asesmens->count() }} orang</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="40">#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th class="text-center">Dokumen</th>
                                <th class="text-center">APL-01</th>
                                <th class="text-center">APL-02</th>
                                <th class="text-center">FR.AK.01</th>
                                <th class="text-center">FR.AK.04</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asesmens as $i => $asesmen)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                                    <div class="text-muted small">{{ $asesmen->nik ?? 'NIK belum diisi' }}</div>
                                </td>
                                <td class="small">{{ $asesmen->user->email }}</td>
                                <td class="text-center">
                                    @if($asesmen->photo_path && $asesmen->ktp_path && $asesmen->document_path)
                                        <i class="bi bi-check-circle-fill text-success" title="Lengkap"></i>
                                    @else
                                        <i class="bi bi-x-circle-fill text-danger" title="Belum Lengkap"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->aplsatu)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->apldua)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->frak01)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($asesmen->frak04)
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                    @else
                                        <i class="bi bi-dash-circle text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $asesmen->status_badge }}">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.asesi.show', $asesmen) }}"
                                        class="btn btn-sm btn-outline-primary" title="Detail Asesi">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- KANAN --}}
    <div class="col-lg-4">
        <div class="sticky-top" style="top:80px;">

            @if($allDataCompleted && !$asesmenStarted)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-primary text-white fw-semibold">
                    <i class="bi bi-play-circle me-2"></i>Mulai Asesmen Batch
                </div>
                <div class="card-body">
                    @php
                        $totalPeserta   = $asesmens->count();
                        $dokumenLengkap = $asesmens->filter(fn($a) =>
                            $a->photo_path && $a->ktp_path && $a->document_path
                        )->count();
                        $allDocReady    = $dokumenLengkap === $totalPeserta;
                    @endphp
                    <p class="small fw-semibold text-muted mb-2">KELENGKAPAN DOKUMEN</p>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small">Dokumen lengkap</span>
                        <span class="fw-semibold small">{{ $dokumenLengkap }}/{{ $totalPeserta }}</span>
                    </div>
                    <div class="progress mb-3" style="height:6px;">
                        <div class="progress-bar {{ $allDocReady ? 'bg-success' : 'bg-warning' }}"
                            style="width: {{ $totalPeserta > 0 ? round($dokumenLengkap / $totalPeserta * 100) : 0 }}%">
                        </div>
                    </div>
                    @if(!$allDocReady)
                    <div class="alert alert-warning py-2 mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <small><strong>{{ $totalPeserta - $dokumenLengkap }} peserta</strong>
                            belum melengkapi dokumen. Anda tetap bisa memulai asesmen.</small>
                    </div>
                    @endif
                    <form action="{{ route('admin.praasesmen.batch.process') }}" method="POST">
                        @csrf
                        <input type="hidden" name="batch_id" value="{{ $batchId }}" id="form-batch-id">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Catatan (opsional)</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="3"
                                placeholder="Catatan untuk batch ini..."></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Mulai asesmen untuk {{ $totalPeserta }} peserta dalam batch ini?')">
                                <i class="bi bi-play-circle me-1"></i>
                                Mulai Asesmen ({{ $totalPeserta }} peserta)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @if($asesmenStarted)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold border-bottom">
                    <i class="bi bi-graph-up me-2 text-success"></i>Progress Dokumen
                </div>
                <div class="card-body">
                    @php $total = $docProgress['total']; @endphp
                    @foreach([
                        ['key' => 'apl01',  'label' => 'APL-01',   'color' => 'primary'],
                        ['key' => 'apl02',  'label' => 'APL-02',   'color' => 'info'],
                        ['key' => 'frak01', 'label' => 'FR.AK.01', 'color' => 'warning'],
                        ['key' => 'frak04', 'label' => 'FR.AK.04', 'color' => 'secondary'],
                    ] as $doc)
                    @php
                        $count   = $docProgress[$doc['key']];
                        $pct     = $total > 0 ? round($count / $total * 100) : 0;
                        $allDone = $count === $total;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold">{{ $doc['label'] }}</span>
                            <span class="small {{ $allDone ? 'text-success fw-semibold' : 'text-muted' }}">
                                {{ $count }}/{{ $total }}
                                @if($allDone)<i class="bi bi-check-circle-fill ms-1"></i>@endif
                            </span>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-{{ $doc['color'] }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold border-bottom">
                    <i class="bi bi-file-excel me-2 text-success"></i>Export Data
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Export biodata semua peserta batch ini.</p>
                    <a href="{{ route('admin.asesi.batch.export', $batchId) }}"
                        class="btn btn-success w-100" id="export-link">
                        <i class="bi bi-download me-1"></i> Download Biodata Excel
                    </a>
                </div>
            </div>
            @endif

            <a href="{{ route('admin.asesi') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>

        </div>
    </div>

</div>

{{-- Modal Rename --}}
<div class="modal fade" id="renameBatchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Ubah Nama Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">BATCH ID SAAT INI</label>
                    <div class="form-control bg-light" style="font-family:monospace;font-size:0.85rem;">{{ $batchId }}</div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Nama baru <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="rename-input"
                        placeholder="cth: Angkatan Feb 2025, Kelas B" maxlength="50">
                    <small class="text-muted">Huruf, angka, spasi. Maks 50 karakter.</small>
                </div>
                <div class="p-3 bg-light rounded border mt-3">
                    <div class="small text-muted mb-1">Preview Batch ID baru:</div>
                    <code id="rename-preview" class="text-primary" style="font-size:0.9rem;">—</code>
                    <div class="small text-muted mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Kode TUK <strong>({{ strtoupper($firstBatch->tuk->code ?? 'TUK') }})</strong>
                        dan suffix unik <strong>({{ substr($batchId, -6) }})</strong> tetap dipertahankan.
                    </div>
                </div>
                <div class="alert alert-warning mt-3 mb-0 py-2">
                    <small>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Semua <strong>{{ $asesmens->count() }} peserta</strong> akan diperbarui dengan Batch ID baru.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="rename-confirm-btn" disabled>
                    <i class="bi bi-check-circle me-1"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    const tukCode   = '{{ strtoupper($firstBatch->tuk->code ?? "TUK") }}';
    const suffix    = '{{ substr($batchId, -6) }}';
    const batchId   = @json($batchId);
    const renameUrl = '{{ route("admin.asesi.batch.rename", $batchId) }}';

    function slugify(text) {
        return text.toString().toUpperCase().trim()
            .replace(/\s+/g, '-')
            .replace(/[^A-Z0-9\-]/g, '')
            .replace(/\-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    // Isi input dengan nama saat ini (hapus TUK code + suffix dari akhir)
    const tukSuffix = '-' + tukCode + '-' + suffix;
    const initName  = batchId.endsWith(tukSuffix)
        ? batchId.slice(0, batchId.length - tukSuffix.length).replace(/-/g, ' ').trim()
        : '';
    $('#rename-input').val(initName);

    function updatePreview() {
        const val     = $('#rename-input').val().trim();
        const slug    = val ? slugify(val) : 'BATCH';
        const preview = slug + '-' + tukCode + '-' + suffix;
        $('#rename-preview').text(preview);
        $('#rename-confirm-btn').prop('disabled', val.length === 0);
    }
    $('#rename-input').on('input', updatePreview);
    updatePreview();

    $('#rename-confirm-btn').on('click', function () {
        const newName = $('#rename-input').val().trim();
        if (!newName) return;

        const $btn = $(this).prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        $.ajax({
            url   : renameUrl,
            method: 'PATCH',
            data  : {
                _token    : $('meta[name="csrf-token"]').attr('content'),
                batch_name: newName,
            },
            success: function (res) {
                if (res.success) {
                    const newId = res.new_batch_id;
                    $('#current-batch-id').text(newId);
                    $('#form-batch-id').val(newId);
                    $('#breadcrumb-batch').text('Batch ' + newId);
                    $('#renameBatchModal').modal('hide');

                    Swal.fire({
                        icon : 'success',
                        title: 'Berhasil!',
                        text : 'Batch ID diubah: ' + newId,
                        timer: 2000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = window.location.href
                            .replace(encodeURIComponent(batchId), encodeURIComponent(newId));
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                    $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan');
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.' });
                $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Simpan');
            },
        });
    });
});
</script>
@endpush