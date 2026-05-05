@extends('layouts.app')

@section('title', 'Honor Asesor')
@section('page-title', 'Honor Asesor')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

{{-- ── STAT CARDS ─────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#eff6ff;">
                    <i class="bi bi-receipt text-primary fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Kwitansi</div>
                    <div class="fw-bold fs-4">{{ $rekapStats['total_honor'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#fef2f2;">
                    <i class="bi bi-hourglass-split text-danger fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Belum Dibayar</div>
                    <div class="fw-bold fs-4 text-danger">{{ $rekapStats['belum_dibayar_count'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        Rp {{ number_format($rekapStats['belum_dibayar_nominal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#fef9c3;">
                    <i class="bi bi-clock-history text-warning fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Sudah Dibayar</div>
                    <div class="fw-bold fs-4 text-warning">{{ $rekapStats['sudah_dibayar_count'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        Rp {{ number_format($rekapStats['sudah_dibayar_nominal'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:#f0fdf4;">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Dikonfirmasi</div>
                    <div class="fw-bold fs-4 text-success">{{ $rekapStats['dikonfirmasi_count'] }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        Rp {{ number_format($rekapStats['dikonfirmasi_nominal'], 0, ',', '.') }}
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
            <i class="bi bi-bar-chart-line me-1"></i>Rekap Honor
            @if($rekapStats['belum_dibayar_count'] > 0)
            <span class="badge bg-danger ms-1">{{ $rekapStats['belum_dibayar_count'] }}</span>
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
                        $honorAsesor = $rekapStats['per_asesor']->firstWhere('asesor_id', $asesor->id);
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
                            @if($honorAsesor)
                                @if($honorAsesor['menunggu'] > 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    {{ $honorAsesor['menunggu'] }} Belum Dibayar
                                </span>
                                @endif
                                @if($honorAsesor['dikonfirmasi'] > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ $honorAsesor['dikonfirmasi'] }} Selesai
                                </span>
                                @endif
                                @if($honorAsesor['menunggu'] === 0 && $honorAsesor['dikonfirmasi'] === 0)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    {{ $honorAsesor['sudah_dibayar'] }} Menunggu Konfirmasi
                                </span>
                                @endif
                            @else
                            <span class="badge bg-secondary-subtle text-secondary">Belum Ada Kwitansi</span>
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

    {{-- ── TAB 2: REKAP HONOR ────────────────────────────────────────────── --}}
    <div class="tab-pane fade" id="rekap" role="tabpanel">
        <div class="p-3 border-bottom bg-light d-flex align-items-center gap-2">
            <i class="bi bi-info-circle text-primary"></i>
            <span class="small text-muted">
                Total nominal seluruh honor asesor:
                <strong class="text-dark">Rp {{ number_format($rekapStats['total_nominal'], 0, ',', '.') }}</strong>
                dari <strong>{{ $rekapStats['total_asesor_honor'] }} asesor</strong>
            </span>
        </div>

        @if($rekapStats['per_asesor']->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
            Belum ada data honor.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabel-rekap">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Asesor</th>
                        <th class="text-center">Total Kwitansi</th>
                        <th class="text-center">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Belum Dibayar</span>
                        </th>
                        <th class="text-center">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Sudah Dibayar</span>
                        </th>
                        <th class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Dikonfirmasi</span>
                        </th>
                        <th class="text-end">Nominal Dibayar</th>
                        <th class="text-end">Belum Dibayar</th>
                        <th class="text-end">Total Honor</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekapStats['per_asesor'] as $i => $row)
                    <tr class="{{ $row['menunggu'] > 0 ? 'table-danger-subtle' : '' }}">
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold small">{{ $row['nama'] }}</div>
                            <div class="text-muted" style="font-size:.72rem;">{{ $row['no_reg_met'] }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $row['total_kwitansi'] }}</span>
                        </td>
                        <td class="text-center">
                            @if($row['menunggu'] > 0)
                            <span class="badge bg-danger">{{ $row['menunggu'] }}</span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['sudah_dibayar'] > $row['dikonfirmasi'])
                            <span class="badge bg-warning text-dark">{{ $row['sudah_dibayar'] - $row['dikonfirmasi'] }}</span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['dikonfirmasi'] > 0)
                            <span class="badge bg-success">{{ $row['dikonfirmasi'] }}</span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end small fw-semibold text-success">
                            Rp {{ number_format($row['dibayar_nominal'], 0, ',', '.') }}
                        </td>
                        <td class="text-end small fw-semibold {{ $row['menunggu_nominal'] > 0 ? 'text-danger' : 'text-muted' }}">
                            Rp {{ number_format($row['menunggu_nominal'], 0, ',', '.') }}
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
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="6" class="text-end">Total Keseluruhan</td>
                        <td class="text-end text-success">
                            Rp {{ number_format($rekapStats['sudah_dibayar_nominal'], 0, ',', '.') }}
                        </td>
                        <td class="text-end text-danger">
                            Rp {{ number_format($rekapStats['belum_dibayar_nominal'], 0, ',', '.') }}
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
// Aktifkan tab dari URL hash jika ada
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;
    if (hash === '#rekap') {
        const tab = document.getElementById('rekap-tab');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
});
</script>
@endpush