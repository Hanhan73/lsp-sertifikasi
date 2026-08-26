@extends('layouts.app')
@section('title', 'Distribusi Sertifikat')
@section('page-title', 'Distribusi Sertifikat')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}</div>
@endif

{{-- ── BATCH KOLEKTIF ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-people-fill me-2 text-primary"></i>Batch Kolektif
        <span class="small text-muted fw-normal ms-2">(klik baris untuk lihat detail)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch ID</th><th>TUK</th><th>Skema</th><th class="text-center">Peserta</th>
                    <th class="text-center">Berita Acara</th><th class="text-center">Status</th><th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $b)
                <tr class="batch-row" style="cursor:pointer;" data-batch-id="{{ $b->batch_id }}">
                    <td><code>{{ $b->batch_id }}</code></td>
                    <td>{{ $b->tuk->name ?? '-' }}</td>
                    <td class="small">{{ $b->skema->name ?? '-' }}</td>
                    <td class="text-center">{{ $b->total }}</td>
                    <td class="text-center">
                        @if($b->ada_ba)
                        <i class="bi bi-check-circle-fill text-success" title="Ada Berita Acara"></i>
                        @else
                        <i class="bi bi-x-circle-fill text-danger" title="Belum ada Berita Acara"></i>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($b->sudah_distribusi)
                        <span class="badge bg-success">Sudah Didistribusikan</span>
                        @if($b->sudah_upload)
                        <span class="badge bg-primary">Semua Sudah Upload</span>
                        @endif
                        @elseif($b->siap_distribusi)
                        <span class="badge bg-warning text-dark">Siap Distribusi</span>
                        @elseif($b->total_kompeten === 0)
                        <span class="badge bg-secondary">Belum ada hasil K</span>
                        @else
                        <span class="badge bg-secondary">Belum di-SK</span>
                        @endif
                    </td>
                    <td class="text-center" onclick="event.stopPropagation();">
                        @if(!$b->sudah_distribusi)
                        <form action="{{ route('admin.sertifikat-distribusi.batch', $b->batch_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning"
                                {{ (!$b->siap_distribusi || !$b->ada_ba) ? 'disabled' : '' }}
                                onclick="return confirm('Tandai batch {{ $b->batch_id }} sudah didistribusikan secara fisik?')">
                                <i class="bi bi-truck me-1"></i>Tandai Terdistribusi
                            </button>
                        </form>
                        <div class="small text-muted mt-1">
                            {{ $b->certified_count }}/{{ $b->total_kompeten }} peserta Kompeten sudah di-SK
                            @if($b->bk_count > 0)
                            <br><span class="text-danger">{{ $b->bk_count }} peserta BK (tidak disertifikasi)</span>
                            @endif
                            @if($b->belum_ada_hasil > 0)
                            <br><span class="text-warning">{{ $b->belum_ada_hasil }} peserta belum ada hasil</span>
                            @endif
                        </div>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada batch yang sudah diases.</td></tr>
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
                    <th>Nama</th><th>Skema</th><th class="text-center">Berita Acara</th>
                    <th class="text-center">Status</th><th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mandiri as $m)
                <tr>
                    <td>{{ $m->full_name }}</td>
                    <td class="small">{{ $m->skema->name ?? '-' }}</td>
                    <td class="text-center">
                        @if($m->schedule?->beritaAcara)
                        <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $m->status_badge }}">{{ $m->status_label }}</span>
                        @if($m->status === 'certificate_distributed' && $m->hasUploadedPhysicalCertificate())
                        <span class="badge bg-primary">Sudah Upload</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($m->status === 'certified')
                        <form action="{{ route('admin.sertifikat-distribusi.individual', $m) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning"
                                {{ !$m->schedule?->beritaAcara ? 'disabled' : '' }}
                                onclick="return confirm('Tandai {{ $m->full_name }} sudah didistribusikan secara fisik?')">
                                <i class="bi bi-truck me-1"></i>Tandai Terdistribusi
                            </button>
                        </form>
                        @if(!$m->schedule?->beritaAcara)
                        <div class="small text-muted mt-1">Berita Acara belum ada</div>
                        @endif
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada asesi mandiri yang sudah diases.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══ MODAL DETAIL BATCH ══ --}}
<div class="modal fade" id="modalBatchDetail" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-people-fill me-2"></i>Detail Batch — <code id="modal-batch-id" class="text-white"></code>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                {{-- Loading state --}}
                <div id="modal-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat data...</p>
                </div>

                {{-- Content (hidden sampai data loaded) --}}
                <div id="modal-content" style="display:none;">

                    {{-- Ringkasan --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="text-muted" width="90">TUK</td><td>: <span id="modal-tuk" class="fw-semibold"></span></td></tr>
                                <tr><td class="text-muted">Skema</td><td>: <span id="modal-skema"></span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-light rounded p-2 text-center">
                                <div class="fw-bold fs-5" id="modal-total">-</div>
                                <div class="small text-muted">Total Peserta</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-success bg-opacity-10 rounded p-2 text-center">
                                <div class="fw-bold fs-5 text-success" id="modal-kompeten">-</div>
                                <div class="small text-muted">Kompeten</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-danger bg-opacity-10 rounded p-2 text-center">
                                <div class="fw-bold fs-5 text-danger" id="modal-bk">-</div>
                                <div class="small text-muted">Belum Kompeten</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="bg-secondary bg-opacity-10 rounded p-2 text-center">
                                <div class="fw-bold fs-5 text-secondary" id="modal-belum">-</div>
                                <div class="small text-muted">Belum Ada Hasil</div>
                            </div>
                        </div>
                    </div>

                    {{-- Filter cepat --}}
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-hasil active" data-filter="all">Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-success filter-hasil" data-filter="kompeten">Kompeten</button>
                        <button type="button" class="btn btn-sm btn-outline-danger filter-hasil" data-filter="belum_kompeten">Belum Kompeten</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary filter-hasil" data-filter="null">Belum Ada Hasil</button>
                    </div>

                    {{-- Tabel peserta --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30">#</th>
                                        <th>Nama</th>
                                        <th>NIK</th>
                                        <th>Instansi</th>
                                        <th class="text-center">Hasil</th>
                                        <th>Jadwal</th>
                                        <th>Asesor</th>
                                        <th class="text-center">Status Sertifikat</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                            <tbody id="modal-peserta-body"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Error state --}}
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
    const asesiShowBaseUrl = '{{ url("admin/asesi") }}';
    let currentPeserta = [];

    document.querySelectorAll('.batch-row').forEach(row => {
        row.addEventListener('click', function () {
            const batchId = this.dataset.batchId;
            openBatchDetail(batchId);
        });
    });

    function openBatchDetail(batchId) {
        const modalEl = document.getElementById('modalBatchDetail');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        document.getElementById('modal-batch-id').textContent = batchId;
        document.getElementById('modal-loading').style.display = 'block';
        document.getElementById('modal-content').style.display = 'none';
        document.getElementById('modal-error').style.display = 'none';

        modal.show();

        const url = `{{ url('admin/sertifikat-distribusi/batch') }}/${encodeURIComponent(batchId)}/detail`;

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
                document.getElementById('modal-kompeten').textContent = data.batch.kompeten;
                document.getElementById('modal-bk').textContent = data.batch.bk;
                document.getElementById('modal-belum').textContent = data.batch.belum;

                currentPeserta = data.peserta;
                renderPeserta(currentPeserta);

                // Reset filter ke "Semua"
                document.querySelectorAll('.filter-hasil').forEach(b => b.classList.remove('active'));
                document.querySelector('.filter-hasil[data-filter="all"]').classList.add('active');
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
            const hasilBadge = p.result === 'kompeten'
                ? '<span class="badge bg-success">K</span>'
                : p.result === 'belum_kompeten'
                    ? '<span class="badge bg-danger">BK</span>'
                    : '<span class="badge bg-secondary">-</span>';

            let sertifikatBadge = '<span class="text-muted small">—</span>';
            if (p.result === 'kompeten') {
                if (p.uploaded_physical) {
                    sertifikatBadge = `<span class="badge bg-primary">Diterima${p.physical_cert_no ? ' — ' + p.physical_cert_no : ''}</span>`;
                } else if (p.status === 'certificate_distributed') {
                    sertifikatBadge = '<span class="badge bg-warning text-dark">Didistribusikan, blm upload</span>';
                } else if (p.status === 'certified') {
                    sertifikatBadge = '<span class="badge bg-info text-dark">Menunggu Distribusi</span>';
                } else {
                    sertifikatBadge = '<span class="badge bg-secondary">Belum di-SK</span>';
                }
            } else if (p.result === 'belum_kompeten') {
                sertifikatBadge = '<span class="text-muted small">Tidak disertifikasi</span>';
            }

            const detailUrl = `${asesiShowBaseUrl}/${p.id}`;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="text-muted">${i + 1}</td>
                <td><strong>${escapeHtml(p.full_name ?? '-')}</strong></td>
                <td class="font-monospace small">${escapeHtml(p.nik ?? '-')}</td>
                <td class="small">${escapeHtml(p.institution ?? '-')}</td>
                <td class="text-center">${hasilBadge}</td>
                <td class="small">${escapeHtml(p.schedule_date ?? '-')}</td>
                <td class="small">${escapeHtml(p.asesor ?? '-')}</td>
                <td class="text-center">${sertifikatBadge}</td>
                <td class="text-center">
                    <a href="${detailUrl}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="Lihat Detail Asesi">
                        <i class="bi bi-eye" style="font-size:.8rem;"></i>
                    </a>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    document.querySelectorAll('.filter-hasil').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-hasil').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            const filtered = filter === 'all'
                ? currentPeserta
                : currentPeserta.filter(p => (p.result ?? 'null') === filter || (filter === 'null' && p.result === null));

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