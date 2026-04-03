@extends('layouts.app')
@section('title', 'Rekap Penilaian — ' . $schedule->skema->name)
@section('breadcrumb', 'Distribusi › Rekap Penilaian')
 
@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection
 
@section('content')
 
{{-- Header --}}
<div class="mb-4">
    <a href="{{ route('manajer-sertifikasi.jadwal.show', $schedule) }}"
       class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Distribusi
    </a>
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Rekap Penilaian</h4>
            <div class="d-flex flex-wrap gap-3 text-muted small">
                <span><i class="bi bi-patch-check me-1"></i>{{ $schedule->skema->name }}</span>
                <span><i class="bi bi-calendar3 me-1"></i>{{ $schedule->assessment_date->translatedFormat('d M Y') }}</span>
                <span><i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}</span>
                <span><i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor?->nama ?? '-' }}</span>
                <span><i class="bi bi-people me-1"></i>{{ $schedule->asesmens->count() }} asesi</span>
            </div>
        </div>
        {{-- Status badges --}}
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge {{ $hasilObservasi->isNotEmpty() ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                <i class="bi bi-eye me-1"></i>Observasi {{ $hasilObservasi->count() }}/{{ $totalObservasi }}
            </span>
            <span class="badge {{ $hasilPortofolio->isNotEmpty() ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                <i class="bi bi-briefcase me-1"></i>Portofolio {{ $hasilPortofolio->count() }}/{{ $totalPortofolio }}
            </span>
            <span class="badge {{ $beritaAcara ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                <i class="bi bi-file-text me-1"></i>Berita Acara {{ $beritaAcara ? '✓' : '—' }}
            </span>
        </div>
    </div>
</div>
 
<div class="row g-4">
 
    {{-- ── KIRI: Dokumen & File ── --}}
    <div class="col-lg-7">
 
        {{-- Daftar Hadir --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
                <div><i class="bi bi-person-check text-primary me-2"></i>Daftar Hadir</div>
                @if($schedule->asesor?->user?->signature)
                <a href="{{ route('manajer-sertifikasi.jadwal.daftar-hadir', $schedule) }}"
                   target="_blank" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-pdf me-1"></i>Download PDF
                </a>
                @else
                <span class="badge bg-secondary">Asesor belum TTD</span>
                @endif

                <a href="{{ route('manajer-sertifikasi.jadwal.rekap', $schedule) }}"
                class="btn btn-sm btn-outline-info align-self-center">
                    <i class="bi bi-clipboard2-data me-1"></i>Rekap Penilaian
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Nama</th>
                                <th class="text-center">Hadir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedule->asesmens as $i => $asesmen)
                            <tr>
                                <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                <td class="small fw-semibold">{{ $asesmen->full_name }}</td>
                                <td class="text-center">
                                    @if($asesmen->hadir)
                                    <span class="badge bg-success">Hadir</span>
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
        </div>
 
        {{-- Hasil Observasi --}}
        @if($totalObservasi > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-eye text-primary me-2"></i>Hasil Observasi
                <span class="badge bg-secondary ms-1">{{ $hasilObservasi->count() }}/{{ $totalObservasi }} diupload</span>
            </div>
            <div class="list-group list-group-flush">
                @foreach($schedule->distribusiSoalObservasi as $dist)
                @php
                    $obs   = $dist->soalObservasi;
                    $hasil = $hasilObservasi->where('soal_observasi_id', $obs->id)->first();
                @endphp
                <div class="list-group-item d-flex align-items-center gap-3 px-4 py-3">
                    <i class="bi {{ $hasil ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $obs->judul }}</div>
                        @if($hasil)
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $hasil->file_name }} · {{ $hasil->uploaded_at->translatedFormat('d M Y H:i') }}
                            @if($hasil->catatan)· {{ $hasil->catatan }}@endif
                        </div>
                        @else
                        <div class="text-muted small">Belum diupload asesor</div>
                        @endif
                    </div>
                    @if($hasil)
                    <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-observasi', [$schedule, $obs]) }}"
                       class="btn btn-sm btn-outline-primary flex-shrink-0">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
 
        {{-- Hasil Portofolio --}}
        @if($totalPortofolio > 0)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-briefcase me-2" style="color:#7c3aed"></i>Hasil Portofolio
                <span class="badge bg-secondary ms-1">{{ $hasilPortofolio->count() }}/{{ $totalPortofolio }} diupload</span>
            </div>
            <div class="list-group list-group-flush">
                @foreach($schedule->distribusiPortofolio as $dist)
                @php
                    $porto = $dist->portofolio;
                    $hasil = $hasilPortofolio->where('portofolio_id', $porto->id)->first();
                @endphp
                <div class="list-group-item d-flex align-items-center gap-3 px-4 py-3">
                    <i class="bi {{ $hasil ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }} fs-5 flex-shrink-0"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $porto->judul }}</div>
                        @if($hasil)
                        <div class="text-muted" style="font-size:.75rem;">
                            {{ $hasil->file_name }} · {{ $hasil->uploaded_at->translatedFormat('d M Y H:i') }}
                        </div>
                        @else
                        <div class="text-muted small">Belum diupload asesor</div>
                        @endif
                    </div>
                    @if($hasil)
                    <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-portofolio', [$schedule, $porto]) }}"
                       class="btn btn-sm btn-outline-primary flex-shrink-0">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
 
    </div>
 
    {{-- ── KANAN: Nilai Teori + Berita Acara ── --}}
    <div class="col-lg-5">
 
        {{-- Nilai Teori per Asesi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-journal-text text-success me-2"></i>Nilai Teori
            </div>
            <div class="list-group list-group-flush">
                @forelse($schedule->asesmens as $asesmen)
                @php
                    $soalAsesi = $asesmen->soalTeoriAsesi ?? collect();
                    $total  = $soalAsesi->count();
                    $benar  = $soalAsesi->filter(fn($s) =>
                        $s->jawaban && $s->jawaban === $s->soalTeori?->jawaban_benar
                    )->count();
                    $submit = $soalAsesi->whereNotNull('submitted_at')->isNotEmpty();
                @endphp
                <div class="list-group-item px-4 py-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-grow-1 small fw-semibold">{{ $asesmen->full_name }}</div>
                        @if($total > 0 && $submit)
                        <div class="text-end flex-shrink-0">
                            <div class="fw-bold text-primary">{{ $benar }}/{{ $total }}</div>
                            <div class="text-muted" style="font-size:.7rem;">
                                {{ $total > 0 ? round($benar/$total*100) : 0 }}%
                            </div>
                        </div>
                        @elseif($total > 0)
                        <span class="badge bg-warning text-dark" style="font-size:.68rem;">Belum Submit</span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-3 small">Tidak ada asesi</div>
                @endforelse
            </div>
        </div>
 
        {{-- Berita Acara --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex align-items-center justify-content-between">
                <div><i class="bi bi-file-text text-warning me-2"></i>Berita Acara</div>
                @if($beritaAcara && $beritaAcara->file_path)
                <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-ba', $schedule) }}"
                   class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-download me-1"></i>File BA
                </a>
                @endif
            </div>
            @if(!$beritaAcara)
            <div class="card-body text-center py-4 text-muted small">
                <i class="bi bi-hourglass-split" style="font-size:2rem;opacity:.3;"></i>
                <p class="mt-2 mb-0">Berita acara belum diisi asesor</p>
            </div>
            @else
            <div class="card-body">
                <div class="small text-muted mb-3">
                    Tanggal: <strong>{{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('d M Y') }}</strong>
                    @if($beritaAcara->catatan)
                    <br>Catatan: {{ $beritaAcara->catatan }}
                    @endif
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($schedule->asesmens as $asesmen)
                    @php
                        $rek = $rekomendasiMap[$asesmen->id] ?? null;
                    @endphp
                    <div class="d-flex align-items-center gap-3 p-2 rounded-2 border
                        {{ $rek === 'K' ? 'border-success bg-success-subtle' : ($rek === 'BK' ? 'border-danger bg-danger-subtle' : 'bg-light') }}">
                        <div class="flex-grow-1 small fw-semibold">{{ $asesmen->full_name }}</div>
                        @if($rek)
                        <span class="badge {{ $rek === 'K' ? 'bg-success' : 'bg-danger' }} px-3">
                            {{ $rek === 'K' ? 'Kompeten' : 'Belum Kompeten' }}
                        </span>
                        @else
                        <span class="badge bg-secondary">—</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
 
    </div>
</div>
 
@endsection