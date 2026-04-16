@extends('layouts.app')
@section('title', 'Rekap Hasil Asesmen')
@section('page-title', 'Rekap Hasil Asesmen')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-0">Rekap Hasil Asesmen</h5>
        <p class="text-muted small mb-0">Download rekap hasil ujian teori, observasi, dan berita acara per batch</p>
    </div>
</div>

{{-- ── NAV TABS ── --}}
@php $activeTab = request('tab', 'teori'); @endphp
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'teori' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['tab' => 'teori']) }}">
            <i class="bi bi-journal-text me-1"></i>Hasil Teori
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'observasi' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['tab' => 'observasi']) }}">
            <i class="bi bi-eye me-1"></i>Hasil Observasi
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $activeTab === 'berita-acara' ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['tab' => 'berita-acara']) }}">
            <i class="bi bi-file-text me-1"></i>Berita Acara
        </a>
    </li>
</ul>


{{-- ================================================================
     TAB 1: HASIL TEORI — konten existing tidak diubah
================================================================ --}}
@if($activeTab === 'teori')

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-people-fill text-primary"></i>
        Batch Kolektif
        <span class="badge bg-primary ms-1">{{ count($batches) }}</span>
    </div>
    <div class="card-body p-0">
        @if($batches->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size:2rem;opacity:.3;"></i>
            <p class="mt-2 small">Belum ada batch kolektif dengan data soal teori.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Batch ID</th>
                        <th>Skema</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Total Asesi</th>
                        <th class="text-center">Status Ujian</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td class="ps-4">
                            <span class="font-monospace fw-semibold text-primary" style="font-size:.8rem;">
                                {{ $batch['batch_id'] }}
                            </span>
                        </td>
                        <td>{{ $batch['skema_name'] }}</td>
                        <td class="text-center text-muted">
                            {{ $batch['tanggal'] ? $batch['tanggal']->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $batch['total_asesi'] }} asesi</span>
                        </td>
                        <td class="text-center">
                            @if($batch['semua_selesai'])
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>Semua Selesai
                            </span>
                            @elseif($batch['sudah_submit'] > 0)
                            <span class="badge bg-warning text-dark">
                                {{ $batch['sudah_submit'] }}/{{ $batch['total_soal'] }} Submit
                            </span>
                            @else
                            <span class="badge bg-secondary">Belum Ada Submit</span>
                            @endif
                            @if($batch['belum_submit'] > 0)
                            <div class="text-muted mt-1" style="font-size:.7rem;">{{ $batch['belum_submit'] }} belum submit</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    onclick="bukaPreviousBatch('{{ $batch['batch_id'] }}', '{{ $batch['skema_name'] }}', {{ $batch['total_asesi'] }}, {{ $batch['sudah_submit'] }}, {{ $batch['belum_submit'] }}, {{ $batch['semua_selesai'] ? 'true' : 'false' }})">
                                <i class="bi bi-eye me-1"></i>Detail & Export
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@if($jadwalMandiri->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-person-fill text-info"></i>
        Jadwal Mandiri
        <span class="badge bg-info ms-1">{{ count($jadwalMandiri) }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Skema</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Total Asesi</th>
                        <th class="text-center">Status Ujian</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jadwalMandiri as $j)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $j['skema_name'] }}</td>
                        <td class="text-center text-muted">
                            {{ $j['tanggal'] ? $j['tanggal']->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $j['total_asesi'] }} asesi</span>
                        </td>
                        <td class="text-center">
                            @if($j['semua_selesai'])
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Semua Selesai</span>
                            @elseif($j['sudah_submit'] > 0)
                            <span class="badge bg-warning text-dark">{{ $j['sudah_submit'] }}/{{ $j['total_asesi'] }} Submit</span>
                            @else
                            <span class="badge bg-secondary">Belum Ada Submit</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('manajer-sertifikasi.export-hasil-teori.jadwal', $j['schedule_id']) }}"
                               class="btn btn-sm {{ $j['semua_selesai'] ? 'btn-success' : 'btn-outline-secondary' }}">
                                <i class="bi bi-download me-1"></i>Export Excel
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endif {{-- end tab teori --}}


{{-- ================================================================
     TAB 2: HASIL OBSERVASI
================================================================ --}}
@if($activeTab === 'observasi')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <small class="text-muted">
            Rekap hasil observasi dari file Excel yang diupload asesor, digabung seluruh jadwal dalam satu batch menjadi satu file.
        </small>
    </div>
    @if($batchData->isEmpty())
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox" style="font-size:2rem;opacity:.3;"></i>
        <p class="mt-2 small">Belum ada batch kolektif dengan jadwal asesmen.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Batch ID</th>
                    <th>Skema</th>
                    <th>TUK</th>
                    <th class="text-center">Total Peserta</th>
                    <th class="text-center">Jadwal Upload</th>
                    <th class="text-center">Peserta Tercakup</th>
                    <th class="text-center pe-4">Download</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batchData as $batch)
                <tr>
                    <td class="ps-4"><code class="small text-primary">{{ $batch['batch_id'] }}</code></td>
                    <td class="small fw-semibold">{{ $batch['skema_name'] }}</td>
                    <td class="small text-muted">{{ $batch['tuk_name'] }}</td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $batch['jumlah_peserta'] }}</span>
                    </td>
                    <td class="text-center">
                        @if($batch['ada_observasi'])
                        <span class="badge {{ $batch['jadwal_obs'] === $batch['total_jadwal'] ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $batch['jadwal_obs'] }}/{{ $batch['total_jadwal'] }} jadwal
                        </span>
                        @else
                        <span class="badge bg-secondary">0/{{ $batch['total_jadwal'] }} jadwal</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($batch['ada_observasi'])
                        <span class="badge {{ $batch['peserta_obs'] === $batch['jumlah_peserta'] ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $batch['peserta_obs'] }}/{{ $batch['jumlah_peserta'] }} peserta
                        </span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center pe-4">
                        @if($batch['ada_observasi'])
                        <a href="{{ route('manajer-sertifikasi.export-hasil-teori.observasi', $batch['batch_id']) }}"
                        class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i>Download
                        </a>
                        @else
                        <span class="btn btn-sm btn-outline-secondary disabled">Belum ada file</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endif {{-- end tab observasi --}}


