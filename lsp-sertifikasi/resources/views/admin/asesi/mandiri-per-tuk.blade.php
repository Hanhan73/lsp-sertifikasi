@extends('layouts.app')
@section('title', 'Asesi Mandiri — ' . $tuk->name)
@section('page-title', 'Asesi Mandiri per TUK')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.asesi') }}">Semua Asesi</a></li>
        <li class="breadcrumb-item active">Mandiri — {{ $tuk->name }}</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Asesi Mandiri</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">
            <i class="bi bi-building me-1"></i>{{ $tuk->name }}
            <span class="text-muted">·</span>
            <code>{{ $tuk->code }}</code>
        </p>
    </div>
    <span class="badge bg-success px-3 py-2">{{ $asesmens->count() }} asesi mandiri</span>
</div>

{{-- ══ FORM EXPORT BLANKO — bungkus tabel supaya checkbox ikut ke-submit ══ --}}
<form id="form-export-blanko" method="GET"
      action="{{ route('admin.asesi.mandiri-per-tuk.export-blanko', $tuk->id) }}">

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="small text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Centang asesi yang ingin di-export, atau kosongkan semua untuk export seluruh asesi mandiri di TUK ini.
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-primary align-self-center" id="selected-count">0 dipilih</span>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                    <span id="btn-export-label">Download Blanko Pengajuan (Semua)</span>
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" width="36">
                                <input type="checkbox" class="form-check-input" id="check-all">
                            </th>
                            <th width="40">#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Skema</th>
                            <th class="text-center">APL-01</th>
                            <th class="text-center">APL-02</th>
                            <th class="text-center">FR.AK.01</th>
                            <th class="text-center">FR.AK.04</th>
                            <th>Jadwal</th>
                            <th>Asesor</th>
                            <th class="text-center">BA</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asesmens as $i => $asesmen)
                        @php
                            $ba  = $asesmen->schedule?->beritaAcara;
                            $rek = $ba?->asesis->where('asesmen_id', $asesmen->id)->first()?->rekomendasi;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" class="form-check-input row-check"
                                       name="ids[]" value="{{ $asesmen->id }}">
                            </td>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td>
                                <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                                <div class="text-muted small">{{ $asesmen->nik ?? 'NIK belum diisi' }}</div>
                            </td>
                            <td class="small text-muted">{{ $asesmen->user->email }}</td>
                            <td class="small">{{ $asesmen->skema?->name ?? '-' }}</td>

                            <td class="text-center">
                                @if($asesmen->aplsatu)
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($asesmen->apldua)
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($asesmen->frak01)
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($asesmen->frak04)
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted"></i>
                                @endif
                            </td>

                            <td class="small">
                                @if($asesmen->schedule)
                                {{ $asesmen->schedule->assessment_date->translatedFormat('d M Y') }}
                                @else
                                <span class="text-muted">Belum dijadwalkan</span>
                                @endif
                            </td>

                            <td class="small">
                                {{ $asesmen->schedule?->asesor?->nama ?? '-' }}
                            </td>

                            <td class="text-center">
                                @if($rek === 'K')
                                <span class="badge bg-success">K</span>
                                @elseif($rek === 'BK')
                                <span class="badge bg-danger">BK</span>
                                @elseif($ba)
                                <span class="badge bg-secondary" style="font-size:.7rem;">Ada</span>
                                @else
                                <span class="badge bg-light text-muted border" style="font-size:.7rem;">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge bg-{{ $asesmen->status_badge }}" style="font-size:.72rem;">
                                    {{ $asesmen->status_label }}
                                </span>
                            </td>

                            <td class="text-center pe-3">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('admin.asesi.show', $asesmen) }}"
                                       class="btn btn-outline-primary btn-sm py-0 px-2"
                                       title="Detail Asesi">
                                        <i class="bi bi-eye" style="font-size:.8rem;"></i>
                                    </a>
                                    @if($ba)
                                    <a href="{{ route('admin.berita-acara.mandiri.download', $asesmen) }}?preview=1"
                                       target="_blank"
                                       class="btn btn-outline-warning btn-sm py-0 px-2"
                                       title="Preview BA">
                                        <i class="bi bi-file-text" style="font-size:.8rem;"></i>
                                    </a>
                                    <a href="{{ route('admin.berita-acara.mandiri.download', $asesmen) }}"
                                       class="btn btn-warning btn-sm py-0 px-2"
                                       title="Download BA">
                                        <i class="bi bi-download" style="font-size:.8rem;"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<div class="mt-3">
    <a href="{{ route('admin.asesi') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Semua Asesi
    </a>
</div>

@endsection

@push('scripts')
<script>
    const checkAll   = document.getElementById('check-all');
    const rowChecks  = document.querySelectorAll('.row-check');
    const countBadge = document.getElementById('selected-count');
    const btnLabel   = document.getElementById('btn-export-label');

    function updateCount() {
        const checked = document.querySelectorAll('.row-check:checked').length;
        countBadge.textContent = checked + ' dipilih';
        btnLabel.textContent = checked > 0
            ? `Download Blanko Pengajuan (${checked} Terpilih)`
            : 'Download Blanko Pengajuan (Semua)';
        checkAll.checked = checked > 0 && checked === rowChecks.length;
    }

    checkAll.addEventListener('change', () => {
        rowChecks.forEach(cb => cb.checked = checkAll.checked);
        updateCount();
    });

    rowChecks.forEach(cb => cb.addEventListener('change', updateCount));
</script>
@endpush