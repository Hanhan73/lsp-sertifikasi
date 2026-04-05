@extends('layouts.app')
@section('title', 'Distribusi Soal ke Jadwal')
@section('breadcrumb', 'Distribusi Jadwal')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">Distribusi Soal</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">
            Kelola distribusi soal untuk setiap jadwal asesmen yang sudah disetujui
        </p>
    </div>
</div>

{{-- Filter & Sort Bar --}}
<form method="GET" action="{{ route('manajer-sertifikasi.distribusi') }}" class="d-flex flex-wrap gap-2 mb-4 align-items-center">
    <input type="text" name="search" id="searchJadwal" class="form-control form-control-sm"
           placeholder="&#xF52A; Cari skema / TUK..."
           value="{{ request('search') }}" style="width:200px;">

    {{-- Filter Status Jadwal --}}
    <div class="btn-group btn-group-sm" role="group">
        <a href="{{ request()->fullUrlWithQuery(['status' => '', 'page' => 1]) }}"
           class="btn {{ !$filterStatus ? 'btn-primary' : 'btn-outline-secondary' }}">
            Semua
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'mendatang', 'page' => 1]) }}"
           class="btn {{ $filterStatus === 'mendatang' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-calendar-check me-1"></i>Mendatang
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai', 'page' => 1]) }}"
           class="btn {{ $filterStatus === 'selesai' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-check2-circle me-1"></i>Selesai
        </a>
    </div>

    {{-- Sort --}}
    <select name="sort" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
        <option value="date_desc" {{ $sortBy === 'date_desc' ? 'selected' : '' }}>Tanggal (Terbaru)</option>
        <option value="date_asc"  {{ $sortBy === 'date_asc'  ? 'selected' : '' }}>Tanggal (Terlama)</option>
        <option value="skema_asc" {{ $sortBy === 'skema_asc' ? 'selected' : '' }}>Skema (A→Z)</option>
        <option value="skema_desc"{{ $sortBy === 'skema_desc'? 'selected' : '' }}>Skema (Z→A)</option>
    </select>

    <button type="submit" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-search"></i>
    </button>
    @if(request()->hasAny(['search','status','sort']))
    <a href="{{ route('manajer-sertifikasi.distribusi') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i>Reset
    </a>
    @endif
</form>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#eff6ff;">
                    <i class="bi bi-calendar-event text-primary" style="font-size:1.2rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Jadwal</div>
                    <div class="fw-bold fs-4 lh-1">{{ $schedules->total() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#f0fdf4;">
                    <i class="bi bi-check-circle text-success" style="font-size:1.2rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Distribusi Lengkap</div>
                    <div class="fw-bold fs-4 lh-1">{{ $jadwalLengkap }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#fffbeb;">
                    <i class="bi bi-exclamation-circle text-warning" style="font-size:1.2rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Perlu Soal Teori</div>
                    <div class="fw-bold fs-4 lh-1">{{ $jadwalBelumTeori }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($schedules->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
            <p class="fw-semibold mb-1">Belum ada jadwal asesmen yang disetujui</p>
            <small>Jadwal akan muncul setelah disetujui oleh Direktur</small>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelJadwal">
                <thead class="table-light">
                    <tr style="font-size:.8rem;">
                        <th class="ps-4" style="width:40px;">#</th>
                        <th>Skema & TUK</th>
                        <th>Tanggal</th>
                        <th class="text-center">Asesi</th>
                        <th class="text-center" colspan="3">Distribusi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4"></th>
                    </tr>
                    <tr class="table-light border-top-0" style="font-size:.72rem;color:#9ca3af;">
                        <th class="ps-4"></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="text-center fw-normal">Observasi</th>
                        <th class="text-center fw-normal">Teori</th>
                        <th class="text-center fw-normal">Portofolio</th>
                        <th></th>
                        <th class="pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $i => $s)
                    @php
                        $hasObservasi = $s->distribusiSoalObservasi->isNotEmpty();
                        $hasTeori     = $s->distribusiSoalTeori !== null;
                        $hasPorto     = $s->distribusiPortofolio->isNotEmpty();
                        $lengkap      = $hasObservasi && $hasTeori;
                        $daysLeft     = now()->startOfDay()->diffInDays($s->assessment_date->startOfDay(), false);
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted small">{{ $schedules->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.875rem;">{{ $s->skema->name }}</div>
                            <div class="text-muted small d-flex align-items-center gap-1 mt-1">
                                <i class="bi bi-building" style="font-size:.7rem;"></i>
                                {{ $s->tuk->name ?? '-' }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size:.875rem;">
                                {{ $s->assessment_date->translatedFormat('d M Y') }}
                            </div>
                            <div class="small mt-1">
                                @if($daysLeft === 0)
                                <span class="badge bg-warning text-dark" style="font-size:.65rem;">Hari ini</span>
                                @elseif($daysLeft > 0 && $daysLeft <= 7)
                                <span class="badge bg-danger" style="font-size:.65rem;">{{ $daysLeft }}h lagi</span>
                                @elseif($daysLeft > 0)
                                <span class="text-muted" style="font-size:.75rem;">{{ $daysLeft }} hari lagi</span>
                                @else
                                <span class="text-muted" style="font-size:.75rem;">Selesai</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $s->asesmens_count }}</span>
                        </td>

                        {{-- Observasi --}}
                        <td class="text-center">
                            @if($hasObservasi)
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiSoalObservasi->count() }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size:1rem;">—</span>
                            @endif
                        </td>

                        {{-- Teori --}}
                        <td class="text-center">
                            @if($hasTeori)
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiSoalTeori->jumlah_soal }}
                            </span>
                            @else
                            <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-2">
                                <i class="bi bi-exclamation me-1"></i>Belum
                            </span>
                            @endif
                        </td>

                        {{-- Portofolio --}}
                        <td class="text-center">
                            @if($hasPorto)
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiPortofolio->count() }}
                            </span>
                            @else
                            <span class="text-muted" style="font-size:1rem;">—</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="text-center">
                            @if($lengkap)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size:.7rem;">
                                <i class="bi bi-check-circle me-1"></i>Lengkap
                            </span>
                            @elseif($hasObservasi || $hasTeori)
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2" style="font-size:.7rem;">
                                Sebagian
                            </span>
                            @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2" style="font-size:.7rem;">
                                Kosong
                            </span>
                            @endif
                        </td>

                        <td class="text-center pe-4">
                            <a href="{{ route('manajer-sertifikasi.jadwal.show', $s) }}"
                               class="btn btn-sm btn-primary px-3">
                                Kelola
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-top">
            {{ $schedules->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
// Submit form saat user tekan Enter di kolom search
document.getElementById('searchJadwal')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.closest('form').submit();
    }
});
</script>
@endpush