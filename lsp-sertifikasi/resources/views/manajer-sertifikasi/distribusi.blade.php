@extends('layouts.app')
@section('title', 'Distribusi Soal ke Jadwal')
@section('breadcrumb', 'Distribusi Jadwal')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h4 class="fw-bold mb-1">Distribusi Soal ke Jadwal</h4>
        <p class="text-muted mb-0" style="font-size:.875rem;">
            Kelola distribusi soal untuk setiap jadwal asesmen yang sudah disetujui
        </p>
    </div>
    <div class="d-flex gap-2">
        <input type="text" id="searchJadwal" class="form-control form-control-sm"
               placeholder="Cari skema / TUK..." style="width:220px;">
    </div>
</div>

{{-- Mini stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="border rounded-3 p-3 d-flex align-items-center gap-3"
             style="background:#f0fdf4;border-color:#bbf7d0!important;">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
            <div>
                <div class="fw-bold fs-5 text-success">{{ $jadwalLengkap }}</div>
                <div class="text-muted small">Distribusi Lengkap</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded-3 p-3 d-flex align-items-center gap-3"
             style="background:#fffbeb;border-color:#fde68a!important;">
            <i class="bi bi-exclamation-circle-fill text-warning fs-4"></i>
            <div>
                <div class="fw-bold fs-5 text-warning">{{ $jadwalBelumTeori }}</div>
                <div class="text-muted small">Belum Ada Soal Teori</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded-3 p-3 d-flex align-items-center gap-3"
             style="background:#eff6ff;border-color:#bfdbfe!important;">
            <i class="bi bi-calendar-event text-primary fs-4"></i>
            <div>
                <div class="fw-bold fs-5 text-primary">{{ $schedules->total() }}</div>
                <div class="text-muted small">Total Jadwal Aktif</div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel jadwal --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($schedules->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x" style="font-size:2.5rem;opacity:.3;"></i>
            <p class="mt-2 fw-semibold">Belum ada jadwal asesmen yang disetujui</p>
            <small>Jadwal akan muncul di sini setelah disetujui oleh Direktur</small>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelJadwal">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:44px;">#</th>
                        <th>Skema</th>
                        <th>TUK</th>
                        <th>Tanggal</th>
                        <th class="text-center">Asesi</th>
                        <th class="text-center">Observasi</th>
                        <th class="text-center">Teori</th>
                        <th class="text-center">Portofolio</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $i => $s)
                    @php
                        $hasObservasi = $s->distribusiSoalObservasi->isNotEmpty();
                        $hasTeori     = $s->distribusiSoalTeori !== null;
                        $hasPorto     = $s->distribusiPortofolio->isNotEmpty();
                        $lengkap      = $hasObservasi && $hasTeori;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted">{{ $schedules->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.875rem;">{{ $s->skema->name }}</div>
                            <small class="text-muted font-monospace">{{ $s->skema->code }}</small>
                        </td>
                        <td style="font-size:.875rem;">{{ $s->tuk->name ?? '-' }}</td>
                        <td>
                            <div style="font-size:.875rem;">{{ $s->assessment_date->translatedFormat('d M Y') }}</div>
                            <small class="text-muted">{{ $s->start_time }} – {{ $s->end_time }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $s->asesmens_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($hasObservasi)
                            <span class="badge bg-success-subtle text-success rounded-pill" style="font-size:.72rem;">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiSoalObservasi->count() }}
                            </span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($hasTeori)
                            <span class="badge bg-success-subtle text-success rounded-pill" style="font-size:.72rem;">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiSoalTeori->jumlah_soal }} soal
                            </span>
                            @else
                            <span class="badge bg-warning-subtle text-warning rounded-pill" style="font-size:.72rem;">
                                <i class="bi bi-exclamation me-1"></i>Belum
                            </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($hasPorto)
                            <span class="badge bg-success-subtle text-success rounded-pill" style="font-size:.72rem;">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiPortofolio->count() }}
                            </span>
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($lengkap)
                            <span class="badge bg-success rounded-pill px-2" style="font-size:.7rem;">Lengkap</span>
                            @elseif($hasObservasi || $hasTeori)
                            <span class="badge bg-warning rounded-pill px-2" style="font-size:.7rem;">Sebagian</span>
                            @else
                            <span class="badge bg-danger rounded-pill px-2" style="font-size:.7rem;">Kosong</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('manajer-sertifikasi.jadwal.show', $s) }}"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-square me-1"></i>Kelola
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $schedules->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
document.getElementById('searchJadwal')?.addEventListener('keyup', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tabelJadwal tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush