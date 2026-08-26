@extends('layouts.app')
@section('title', 'Arsip Sertifikat Fisik')
@section('page-title', 'Arsip Sertifikat Fisik')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<div class="alert alert-info border-0 shadow-sm small">
    <i class="bi bi-info-circle me-1"></i>
    Halaman ini menampilkan asesi/batch yang sertifikat fisiknya sudah didistribusikan, beserta status upload bukti terima dan link download.
</div>

{{-- ── BATCH KOLEKTIF ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-people-fill me-2 text-primary"></i>Batch Kolektif
        <span class="small text-muted fw-normal ms-2">(klik baris untuk lihat detail & download)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch ID</th><th>TUK</th><th>Skema</th>
                    <th class="text-center">Progress Upload</th>
                    <th>Tgl Distribusi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $b)
                @php $pct = $b->total > 0 ? round($b->uploaded_count / $b->total * 100) : 0; @endphp
                <tr class="batch-row" style="cursor:pointer;" data-batch-id="{{ $b->batch_id }}">
                    <td><code>{{ $b->batch_id }}</code></td>
                    <td>{{ $b->tuk->name ?? '-' }}</td>
                    <td class="small">{{ $b->skema->name ?? '-' }}</td>
                    <td class="text-center" style="min-width:160px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar {{ $pct === 100 ? 'bg-success' : 'bg-warning' }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="small text-nowrap">{{ $b->uploaded_count }}/{{ $b->total }}</span>
                        </div>
                    </td>
                    <td class="small">{{ $b->distributed_at?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td class="text-center" onclick="event.stopPropagation();">
                        @if($b->uploaded_count > 0)
                        <a href="{{ route('admin.arsip-sertifikat.batch.download-zip', $b->batch_id) }}"
                           class="btn btn-sm btn-success">
                            <i class="bi bi-file-zip me-1"></i>Download ZIP
                        </a>
                        @else
                        <span class="text-muted small">Belum ada upload</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada batch yang sudah didistribusikan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── MANDIRI ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-person-fill me-2 text-success"></i>Asesi Mandiri
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama</th><th>Skema</th><th class="text-center">Status Upload</th>
                    <th>No. Sertifikat</th><th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mandiri as $m)
                <tr>
                    <td>{{ $m->full_name }}</td>
                    <td class="small">{{ $m->skema->name ?? '-' }}</td>
                    <td class="text-center">
                        @if($m->hasUploadedPhysicalCertificate())
                        <span class="badge bg-success">Sudah Upload</span>
                        @else
                        <span class="badge bg-danger">Belum Upload</span>
                        @endif
                    </td>
                    <td class="small font-monospace">{{ $m->physical_certificate_number ?? '-' }}</td>
                    <td class="text-center">
                        @if($m->hasUploadedPhysicalCertificate())
                        <a href="{{ route('admin.arsip-sertifikat.download', $m->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i>Download
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada asesi mandiri yang sudah didistribusikan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══ MODAL DETAIL BATCH ══ --}}
<div class="modal fade" id="modalArsipDetail" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-archive me-2"></i>Arsip Sertifikat — <code id="modal-batch-id" class="text-white"></code>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div id="modal-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat data...</p>
                </div>

                <div id="modal-content" style="display:none;">

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex gap-4">
                            <div>
                                <div class="small text-muted">TUK</div>
                                <div class="fw-semibold" id="modal-tuk">-</div>
                            </div>
                            <div>
                                <div class="small text-muted">Skema</div>
                                <div id="modal-skema">-</div>
                            </div>
                            <div>
                                <div class="small text-muted">Progress</div>
                                <div class="fw-semibold"><span id="modal-uploaded">-</span>/<span id="modal-total">-</span> sudah upload</div>
                            </div>
                        </div>
                        <a href="#" id="modal-download-zip" class="btn btn-success">
                            <i class="bi bi-file-zip me-1"></i>Download Semua (ZIP)
                        </a>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-upload active" data-filter="all">Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-upload" data-filter="uploaded">Sudah Upload</button>
                        <button type="button" class="btn btn-sm btn-outline-danger filter-upload" data-filter="not_uploaded">Belum Upload</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">#</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Instansi</th>
                                    <th>No. Sertifikat</th>
                                    <th>No. Adm</th>
                                    <th>Diupload</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modal-peserta-body"></tbody>
                        </table>
                    </div>
                </div>

                <div id="modal-error" class="text-center py-5 text-danger" style="display:none;">
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    <p class="mt-2 mb-0" id="modal-error-message">Gagal memuat data.</p>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentPeserta = [];

    document.querySelectorAll('.batch-row').forEach(row => {
        row.addEventListener('click', function () {
            openBatchDetail(this.dataset.batchId);
        });
    });

    function openBatchDetail(batchId) {
        const modalEl = document.getElementById('modalArsipDetail');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('modal-batch-id').textContent = batchId;
        document.getElementById('modal-loading').style.display = 'block';
        document.getElementById('modal-content').style.display = 'none';
        document.getElementById('modal-error').style.display = 'none';

        modal.show();

        const url = `{{ url('admin/arsip-sertifikat/batch') }}/${encodeURIComponent(batchId)}/detail`;
        const zipUrl = `{{ url('admin/arsip-sertifikat/batch') }}/${encodeURIComponent(batchId)}/download-zip`;
        document.getElementById('modal-download-zip').href = zipUrl;

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                document.getElementById('modal-loading').style.display = 'none';

                if (!data.success) {
                    document.getElementById('modal-error').style.display = 'block';
                    document.getElementById('modal-error-message').textContent = data.message ?? 'Gagal memuat data.';
                    return;
                }

                document.getElementById('modal-content').style.display = 'block';
                document.getElementById('modal-tuk').textContent = data.batch.tuk ?? '-';
                document.getElementById('modal-skema').textContent = data.batch.skema ?? '-';
                document.getElementById('modal-total').textContent = data.batch.total;
                document.getElementById('modal-uploaded').textContent = data.batch.uploaded_count;

                currentPeserta = data.peserta;
                renderPeserta(currentPeserta);

                document.querySelectorAll('.filter-upload').forEach(b => b.classList.remove('active'));
                document.querySelector('.filter-upload[data-filter="all"]').classList.add('active');
            })
            .catch(() => {
                document.getElementById('modal-loading').style.display = 'none';
                document.getElementById('modal-error').style.display = 'block';
                document.getElementById('modal-error-message').textContent = 'Terjadi kesalahan jaringan.';
            });
    }

    function renderPeserta(list) {
        const tbody = document.getElementById('modal-peserta-body');
        tbody.innerHTML = '';

        if (list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
            return;
        }

        list.forEach((p, i) => {
            const statusBadge = p.uploaded
                ? '<span class="badge bg-success">Sudah Upload</span>'
                : '<span class="badge bg-danger">Belum Upload</span>';

            const aksi = p.uploaded
                ? `<a href="${p.download_url}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="bi bi-download" style="font-size:.8rem;"></i></a>`
                : '<span class="text-muted small">—</span>';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="text-muted">${i + 1}</td>
                <td><strong>${escapeHtml(p.full_name ?? '-')}</strong><br>${statusBadge}</td>
                <td class="font-monospace small">${escapeHtml(p.nik ?? '-')}</td>
                <td class="small">${escapeHtml(p.institution ?? '-')}</td>
                <td class="small font-monospace">${escapeHtml(p.cert_number ?? '-')}</td>
                <td class="small font-monospace">${escapeHtml(p.adm_number ?? '-')}</td>
                <td class="small">${escapeHtml(p.uploaded_at ?? '-')}</td>
                <td class="text-center">${aksi}</td>
            `;
            tbody.appendChild(row);
        });
    }

    document.querySelectorAll('.filter-upload').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-upload').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            const filtered = filter === 'all'
                ? currentPeserta
                : currentPeserta.filter(p => filter === 'uploaded' ? p.uploaded : !p.uploaded);

            renderPeserta(filtered);
        });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
</script>
@endpush