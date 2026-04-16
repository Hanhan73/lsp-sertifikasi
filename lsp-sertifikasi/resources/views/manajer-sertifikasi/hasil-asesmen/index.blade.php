@extends('layouts.app')
@section('title', 'Hasil Asesmen')
@section('breadcrumb', 'Hasil Asesmen')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <h4 class="fw-bold mb-1">Hasil Asesmen</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
        Pantau hasil penilaian per jadwal maupun per batch kolektif
    </p>
</div>

{{-- Tab Navigation --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white pb-0">
        <ul class="nav nav-tabs" id="hasilTabs">
            <li class="nav-item">
                <button class="nav-link {{ $activeTab !== 'batch' ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#pane-jadwal">
                    <i class="bi bi-calendar3 me-1"></i>Per Jadwal
                    <span class="badge bg-secondary ms-1" style="font-size:.6rem">{{ $jadwalList->total() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ $activeTab === 'batch' ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#pane-batch">
                    <i class="bi bi-people me-1"></i>Per Batch Kolektif
                    <span class="badge bg-secondary ms-1" style="font-size:.6rem">{{ $batchData->count() }}</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">

        {{-- ============================================================
             TAB 1: PER JADWAL
        ============================================================ --}}
        <div class="tab-pane fade {{ $activeTab !== 'batch' ? 'show active' : '' }} p-4" id="pane-jadwal">

            {{-- Filter --}}
            <form method="GET" action="{{ route('manajer-sertifikasi.hasil-asesmen.index') }}"
                  class="d-flex flex-wrap gap-2 mb-4 align-items-center">
                <input type="hidden" name="tab" value="jadwal">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari skema / TUK..."
                       value="{{ request('search') }}" style="width:200px;">
                <div class="btn-group btn-group-sm">
                    <a href="{{ request()->fullUrlWithQuery(['status' => '', 'tab' => 'jadwal', 'jadwal_page' => 1]) }}"
                       class="btn {{ !$filterStatus ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'mendatang', 'tab' => 'jadwal', 'jadwal_page' => 1]) }}"
                       class="btn {{ $filterStatus === 'mendatang' ? 'btn-primary' : 'btn-outline-secondary' }}">Mendatang</a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai', 'tab' => 'jadwal', 'jadwal_page' => 1]) }}"
                       class="btn {{ $filterStatus === 'selesai' ? 'btn-primary' : 'btn-outline-secondary' }}">Selesai</a>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
                @if(request()->hasAny(['search','status']))
                <a href="{{ route('manajer-sertifikasi.hasil-asesmen.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                @endif
            </form>

            @if($jadwalList->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
                <p class="fw-semibold mb-0">Belum ada jadwal</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size:.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Skema & TUK</th>
                            <th>Batch</th>
                            <th> Asesor</th>
                            <th>Tanggal</th>
                            <th class="text-center">Asesi</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Berita Acara</th>
                            <th class="text-center">K / BK</th>
                            <th class="text-center pe-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jadwalList as $i => $s)
                        @php
                            $ba       = $s->beritaAcara;
                            $hadirCnt = $s->asesmens_count; // approximation; load jika perlu
                            $totalK   = $ba ? $ba->asesis->where('rekomendasi','K')->count() : 0;
                            $totalBK  = $ba ? $ba->asesis->where('rekomendasi','BK')->count() : 0;
                            $lewat    = $s->assessment_date < now()->startOfDay();
                        @endphp
                        <tr>
                            <td class="ps-3 text-muted">{{ $jadwalList->firstItem() + $i }}</td>
                            <td>
                                <div class="fw-semibold">{{ $s->skema->name }}</div>
                                <div class="text-muted small"><i class="bi bi-building me-1"></i>{{ $s->tuk->name ?? '-' }}</div>
                            </td>
                            <td>
                                @php
                                    $batchIds = $s->asesmens
                                        ->whereNotNull('collective_batch_id')
                                        ->pluck('collective_batch_id')
                                        ->unique()
                                        ->values();
                                @endphp
                                @if($batchIds->isEmpty())
                                    <span class="text-muted small">—</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($batchIds as $bid)
                                        <a href="{{ route('manajer-sertifikasi.hasil-asesmen.batch', $bid) }}"
                                        class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none"
                                        style="font-size:.68rem;font-family:monospace;"
                                        title="{{ $bid }}">
                                            {{ Str::limit($bid, 20) }}
                                        </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $s->asesor->nama ?? '-' }}</div>
                            </td>
                            <td>
                                {{ $s->assessment_date->translatedFormat('d M Y') }}
                                @if(!$lewat)
                                    <br><span class="badge bg-info text-dark" style="font-size:.65rem;">Mendatang</span>
                                @else
                                    <br><span class="badge bg-secondary" style="font-size:.65rem;">Selesai</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $s->asesmens_count }}</span>
                            </td>
                            <td class="text-center text-muted small">—</td>
                            <td class="text-center">
                                @if($ba)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-lg me-1"></i>Ada
                                </span>
                                @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    Belum
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($ba && ($totalK + $totalBK > 0))
                                <span class="text-success fw-bold">{{ $totalK }}K</span>
                                <span class="text-muted"> / </span>
                                <span class="text-danger fw-bold">{{ $totalBK }}BK</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('manajer-sertifikasi.hasil-asesmen.jadwal', $s) }}"
                                   class="btn btn-sm btn-primary px-3">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $jadwalList->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>

        {{-- ============================================================
             TAB 2: PER BATCH
        ============================================================ --}}
        <div class="tab-pane fade {{ $activeTab === 'batch' ? 'show active' : '' }} p-4" id="pane-batch">

            @if($batchData->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
                <p class="fw-semibold mb-0">Belum ada batch kolektif</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size:.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Batch ID</th>
                            <th>Skema</th>
                            <th>TUK</th>
                            <th class="text-center">Jadwal</th>
                            <th class="text-center">Asesi</th>
                            <th class="text-center">BA Tersedia</th>
                            <th class="text-center">K / BK</th>
                            <th class="text-center pe-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batchData as $i => $b)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold text-primary" style="font-size:.8rem;font-family:monospace;">
                                    {{ $b['batch_id'] }}
                                </div>
                            </td>
                            <td>{{ $b['skema']?->name ?? '-' }}</td>
                            <td class="text-muted">{{ $b['tuk']?->name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $b['total_jadwal'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $b['total_asesi'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($b['total_ba'] > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ $b['total_ba'] }}/{{ $b['total_jadwal'] }}
                                </span>
                                @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Belum</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($b['total_k'] + $b['total_bk'] > 0)
                                <span class="text-success fw-bold">{{ $b['total_k'] }}K</span>
                                <span class="text-muted"> / </span>
                                <span class="text-danger fw-bold">{{ $b['total_bk'] }}BK</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('manajer-sertifikasi.hasil-asesmen.batch', $b['batch_id']) }}"
                                   class="btn btn-sm btn-primary px-3">Detail</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>{{-- /tab-content --}}
</div>

@endsection

@push('scripts')
<script>
// Sync tab dari URL hash
const hash = window.location.hash;
if (hash) {
    const t = document.querySelector(`[data-bs-target="${hash}"]`);
    if (t) new bootstrap.Tab(t).show();
}
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(t => {
    t.addEventListener('shown.bs.tab', e => {
        history.replaceState(null, null, e.target.getAttribute('data-bs-target'));
    });
});
</script>
@endpush