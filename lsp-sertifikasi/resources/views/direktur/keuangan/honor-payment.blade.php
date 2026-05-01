@extends('layouts.app')
@section('title', 'Detail Kwitansi Honor')
@section('page-title', 'Detail Kwitansi Honor')

@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('direktur.keuangan.honor') }}">Honor Asesor</a></li>
        <li class="breadcrumb-item"><a href="{{ route('direktur.keuangan.honor.detail', $honor->asesor) }}">{{ $honor->asesor->nama }}</a></li>
        <li class="breadcrumb-item active">{{ $honor->nomor_kwitansi }}</li>
    </ol>
</nav>

<div class="row g-4">

    {{-- Kiri: Detail kwitansi --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                <span class="fw-semibold">{{ $honor->nomor_kwitansi }}</span>
                <span class="badge bg-{{ $honor->status_badge }} ms-auto">{{ $honor->status_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Asesor</div>
                        <div class="fw-semibold">{{ $honor->asesor->nama }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Tanggal Kwitansi</div>
                        <div>{{ optional($honor->tanggal_kwitansi)->translatedFormat('d F Y') }}</div>
                    </div>
                </div>

                {{-- Detail per jadwal --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Skema</th>
                                <th>TUK</th>
                                <th>Tanggal</th>
                                <th class="text-center">Asesi</th>
                                <th class="text-end">@Rp</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($honor->details as $i => $detail)
                            <tr>
                                <td class="text-muted small">{{ $i+1 }}</td>
                                <td class="small fw-semibold">{{ $detail->schedule->skema->name }}</td>
                                <td class="small">{{ $detail->schedule->tuk->name ?? '-' }}</td>
                                <td class="small">
                                    {{ optional($detail->schedule->assessment_date)->translatedFormat('d M Y') }}</td>
                                <td class="text-center small">{{ $detail->jumlah_asesi }}</td>
                                <td class="text-end small">{{ number_format($detail->honor_per_asesi, 0, ',', '.') }}</td>
                                <td class="text-end small fw-semibold">
                                    {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="6" class="fw-bold text-end">Total Honor</td>
                                <td class="text-end fw-bold">Rp {{ number_format($honor->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Download kwitansi --}}
                @if($honor->isDikonfirmasi())
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('direktur.keuangan.download.kwitansi-honor', ['honor' => $honor, 'preview' => 1]) }}"
                        target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Preview Kwitansi
                    </a>
                    <a href="{{ route('direktur.keuangan.download.kwitansi-honor', $honor) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-download me-1"></i>Download Kwitansi PDF
                    </a>
                </div>
                @else
                <div class="text-muted small fst-italic mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Kwitansi tersedia setelah asesor konfirmasi penerimaan.
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kanan: Bukti transfer (read only) + status alur --}}
    <div class="col-lg-5">

        {{-- Bukti Transfer --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt me-1 text-success"></i>Bukti Transfer
            </div>
            <div class="card-body">
                @if($honor->isSudahDibayar() || $honor->isDikonfirmasi())
                <div class="mb-3">
                    <div class="text-muted small">Dibayar pada</div>
                    <div>{{ optional($honor->dibayar_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>

                @php
                    $buktiPath  = $honor->bukti_transfer_path ?? null;
                    $buktiExt   = $buktiPath ? strtolower(pathinfo($buktiPath, PATHINFO_EXTENSION)) : null;
                    $buktiImage = in_array($buktiExt, ['jpg','jpeg','png']);
                    $buktiUrl = $buktiPath ? route('direktur.keuangan.download.bukti-honor', $honor) : null;
                @endphp

                @if($buktiUrl && $buktiImage)
                <div class="border rounded-3 overflow-hidden mb-3">
                    <div style="cursor:zoom-in;"
                         onclick="bukaZoomHonor('{{ $buktiUrl }}', '{{ $honor->nomor_kwitansi }}', '{{ $buktiUrl }}?download=1')">
                        <img src="{{ $buktiUrl }}"
                             class="w-100"
                             style="max-height:260px;object-fit:cover;transition:opacity .2s;"
                             onmouseover="this.style.opacity='.85'"
                             onmouseout="this.style.opacity='1'"
                             alt="Bukti Transfer">
                    </div>
                    <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between"
                         style="font-size:.75rem;">
                        <span class="text-muted"><i class="bi bi-receipt me-1"></i>Bukti Transfer</span>
                        <div class="d-flex gap-1">
                            <button class="btn btn-outline-secondary py-0 px-1" style="font-size:.7rem;"
                                    onclick="bukaZoomHonor('{{ $buktiUrl }}', '{{ $honor->nomor_kwitansi }}', '{{ $buktiUrl }}?download=1')"
                                    title="Perbesar">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                            <a href="{{ $buktiUrl }}?download=1"
                               class="btn btn-outline-primary py-0 px-1" style="font-size:.7rem;" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @elseif($buktiUrl)
                <div class="border rounded-3 overflow-hidden mb-3">
                    <div class="p-3 text-center bg-light">
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:3rem;"></i>
                        <div class="small text-muted mt-1">Bukti dalam format PDF</div>
                    </div>
                    <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between border-top"
                         style="font-size:.75rem;">
                        <span class="text-muted"><i class="bi bi-receipt me-1"></i>Bukti Transfer</span>
                        <a href="{{ $buktiUrl }}?download=1"
                           class="btn btn-outline-primary py-0 px-1" style="font-size:.7rem;">
                            <i class="bi bi-download me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
                @else
                <div class="text-muted small">Bukti transfer belum diupload.</div>
                @endif

                @else
                <div class="text-muted small">
                    <i class="bi bi-clock me-1"></i>
                    Menunggu bendahara upload bukti transfer.
                </div>
                @endif
            </div>
        </div>

        {{-- Status alur --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-arrow-right-circle me-1"></i>Status Alur
            </div>
            <div class="card-body py-2">
                @php
                $steps = [
                    ['label' => 'Kwitansi Dibuat', 'done' => true],
                    ['label' => 'Bukti Transfer Upload', 'done' => in_array($honor->status, ['sudah_dibayar','dikonfirmasi'])],
                    ['label' => 'Konfirmasi Asesor', 'done' => $honor->isDikonfirmasi()],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                    <i class="bi {{ $step['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                    <span class="small {{ $step['done'] ? 'fw-semibold' : 'text-muted' }}">{{ $step['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

{{-- Modal Zoom Bukti --}}
<div class="modal fade" id="modalZoomHonor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 pb-1 px-0">
                <span class="text-white fw-semibold" id="zoomHonorLabel" style="font-size:.9rem;"></span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomHonorImg" src="" class="img-fluid rounded-3 shadow"
                     style="max-height:85vh;object-fit:contain;">
            </div>
            <div class="modal-footer border-0 justify-content-center py-2">
                <a id="zoomHonorDownload" href="#" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i>Download
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<style>
#modalZoomHonor .modal-dialog { max-width:90vw; }
#modalZoomHonor { background:rgba(0,0,0,.85); }
</style>

@endsection

@push('scripts')
<script>
function bukaZoomHonor(src, label, downloadUrl) {
    document.getElementById('zoomHonorImg').src           = src;
    document.getElementById('zoomHonorLabel').textContent = label;
    document.getElementById('zoomHonorDownload').href     = downloadUrl;
    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalZoomHonor')
    ).show();
}
</script>
@endpush