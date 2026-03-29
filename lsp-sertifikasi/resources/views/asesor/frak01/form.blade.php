@extends('layouts.app')
@section('title', 'FR.AK.01 - Verifikasi Asesor')
@section('page-title', 'FR.AK.01 - Persetujuan Asesmen dan Kerahasiaan')
@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@push('styles')
<style>
.info-row td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.info-row td:first-child { color: #64748b; width: 38%; font-size: .88rem; }
#sig-asesor { border: 2px dashed #cbd5e1; border-radius: 8px; cursor: crosshair; touch-action: none; background: #fafafa; }
#sig-asesor.has-sig { border-color: #198754; border-style: solid; }
</style>
@endpush

@section('content')

{{-- Status banner --}}
@if(in_array($frak01->status, ['verified', 'approved']))
<div class="alert alert-success d-flex align-items-center gap-3 mb-4 border-0 shadow-sm">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div class="flex-grow-1"><strong>FR.AK.01 sudah selesai.</strong> Kedua pihak telah menandatangani.</div>
    <a href="{{ route('asesor.frak01.pdf', [$schedule, $asesmen]) }}" target="_blank" class="btn btn-sm btn-success">
        <i class="bi bi-file-pdf me-1"></i>Lihat PDF
    </a>
</div>
@elseif($frak01->status === 'submitted')
<div class="alert alert-info d-flex align-items-center gap-3 mb-4 border-0 shadow-sm">
    <i class="bi bi-pen fs-4"></i>
    <div><strong>Asesi sudah menandatangani.</strong> Silakan review checklist bukti dan berikan tanda tangan Anda.</div>
</div>
@elseif($frak01->status === 'draft')
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4 border-0 shadow-sm">
    <i class="bi bi-hourglass-split fs-4"></i>
    <div><strong>Menunggu tanda tangan asesi.</strong> Asesi belum menandatangani FR.AK.01 ini.</div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-check fs-5"></i>
        <h5 class="mb-0">FR.AK.01 — Persetujuan Asesmen dan Kerahasiaan</h5>
    </div>
    <div class="card-body">

        <p class="text-muted mb-4">
            Review data persetujuan asesmen di bawah ini. Setelah memverifikasi bahwa semua informasi
            sudah benar, berikan tanda tangan Anda.
        </p>

        {{-- ── Info Jadwal ── --}}
        <div class="card bg-light border-0 mb-4">
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="info-row"><td>Skema</td><td><strong>{{ $frak01->skema_judul ?? $asesmen->skema?->name ?? '-' }}</strong></td></tr>
                        <tr class="info-row"><td>Nomor Skema</td><td>{{ $frak01->skema_nomor ?? $asesmen->skema?->code ?? '-' }}</td></tr>
                        <tr class="info-row"><td>TUK</td><td>{{ $frak01->tuk_nama ?? $asesmen->tuk?->name ?? '-' }}</td></tr>
                        <tr class="info-row"><td>Nama Asesor</td><td>{{ $asesor->nama }}</td></tr>
                        <tr class="info-row"><td>Nama Asesi</td><td><strong>{{ $asesmen->full_name }}</strong></td></tr>
                        <tr class="info-row"><td>Hari / Tanggal</td><td>{{ $frak01->hari_tanggal ?? $schedule->assessment_date->translatedFormat('l, d F Y') }}</td></tr>
                        <tr class="info-row"><td>Waktu</td><td>{{ $frak01->waktu_asesmen ?? ($schedule->start_time . ' – ' . $schedule->end_time) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Checklist Bukti (read-only, diisi asesi) ── --}}
        <div class="card mb-4">
            <div class="card-header bg-light fw-semibold">
                <i class="bi bi-clipboard-check me-2"></i>Bukti yang Dikumpulkan
                <span class="text-muted fw-normal small ms-1">— Diisi oleh asesi</span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @php
                    $buktis = [
                        ['field' => 'bukti_verifikasi_portofolio',     'label' => 'Hasil Verifikasi Portofolio'],
                        ['field' => 'bukti_hasil_review_produk',        'label' => 'Hasil Review Produk'],
                        ['field' => 'bukti_observasi_langsung',         'label' => 'Hasil Observasi Langsung'],
                        ['field' => 'bukti_hasil_kegiatan_terstruktur', 'label' => 'Hasil Kegiatan Terstruktur'],
                        ['field' => 'bukti_pertanyaan_lisan',           'label' => 'Hasil Pertanyaan Lisan'],
                        ['field' => 'bukti_pertanyaan_tertulis',        'label' => 'Hasil Pertanyaan Tertulis'],
                        ['field' => 'bukti_pertanyaan_wawancara',       'label' => 'Hasil Pertanyaan Wawancara'],
                        ['field' => 'bukti_lainnya',                    'label' => 'Lainnya' . ($frak01->bukti_lainnya_keterangan ? ': ' . $frak01->bukti_lainnya_keterangan : '')],
                    ];
                    @endphp
                    @foreach($buktis as $b)
                    <div class="col-md-6 d-flex align-items-center gap-2 py-1">
                        <i class="bi {{ $frak01->{$b['field']} ? 'bi-check-square-fill text-primary' : 'bi-square text-muted' }} fs-5"></i>
                        <span class="{{ $frak01->{$b['field']} ? 'fw-semibold' : 'text-muted' }} small">
                            {{ $b['label'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Tanda Tangan ── --}}
        <div class="row g-3 mb-4">

            {{-- TTD Asesi (read-only) --}}
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">Tanda Tangan Asesi</span>
                        @if($frak01->ttd_asesi)
                            <span class="badge bg-success">Sudah TTD</span>
                        @else
                            <span class="badge bg-secondary">Belum TTD</span>
                        @endif
                    </div>
                    <div class="card-body text-center">
                        @if($frak01->ttd_asesi)
                        <img src="{{ $frak01->ttd_asesi_image }}"
                            style="max-height:80px; max-width:100%;" alt="TTD Asesi">
                        <div class="small fw-semibold mt-2">{{ $frak01->nama_ttd_asesi }}</div>
                        <div class="text-muted small">{{ $frak01->tanggal_ttd_asesi?->format('d M Y, H:i') }}</div>
                        @else
                        <div class="py-4 text-muted">
                            <i class="bi bi-hourglass-split fs-2 d-block mb-2 opacity-50"></i>
                            <span class="small">Menunggu tanda tangan asesi</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TTD Asesor --}}
            <div class="col-md-6">
                <div class="card h-100 {{ $frak01->status === 'submitted' ? 'border-success' : '' }}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">Tanda Tangan Asesor</span>
                        @if($frak01->ttd_asesor)
                            <span class="badge bg-success">Sudah TTD</span>
                        @else
                            <span class="badge bg-secondary">Belum TTD</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($frak01->ttd_asesor)
                        <div class="text-center">
                            <img src="{{ $frak01->ttd_asesor_image }}"
                                style="max-height:80px; max-width:100%;" alt="TTD Asesor">
                            <div class="small fw-semibold mt-2">{{ $frak01->nama_ttd_asesor }}</div>
                            <div class="text-muted small">{{ $frak01->tanggal_ttd_asesor?->format('d M Y, H:i') }}</div>
                        </div>
                        @elseif($frak01->status === 'submitted')
                        {{-- Form TTD asesor --}}
                        <canvas id="sig-asesor" width="100%" height="160" style="width:100%;"></canvas>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAsesorSig()">
                                <i class="bi bi-eraser me-1"></i>Hapus
                            </button>
                            <button type="button" class="btn btn-success btn-sm ms-auto" onclick="signAsesor()">
                                <i class="bi bi-pen me-1"></i>Tanda Tangan
                            </button>
                        </div>
                        @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-lock fs-2 d-block mb-2 opacity-50"></i>
                            <span class="small">Tersedia setelah asesi menandatangani</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('asesor.schedule.detail', $schedule) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            @if(in_array($frak01->status, ['verified', 'approved']))
            <a href="{{ route('asesor.frak01.pdf', [$schedule, $asesmen]) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-file-pdf me-1"></i>Download PDF
            </a>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
