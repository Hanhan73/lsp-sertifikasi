@extends('layouts.app')
@section('title', 'FR.AK.01 - Persetujuan Asesmen')
@section('page-title', 'FR.AK.01 - Persetujuan Asesmen dan Kerahasiaan')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@push('styles')
<style>
.info-row td { padding: 7px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.info-row td:first-child { color: #64748b; width: 35%; font-size: .9rem; }
.status-badge-wrap { display: inline-flex; align-items: center; gap: 6px; }
.cb-item { display: flex; align-items: center; gap: 8px; padding: 5px 0; }
.cb-item input[type="checkbox"] { width: 17px; height: 17px; cursor: pointer; }
</style>
@endpush

@section('content')

{{-- ── Status Banner ── --}}
@if(in_array($frak01->status, ['verified','approved']))
<div class="alert alert-success d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div><strong>FR.AK.01 sudah selesai</strong> — kedua pihak telah menandatangani.</div>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('asesor.frak01.pdf', [$schedule, $asesmen]) }}" target="_blank" class="btn btn-sm btn-success">
            <i class="bi bi-file-pdf me-1"></i> Lihat PDF
        </a>
    </div>
</div>
@elseif($frak01->status === 'submitted')
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-hourglass-split fs-4"></i>
    <div><strong>Asesi sudah menandatangani</strong> — menunggu tanda tangan Asesor.</div>
</div>
@elseif($frak01->status === 'draft')
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-exclamation-triangle fs-4"></i>
    <div><strong>Menunggu tanda tangan Asesi</strong></div>
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

        {{-- ── Info Skema & Jadwal (read-only untuk asesor) ── --}}
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
                            <td>{{ $frak01->nama_asesor ?? $asesor->nama ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Nama Asesi</td>
                            <td>{{ $frak01->nama_asesi ?? $asesmen->full_name ?? '-' }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Hari / Tanggal</td>
                            <td>{{ $frak01->hari_tanggal ?? $schedule->assessment_date->translatedFormat('l, d F Y') }}</td>
                        </tr>
                        <tr class="info-row">
                            <td>Waktu</td>
                            <td>{{ $frak01->waktu_asesmen ?? ($schedule->start_time . ($schedule->end_time ? ' – ' . $schedule->end_time : '')) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Bukti (asesor bisa update jika masih draft) ── --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Bukti yang Akan Dikumpulkan</h6>
            </div>
            <div class="card-body">
                @if($frak01->status === 'draft')
                <p class="small text-muted mb-3">Centang bukti yang akan dikumpulkan dalam proses asesmen ini, lalu simpan.</p>
                <form id="bukti-form">
                    @csrf
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
                        ];
                        @endphp
                        @foreach($buktis as $b)
                        <div class="col-md-6">
                            <div class="cb-item">
                                <input type="checkbox" name="{{ $b['field'] }}" id="{{ $b['field'] }}"
                                    {{ $frak01->{$b['field']} ? 'checked' : '' }}>
                                <label for="{{ $b['field'] }}">{{ $b['label'] }}</label>
                            </div>
                        </div>
                        @endforeach
                        <div class="col-md-6">
                            <div class="cb-item">
                                <input type="checkbox" name="bukti_lainnya" id="bukti_lainnya"
                                    {{ $frak01->bukti_lainnya ? 'checked' : '' }}
                                    onchange="document.getElementById('lainnya-text').style.display = this.checked ? 'block' : 'none'">
                                <label for="bukti_lainnya">Lainnya</label>
                            </div>
                            <input type="text" name="bukti_lainnya_keterangan" id="lainnya-text"
                                class="form-control form-control-sm mt-1"
                                style="{{ $frak01->bukti_lainnya ? '' : 'display:none;' }}"
                                placeholder="Sebutkan bukti lainnya..."
                                value="{{ $frak01->bukti_lainnya_keterangan }}">
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm mt-3" onclick="saveBukti()">
                        <i class="bi bi-save me-1"></i> Simpan Bukti
                    </button>
                </form>
                @else
                <div class="row g-2">
                    @php
                    $allBuktis = [
                        ['field' => 'bukti_verifikasi_portofolio',     'label' => 'Hasil Verifikasi Portofolio'],
                        ['field' => 'bukti_hasil_review_produk',        'label' => 'Hasil Review Produk'],
                        ['field' => 'bukti_observasi_langsung',         'label' => 'Hasil Observasi Langsung'],
                        ['field' => 'bukti_hasil_kegiatan_terstruktur', 'label' => 'Hasil Kegiatan Terstruktur'],
                        ['field' => 'bukti_pertanyaan_lisan',           'label' => 'Hasil Pertanyaan Lisan'],
                        ['field' => 'bukti_pertanyaan_tertulis',        'label' => 'Hasil Pertanyaan Tertulis'],
                        ['field' => 'bukti_lainnya',                    'label' => 'Lainnya' . ($frak01->bukti_lainnya_keterangan ? ': ' . $frak01->bukti_lainnya_keterangan : '')],
                        ['field' => 'bukti_pertanyaan_wawancara',       'label' => 'Hasil Pertanyaan Wawancara'],
                    ];
                    @endphp
                    @foreach($allBuktis as $b)
                    <div class="col-md-6 d-flex align-items-center gap-2">
                        <i class="bi {{ $frak01->{$b['field']} ? 'bi-check-square-fill text-primary' : 'bi-square text-muted' }}"></i>
                        <span class="{{ $frak01->{$b['field']} ? '' : 'text-muted' }}">{{ $b['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
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
                        <img src="{{ $frak01->ttd_asesi_image }}" style="max-height:80px; max-width:100%;" alt="TTD Asesi">
                        <div class="small fw-semibold mt-2">{{ $frak01->nama_ttd_asesi }}</div>
                        <div class="small text-muted">{{ $frak01->tanggal_ttd_asesi?->format('d M Y H:i') }}</div>
                        @else
                        <div class="text-muted small py-4">Menunggu tanda tangan asesi</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TTD Asesor --}}
            <div class="col-md-6">
                <div class="card h-100">
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
                            <img src="{{ $frak01->ttd_asesor_image }}" style="max-height:80px; max-width:100%;" alt="TTD Asesor">
                            <div class="small fw-semibold mt-2">{{ $frak01->nama_ttd_asesor }}</div>
                            <div class="small text-muted">{{ $frak01->tanggal_ttd_asesor?->format('d M Y H:i') }}</div>
                        </div>
                        @elseif($frak01->status === 'submitted')
                        {{-- Asesor belum TTD tapi asesi sudah -- tampilkan form TTD asesor --}}
                        @include('partials._signature_pad', [
                            'padId'    => 'asesor',
                            'padLabel' => 'Tanda Tangan Asesor',
                            'padHeight' => 180,
                        ])
                        <button class="btn btn-success btn-sm mt-3 w-100" onclick="signAsesor()">
                            <i class="bi bi-pen me-1"></i> Tanda Tangan Asesor
                        </button>
                        @else
                        <div class="text-muted small text-center py-4">
                            Tersedia setelah asesi menandatangani
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('asesor.schedule.detail', $schedule) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            @if(in_array($frak01->status, ['verified','approved']))
            <a href="{{ route('asesor.frak01.pdf', [$schedule, $asesmen]) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-file-pdf me-1"></i> Lihat / Download PDF
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

document.addEventListener('DOMContentLoaded', () => {
    @if($frak01->status === 'submitted')
    SigPadManager.init('asesor');
    SigPadManager.init('asesor-apl02');
    @endif
});

async function saveBukti() {
    const form = document.getElementById('bukti-form');
    const data = new FormData(form);

    try {
        const res  = await fetch('{{ route("asesor.frak01.bukti", [$schedule, $asesmen]) }}', {
            method: 'POST', body: data,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const json = await res.json();
        if (json.success) {
            Swal.fire({ icon: 'success', title: 'Tersimpan!', text: 'Data bukti berhasil disimpan.', timer: 1500, showConfirmButton: false });
        } else {
            Swal.fire('Gagal', json.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
}

async function signAsesor() {
    if (SigPadManager.isEmpty('asesor')) {
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Diperlukan', text: 'Mohon tanda tangan di kotak yang tersedia.' });
        return;
    }

    const result = await Swal.fire({
        title: 'Konfirmasi Tanda Tangan',
        text: 'Anda akan menandatangani FR.AK.01 sebagai Asesor.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Tanda Tangan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
    });
    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('signature',   SigPadManager.getDataURL('asesor'));
    formData.append('nama_asesor', '{{ $asesor->nama }}');

    try {
        const res  = await fetch('{{ route("asesor.frak01.sign", [$schedule, $asesmen]) }}', {
            method: 'POST', body: formData,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message })
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
    }
}
</script>
@endpush