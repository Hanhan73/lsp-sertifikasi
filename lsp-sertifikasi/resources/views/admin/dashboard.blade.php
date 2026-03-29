@extends('layouts.app')
@section('title', 'Dashboard Admin LSP')
@section('page-title', 'Dashboard Admin LSP')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

{{-- ── Stats Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10"
                    style="width:52px;height:52px;flex-shrink:0;">
                    <i class="bi bi-people text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Asesi</div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['total_asesi'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10"
                    style="width:52px;height:52px;flex-shrink:0;">
                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Menunggu Mulai Asesmen</div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['pending_mulai'] }}</div>
                </div>
            </div>
            @if($stats['pending_mulai'] > 0)
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('admin.verifications.index') }}" class="btn btn-warning btn-sm w-100">
                    <i class="bi bi-play-circle me-1"></i>Mulai Sekarang
                </a>
            </div>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10"
                    style="width:52px;height:52px;flex-shrink:0;">
                    <i class="bi bi-pencil-square text-info fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Sedang Isi Dokumen</div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['sedang_asesmen'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10"
                    style="width:52px;height:52px;flex-shrink:0;">
                    <i class="bi bi-award text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tersertifikasi</div>
                    <div class="fs-2 fw-bold lh-1">{{ $stats['certified'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Perlu Perhatian ── --}}
@if($stats['pending_mulai'] > 0 || $stats['pending_asesor'] > 0)
<div class="row g-3 mb-4">

    {{-- Menunggu mulai asesmen --}}
    @if($needsAttention['mulai_asesmen']->isNotEmpty())
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-play-circle me-2 text-warning"></i>Perlu Dimulai
                </span>
                <a href="{{ route('admin.verifications.index') }}" class="btn btn-warning btn-sm">
                    Lihat Semua <span class="badge bg-white text-warning ms-1">{{ $stats['pending_mulai'] }}</span>
                </a>
            </div>
            <div class="list-group list-group-flush">
                @foreach($needsAttention['mulai_asesmen'] as $a)
                <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold small">{{ $a->full_name ?? $a->user->name }}</div>
                        <div class="text-muted" style="font-size:.78rem;">
                            {{ $a->skema->name ?? '-' }}
                            @if($a->is_collective)
                                &bull; <span class="badge bg-primary" style="font-size:.6rem;">Kolektif</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.verifications.show', $a) }}"
                        class="btn btn-warning btn-sm py-1">
                        <i class="bi bi-play-circle"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Jadwal belum ada asesor --}}
    @if($needsAttention['belum_asesor']->isNotEmpty())
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
            <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-person-x me-2 text-danger"></i>Asesor Belum Ditugaskan
                </span>
                <a href="{{ route('admin.asesor-assignments.index') }}" class="btn btn-danger btn-sm">
                    Lihat Semua <span class="badge bg-white text-danger ms-1">{{ $stats['pending_asesor'] }}</span>
                </a>
            </div>
            <div class="list-group list-group-flush">
                @foreach($needsAttention['belum_asesor'] as $s)
                <div class="list-group-item px-3 py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold small">
                            {{ $s->assessment_date->format('d M Y') }}
                            &bull; {{ $s->start_time }}
                        </div>
                        <div class="text-muted" style="font-size:.78rem;">
                            {{ $s->tuk->name ?? '-' }}
                            &bull; {{ $s->asesmens->count() }} asesi
                        </div>
                    </div>
                    <a href="{{ route('admin.asesor-assignments.index') }}"
                        class="btn btn-danger btn-sm py-1">
                        <i class="bi bi-person-plus"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endif

{{-- ── Batch Kolektif Terbaru ── --}}
@if($latestBatch)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold border-bottom">
        <i class="bi bi-layers me-2 text-primary"></i>Batch Kolektif Terbaru
    </div>
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-muted" width="130">Batch ID</td>
                        <td>: <code>{{ $latestBatch['batch_id'] }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">TUK</td>
                        <td>: {{ $latestBatch['tuk']->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Didaftarkan oleh</td>
                        <td>: {{ $latestBatch['registered_by']->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Peserta</td>
                        <td>: <strong>{{ $latestBatch['total_members'] }} orang</strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-7">
                <div class="small text-muted fw-semibold mb-2">Progress Peserta</div>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach([
                        ['key' => 'registered',      'label' => 'Terdaftar',     'color' => 'secondary'],
                        ['key' => 'data_completed',  'label' => 'Data Lengkap',  'color' => 'info'],
                        ['key' => 'asesmen_started', 'label' => 'Asesmen Mulai', 'color' => 'primary'],
                        ['key' => 'scheduled',       'label' => 'Terjadwal',     'color' => 'warning'],
                        ['key' => 'certified',       'label' => 'Tersertifikasi','color' => 'success'],
                    ] as $s)
                    @if($latestBatch['status_counts'][$s['key']] > 0)
                    <span class="badge bg-{{ $s['color'] }} px-3 py-2" style="font-size:.8rem;">
                        {{ $s['label'] }}: {{ $latestBatch['status_counts'][$s['key']] }}
                    </span>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Asesi Terbaru ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Asesi Terbaru</span>
        <a href="{{ route('admin.asesi.index') }}" class="btn btn-outline-primary btn-sm">
            Lihat Semua
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="table-asesmens">
                <thead class="table-light">
                    <tr>
                        <th>No. Reg</th>
                        <th>Nama</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-center">Status</th>
                        <th>Tgl Daftar</th>
                        <th width="60"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($asesmens as $asesmen)
                    <tr>
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>
                            <div class="fw-semibold">{{ $asesmen->full_name ?? $asesmen->user->name }}</div>
                            @if($asesmen->is_collective)
                            <div class="text-muted" style="font-size:.75rem;">
                                <i class="bi bi-layers"></i> {{ $asesmen->collective_batch_id }}
                            </div>
                            @endif
                        </td>
                        <td class="small">{{ $asesmen->tuk->name ?? '-' }}</td>
                        <td class="small">{{ $asesmen->skema->name ?? '-' }}</td>
                        <td class="text-center">
                            @if($asesmen->is_collective)
                                <span class="badge bg-primary">Kolektif</span>
                            @else
                                <span class="badge bg-success">Mandiri</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $asesmen->status_badge }}">
                                {{ $asesmen->status_label }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $asesmen->registration_date->format('d/m/Y') }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="viewDetail({{ $asesmen->id }})" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            Belum ada data asesi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Detail --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detail Asesi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#table-asesmens').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        order: [[6, 'desc']],
        pageLength: 10,
        responsive: true,
        columnDefs: [{ orderable: false, targets: [4, 5, 7] }],
    });
});

function viewDetail(asesmenId) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();

    document.getElementById('detail-content').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
            <p class="mt-3 text-muted">Memuat data...</p>
        </div>`;

    fetch(`/admin/asesmens/${asesmenId}/detail`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('detail-content').innerHTML = data.success
                ? data.html
                : `<div class="alert alert-warning">${data.message ?? 'Gagal memuat data.'}</div>`;
        })
        .catch(() => {
            document.getElementById('detail-content').innerHTML =
                `<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>`;
        });
}
</script>
@endpush