let sigPadAsesor = null;

document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('sig-asesor');
    if (!canvas) return;

    sigPadAsesor = new SignaturePad(canvas, { penColor: '#0f172a' });

    function resize() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth * ratio;
        canvas.height = 160 * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        sigPadAsesor.clear();
    }
    resize();
    window.addEventListener('resize', resize);

    sigPadAsesor.addEventListener('endStroke', () => canvas.classList.add('has-sig'));
});

function clearAsesorSig() {
    if (sigPadAsesor) {
        sigPadAsesor.clear();
        document.getElementById('sig-asesor')?.classList.remove('has-sig');
    }
}

async function signAsesor() {
    if (!sigPadAsesor || sigPadAsesor.isEmpty()) {
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Diperlukan', text: 'Silakan tanda tangan terlebih dahulu.' });
        return;
    }

    const result = await Swal.fire({
        title: 'Verifikasi FR.AK.01?',
        text: 'Anda akan menandatangani dan memverifikasi FR.AK.01 ini.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-pen me-1"></i>Ya, Verifikasi',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
    });
    if (!result.isConfirmed) return;

    try {
        const res  = await fetch('{{ route("asesor.frak01.sign", [$schedule, $asesmen]) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                signature:   sigPadAsesor.toDataURL('image/png'),
                nama_asesor: '{{ $asesor->nama }}',
            }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, showConfirmButton: false, timer: 1800 })
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush