@extends('layouts.app')
@section('title', 'Agenda Surat Keluar')
@section('page-title', 'Agenda Surat Keluar')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

{{-- ══ PANEL KODE KLASIFIKASI ══ --}}
<div class="card mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center" style="cursor:pointer"
         data-bs-toggle="collapse" data-bs-target="#panelKlasifikasi">
        <span class="fw-semibold" style="font-size:.9rem">
            <i class="bi bi-tag me-2 text-primary"></i>Kode Klasifikasi Surat LSP-KAP
        </span>
        <i class="bi bi-chevron-down text-muted" id="iconKlasifikasi"></i>
    </div>
    <div class="collapse" id="panelKlasifikasi">
        <div class="card-body pt-2 pb-3">
            <div class="row g-3">
                @foreach($kodeKlasifikasi as $kode => $grup)
                <div class="col-md-6 col-xl-4">
                    <div class="border rounded p-2" style="font-size:.8rem">
                        <div class="fw-bold mb-1" style="color:#1d4ed8">
                            <span class="badge me-1" style="background:#dbeafe;color:#1d4ed8;font-size:.75rem">{{ $kode }}</span>
                            {{ $grup['label'] }}
                        </div>
                        @foreach($grup['sub'] as $subKode => $sub)
                        <div class="ms-2 mb-1">
                            <span class="text-muted fw-semibold">{{ $kode }}.{{ $subKode }}</span>
                            — {{ $sub['label'] }}
                            @if(!empty($sub['items']))
                            <div class="ms-3">
                                @foreach($sub['items'] as $itemKode => $item)
                                <div class="text-muted">
                                    <span class="fw-semibold" style="color:#6b7280">{{ $kode }}.{{ $subKode }}.{{ $itemKode }}</span>
                                    {{ $item }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══ REKAP PER TAHUN ══ --}}
<div class="card mb-3">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-semibold" style="font-size:.9rem;cursor:pointer"
                  data-bs-toggle="collapse" data-bs-target="#panelRekap">
                <i class="bi bi-bar-chart-line me-2 text-success"></i>Rekap Surat Keluar
                <span class="badge bg-success ms-1">{{ $surats->count() }} surat</span>
                <i class="bi bi-chevron-down text-muted ms-1" style="font-size:.8rem"></i>
            </span>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Filter tahun --}}
                <form method="GET" action="{{ route('admin.surat.keluar.index') }}"
                      class="d-flex align-items-center gap-2 mb-0">
                    <select name="tahun" class="form-select form-select-sm" style="width:100px"
                            onchange="this.form.submit()">
                        @foreach($tahunList as $t)
                        <option value="{{ $t }}" @selected($t == $tahun)>{{ $t }}</option>
                        @endforeach
                    </select>
                </form>
                {{-- Export tahunan --}}
                <a href="{{ route('admin.surat.keluar.rekap.export', ['tahun' => $tahun]) }}"
                   class="btn btn-sm btn-success">
                    <i class="bi bi-file-excel"></i> Export Tahunan
                </a>
                <span class="text-muted small">|</span>
                {{-- Export per bulan --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-success dropdown-toggle"
                            data-bs-toggle="dropdown">
                        <i class="bi bi-file-excel"></i> Export per Bulan
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach($bulanList as $i => $namaBulan)
                        @php $cnt = $rekap->get($i + 1, collect())->count(); @endphp
                        <li>
                            <a class="dropdown-item d-flex justify-content-between align-items-center"
                               style="font-size:.85rem"
                               href="{{ route('admin.surat.keluar.rekap.export-bulan', ['tahun' => $tahun, 'bulan' => $i + 1]) }}">
                                <span>{{ $namaBulan }} {{ $tahun }}</span>
                                @if($cnt > 0)
                                <span class="badge bg-primary ms-3">{{ $cnt }}</span>
                                @endif
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="collapse show" id="panelRekap">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 align-middle" style="font-size:.8rem">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:120px">Bulan</th>
                            <th class="text-center" style="width:80px">Jumlah</th>
                            <th>Kode Klasifikasi</th>
                            <th class="text-center" style="width:130px">Bukti Surat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalAll = 0; @endphp
                        @foreach($bulanList as $i => $namaBulan)
                        @php
                            $bulanNo  = $i + 1;
                            $data     = $rekap->get($bulanNo, collect());
                            $jumlah   = $data->count();
                            $totalAll += $jumlah;
                            $kodes    = $data->pluck('kode_klasifikasi')->filter()->countBy()->sortDesc();
                        @endphp
                        <tr @if($jumlah === 0) class="text-muted" @endif>
                            <td class="fw-semibold">{{ $namaBulan }}</td>
                            <td class="text-center">
                                @if($jumlah > 0)
                                    <span class="badge bg-primary">{{ $jumlah }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($kodes->isNotEmpty())
                                    @foreach($kodes as $k => $cnt)
                                    <span class="badge me-1 mb-1"
                                          style="background:#eff6ff;color:#1d4ed8;font-weight:500">
                                        {{ $k }}
                                        <span class="badge bg-primary ms-1"
                                              style="font-size:.65rem">{{ $cnt }}</span>
                                    </span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($jumlah > 0)
                                    <a href="#daftar-{{ $bulanNo }}"
                                       class="btn btn-outline-secondary py-0 px-2"
                                       style="font-size:.75rem"
                                       title="Scroll ke surat bulan {{ $namaBulan }}">
                                        <i class="bi bi-list-ul"></i> {{ $jumlah }} surat
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>Total {{ $tahun }}</td>
                            <td class="text-center">
                                <span class="badge bg-dark">{{ $totalAll }}</span>
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Anchor target per bulan — harus ada sebelum tabel utama --}}
@foreach(range(1, 12) as $bulanNo)
<div id="daftar-{{ $bulanNo }}" style="height:0;overflow:hidden;margin-top:-70px;padding-top:70px"></div>
@endforeach

{{-- ══ TABEL UTAMA ══ --}}
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-envelope-arrow-up me-2"></i>Buku Agenda Surat Keluar LSP KAP
            @if(request('tahun'))
            <span class="badge bg-secondary ms-1" style="font-size:.75rem">{{ $tahun }}</span>
            @endif
        </h5>
        <a href="{{ route('admin.surat.keluar.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Surat
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 align-middle" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:55px">No</th>
                        <th style="width:110px">Tgl Agenda</th>
                        <th>No. Surat</th>
                        <th style="width:100px">Tgl Surat</th>
                        <th>Kepada</th>
                        <th>Isi Ringkas</th>
                        <th style="width:90px">Kode</th>
                        <th class="text-center" style="width:100px">Dokumen</th>
                        <th class="text-center" style="width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $bulanAktif = null; @endphp
                    @forelse($surats as $surat)
                    @php $bulanSurat = $surat->tanggal_agenda->month; @endphp

                    {{-- Separator baris bulan --}}
                    @if($bulanAktif !== $bulanSurat)
                    @php $bulanAktif = $bulanSurat; @endphp
                    <tr>
                        <td colspan="9" class="py-1 px-3"
                            style="background:#f0f7ff;font-size:.75rem;font-weight:600;color:#1d4ed8;border-left:3px solid #2563eb">
                            {{ $bulanList[$bulanSurat - 1] }} {{ $surat->tanggal_agenda->year }}
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td class="text-center fw-semibold">{{ $surat->nomor_urut }}</td>
                        <td>{{ $surat->tanggal_agenda->format('d/m/Y') }}</td>
                        <td style="font-size:.8rem">{{ $surat->nomor_surat }}</td>
                        <td>{{ $surat->tanggal_surat->format('d/m/Y') }}</td>
                        <td>{{ $surat->kepada }}</td>
                        <td>{{ $surat->isi_ringkas }}</td>
                        <td>
                            @if($surat->kode_klasifikasi)
                            <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-weight:500;font-size:.75rem">
                                {{ $surat->kode_klasifikasi }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($surat->file_path)
                                <button type="button" class="btn btn-sm btn-outline-info btn-preview"
                                    data-url="{{ route('admin.surat.keluar.preview', $surat) }}"
                                    data-mime="{{ Storage::disk('public_html')->mimeType($surat->file_path) }}"
                                    data-label="Surat Keluar #{{ $surat->nomor_urut }}"
                                    title="Preview">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="{{ route('admin.surat.keluar.download', $surat) }}"
                                   class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.surat.keluar.edit', $surat) }}"
                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.surat.keluar.destroy', $surat) }}"
                                  method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Hapus surat ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"
                               style="font-size:2rem;display:block;opacity:.3;margin-bottom:.5rem"></i>
                            Belum ada data surat keluar tahun {{ $tahun }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Preview --}}
<div class="modal fade" id="modalPreview" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height:90vh">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">
                    <i class="bi bi-file-earmark-text me-1"></i><span id="previewTitle"></span>
                </h6>
                <div class="ms-auto d-flex gap-2 align-items-center">
                    <a href="#" id="previewDownloadBtn" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn-close ms-1" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0" style="overflow:hidden;height:calc(90vh - 53px)">
                <div id="previewContainer" style="width:100%;height:100%"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Chevron klasifikasi ──
document.getElementById('panelKlasifikasi').addEventListener('show.bs.collapse', () => {
    document.getElementById('iconKlasifikasi').className = 'bi bi-chevron-up text-muted';
});
document.getElementById('panelKlasifikasi').addEventListener('hide.bs.collapse', () => {
    document.getElementById('iconKlasifikasi').className = 'bi bi-chevron-down text-muted';
});

// ── Smooth scroll untuk link bukti surat ──
document.querySelectorAll('a[href^="#daftar-"]').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ── Preview ──
document.querySelectorAll('.btn-preview').forEach(btn => {
    btn.addEventListener('click', function () {
        const url   = this.dataset.url;
        const mime  = this.dataset.mime;
        const label = this.dataset.label;

        document.getElementById('previewTitle').textContent = label;
        document.getElementById('previewDownloadBtn').href = url.replace('/preview', '/download');

        const container = document.getElementById('previewContainer');

        if (mime === 'application/pdf') {
            fetch(url)
                .then(r => r.blob())
                .then(blob => {
                    const blobUrl = URL.createObjectURL(blob);
                    container.innerHTML = `<embed src="${blobUrl}" type="application/pdf" width="100%" height="100%">`;
                })
                .catch(() => {
                    container.innerHTML = `<div class="d-flex align-items-center justify-content-center h-100 text-muted flex-column gap-2">
                        <i class="bi bi-exclamation-circle" style="font-size:2rem"></i>
                        <span>Gagal memuat PDF.
                            <a href="${url.replace('/preview', '/download')}">Download file</a>
                        </span>
                    </div>`;
                });
        } else {
            container.innerHTML = `<div class="d-flex justify-content-center align-items-center"
                style="width:100%;height:100%;background:#f8f9fa;overflow:auto">
                <img src="${url}" style="max-width:100%;max-height:100%;object-fit:contain" alt="Preview">
            </div>`;
        }

        new bootstrap.Modal(document.getElementById('modalPreview')).show();
    });
});

document.getElementById('modalPreview').addEventListener('hidden.bs.modal', function () {
    const container = document.getElementById('previewContainer');
    const embed = container.querySelector('embed');
    if (embed) URL.revokeObjectURL(embed.src);
    container.innerHTML = '';
});
</script>
@endpush