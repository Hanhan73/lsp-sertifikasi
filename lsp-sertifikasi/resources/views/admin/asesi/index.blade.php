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
        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-asesi"
            type="button" role="tab">
            <i class="bi bi-people me-1"></i> Semua Asesi
            <span class="badge bg-primary ms-1">{{ $asesmens->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tuk-tab" data-bs-toggle="tab" data-bs-target="#per-tuk"
            type="button" role="tab">
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
                        <button class="btn btn-sm btn-success dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-excel"></i> Export Excel
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width:220px;">
                            <li>
                                <h6 class="dropdown-header">Export Biodata</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" id="export-all-link"
                                    href="{{ route('admin.asesi.export') }}">
                                    <i class="bi bi-people me-2 text-primary"></i>Semua Asesi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('admin.asesi.export') }}?type=mandiri">
                                    <i class="bi bi-person me-2 text-success"></i>Mandiri saja
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('admin.asesi.export') }}?type=collective">
                                    <i class="bi bi-layers me-2 text-info"></i>Kolektif saja
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
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
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="stat-card" style="--bg-color: #6c757d; --bg-color-end: #495057;">
                            <p class="mb-1 small">Registered</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'registered')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card" style="--bg-color: #0dcaf0; --bg-color-end: #0aa2c0;">
                            <p class="mb-1 small">Data Completed</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'data_completed')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card" style="--bg-color: #0d6efd; --bg-color-end: #0a58ca;">
                            <p class="mb-1 small">Verified</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'verified')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card" style="--bg-color: #198754; --bg-color-end: #146c43;">
                            <p class="mb-1 small">Paid</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'paid')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card" style="--bg-color: #ffc107; --bg-color-end: #cc9a06;">
                            <p class="mb-1 small">Scheduled</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'scheduled')->count() }}</h5>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="stat-card" style="--bg-color: #20c997; --bg-color-end: #19a077;">
                            <p class="mb-1 small">Certified</p>
                            <h5 class="mb-0">{{ $asesmens->where('status', 'certified')->count() }}</h5>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label small">Filter Status</label>
                        <select id="filter-status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="registered">Registered</option>
                            <option value="data_completed">Data Completed</option>
                            <option value="verified">Verified</option>
                            <option value="paid">Paid</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="pre_assessment_completed">Pra-Asesmen Selesai</option>
                            <option value="assessed">Assessed</option>
                            <option value="certified">Certified</option>
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
                                data-tuk="{{ $asesmen->tuk_id }}"
                                data-skema="{{ $asesmen->skema_id }}">
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
                                    <span class="badge bg-{{ $asesmen->payment->status === 'verified' ? 'success' : 'warning' }}">
                                        {{ ucfirst($asesmen->payment->status) }}
                                    </span>
                                    <br><small>Rp {{ number_format($asesmen->payment->amount, 0, ',', '.') }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
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
                                <td><small>{{ $asesmen->registration_date->translatedFormat('d/m/Y') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.asesi.show', $asesmen) }}"
                                        class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
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
                // Ambil semua batch unik milik TUK ini
                $tukBatches = $asesmens
                    ->where('tuk_id', $tuk->id)
                    ->whereNotNull('collective_batch_id')
                    ->groupBy('collective_batch_id');

                $mandiriCount = $asesmens
                    ->where('tuk_id', $tuk->id)
                    ->where('is_collective', false)
                    ->count();
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white d-flex align-items-center gap-2 border-bottom">
                        @if($tuk->logo_path)
                            <img src="{{ asset('storage/' . $tuk->logo_path) }}"
                                style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded"
                                style="width:32px;height:32px;font-size:0.8rem;font-weight:700;">
                                {{ strtoupper(substr($tuk->name, 0, 2)) }}
                            </div>
                        @endif
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold text-truncate">{{ $tuk->name }}</div>
                            <small class="text-muted">{{ $tuk->code }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $tuk->asesmens_count }}</span>
                    </div>
                    <div class="card-body p-0">

                        {{-- Mandiri summary jika ada --}}
                        @if($mandiriCount > 0)
                        <div class="px-3 py-2 border-bottom bg-light">
                            <small class="text-muted fw-semibold">MANDIRI</small>
                            <div class="d-flex align-items-center justify-content-between mt-1">
                                <span class="small">{{ $mandiriCount }} asesi mandiri</span>
                                <a href="{{ route('admin.asesi') }}?tuk={{ $tuk->id }}&type=mandiri"
                                    class="btn btn-xs btn-outline-secondary py-0 px-2"
                                    style="font-size:0.75rem;">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                            </div>
                        </div>
                        @endif

                        {{-- Daftar batch kolektif --}}
                        @if($tukBatches->isEmpty())
                            <div class="text-center text-muted py-3">
                                <small>Tidak ada batch kolektif</small>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($tukBatches as $batchId => $members)
                                @php
                                    $first       = $members->first();
                                    $count       = $members->count();
                                    $statuses    = $members->pluck('status')->unique()->values();
                                    $allComplete = $members->every(fn($m) => $m->status === 'data_completed');
                                    $anyStarted  = $members->contains(
                                        fn($m) => !in_array($m->status, ['registered','data_completed'])
                                    );

                                    // Badge warna batch
                                    $batchBadge = $anyStarted ? 'success' : ($allComplete ? 'warning' : 'secondary');
                                    $batchLabel = $anyStarted ? 'Berjalan' : ($allComplete ? 'Siap Mulai' : 'Dalam Proses');
                                @endphp
                                <a href="{{ route('admin.asesi.batch.show', $batchId) }}"
                                    class="list-group-item list-group-item-action px-3 py-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="small fw-semibold text-truncate" style="max-width:160px;">
                                                <i class="bi bi-layers me-1 text-primary"></i>
                                                {{ $batchId }}
                                            </div>
                                            <div class="text-muted" style="font-size:0.72rem;">
                                                {{ $count }} peserta
                                                &bull;
                                                {{ $first->skema->name ?? '-' }}
                                            </div>
                                            <div class="text-muted" style="font-size:0.72rem;">
                                                {{ $first->registration_date->translatedFormat('d M Y') }}
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end gap-1">
                                            <span class="badge bg-{{ $batchBadge }} rounded-pill">
                                                {{ $batchLabel }}
                                            </span>
                                            <i class="bi bi-chevron-right text-muted small"></i>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        @endif
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

{{-- Detail Modal (untuk tombol AJAX jika masih dipakai) --}}
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
$(document).ready(function () {

    $('[data-bs-toggle="tooltip"]').tooltip();

    if ($.fn.DataTable.isDataTable('#asesi-table')) {
        $('#asesi-table').DataTable().destroy();
    }

    const table = $('#asesi-table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        order: [[10, 'desc']],
        pageLength: 25,
        responsive: true,
        columnDefs: [{ orderable: false, targets: 11 }]
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        const status = $('#filter-status').val();
        const type   = $('#filter-type').val();
        const tuk    = $('#filter-tuk').val();
        const skema  = $('#filter-skema').val();

        const row      = table.row(dataIndex).node();
        const rowStatus = $(row).data('status');
        const rowType   = $(row).data('type');
        const rowTuk    = $(row).data('tuk');
        const rowSkema  = $(row).data('skema');

        if (status && rowStatus !== status) return false;
        if (type   && rowType   !== type)   return false;
        if (tuk    && rowTuk    != tuk)     return false;
        if (skema  && rowSkema  != skema)   return false;

        return true;
    });

    $('#filter-status, #filter-type, #filter-tuk, #filter-skema').on('change', function () {
        table.draw();
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

// Update link "Export sesuai filter aktif" berdasarkan filter yang dipilih
function updateFilteredExportLink() {
    const baseUrl = '{{ route("admin.asesi.export") }}';
    const params  = new URLSearchParams();

    const status = $('#filter-status').val();
    const type   = $('#filter-type').val();
    const tukId  = $('#filter-tuk').val();
    const skemaId = $('#filter-skema').val();

    if (status)  params.set('status',   status);
    if (type)    params.set('type',     type);
    if (tukId)   params.set('tuk_id',  tukId);
    if (skemaId) params.set('skema_id', skemaId);

    const qs = params.toString();
    const url = qs ? baseUrl + '?' + qs : baseUrl;

    $('#export-filtered-link').attr('href', url);

    // Highlight jika ada filter aktif
    if (qs) {
        $('#export-filtered-link').removeClass('text-muted').addClass('text-primary fw-semibold');
    } else {
        $('#export-filtered-link').addClass('text-muted').removeClass('text-primary fw-semibold');
    }
}
</script>
@endpush