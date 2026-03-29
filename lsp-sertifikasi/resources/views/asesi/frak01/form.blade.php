@extends('layouts.app')
@section('title', 'FR.AK.01 - Persetujuan Asesmen')
@section('page-title', 'FR.AK.01 - Persetujuan Asesmen dan Kerahasiaan')
@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@push('styles')
<style>
.info-row td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.info-row td:first-child { color: #64748b; width: 38%; font-size: .88rem; }
.cb-item { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.cb-item:last-child { border-bottom: none; }
.cb-item input[type="checkbox"] { width: 18px; height: 18px; margin-top: 2px; flex-shrink: 0; }
#signature-pad { border: 2px dashed #cbd5e1; border-radius: 8px; cursor: crosshair; touch-action: none; }
#signature-pad.has-sig { border-color: #0d6efd; border-style: solid; }
</style>
@endpush

@section('content')

{{-- Status banner --}}
@if(in_array($frak01->status, ['verified', 'approved']))
<div class="alert alert-success d-flex align-items-center gap-3 mb-4 border-0 shadow-sm">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div class="flex-grow-1">
        <strong>FR.AK.01 sudah diverifikasi asesor.</strong>
        PDF sudah tersedia untuk diunduh.
    </div>
    <a href="{{ route('asesi.frak01.pdf', ['preview' => 1]) }}" target="_blank"
        class="btn btn-sm btn-success">
        <i class="bi bi-file-pdf me-1"></i>Lihat PDF
    </a>
    <a href="{{ route('asesi.frak01.pdf') }}" class="btn btn-sm btn-outline-success">
        <i class="bi bi-download me-1"></i>Download
    </a>
</div>
@elseif($frak01->status === 'submitted')
<div class="alert alert-info d-flex align-items-center gap-3 mb-4 border-0 shadow-sm">
    <i class="bi bi-hourglass-split fs-4"></i>
    <div>
        <strong>Anda sudah menandatangani FR.AK.01.</strong><br>
        <span class="small">Menunggu tanda tangan asesor untuk menyelesaikan dokumen ini.</span>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-check fs-5"></i>
        <h5 class="mb-0">FR.AK.01 — Persetujuan Asesmen dan Kerahasiaan</h5>
    </div>
    <div class="card-body">

        <p class="text-muted mb-4">
            Dokumen ini menyatakan persetujuan Anda mengikuti proses asesmen dan menjaga kerahasiaan
            materi asesmen. Baca dengan teliti, isi checklist bukti, lalu tanda tangani.
        </p>

        {{-- ── Info Jadwal ── --}}
        <div class="card bg-light border-0 mb-4">
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="info-row">
                            <td>Skema Sertifikasi</td>
                            <td><strong>{{ $frak01->skema_judul ?? $asesmen->skema?->name ?? '-' }}</strong></td>
                        </tr>
                        <tr class="info-row">
                            <td>Nomor Skema</td>
                            <td>{{ $frak01->skema_nomor ?? $asesmen->skema?->code ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>TUK</td>
                            <td>{{ $frak01->tuk_nama ?? $asesmen->tuk?->name ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Asesor</td>
                            <td>{{ $frak01->nama_asesor ?? $asesmen->schedule?->asesor?->nama ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Nama Asesi</td>
                            <td><strong>{{ $asesmen->full_name }}</strong></td>
                        </tr>
                        <tr class="info-row">
                            <td>Hari / Tanggal</td>
                            <td>{{ $frak01->hari_tanggal ?? $asesmen->schedule?->assessment_date?->translatedFormat('l, d F Y') ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Waktu</td>
                            <td>{{ $frak01->waktu_asesmen ?? ($asesmen->schedule ? $asesmen->schedule->start_time . ' – ' . $asesmen->schedule->end_time : '-') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Checklist Bukti ── --}}
        <div class="card mb-4 {{ in_array($frak01->status, ['submitted', 'verified', 'approved']) ? 'opacity-75' : '' }}">
            <div class="card-header bg-light fw-semibold">
                <i class="bi bi-clipboard-check me-2"></i>Bukti yang Akan Dikumpulkan
                <span class="text-muted fw-normal small ms-1">— Centang bukti yang akan Anda siapkan</span>
            </div>
            <div class="card-body">
                @if(in_array($frak01->status, ['submitted', 'verified', 'approved']))
                {{-- Read-only setelah submit --}}
                <div class="row g-2">
                    @php
                    $buktis = [
                        ['field' => 'bukti_verifikasi_portofolio',      'label' => 'Hasil Verifikasi Portofolio'],
                        ['field' => 'bukti_hasil_review_produk',         'label' => 'Hasil Review Produk'],
                        ['field' => 'bukti_observasi_langsung',          'label' => 'Hasil Observasi Langsung'],
                        ['field' => 'bukti_hasil_kegiatan_terstruktur',  'label' => 'Hasil Kegiatan Terstruktur'],
                        ['field' => 'bukti_pertanyaan_lisan',            'label' => 'Hasil Pertanyaan Lisan'],
                        ['field' => 'bukti_pertanyaan_tertulis',         'label' => 'Hasil Pertanyaan Tertulis'],
                        ['field' => 'bukti_pertanyaan_wawancara',        'label' => 'Hasil Pertanyaan Wawancara'],
                        ['field' => 'bukti_lainnya',                     'label' => 'Lainnya' . ($frak01->bukti_lainnya_keterangan ? ': ' . $frak01->bukti_lainnya_keterangan : '')],
                    ];
                    @endphp
                    @foreach($buktis as $b)
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <i class="bi {{ $frak01->{$b['field']} ? 'bi-check-square-fill text-primary' : 'bi-square text-muted' }}"></i>
                        <span class="{{ $frak01->{$b['field']} ? '' : 'text-muted' }} small">{{ $b['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                {{-- Editable --}}
                <p class="small text-muted mb-3">
                    Centang jenis bukti yang akan Anda siapkan untuk proses asesmen.
                </p>
                <div id="bukti-list">
                    @php
                    $buktis = [
                        ['field' => 'bukti_verifikasi_portofolio',      'label' => 'Hasil Verifikasi Portofolio',      'desc' => 'Dokumen portofolio pekerjaan, sertifikat, atau karya nyata.'],
                        ['field' => 'bukti_hasil_review_produk',         'label' => 'Hasil Review Produk',              'desc' => 'Produk atau hasil kerja yang dapat diperiksa asesor.'],
                        ['field' => 'bukti_observasi_langsung',          'label' => 'Hasil Observasi Langsung',         'desc' => 'Demonstrasi langsung keterampilan di hadapan asesor.'],
                        ['field' => 'bukti_hasil_kegiatan_terstruktur',  'label' => 'Hasil Kegiatan Terstruktur',       'desc' => 'Hasil simulasi, studi kasus, atau latihan terstruktur.'],
                        ['field' => 'bukti_pertanyaan_lisan',            'label' => 'Hasil Pertanyaan Lisan',           'desc' => 'Tanya jawab lisan dengan asesor.'],
                        ['field' => 'bukti_pertanyaan_tertulis',         'label' => 'Hasil Pertanyaan Tertulis',        'desc' => 'Ujian tertulis atau kuis.'],
                        ['field' => 'bukti_pertanyaan_wawancara',        'label' => 'Hasil Pertanyaan Wawancara',       'desc' => 'Wawancara mendalam dengan asesor.'],
                    ];
                    @endphp
                    @foreach($buktis as $b)
                    <div class="cb-item">
                        <input type="checkbox" id="{{ $b['field'] }}"
                            class="form-check-input bukti-check"
                            data-field="{{ $b['field'] }}"
                            {{ $frak01->{$b['field']} ? 'checked' : '' }}>
                        <div>
                            <label for="{{ $b['field'] }}" class="fw-semibold small mb-0" style="cursor:pointer;">
                                {{ $b['label'] }}
                            </label>
                            <div class="text-muted" style="font-size:.78rem;">{{ $b['desc'] }}</div>
                        </div>
                    </div>
                    @endforeach
                    {{-- Lainnya --}}
                    <div class="cb-item">
                        <input type="checkbox" id="bukti_lainnya"
                            class="form-check-input bukti-check"
                            data-field="bukti_lainnya"
                            {{ $frak01->bukti_lainnya ? 'checked' : '' }}
                            onchange="document.getElementById('lainnya-wrap').style.display = this.checked ? 'block' : 'none'">
                        <div class="flex-grow-1">
                            <label for="bukti_lainnya" class="fw-semibold small mb-0" style="cursor:pointer;">Lainnya</label>
                            <div id="lainnya-wrap" style="{{ $frak01->bukti_lainnya ? '' : 'display:none;' }}">
                                <input type="text" id="bukti_lainnya_keterangan"
                                    class="form-control form-control-sm mt-2"
                                    placeholder="Sebutkan bukti lainnya..."
                                    value="{{ $frak01->bukti_lainnya_keterangan }}">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-3" onclick="saveBukti()" id="btn-save-bukti">
                    <i class="bi bi-save me-1"></i>Simpan Checklist
                </button>
                <span id="bukti-save-status" class="text-success small ms-2" style="display:none;">
                    <i class="bi bi-check-circle me-1"></i>Tersimpan
                </span>
                @endif
            </div>
        </div>

        {{-- ── Tanda Tangan ── --}}
        <div class="row g-3 mb-4">

            {{-- TTD Asesi --}}
            <div class="col-md-6">
                <div class="card h-100 {{ $frak01->status === 'draft' ? 'border-primary' : '' }}">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">Tanda Tangan Asesi (Anda)</span>

                        @if($frak01->ttd_asesi)
                            <span class="badge bg-success">Sudah TTD</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum TTD</span>
                        @endif
                    </div>

                    <div class="card-body">

                        @if($frak01->ttd_asesi)

                            <div class="text-center">
                                <img src="{{ $frak01->ttd_asesi_image }}"
                                    style="max-height:80px; max-width:100%;" alt="TTD Asesi">

                                <div class="small fw-semibold mt-2">
                                    {{ $frak01->nama_ttd_asesi }}
                                </div>

                                <div class="text-muted small">
                                    {{ $frak01->tanggal_ttd_asesi?->format('d M Y, H:i') }}
                                </div>
                            </div>

                        @else

                            <p class="small text-muted mb-2">
                                Anda bisa menggambar tanda tangan atau upload gambar tanda tangan.
                            </p>

                            {{-- 🔥 COMPONENT SIGNATURE --}}
                            @include('partials._signature_pad', [
                                'padId' => 'asesi',
                                'padLabel' => 'Tanda Tangan Pemohon',
                                'padHeight' => 180,
                                'savedSig' => auth()->user()->signature_image,
                            ])

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="SigPadManager.clear('asesi')">
                                    <i class="bi bi-eraser me-1"></i>Hapus
                                </button>

                                <button type="button" class="btn btn-primary btn-sm ms-auto"
                                    onclick="submitSign()">
                                    <i class="bi bi-pen me-1"></i>Tanda Tangan & Submit
                                </button>
                            </div>

                        @endif

                    </div>
                </div>
            </div>

            {{-- TTD Asesor --}}
            <div class="col-md-6">
                <div class="card h-100 bg-light">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="small fw-bold">Tanda Tangan Asesor</span>
                        @if($frak01->ttd_asesor)
                            <span class="badge bg-success">Sudah TTD</span>
                        @else
                            <span class="badge bg-secondary">Menunggu</span>
                        @endif
                    </div>
                    <div class="card-body text-center">
                        @if($frak01->ttd_asesor)
                        <img src="{{ $frak01->ttd_asesor_image }}"
                            style="max-height:80px; max-width:100%;" alt="TTD Asesor">
                        <div class="small fw-semibold mt-2">{{ $frak01->nama_ttd_asesor }}</div>
                        <div class="text-muted small">{{ $frak01->tanggal_ttd_asesor?->format('d M Y, H:i') }}</div>
                        @else
                        <div class="py-4 text-muted">
                            <i class="bi bi-lock fs-2 d-block mb-2 opacity-50"></i>
                            <span class="small">
                                @if($frak01->status === 'draft')
                                    Tersedia setelah Anda menandatangani
                                @else
                                    Menunggu tanda tangan asesor
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('asesi.schedule') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Jadwal
        </a>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
let sigPad = null;
document.addEventListener('DOMContentLoaded', () => {
    // INIT SIGNATURE PAD
    SigPadManager.init('asesi', @json(auth()->user()->signature_image));
});
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('signature-pad');
    if (!canvas) return;

    sigPad = new SignaturePad(canvas, { penColor: '#1e293b' });

    // Resize canvas
    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = 160 * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        sigPad.clear();
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    sigPad.addEventListener('endStroke', () => {
        canvas.classList.add('has-sig');
    });
});

function clearSig() {
    if (sigPad) {
        sigPad.clear();
        document.getElementById('signature-pad')?.classList.remove('has-sig');
    }
}

async function saveBukti() {
    const payload = {};
    document.querySelectorAll('.bukti-check').forEach(cb => {
        payload[cb.dataset.field] = cb.checked;
    });
    payload['bukti_lainnya_keterangan'] = document.getElementById('bukti_lainnya_keterangan')?.value || '';

    const btn = document.getElementById('btn-save-bukti');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    try {
        const res  = await fetch('{{ route("asesi.frak01.bukti.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (data.success) {
            const status = document.getElementById('bukti-save-status');
            status.style.display = 'inline';
            setTimeout(() => status.style.display = 'none', 3000);
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Checklist';
    }
}

async function submitSign() {

    const sig = await SigPadManager.prepareAndGet('asesi');

    if (!sig) {
        Swal.fire({
            icon: 'warning',
            title: 'Tanda Tangan Diperlukan',
            text: 'Silakan gambar atau upload tanda tangan terlebih dahulu.'
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Konfirmasi Tanda Tangan',
        html: 'Setelah menandatangani, data tidak dapat diubah. Lanjutkan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tanda Tangan',
        cancelButtonText: 'Periksa Ulang',
    });

    if (!result.isConfirmed) return;

    try {

        const res = await fetch('{{ route("asesi.frak01.sign") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                signature: sig,
                nama_asesi: '{{ $asesmen->full_name }}',
            }),
        });

        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }

    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush