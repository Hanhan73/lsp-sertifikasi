@extends('layouts.app')

@section('title', 'Honor Asesor')
@section('page-title', 'Honor Asesor')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

{{-- ── STAT CARDS ─────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Belum Dibuat Kwitansi --}}
    <div class="col-6 col-lg">
        <div class="card border-0 shadow-sm h-100 {{ $rekapStats['belum_dibuat_count'] > 0 ? 'border-start border-4 border-secondary' : '' }}">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#f1f5f9;">
                    <i class="bi bi-file-earmark-plus text-secondary fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Belum Dibuat</div>
                    <div class="fw-bold fs-4 lh-1 {{ $rekapStats['belum_dibuat_count'] > 0 ? 'text-secondary' : 'text-muted' }}">
                        {{ $rekapStats['belum_dibuat_count'] }}
                    </div>
                    <div class="text-muted" style="font-size:.72rem;">Jadwal</div>

                </div>
            </div>
        </div>
    </div>

    {{-- Belum Dibayar --}}
    <div class="col-6 col-lg">
        <div class="card border-0 shadow-sm h-100 {{ $rekapStats['belum_dibayar_count'] > 0 ? 'border-start border-4 border-danger' : '' }}">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#fef2f2;">
                    <i class="bi bi-hourglass-split text-danger fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Belum Dibayar</div>
                    <div class="fw-bold fs-4 lh-1 text-danger">{{ $rekapStats['belum_dibayar_count'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        Rp {{ number_format($rekapStats['belum_dibayar_nominal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sudah Dibayar (menunggu konfirmasi asesor) --}}
    <div class="col-6 col-lg">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#fef9c3;">
                    <i class="bi bi-clock-history text-warning fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Sudah Dibayar</div>
                    <div class="fw-bold fs-4 lh-1 text-warning">{{ $rekapStats['sudah_dibayar_count'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        Rp {{ number_format($rekapStats['sudah_dibayar_nominal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dikonfirmasi --}}
    <div class="col-6 col-lg">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#f0fdf4;">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Dikonfirmasi</div>
                    <div class="fw-bold fs-4 lh-1 text-success">{{ $rekapStats['dikonfirmasi_count'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        Rp {{ number_format($rekapStats['dikonfirmasi_nominal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Total Kwitansi --}}
    <div class="col-6 col-lg">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#eff6ff;">
                    <i class="bi bi-receipt text-primary fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Kwitansi</div>
                    <div class="fw-bold fs-4 lh-1">{{ $rekapStats['total_honor'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        Rp {{ number_format($rekapStats['total_nominal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── TABS ─────────────────────────────────────────────────────────────── --}}
<ul class="nav nav-tabs mb-0" id="honorTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="daftar-tab" data-bs-toggle="tab" data-bs-target="#daftar"
                type="button" role="tab">
            <i class="bi bi-person-badge me-1"></i>Daftar Asesor
            <span class="badge bg-primary ms-1">{{ $asesors->count() }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="rekap-tab" data-bs-toggle="tab" data-bs-target="#rekap"
                type="button" role="tab">
            <i class="bi bi-bar-chart-line me-1"></i>Rekap per Asesor
            @if($rekapStats['belum_dibayar_count'] > 0 || $rekapStats['belum_dibuat_count'] > 0)
            <span class="badge bg-danger ms-1">
                {{ $rekapStats['belum_dibayar_count'] + $rekapStats['belum_dibuat_count'] }}
            </span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom shadow-sm bg-white">

    {{-- ── TAB 1: DAFTAR ASESOR ──────────────────────────────────────────── --}}
    <div class="tab-pane fade show active p-0" id="daftar" role="tabpanel">
        @if($asesors->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada asesor dengan berita acara yang diupload.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Asesor</th>
                        <th>No. Reg MET</th>
                        <th class="text-center">Jadwal Selesai</th>
                        <th class="text-center">Status Honor</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                   @foreach($asesors as $i => $asesor)
@php
    $rekapAsesor = $rekapStats['per_asesor']->firstWhere('asesor_id', $asesor->id);
@endphp
<tr>
    <td class="text-muted small">{{ $i + 1 }}</td>
    <td>
        <div class="fw-semibold">{{ $asesor->nama }}</div>
        <div class="text-muted small">{{ $asesor->email }}</div>
    </td>
    <td class="small">{{ $asesor->no_reg_met ?? '-' }}</td>
    <td class="text-center">
        <span class="badge bg-success">{{ $asesor->schedules->count() }} Jadwal</span>
    </td>
    <td class="text-center">
    @if($rekapAsesor)
        @if($rekapAsesor['menunggu'] > 0)
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1">
            {{ $rekapAsesor['menunggu'] }} Belum Bayar
        </span>
        @endif
        @if(($rekapAsesor['sudah_dibayar'] - $rekapAsesor['dikonfirmasi']) > 0)
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle me-1">
            {{ $rekapAsesor['sudah_dibayar'] - $rekapAsesor['dikonfirmasi'] }} Menunggu Konfirmasi
        </span>
        @endif
        @if($rekapAsesor['dikonfirmasi'] > 0)
        <span class="badge bg-success-subtle text-success border border-success-subtle me-1">
            {{ $rekapAsesor['dikonfirmasi'] }} Selesai
        </span>
        @endif
    @endif

    @if($asesor->jadwal_belum_dibuat > 0)
    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
        <i class="bi bi-file-earmark-plus me-1"></i>{{ $asesor->jadwal_belum_dibuat }} Jadwal Belum Dibuat
    </span>
    @else
    <span class="badge bg-success-subtle text-success border border-success-subtle">
        <i class="bi bi-check-circle me-1"></i>Semua Jadwal Sudah Dibuat Kwitansi
    </span>
    @endif
</td>
    <td class="text-center">
        <a href="{{ route('bendahara.honor.show', $asesor) }}"
            class="btn btn-sm btn-outline-primary">
            <i class="bi bi-eye me-1"></i>Detail
        </a>
    </td>
</tr>
@endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── TAB 2: REKAP PER ASESOR ────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="rekap" role="tabpanel">
        <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center gap-2">
            <i class="bi bi-info-circle text-primary"></i>
            <span class="small text-muted">
                Total nominal seluruh honor:
                <strong class="text-dark">Rp {{ number_format($rekapStats['total_nominal'], 0, ',', '.') }}</strong>
                dari <strong>{{ $rekapStats['total_asesor_honor'] }} asesor</strong>
            </span>
            @if($rekapStats['belum_dibuat_count'] > 0)
            <span class="badge bg-secondary ms-auto">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $rekapStats['belum_dibuat_count'] }} asesor belum dibuatkan kwitansi
            </span>
            @endif
        </div>

        @if($rekapStats['per_asesor']->isEmpty() && $rekapStats['belum_dibuat_count'] === 0)
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
            Belum ada data honor.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Asesor</th>
                        <th class="text-center">Kwitansi</th>
                        <th class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.7rem;">Belum Dibuat</span>
                        </th>
                        <th class="text-center">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.7rem;">Belum Dibayar</span>
                        </th>
                        <th class="text-center">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.7rem;">Sudah Dibayar</span>
                        </th>
                        <th class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem;">Dikonfirmasi</span>
                        </th>
                        <th class="text-end">Nominal Belum Bayar</th>
                        <th class="text-end">Nominal Sudah Bayar</th>
                        <th class="text-end">Total Honor</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Asesor yang sudah ada kwitansi --}}
                    @foreach($rekapStats['per_asesor'] as $i => $row)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold small">{{ $row['nama'] }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $row['no_reg_met'] }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $row['total_kwitansi'] }}</span>
                        </td>
                        <td class="text-center">
                            @if($row['jadwal_belum_dibuat'] > 0)
                            <span class="badge bg-secondary">{{ $row['jadwal_belum_dibuat'] }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['menunggu'] > 0)
                            <span class="badge bg-danger">{{ $row['menunggu'] }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $menungguKonfirmasi = $row['sudah_dibayar'] - $row['dikonfirmasi']; @endphp
                            @if($menungguKonfirmasi > 0)
                            <span class="badge bg-warning text-dark">{{ $menungguKonfirmasi }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['dikonfirmasi'] > 0)
                            <span class="badge bg-success">{{ $row['dikonfirmasi'] }}</span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end small {{ $row['menunggu_nominal'] > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                            Rp {{ number_format($row['menunggu_nominal'], 0, ',', '.') }}
                        </td>
                        <td class="text-end small text-success fw-semibold">
                            Rp {{ number_format($row['dibayar_nominal'], 0, ',', '.') }}
                        </td>
                        <td class="text-end small fw-bold">
                            Rp {{ number_format($row['total_nominal'], 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($row['asesor_id'])
                            <a href="{{ route('bendahara.honor.show', $row['asesor_id']) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach

                    {{-- Asesor yang BELUM punya kwitansi sama sekali --}}
                    @foreach($asesors as $asesor)
                    @if(!$rekapStats['per_asesor']->firstWhere('asesor_id', $asesor->id))
                    <tr class="table-light">
                        <td class="text-muted small">—</td>
                        <td>
                            <div class="fw-semibold small">{{ $asesor->nama }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $asesor->no_reg_met ?? '-' }}</div>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary">0</span></td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                {{ $asesor->jadwal_belum_dibuat }} Belum Dibuat
                            </span>
                        </td>
                        <td class="text-center"><span class="text-muted">—</span></td>
                        <td class="text-center"><span class="text-muted">—</span></td>
                        <td class="text-center"><span class="text-muted">—</span></td>
                        <td class="text-end text-muted small">—</td>
                        <td class="text-end text-muted small">—</td>
                        <td class="text-end text-muted small">—</td>
                        <td class="text-center">
                            <a href="{{ route('bendahara.honor.show', $asesor) }}"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-plus-circle me-1"></i>Buat
                            </a>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="7" class="text-end pe-3">Total Keseluruhan</td>
                        <td class="text-end text-danger">
                            Rp {{ number_format($rekapStats['belum_dibayar_nominal'], 0, ',', '.') }}
                        </td>
                        <td class="text-end text-success">
                            Rp {{ number_format($rekapStats['sudah_dibayar_nominal'], 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                            Rp {{ number_format($rekapStats['total_nominal'], 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.location.hash === '#rekap') {
        const tab = document.getElementById('rekap-tab');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
});
</script>
@endpush