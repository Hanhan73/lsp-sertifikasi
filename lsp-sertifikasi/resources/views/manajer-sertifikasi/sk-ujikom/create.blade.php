@extends('layouts.app')
@section('title', 'Ajukan SK Hasil Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom › Ajukan')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <a href="{{ route('manajer-sertifikasi.sk-ujikom.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <h4 class="fw-bold mb-1">Ajukan SK Hasil Ujikom</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
        Batch: <code>{{ $batchId }}</code> &nbsp;·&nbsp;
        {{ $first->skema?->name ?? '-' }} &nbsp;·&nbsp;
        {{ $first->tuk?->name ?? '-' }}
    </p>
</div>

<div class="row g-4">

    {{-- ── KIRI: Form ── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Data SK
            </div>
            <div class="card-body">
                <form action="{{ route('manajer-sertifikasi.sk-ujikom.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="collective_batch_id" value="{{ $batchId }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor SK</label>
                        <div class="form-control bg-light text-muted" style="cursor:default;">
                            <i class="bi bi-magic me-1"></i>Otomatis digenerate saat pengajuan
                        </div>
                        <div class="form-text">Format: 001/LSP-KAP/SER.20.06/Bulan/Tahun</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tanggal Pleno <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pleno"
                               class="form-control @error('tanggal_pleno') is-invalid @enderror"
                               value="{{ old('tanggal_pleno') }}">
                        @error('tanggal_pleno')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tanggal rapat pleno panitia teknis ujian kompetensi.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Dikeluarkan di <span class="text-danger">*</span></label>
                        <input type="text" name="tempat_dikeluarkan"
                               class="form-control @error('tempat_dikeluarkan') is-invalid @enderror"
                               value="{{ old('tempat_dikeluarkan', 'Bandung') }}">
                        @error('tempat_dikeluarkan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info border-0 py-2 mb-3" style="font-size:.85rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Setelah diajukan, SK akan dikirim ke Direktur untuk disetujui.
                        Dokumen PDF akan otomatis dibuat setelah Direktur menyetujui.
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send me-2"></i>Kirim ke Direktur
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── KANAN: Preview Peserta Kompeten ── --}}
    <div class="col-lg-7">

        {{-- Jadwal dalam batch --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar3 me-2 text-secondary"></i>Jadwal dalam Batch
                <span class="badge bg-light text-dark border ms-2">{{ $schedules->count() }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle" style="font-size:.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>TUK</th>
                            <th>Asesor</th>
                            <th class="text-center">Berita Acara</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $s)
                        @php $ba = $s->beritaAcara; @endphp
                        <tr>
                            <td class="ps-3">{{ $s->assessment_date->translatedFormat('d M Y') }}</td>
                            <td>{{ $s->tuk?->name ?? '-' }}</td>
                            <td>{{ $s->asesor?->nama ?? '-' }}</td>
                            <td class="text-center">
                                @if($ba)
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                    {{-- PDF BA --}}
                                    <a href="{{ route('manajer-sertifikasi.jadwal.berita-acara.pdf', $s) }}?preview=1"
                                       target="_blank"
                                       class="btn btn-outline-danger btn-sm py-0 px-2"
                                       title="Lihat PDF Berita Acara">
                                        <i class="bi bi-file-pdf" style="font-size:.8rem;"></i>
                                    </a>
                                    {{-- File xlsx BA (hanya kalau ada) --}}
                                    @if($ba->file_path)
                                    <a href="{{ route('manajer-sertifikasi.jadwal.rekap.download-ba', $s) }}"
                                       class="btn btn-outline-secondary btn-sm py-0 px-2"
                                       title="Download file Excel BA">
                                        <i class="bi bi-file-earmark-spreadsheet" style="font-size:.8rem;"></i>
                                    </a>
                                    @endif
                                </div>
                                @else
                                <span class="badge bg-secondary" style="font-size:.7rem;">Belum ada</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Peserta Kompeten --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <span class="fw-semibold">
                    <i class="bi bi-people me-2 text-success"></i>Peserta Kompeten (K)
                </span>
                <span class="badge bg-success px-3">{{ $pesertaKompeten->count() }} orang</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle" style="font-size:.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>Instansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesertaKompeten as $i => $asesi)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}.</td>
                            <td class="fw-semibold">{{ $asesi->full_name }}</td>
                            <td class="text-muted">{{ $asesi->institution ?? $first->tuk?->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection