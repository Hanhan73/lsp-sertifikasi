@extends('layouts.app')

@section('title', 'Hasil Asesmen Batch — ' . $first->skema->name)
@section('breadcrumb', 'Hasil Asesmen › Batch')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

{{-- ===== HEADER ===== --}}
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <a href="{{ route('manajer-sertifikasi.hasil-asesmen.index', ['tab' => 'batch']) }}"
            class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Hasil Asesmen
        </a>
    </div>

    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Hasil Asesmen — Batch Kolektif</h4>
            <div class="d-flex flex-wrap gap-3 text-muted small">
                <span><i class="bi bi-patch-check me-1"></i>{{ $first->skema->name }}</span>
                <span><i class="bi bi-building me-1"></i>{{ $first->tuk->name ?? '-' }}</span>
                <span class="text-primary fw-semibold" style="font-family:monospace;">
                    <i class="bi bi-layers me-1"></i>{{ $batchId }}
                </span>
                <span><i class="bi bi-calendar3 me-1"></i>{{ $schedules->count() }} jadwal</span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-start">
            @php $totalAsesiAll = $schedules->sum(fn($s) => $s->asesmens->count()); @endphp
            <div class="text-center px-3 py-2 rounded-3"
                style="background:#f0fdf4;border:1px solid #bbf7d0;min-width:70px">
                <div class="fw-bold" style="font-size:1.2rem;color:#16a34a">{{ $totalK }}</div>
                <div style="font-size:.65rem;color:#6b7280;font-weight:600">KOMPETEN</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                style="background:#fff1f2;border:1px solid #fecdd3;min-width:70px">
                <div class="fw-bold" style="font-size:1.2rem;color:#ef4444">{{ $totalBK }}</div>
                <div style="font-size:.65rem;color:#6b7280;font-weight:600">BELUM K</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                style="background:#eff6ff;border:1px solid #bfdbfe;min-width:70px">
                <div class="fw-bold" style="font-size:1.2rem;color:#2563eb">{{ $totalAsesiAll }}</div>
                <div style="font-size:.65rem;color:#6b7280;font-weight:600">TOTAL ASESI</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== RINGKASAN BATCH ===== --}}