{{-- ================================================================
     TAB 3: BERITA ACARA
================================================================ --}}
@if($activeTab === 'berita-acara')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <small class="text-muted">
            Rekap rekomendasi K/BK dari berita acara seluruh jadwal dalam satu batch, digabung menjadi satu file Excel.
        </small>
    </div>
    @if($batchData->isEmpty())
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-inbox" style="font-size:2rem;opacity:.3;"></i>
        <p class="mt-2 small">Belum ada batch kolektif dengan jadwal asesmen.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Batch ID</th>
                    <th>Skema</th>
                    <th>TUK</th>
                    <th class="text-center">Total Peserta</th>
                    <th class="text-center">Jadwal BA</th>
                    <th class="text-center">Peserta Tercakup</th>
                    <th class="text-center pe-4">Download</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batchData as $batch)
                <tr>
                    <td class="ps-4"><code class="small text-primary">{{ $batch['batch_id'] }}</code></td>
                    <td class="small fw-semibold">{{ $batch['skema_name'] }}</td>
                    <td class="small text-muted">{{ $batch['tuk_name'] }}</td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $batch['jumlah_peserta'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $batch['jadwal_ba'] === $batch['total_jadwal'] ? 'bg-success' : ($batch['jadwal_ba'] > 0 ? 'bg-warning text-dark' : 'bg-secondary') }}">
                            {{ $batch['jadwal_ba'] }}/{{ $batch['total_jadwal'] }} jadwal
                        </span>
                    </td>
                    <td class="text-center">
                        @if($batch['peserta_ba'] > 0)
                        <span class="badge {{ $batch['peserta_ba'] === $batch['jumlah_peserta'] ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $batch['peserta_ba'] }}/{{ $batch['jumlah_peserta'] }} peserta
                        </span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center pe-4">
                        @if($batch['ada_ba'])
                        <a href="{{ route('manajer-sertifikasi.export-hasil-teori.berita-acara', $batch['batch_id']) }}"
                        class="btn btn-sm btn-success">
                            <i class="bi bi-file-earmark-excel me-1"></i>Download
                        </a>
                        @else
                        <span class="btn btn-sm btn-outline-secondary disabled">Belum ada BA</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endif {{-- end tab berita-acara --}}


{{-- Modal detail batch teori (existing) --}}
<div class="modal fade" id="modalBatchDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:#1e3a5f;">
                <h6 class="modal-title fw-bold text-white">
                    <i class="bi bi-people-fill me-2"></i>
                    Detail Batch — <span id="modalBatchId" class="font-monospace"></span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-3 rounded-3 bg-light">
                    <div class="fw-semibold mb-1" id="modalSkemaName"></div>
                    <div class="d-flex gap-3 text-muted" style="font-size:.82rem;">
                        <span><i class="bi bi-people me-1"></i><span id="modalTotalAsesi"></span> asesi</span>
                        <span><i class="bi bi-check-circle me-1 text-success"></i><span id="modalSudahSubmit"></span> sudah submit</span>
                        <span><i class="bi bi-clock me-1 text-warning"></i><span id="modalBelumSubmit"></span> belum submit</span>
                    </div>
                </div>
                <div id="modalStatusInfo" class="mb-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                <a href="#" id="btnExportBatch" class="btn btn-success btn-sm fw-semibold">
                    <i class="bi bi-download me-1"></i>Export Excel
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function bukaPreviousBatch(batchId, skemaName, totalAsesi, sudahSubmit, belumSubmit, semuaSelesai) {
    document.getElementById('modalBatchId').textContent     = batchId;
    document.getElementById('modalSkemaName').textContent   = skemaName;
    document.getElementById('modalTotalAsesi').textContent  = totalAsesi;
    document.getElementById('modalSudahSubmit').textContent = sudahSubmit;
    document.getElementById('modalBelumSubmit').textContent = belumSubmit;

    const statusEl = document.getElementById('modalStatusInfo');
    if (semuaSelesai) {
        statusEl.innerHTML = `<span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill me-1"></i>Semua asesi sudah submit ujian</span>`;
    } else if (sudahSubmit > 0) {
        statusEl.innerHTML = `<span class="badge bg-warning text-dark fs-6"><i class="bi bi-exclamation-circle me-1"></i>${belumSubmit} asesi belum submit — data sebagian</span>`;
    } else {
        statusEl.innerHTML = `<span class="badge bg-secondary fs-6"><i class="bi bi-clock me-1"></i>Belum ada yang submit ujian</span>`;
    }

    document.getElementById('btnExportBatch').href =
        `{{ url('manajer-sertifikasi/export-hasil-teori/batch') }}/${batchId}`;

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBatchDetail')).show();
}
</script>
@endpush

@endsection