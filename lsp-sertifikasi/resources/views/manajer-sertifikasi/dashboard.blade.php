@extends('layouts.app')

@section('title', 'Dashboard Manajer Sertifikasi')
@section('breadcrumb', 'Dashboard')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dashboard Manajer Sertifikasi</h4>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Selamat datang, <strong>{{ auth()->user()->name }}</strong> —
            kelola dan distribusikan soal ke jadwal asesmen.
        </p>
    </div>
    <a href="{{ route('manajer-sertifikasi.jadwal.show', ['schedule' => '__first__']) }}"
        class="btn btn-primary btn-sm d-none">
    </a>
</div>

{{-- ===== STAT CARDS ===== --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#2563eb,#1e40af)">
            <p>Total Jadwal Aktif</p>
            <h3>{{ $totalJadwal }}</h3>
            <i class="bi bi-calendar-event stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#059669,#065f46)">
            <p>Jadwal Sudah Lengkap</p>
            <h3>{{ $jadwalLengkap }}</h3>
            <i class="bi bi-check-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#d97706,#92400e)">
            <p>Belum Ada Soal Teori</p>
            <h3>{{ $jadwalBelumTeori }}</h3>
            <i class="bi bi-exclamation-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg,#7c3aed,#4c1d95)">
            <p>Total Bank Soal PG</p>
            <h3>{{ $totalBankSoal }}</h3>
            <i class="bi bi-journal-text stat-icon"></i>
        </div>
    </div>
</div>

{{-- ===== DISTRIBUSI PER SKEMA ===== --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h6 class="fw-bold mb-0"><i class="bi bi-send-check text-primary me-2"></i>Distribusi Soal per Jadwal</h6>
            <small class="text-muted">Pilih jadwal untuk mengelola distribusi soal</small>
        </div>
        <div class="d-flex gap-2">
            <input type="text" id="searchJadwal" class="form-control form-control-sm" placeholder="Cari skema / TUK..."
                style="width:220px">
        </div>
    </div>
    <div class="card-body p-0">
        @if($schedules->isEmpty())
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <p class="fw-semibold">Belum ada jadwal asesmen yang disetujui</p>
            <small>Jadwal akan muncul di sini setelah disetujui oleh admin</small>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelJadwal">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Skema</th>
                        <th>TUK</th>
                        <th>Tanggal Asesmen</th>
                        <th>Asesi</th>
                        <th>Observasi</th>
                        <th>Paket Soal</th>
                        <th>Soal Teori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $i => $s)
                    @php
                    $hasObservasi = $s->distribusiSoalObservasi->isNotEmpty();
                    $hasPaket = $s->distribusiPaketSoal->isNotEmpty();
                    $hasTeori = $s->distribusiSoalTeori !== null;
                    $lengkap = $hasObservasi && $hasPaket && $hasTeori;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted">{{ $schedules->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.875rem">{{ $s->skema->name }}</div>
                            <small class="text-muted">{{ $s->skema->code }}</small>
                        </td>
                        <td style="font-size:.875rem">{{ $s->tuk->name ?? '-' }}</td>
                        <td>
                            <span style="font-size:.875rem">{{ $s->assessment_date->format('d M Y') }}</span><br>
                            <small class="text-muted">{{ $s->start_time }} – {{ $s->end_time }}</small>
                        </td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">{{ $s->asesmens_count }} orang</span>
                        </td>
                        <td class="text-center">
                            @if($hasObservasi)
                            <span class="badge bg-success-subtle text-success rounded-pill">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiSoalObservasi->count() }} file
                            </span>
                            @else
                            <span class="badge bg-light text-muted rounded-pill">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($hasPaket)
                            <span class="badge bg-success-subtle text-success rounded-pill">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiPaketSoal->count() }} file
                            </span>
                            @else
                            <span class="badge bg-light text-muted rounded-pill">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($hasTeori)
                            <span class="badge bg-success-subtle text-success rounded-pill">
                                <i class="bi bi-check-lg me-1"></i>{{ $s->distribusiSoalTeori->jumlah_soal }} soal
                            </span>
                            @else
                            <span class="badge bg-warning-subtle text-warning rounded-pill">
                                <i class="bi bi-exclamation me-1"></i>Belum
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($lengkap)
                            <span class="badge bg-success rounded-pill px-3">Lengkap</span>
                            @elseif($hasObservasi || $hasPaket || $hasTeori)
                            <span class="badge bg-warning rounded-pill px-3">Sebagian</span>
                            @else
                            <span class="badge bg-danger rounded-pill px-3">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('manajer-sertifikasi.jadwal.show', $s) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-arrow-right-circle me-1"></i> Kelola
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $schedules->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ===== SKEMA SUMMARY ===== --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart text-primary me-2"></i>Bank Soal per Skema</h6>
            </div>
            <div class="card-body p-0">
                @if($bankSoalPerSkema->isEmpty())
                <div class="empty-state py-4">
                    <i class="bi bi-journal-x" style="font-size:1.8rem"></i>
                    <p class="mb-0">Belum ada soal teori di bank soal</p>
                </div>
                @else
                <ul class="list-group list-group-flush">
                    @foreach($bankSoalPerSkema as $item)
                    <li class="list-group-item d-flex align-items-center justify-content-between px-4 py-3">
                        <div>
                            <div class="fw-semibold" style="font-size:.875rem">{{ $item->skema_name }}</div>
                            <small class="text-muted">{{ $item->skema_code }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary rounded-pill px-3">{{ $item->total }} soal PG</span>
                            @if($item->total < 30) <br><small class="text-danger"><i
                                        class="bi bi-exclamation-triangle me-1"></i>Kurang dari 30</small>
                                @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history text-warning me-2"></i>Jadwal Mendatang</h6>
            </div>
            <div class="card-body p-0">
                @if($jadwalMendatang->isEmpty())
                <div class="empty-state py-4">
                    <i class="bi bi-calendar-check" style="font-size:1.8rem"></i>
                    <p class="mb-0">Tidak ada jadwal mendatang</p>
                </div>
                @else
                <ul class="list-group list-group-flush">
                    @foreach($jadwalMendatang as $s)
                    <li class="list-group-item px-4 py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-semibold" style="font-size:.875rem">{{ $s->skema->name }}</div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $s->assessment_date->format('d M Y') }}
                                    &nbsp;·&nbsp;
                                    <i class="bi bi-building me-1"></i>{{ $s->tuk->name ?? '-' }}
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if(!$s->distribusiSoalTeori)
                                <span class="badge bg-warning-subtle text-warning" style="font-size:.7rem">
                                    <i class="bi bi-exclamation"></i> Belum ada soal
                                </span>
                                @endif
                                <a href="{{ route('manajer-sertifikasi.jadwal.show', $s) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Live search tabel jadwal
document.getElementById('searchJadwal').addEventListener('keyup', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tabelJadwal tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush