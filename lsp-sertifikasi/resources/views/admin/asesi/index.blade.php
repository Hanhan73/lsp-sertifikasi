@extends('layouts.app')

@section('title', 'Semua Asesi')
@section('page-title', 'Database Asesi')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

{{-- Tab Navigation --}}
<ul class="nav nav-tabs mb-4" id="asesiTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-asesi" type="button"
            role="tab">
            <i class="bi bi-people me-1"></i> Semua Asesi
            <span class="badge bg-primary ms-1">{{ $asesmens->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tuk-tab" data-bs-toggle="tab" data-bs-target="#per-tuk" type="button" role="tab">
            <i class="bi bi-building me-1"></i> Per TUK
            <span class="badge bg-secondary ms-1">{{ $tuks->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="asesiTabContent">

    {{-- ══════════════════════════════════════════════════════════
         TAB 1: SEMUA ASESI
    ══════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="all-asesi" role="tabpanel">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people"></i> Semua Data Asesi
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-file-excel"></i> Export Excel
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width:220px;">
                            <li>
                                <h6 class="dropdown-header">Export Biodata</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" id="export-all-link" href="{{ route('admin.asesi.export') }}">
                                    <i class="bi bi-people me-2 text-primary"></i>Semua Asesi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.asesi.export') }}?type=mandiri">
                                    <i class="bi bi-person me-2 text-success"></i>Mandiri saja
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.asesi.export') }}?type=collective">
                                    <i class="bi bi-layers me-2 text-info"></i>Kolektif saja
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-muted" id="export-filtered-link"
                                    href="{{ route('admin.asesi.export') }}"
                                    title="Export sesuai filter aktif saat ini">
                                    <i class="bi bi-funnel me-2"></i>Export sesuai filter aktif
                                </a>
                            </li>
                        </ul>
                    </div>
                    <span class="badge bg-primary">{{ $asesmens->count() }} Total</span>
                </div>
            </div>
            <div class="card-body">
                {{-- Statistics Row --}}
                <div class="row mb-4 g-2">
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #6c757d; --bg-color-end: #495057;">
                            <p class="mb-1 small">Terdaftar</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'registered')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #0dcaf0; --bg-color-end: #0aa2c0;">
                            <p class="mb-1 small">Data Lengkap</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'data_completed')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #0d6efd; --bg-color-end: #0a58ca;">
                            <p class="mb-1 small">Pra-Asesmen</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'pra_asesmen_started')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #ffc107; --bg-color-end: #cc9a06;">
                            <p class="mb-1 small">Terjadwal</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'scheduled')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #6366f1; --bg-color-end: #4f46e5;">
                            <p class="mb-1 small">Asesmen Dimulai</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'asesmen_started')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #0ea5e9; --bg-color-end: #0284c7;">
                            <p class="mb-1 small">Sudah Diases</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'assessed')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #198754; --bg-color-end: #146c43;">
                            <p class="mb-1 small">Tersertifikasi</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'certified')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col">
                        <div class="stat-card" style="--bg-color: #20c997; --bg-color-end: #19a077;">
                            <p class="mb-1 small">Sertifikat Terdistribusi</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'certificate_distributed')->count() }}</h5>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label small">Filter Status</label>
                            <select id="filter-status" class="form-select form-select-sm">
                                <option value="">Semua Status</option>
                                <option value="registered">Terdaftar</option>
                                <option value="data_completed">Data Lengkap</option>
                                <option value="pra_asesmen_started">Pra-Asesmen</option>
                                <option value="scheduled">Terjadwal</option>
                                <option value="asesmen_started">Asesmen Dimulai</option>
                                <option value="assessed">Sudah Diases</option>
                                <option value="certified">Tersertifikasi</option>
                                <option value="certificate_distributed">Sertifikat Didistribusikan</option>
                            </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Filter Jenis</label>
                        <select id="filter-type" class="form-select form-select-sm">
                            <option value="">Semua Jenis</option>
                            <option value="mandiri">Mandiri</option>
                            <option value="collective">Kolektif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Filter TUK</label>
                        <select id="filter-tuk" class="form-select form-select-sm">
                            <option value="">Semua TUK</option>
                            @foreach($asesmens->pluck('tuk')->unique('id')->filter()->sortBy('name') as $tuk)
                            <option value="{{ $tuk->id }}">{{ $tuk->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Filter Skema</label>
                        <select id="filter-skema" class="form-select form-select-sm">
                            <option value="">Semua Skema</option>
                            @foreach($asesmens->pluck('skema')->unique('id')->filter()->sortBy('name') as $skema)
                            <option value="{{ $skema->id }}">{{ $skema->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover table-sm" id="asesi-table">
                        <thead class="table-light">
                            <tr>
                                <th>No Reg</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>TUK</th>
                                <th>Skema</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                                <th>Jadwal</th>
                                <th>Hasil</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asesmens as $asesmen)
                            <tr data-status="{{ $asesmen->status }}"
                                data-type="{{ $asesmen->is_collective ? 'collective' : 'mandiri' }}"
                                data-tuk="{{ $asesmen->tuk_id }}" data-skema="{{ $asesmen->skema_id }}"
                                data-asesmen-id="{{ $asesmen->id }}">
                                <td><strong>#{{ $asesmen->id }}</strong></td>
                                <td>
                                    {{ $asesmen->full_name ?? $asesmen->user->name ?? '-' }}
                                    @if($asesmen->is_collective)
                                    <br><small class="text-muted"><i class="bi bi-layers"></i>
                                        {{ $asesmen->collective_batch_id }}</small>
                                    @endif
                                </td>
                                <td><small>{{ $asesmen->email ?? $asesmen->user->email ?? '-' }}</small></td>
                                <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                                <td><small>{{ $asesmen->skema->name ?? '-' }}</small></td>
                                <td>
                                    @if($asesmen->is_collective)
                                    <span class="badge bg-primary"><i class="bi bi-people"></i> Kolektif</span>
                                    @else
                                    <span class="badge bg-success"><i class="bi bi-person"></i> Mandiri</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $asesmen->status_badge }}">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($asesmen->payment)
                                    <span
                                        class="badge bg-{{ $asesmen->payment->status === 'verified' ? 'success' : 'warning' }}">
                                        {{ ucfirst($asesmen->payment->status) }}
                                    </span>
                                    <br><small>Rp {{ number_format($asesmen->payment->amount, 0, ',', '.') }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-order="{{ $asesmen->schedule?->assessment_date?->format('Y-m-d') ?? '0000-00-00' }}">
                                    @if($asesmen->schedule)
                                    <small>{{ $asesmen->schedule->assessment_date->translatedFormat('d/m/Y') }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($asesmen->result)
                                    <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                                        {{ ucfirst($asesmen->result) }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-order="{{ $asesmen->registration_date->format('Y-m-d') }}">
                                    <small>{{ $asesmen->registration_date->translatedFormat('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.asesi.show', $asesmen) }}" class="btn btn-sm btn-info"
                                        data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(!$asesmen->is_collective && !in_array($asesmen->status, ['certified',
                                    'assessed', 'asesmen_started']))
                                    <button class="btn btn-sm btn-danger ms-1"
                                        onclick="hapusMandiri({{ $asesmen->id }}, '{{ addslashes($asesmen->full_name ?? $asesmen->user->name) }}')"
                                        data-bs-toggle="tooltip" title="Hapus Akun Mandiri">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">Belum ada data asesi</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TAB 2: PER TUK
    ══════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="per-tuk" role="tabpanel">
    <div class="row g-3">
        @forelse($tuks as $tuk)
        @php
        $tukBatchCount = $asesmens
        ->where('tuk_id', $tuk->id)
        ->whereNotNull('collective_batch_id')
        ->pluck('collective_batch_id')
        ->unique()
        ->count();

        $mandiriCount = $asesmens
        ->where('tuk_id', $tuk->id)
        ->where('is_collective', false)
        ->count();

        $totalPeserta = $tuk->_total ?? $tuk->asesmens_count;
        @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 tuk-card" style="cursor:pointer;"
                data-tuk-id="{{ $tuk->id }}" data-tuk-name="{{ $tuk->name }}">
                <div class="card-body d-flex align-items-center gap-3">
                    @if($tuk->logo_path)
                    <img src="{{ asset('storage/' . $tuk->logo_path) }}"
                        style="width:44px;height:44px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                    @else
                    <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded flex-shrink-0"
                        style="width:44px;height:44px;font-size:0.9rem;font-weight:700;">
                        {{ strtoupper(substr($tuk->name, 0, 2)) }}
                    </div>
                    @endif
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-truncate">{{ $tuk->name }}</div>
                        <small class="text-muted">{{ $tuk->code }}</small>
                        <div class="d-flex gap-2 mt-1">
                            <span class="badge bg-primary rounded-pill">{{ $totalPeserta }} peserta</span>
                            @if($tukBatchCount > 0)
                            <span class="badge bg-light text-dark border">{{ $tukBatchCount }} batch</span>
                            @endif
                            @if($mandiriCount > 0)
                            <span class="badge bg-light text-dark border">{{ $mandiriCount }} mandiri</span>
                            @endif
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted flex-shrink-0"></i>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <i class="bi bi-building" style="font-size: 2.5rem;"></i>
                <p class="mt-2 mb-0">Belum ada TUK dengan data asesi</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

</div>

{{-- ══ MODAL DETAIL TUK — daftar batch + link mandiri ══ --}}
<div class="modal fade" id="modalTukDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-building me-2"></i><span id="modal-tuk-name">-</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div id="tuk-modal-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2 mb-0">Memuat data...</p>
                </div>

                <div id="tuk-modal-content" style="display:none;">

                    {{-- Link asesi mandiri --}}
                    <div id="tuk-modal-mandiri-box" class="alert alert-light border d-flex align-items-center justify-content-between mb-3" style="display:none;">
                        <span><i class="bi bi-person me-1"></i><span id="tuk-modal-mandiri-count">0</span> asesi mandiri</span>
                        <a href="#" id="tuk-modal-mandiri-link" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Lihat
                        </a>
                    </div>

                    {{-- Search batch --}}
                    <div class="mb-3">
                        <input type="text" id="tuk-modal-search" class="form-control form-control-sm"
                            placeholder="Cari batch berdasarkan nama atau skema...">
                    </div>

                    {{-- List batch --}}
                    <div class="list-group list-group-flush" id="tuk-modal-batch-list" style="max-height:400px; overflow-y:auto;"></div>

                    <div id="tuk-modal-empty" class="text-center text-muted py-4" style="display:none;">
                        <small>Tidak ada batch yang cocok.</small>
                    </div>
                </div>

                <div id="tuk-modal-error" class="text-center py-5 text-danger" style="display:none;">
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                    <p class="mt-2 mb-0">Gagal memuat data.</p>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


{{-- Detail Modal --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-badge"></i> Detail Asesi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let dtTable = null;

    $(document).ready(function() {

        $('[data-bs-toggle="tooltip"]').tooltip();

        if ($.fn.DataTable.isDataTable('#asesi-table')) {
            $('#asesi-table').DataTable().destroy();
        }

        dtTable = $('#asesi-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [
                [10, 'desc']
            ],
            pageLength: 25,
            responsive: true,
            columnDefs: [{
                orderable: false,
                targets: 11
            }]
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            const status = $('#filter-status').val();
            const type = $('#filter-type').val();
            const tuk = $('#filter-tuk').val();
            const skema = $('#filter-skema').val();

            const row = dtTable.row(dataIndex).node();
            const rowStatus = $(row).data('status');
            const rowType = $(row).data('type');
            const rowTuk = $(row).data('tuk');
            const rowSkema = $(row).data('skema');

            if (status && rowStatus !== status) return false;
            if (type && rowType !== type) return false;
            if (tuk && rowTuk != tuk) return false;
            if (skema && rowSkema != skema) return false;

            return true;
        });

        $('#filter-status, #filter-type, #filter-tuk, #filter-skema').on('change', function() {
            dtTable.draw();
            updateFilteredExportLink();
        });

        // Jika URL punya query param ?tuk=...&type=... langsung pindah tab Per TUK
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tuk') || urlParams.get('type') === 'mandiri') {
            const tukTab = new bootstrap.Tab(document.getElementById('tuk-tab'));
            tukTab.show();
            if (urlParams.get('tuk')) {
                $('#filter-tuk').val(urlParams.get('tuk')).trigger('change');
            }
        }
    });

    function updateFilteredExportLink() {
        const baseUrl = '{{ route("admin.asesi.export") }}';
        const params = new URLSearchParams();

        const status = $('#filter-status').val();
        const type = $('#filter-type').val();
        const tukId = $('#filter-tuk').val();
        const skemaId = $('#filter-skema').val();

        if (status) params.set('status', status);
        if (type) params.set('type', type);
        if (tukId) params.set('tuk_id', tukId);
        if (skemaId) params.set('skema_id', skemaId);

        const qs = params.toString();
        const url = qs ? baseUrl + '?' + qs : baseUrl;

        $('#export-filtered-link').attr('href', url);

        if (qs) {
            $('#export-filtered-link').removeClass('text-muted').addClass('text-primary fw-semibold');
        } else {
            $('#export-filtered-link').addClass('text-muted').removeClass('text-primary fw-semibold');
        }
    }

    // ── Hapus Akun Mandiri ──────────────────────────────────────
    async function hapusMandiri(asesmenId, nama) {
        const result = await Swal.fire({
            title: 'Hapus Akun Mandiri?',
            html: `Akun <strong>${nama}</strong> (Mandiri) akan dihapus permanen beserta seluruh datanya.<br>
               <span class="text-danger small fw-semibold">Tindakan ini tidak bisa dibatalkan!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus Permanen',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        });

        if (!result.isConfirmed) return;

        try {
            const res = await fetch(`/admin/asesi/${asesmenId}/hapus-mandiri`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Dihapus!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                });

                // Hapus row dari DataTable tanpa reload
                const row = document.querySelector(`tr[data-asesmen-id="${asesmenId}"]`);
                if (row && dtTable) {
                    dtTable.row(row).remove().draw();
                } else {
                    location.reload();
                }
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        }
    }

    let currentTukBatches = [];

document.querySelectorAll('.tuk-card').forEach(card => {
    card.addEventListener('click', function () {
        openTukModal(this.dataset.tukId, this.dataset.tukName);
    });
});

function openTukModal(tukId, tukName) {
    const modalEl = document.getElementById('modalTukDetail');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.getElementById('modal-tuk-name').textContent = tukName;
    document.getElementById('tuk-modal-loading').style.display = 'block';
    document.getElementById('tuk-modal-content').style.display = 'none';
    document.getElementById('tuk-modal-error').style.display = 'none';
    document.getElementById('tuk-modal-search').value = '';

    modal.show();

    const url = `{{ url('admin/asesi/tuk') }}/${tukId}/batches`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            document.getElementById('tuk-modal-loading').style.display = 'none';

            if (!data.success) {
                document.getElementById('tuk-modal-error').style.display = 'block';
                return;
            }

            document.getElementById('tuk-modal-content').style.display = 'block';

            const mandiriBox = document.getElementById('tuk-modal-mandiri-box');
            if (data.mandiri_count > 0) {
                mandiriBox.style.display = 'flex';
                document.getElementById('tuk-modal-mandiri-count').textContent = data.mandiri_count;
                document.getElementById('tuk-modal-mandiri-link').href = data.mandiri_url;
            } else {
                mandiriBox.style.display = 'none';
            }

            currentTukBatches = data.batches;
            renderTukBatchList(currentTukBatches);
        })
        .catch(() => {
            document.getElementById('tuk-modal-loading').style.display = 'none';
            document.getElementById('tuk-modal-error').style.display = 'block';
        });
}

function renderTukBatchList(list) {
    const container = document.getElementById('tuk-modal-batch-list');
    const emptyMsg = document.getElementById('tuk-modal-empty');
    container.innerHTML = '';

    if (list.length === 0) {
        emptyMsg.style.display = 'block';
        return;
    }
    emptyMsg.style.display = 'none';

    list.forEach(b => {
        const item = document.createElement('a');
        item.href = b.url;
        item.className = 'list-group-item list-group-item-action px-3 py-2';
        item.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <div class="min-width-0">
                    <div class="small fw-semibold text-truncate"><i class="bi bi-layers me-1 text-primary"></i>${escapeHtmlTuk(b.batch_id)}</div>
                    <div class="text-muted" style="font-size:0.75rem;">${b.total} peserta &bull; ${escapeHtmlTuk(b.skema)}</div>
                    <div class="text-muted" style="font-size:0.72rem;">${b.registration_date ?? '-'}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0 ms-2">
                    <span class="badge bg-${b.badge} rounded-pill">${b.label}</span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </div>
            </div>
        `;
        container.appendChild(item);
    });
}

document.getElementById('tuk-modal-search').addEventListener('input', function () {
    const term = this.value.toLowerCase();
    const filtered = currentTukBatches.filter(b =>
        b.batch_id.toLowerCase().includes(term) || b.skema.toLowerCase().includes(term)
    );
    renderTukBatchList(filtered);
});

function escapeHtmlTuk(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
}
</script>
@endpush