<div class="mb-4">
    <p class="text-muted mb-3" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600;">
        Ringkasan Batch
    </p>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="text-muted small mb-1">Kompeten</div>
                <div class="fw-semibold text-success" style="font-size:1.75rem;">{{ $totalK }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="text-muted small mb-1">Belum Kompeten</div>
                <div class="fw-semibold text-danger" style="font-size:1.75rem;">{{ $totalBK }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="text-muted small mb-1">Total Asesi</div>
                <div class="fw-semibold" style="font-size:1.75rem;">{{ $totalAsesiAll }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background:#f8f9fa;">
                <div class="text-muted small mb-1">Jadwal</div>
                <div class="fw-semibold" style="font-size:1.75rem;">{{ $schedules->count() }}</div>
            </div>
        </div>
    </div>
    @if($totalK + $totalBK > 0)
    <div class="mt-3 d-flex align-items-center justify-content-between px-3 py-2 rounded-3" style="background:#f8f9fa;">
        <span class="text-muted small">Tingkat kompetensi</span>
        <span class="fw-semibold">{{ round($totalK / ($totalK + $totalBK) * 100) }}%</span>
    </div>
    @endif
</div>

{{-- ===== LOOP PER JADWAL ===== --}}
@foreach($schedules as $loop_idx => $schedule)
@php
$distribusiTeori = $distribusiTeoriMap[$schedule->id] ?? null;
$hasilObservasi = $hasilObservasiAll->where('schedule_id', $schedule->id);
$hasilPortofolio = $hasilPortofolioAll->where('schedule_id', $schedule->id);
$beritaAcara = $schedule->beritaAcara;
$semua = $schedule->asesmens->count();
$hadir = $schedule->asesmens->where('hadir', true)->count();
$adaFoto = $schedule->foto_dokumentasi_1 || $schedule->foto_dokumentasi_2;
$collapseId = 'jadwal-collapse-' . $schedule->id;
@endphp

<div class="card border-0 shadow-sm mb-3">
    {{-- Header Jadwal --}}
    <div class="card-header bg-white py-3 pe-3" style="border-left:4px solid #2563eb;">

        <div class="d-flex align-items-center justify-content-between">

            <div style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                aria-expanded="false" aria-controls="{{ $collapseId }}">

                <div class="fw-bold">
                    <i class="bi bi-calendar3 text-primary me-2"></i>
                    {{ $schedule->assessment_date->translatedFormat('d F Y') }}
                    <span class="text-muted fw-normal small ms-2">
                        {{ $schedule->start_time }} – {{ $schedule->end_time }}
                    </span>
                </div>

                <div class="text-muted small mt-1">
                    <i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}

                    @if($schedule->asesor)
                    · <i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor->user->name ?? '-' }}
                    @endif

                    · <i class="bi bi-people me-1"></i>{{ $semua }} asesi ({{ $hadir }} hadir)

                    @if($adaFoto)
                    · <i class="bi bi-camera text-info me-1"></i>
                    <span class="text-info">
                        {{ $schedule->hasFotoDokumentasi() ? '2' : '1' }} foto
                    </span>
                    @endif
                </div>
            </div>

            <div class="d-flex gap-2 align-items-center flex-shrink-0">

                @if($beritaAcara)
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2">
                    <i class="bi bi-check-circle me-1"></i>BA Ada
                </span>

                <a href="{{ route('manajer-sertifikasi.jadwal.berita-acara.pdf', $schedule) }}" target="_blank"
                    class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-pdf"></i>
                </a>
                @else
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2">
                    Belum ada BA
                </span>
                @endif

                <a href="{{ route('manajer-sertifikasi.hasil-asesmen.jadwal', $schedule) }}"
                    class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Detail
                </a>

                <i class="bi bi-chevron-down text-muted collapse-chevron"
                    style="font-size:.85rem;transition:transform .2s;" data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}">
                </i>

            </div>

        </div>
    </div>

    {{-- Collapsible content --}}
    <div class="collapse" id="{{ $collapseId }}">

        {{-- Tabel rekap asesi --}}
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size:.83rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Nama Asesi</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Teori</th>
                            @foreach($schedule->distribusiSoalObservasi as $distObs)
                            <th class="text-center" style="max-width:100px;">
                                {{ Str::limit($distObs->soalObservasi->judul ?? '-', 18) }}
                            </th>
                            @endforeach
                            <th class="text-center pe-4">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule->asesmens as $i => $asesmen)
                        @php
                        $soalAsesi = $distribusiTeori ? $distribusiTeori->soalAsesi->where('asesmen_id', $asesmen->id) :
                        collect();
                        $totalSoal = $soalAsesi->count();
                        $benarSoal = $soalAsesi->filter(fn($s) => $s->jawaban && $s->jawaban ===
                        $s->soalTeori?->jawaban_benar)->count();
                        $sudahSubmit = $soalAsesi->whereNotNull('submitted_at')->isNotEmpty();
                        $rek = $rekomendasiMap[$asesmen->id] ?? null;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $asesmen->full_name }}</td>
                            <td class="text-center">
                                @if($asesmen->hadir)
                                <i class="bi bi-check-circle-fill text-success"></i>
                                @else
                                <i class="bi bi-dash-circle text-muted"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($totalSoal === 0)
                                <span class="text-muted">—</span>
                                @elseif(!$sudahSubmit)
                                <span class="badge bg-warning text-dark" style="font-size:.65rem;">Belum</span>
                                @else
                                <span
                                    class="fw-bold {{ round($benarSoal / $totalSoal * 100) >= 60 ? 'text-success' : 'text-danger' }}">
                                    {{ $benarSoal }}/{{ $totalSoal }}
                                </span>
                                @endif
                            </td>
                            @foreach($schedule->distribusiSoalObservasi as $distObs)
                            @php
                            $jwb = $jawabanObservasiAll
                            ->where('asesmen_id', $asesmen->id)
                            ->where('distribusi_soal_observasi_id', $distObs->id)
                            ->first();
                            @endphp
                            <td class="text-center">
                                @if($jwb && $jwb->gdrive_link)
                                <a href="{{ $jwb->gdrive_link }}" target="_blank"
                                    class="btn btn-sm btn-outline-primary py-0 px-1" style="font-size:.7rem;">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="text-center pe-4">
                                @if($rek === 'K')
                                <span class="badge bg-success">Kompeten</span>
                                @elseif($rek === 'BK')
                                <span class="badge bg-danger">Belum K</span>
                                @else
                                <span class="badge bg-secondary">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Foto Dokumentasi (jika ada) --}}
        @if($adaFoto)
        <div class="px-4 py-3 border-top" style="background:#f8fafc;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-camera text-info"></i>
                <span class="fw-semibold small">Foto Dokumentasi</span>
                @if($schedule->hasFotoDokumentasi())
                <span class="badge bg-success" style="font-size:.6rem;">Lengkap</span>
                @else
                <span class="badge bg-warning text-dark" style="font-size:.6rem;">Sebagian</span>
                @endif
            </div>
            <div class="d-flex gap-3">
                @foreach([1, 2] as $slot)
                @php $col = "foto_dokumentasi_{$slot}"; @endphp
                @if($schedule->$col)
                <div class="border rounded-3 overflow-hidden" style="width:140px;">
                    <img src="{{ route('manajer-sertifikasi.hasil-asesmen.foto', [$schedule, $slot]) }}"
                        style="width:140px;height:90px;object-fit:cover;" alt="Foto {{ $slot }}">
                    <div class="text-center text-muted py-1" style="font-size:.7rem;">
                        <i class="bi bi-image me-1"></i>Foto {{ $slot }}
                    </div>
                </div>
                @endif
                @endforeach
                @if($schedule->foto_uploaded_at)
                <div class="text-muted align-self-end" style="font-size:.72rem;">
                    <i class="bi bi-clock me-1"></i>
                    {{ \Carbon\Carbon::parse($schedule->foto_uploaded_at)->translatedFormat('d M Y, H:i') }}
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>{{-- /collapse --}}
</div>
@endforeach

@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(header) {
    var target = document.querySelector(header.getAttribute('data-bs-target'));
    var chevron = header.querySelector('.collapse-chevron');
    if (!target || !chevron) return;

    if (target.classList.contains('show')) {
        chevron.style.transform = 'rotate(180deg)';
    }

    target.addEventListener('show.bs.collapse', function() {
        chevron.style.transform = 'rotate(180deg)';
    });
    target.addEventListener('hide.bs.collapse', function() {
        chevron.style.transform = 'rotate(0deg)';
    });
});
</script>
@endpush