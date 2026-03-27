@extends('layouts.app')
@section('title', 'FR.AK.01 - Persetujuan Asesmen')
@section('page-title', 'FR.AK.01 - Persetujuan Asesmen dan Kerahasiaan')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@push('styles')
<style>
.info-row td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.info-row td:first-child { color: #64748b; width: 35%; font-size: .9rem; }
.info-row td:last-child  { font-weight: 500; }
.cb-box {
    display: inline-flex; align-items: center; justify-content: center;
    width: 18px; height: 18px;
    border: 1.5px solid #64748b;
    border-radius: 3px;
    font-size: .75rem; font-weight: 700;
    color: #1d4ed8;
    vertical-align: middle;
    margin-right: 6px;
}
.cb-box.checked { border-color: #1d4ed8; background: #eff6ff; }
</style>
@endpush

@section('content')

{{-- ── Status Banner ── --}}
@if(in_array($frak01->status, ['submitted','verified','approved']))
<div class="alert alert-success d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div>
        <strong>FR.AK.01 sudah ditandatangani</strong> pada {{ $frak01->submitted_at?->format('d M Y H:i') }}.
        @if($frak01->status === 'verified' || $frak01->status === 'approved')
            <span class="badge bg-success ms-2">Sudah Diverifikasi Asesor</span>
        @else
            <span class="badge bg-warning text-dark ms-2">Menunggu Tanda Tangan Asesor</span>
        @endif
    </div>
    @if(in_array($frak01->status, ['verified','approved']))
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('asesi.frak01.pdf', ['preview'=>1]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye me-1"></i> Preview PDF
        </a>
        <a href="{{ route('asesi.frak01.pdf') }}" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i> Download PDF
        </a>
    </div>
    @else
    <div class="ms-auto">
        <span class="badge bg-secondary px-3 py-2">
            <i class="bi bi-lock me-1"></i> PDF tersedia setelah asesor menandatangani
        </span>
    </div>
    @endif
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-check fs-5"></i>
        <h5 class="mb-0">FR.AK.01 - PERSETUJUAN ASESMEN DAN KERAHASIAAN</h5>
    </div>
    <div class="card-body">

        <p class="text-muted mb-4">
            Persetujuan Asesmen ini untuk menjamin bahwa Asesi telah diberi arahan secara rinci tentang
            perencanaan dan proses asesmen.
        </p>

        {{-- ── Info Skema & Jadwal ── --}}
        <div class="card border-0 bg-light mb-4">
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr class="info-row">
                            <td>Skema Sertifikasi</td>
                            <td><strong>{{ $frak01->skema_judul ?? $asesmen->skema?->name ?? '-' }}</strong></td>
                        </tr>
                        <tr class="info-row">
                            <td>Nomor Skema</td>
                            <td>{{ $frak01->skema_nomor ?? $asesmen->skema?->nomor_skema ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>TUK</td>
                            <td>{{ $frak01->tuk_nama ?? $asesmen->tuk?->name ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Nama Asesor</td>
                            <td>{{ $frak01->nama_asesor ?? $asesmen->schedule?->asesor?->nama ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Nama Asesi</td>
                            <td>{{ $frak01->nama_asesi ?? $asesmen->full_name ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Hari / Tanggal</td>
                            <td>{{ $frak01->hari_tanggal ?? $asesmen->schedule?->assessment_date?->translatedFormat('l, d F Y') ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Waktu</td>
                            <td>{{ $frak01->waktu_asesmen ?? ($asesmen->schedule ? ($asesmen->schedule->start_time . ($asesmen->schedule->end_time ? ' – ' . $asesmen->schedule->end_time : '')) : '-') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Bukti ── --}}
        <h6 class="fw-bold mb-3">Bukti yang Akan Dikumpulkan</h6>
        <div class="row g-2 mb-4">
            @php
            $buktis = [
                ['field' => 'bukti_verifikasi_portofolio',      'label' => 'Hasil Verifikasi Portofolio'],
                ['field' => 'bukti_hasil_review_produk',         'label' => 'Hasil Review Produk'],
                ['field' => 'bukti_observasi_langsung',          'label' => 'Hasil Observasi Langsung'],
                ['field' => 'bukti_hasil_kegiatan_terstruktur',  'label' => 'Hasil Kegiatan Terstruktur'],
                ['field' => 'bukti_pertanyaan_lisan',            'label' => 'Hasil Pertanyaan Lisan'],
                ['field' => 'bukti_pertanyaan_tertulis',         'label' => 'Hasil Pertanyaan Tertulis'],
                ['field' => 'bukti_lainnya',                     'label' => 'Lainnya' . ($frak01->bukti_lainnya_keterangan ? ': ' . $frak01->bukti_lainnya_keterangan : '')],
                ['field' => 'bukti_pertanyaan_wawancara',        'label' => 'Hasil Pertanyaan Wawancara'],
            ];
            @endphp
            @foreach($buktis as $b)
            <div class="col-md-6">
                <span class="cb-box {{ $frak01->{$b['field']} ? 'checked' : '' }}">
                    {{ $frak01->{$b['field']} ? '✓' : '' }}
                </span>
                <span class="{{ $frak01->{$b['field']} ? 'fw-semibold' : 'text-muted' }}">{{ $b['label'] }}</span>
            </div>
            @endforeach
        </div>

        {{-- ── Pernyataan ── --}}
        <div class="card mb-4 border-warning">
            <div class="card-header bg-warning bg-opacity-10 border-warning">
                <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Pernyataan Persetujuan</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-primary mb-1">Asesi</span>
                    <p class="mb-0 small">Bahwa saya telah mendapatkan penjelasan terkait hak dan prosedur banding asesmen dari asesor.</p>
                </div>
                <hr class="my-2">
                <div class="mb-3">
                    <span class="badge bg-secondary mb-1">Asesor</span>
                    <p class="mb-0 small">Menyatakan tidak akan membuka hasil pekerjaan yang saya peroleh karena penugasan saya sebagai Asesor dalam pekerjaan <em>Asesmen</em> kepada siapapun atau organisasi apapun selain kepada pihak yang berwenang sehubungan dengan kewajiban saya sebagai Asesor yang ditugaskan oleh LSP.</p>
                </div>
                <hr class="my-2">
                <div>
                    <span class="badge bg-primary mb-1">Asesi</span>
                    <p class="mb-0 small">Saya setuju mengikuti asesmen dengan pemahaman bahwa informasi yang dikumpulkan hanya <strong>digunakan</strong> untuk pengembangan profesional dan hanya dapat diakses oleh orang tertentu saja.</p>
                </div>
            </div>
        </div>

        {{-- ── Tanda Tangan Asesi ── --}}
        @if($frak01->is_editable)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-pen me-2"></i>Tanda Tangan Asesi</h6>
            </div>
            <div class="card-body">
                @include('partials._signature_pad', [
                    'padId'    => 'asesi',
                    'padLabel' => 'Tanda Tangan Asesi',
                    'padHeight' => 200,
                ])

                <div class="form-check mt-3 mb-3">
                    <input class="form-check-input" type="checkbox" id="agreement-check">
                    <label class="form-check-label small" for="agreement-check">
                        Saya telah membaca dan memahami isi pernyataan di atas, serta menyetujui untuk mengikuti asesmen.
                    </label>
                </div>

                <button type="button" class="btn btn-success" id="btn-sign" onclick="submitSign()">
                    <i class="bi bi-check-circle me-1"></i> Tanda Tangan & Setujui
                </button>
            </div>
        </div>
        @else
        {{-- View TTD yang sudah ada --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light small fw-bold">Tanda Tangan Asesor</div>
                    <div class="card-body text-center">
                        @if($frak01->ttd_asesor)
                        <img src="{{ $frak01->ttd_asesor_image }}" style="max-height:80px; max-width:100%;" alt="TTD Asesor">
                        <div class="small text-muted mt-2">{{ $frak01->nama_ttd_asesor }}</div>
                        <div class="small text-muted">{{ $frak01->tanggal_ttd_asesor?->format('d M Y') }}</div>
                        @else
                        <span class="text-muted small">Belum ditandatangani</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light small fw-bold">Tanda Tangan Asesi</div>
                    <div class="card-body text-center">
                        @if($frak01->ttd_asesi)
                        <img src="{{ $frak01->ttd_asesi_image }}" style="max-height:80px; max-width:100%;" alt="TTD Asesi">
                        <div class="small text-muted mt-2">{{ $frak01->nama_ttd_asesi }}</div>
                        <div class="small text-muted">{{ $frak01->tanggal_ttd_asesi?->format('d M Y') }}</div>
                        @else
                        <span class="text-muted small">Belum ditandatangani</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="d-flex justify-content-between">
            <a href="{{ route('asesi.schedule') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

document.addEventListener('DOMContentLoaded', () => {
    SigPadManager.init('asesi');
});

async function submitSign() {
    if (!document.getElementById('agreement-check')?.checked) {
        Swal.fire({ icon: 'warning', title: 'Centang Persetujuan', text: 'Anda harus mencentang pernyataan persetujuan terlebih dahulu.' });
        return;
    }
    if (SigPadManager.isEmpty('asesi')) {
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Diperlukan', text: 'Mohon tanda tangan di kotak yang tersedia.' });
        return;
    }

    const result = await Swal.fire({
        title: 'Konfirmasi Tanda Tangan',
        text: 'Dengan menandatangani, Anda menyetujui pernyataan persetujuan asesmen dan kerahasiaan ini.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tanda Tangan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
    });
    if (!result.isConfirmed) return;

    const btn = document.getElementById('btn-sign');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const formData = new FormData();
    formData.append('signature', SigPadManager.getDataURL('asesi'));
    formData.append('nama_asesi', '{{ $asesmen->full_name }}');

    try {
        const res  = await fetch('{{ route("asesi.frak01.sign") }}', {
            method: 'POST', body: formData,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message })
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Tanda Tangan & Setujui';
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Tanda Tangan & Setujui';
    }
}
</script>
@endpush