@extends('layouts.app')
@section('title', 'Daftar Pembayaran')
@section('page-title', 'Daftar Pembayaran Mandiri')

@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Cari nama / NIK…" value="{{ request('search') }}" style="max-width:220px;">
            <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
            @if(request('search') || request('status'))
            <a href="{{ route('direktur.keuangan.pembayaran-mandiri') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Asesi</th>
                    <th>Skema</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Tgl Upload</th>
                    <th>Dokumen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                @php
                    $ext        = $p->proof_path ? strtolower(pathinfo($p->proof_path, PATHINFO_EXTENSION)) : null;
                    $buktiImage = in_array($ext, ['jpg','jpeg','png']);
                    $buktiUrl   = $p->proof_path
                        ? route('direktur.keuangan.download.bukti-mandiri', $p)
                        : null;
                @endphp
                <tr>
                    <td class="text-muted small">{{ $p->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $p->asesmen->full_name }}</div>
                        <div class="small text-muted">{{ $p->asesmen->tuk->name ?? '-' }}</div>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ Str::limit($p->asesmen->skema->name ?? '-', 30) }}</span></td>
                    <td><span class="badge bg-secondary">{{ strtoupper($p->method) }}</span></td>
                    <td class="fw-semibold text-success">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td>
                        @if($buktiUrl && $buktiImage)
                        <div class="border rounded-2 overflow-hidden" style="max-width:80px;">
                            <div style="cursor:zoom-in;"
                                 onclick="bukaZoom('{{ $buktiUrl }}', '#{{ $p->id }} — {{ $p->asesmen->full_name }}', '{{ $buktiUrl }}?download=1')">
                                <img src="{{ $buktiUrl }}"
                                     style="width:80px;height:60px;object-fit:cover;transition:opacity .2s;"
                                     onmouseover="this.style.opacity='.8'"
                                     onmouseout="this.style.opacity='1'"
                                     alt="Bukti">
                            </div>
                        </div>
                        @elseif($buktiUrl)
                        <a href="{{ $buktiUrl }}?download=1" class="btn btn-sm btn-outline-secondary" target="_blank">
                            <i class="bi bi-file-earmark-pdf text-danger"></i>
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($p->status === 'pending')
                        <span class="badge bg-warning text-dark">Menunggu</span>
                        @elseif($p->status === 'verified')
                        <span class="badge bg-success">Terverifikasi</span>
                        @else
                        <span class="badge bg-danger">Ditolak</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                    <td>
                        @if($p->status === 'verified')
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-download"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="{{ route('direktur.keuangan.download.invoice-mandiri', $p) }}">
                                        <i class="bi bi-file-earmark-text me-1"></i>Invoice
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="{{ route('direktur.keuangan.download.kwitansi-mandiri', [$p, 'versi' => 'berisi']) }}">
                                        <i class="bi bi-file-earmark-check me-1"></i>Kwitansi (TTD)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" target="_blank"
                                       href="{{ route('direktur.keuangan.download.kwitansi-mandiri', [$p, 'versi' => 'kosong']) }}">
                                        <i class="bi bi-file-earmark me-1"></i>Kwitansi (kosong)
                                    </a>
                                </li>
                                @if($buktiUrl)
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ $buktiUrl }}?download=1">
                                        <i class="bi bi-image me-1"></i>Bukti Transfer
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @elseif($buktiUrl)
                        <a href="{{ $buktiUrl }}?download=1" class="btn btn-sm btn-outline-secondary" title="Download bukti">
                            <i class="bi bi-download"></i>
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Tidak ada data pembayaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="card-footer bg-white">
        {{ $payments->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Modal Zoom Bukti --}}
<div class="modal fade" id="modalZoomPembayaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 pb-1 px-0">
                <span class="text-white fw-semibold" id="zoomPembayaranLabel" style="font-size:.9rem;"></span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="zoomPembayaranImg" src="" class="img-fluid rounded-3 shadow"
                     style="max-height:85vh;object-fit:contain;">
            </div>
            <div class="modal-footer border-0 justify-content-center pt-2">
                <a id="zoomPembayaranDownload" href="#" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i>Unduh
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<style>
#modalZoomPembayaran .modal-dialog { max-width:90vw; }
#modalZoomPembayaran { background:rgba(0,0,0,.85); }
</style>

@endsection

@push('scripts')
<script>
function bukaZoom(src, label, downloadUrl) {
    document.getElementById('zoomPembayaranImg').src           = src;
    document.getElementById('zoomPembayaranLabel').textContent = label;
    document.getElementById('zoomPembayaranDownload').href     = downloadUrl;
    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalZoomPembayaran')
    ).show();
}
</script>
@endpush