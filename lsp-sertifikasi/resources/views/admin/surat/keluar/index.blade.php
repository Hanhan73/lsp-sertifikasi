@extends('layouts.app')
@section('title', 'Agenda Surat Keluar')
@section('page-title', 'Agenda Surat Keluar')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-envelope-arrow-up me-2"></i>Buku Agenda Surat Keluar LSP KAP</h5>
        <a href="{{ route('admin.surat.keluar.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Surat
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 align-middle" style="font-size:.875rem">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:60px">No</th>
                        <th>Tanggal Agenda</th>
                        <th>No. Surat</th>
                        <th>Tanggal Surat</th>
                        <th>Kepada</th>
                        <th>Isi Ringkas</th>
                        <th class="text-center" style="width:110px">Dokumen</th>
                        <th class="text-center" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surats as $surat)
                    <tr>
                        <td class="text-center fw-semibold">{{ $surat->nomor_urut }}</td>
                        <td>{{ $surat->tanggal_agenda->format('d/m/Y') }}</td>
                        <td>{{ $surat->nomor_surat }}</td>
                        <td>{{ $surat->tanggal_surat->format('d/m/Y') }}</td>
                        <td>{{ $surat->kepada }}</td>
                        <td>{{ $surat->isi_ringkas }}</td>
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
                            <form action="{{ route('admin.surat.keluar.destroy', $surat) }}" method="POST" class="d-inline">
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
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size:2rem;display:block;opacity:.3;margin-bottom:.5rem"></i>
                            Belum ada data surat keluar.
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
document.querySelectorAll('.btn-preview').forEach(btn => {
    btn.addEventListener('click', function () {
        const url   = this.dataset.url;
        const mime  = this.dataset.mime;
        const label = this.dataset.label;

        document.getElementById('previewTitle').textContent = label;
        document.getElementById('previewDownloadBtn').href = url.replace('/preview', '/download');

        const container = document.getElementById('previewContainer');

        if (mime === 'application/pdf') {
            // Fetch dulu sebagai blob, lalu buat object URL — bypass CORS/header issue
            fetch(url)
                .then(r => r.blob())
                .then(blob => {
                    const blobUrl = URL.createObjectURL(blob);
                    container.innerHTML = `<embed src="${blobUrl}" type="application/pdf" width="100%" height="100%">`;
                })
                .catch(() => {
                    container.innerHTML = `<div class="d-flex align-items-center justify-content-center h-100 text-muted flex-column gap-2">
                        <i class="bi bi-exclamation-circle" style="font-size:2rem"></i>
                        <span>Gagal memuat PDF. <a href="${url.replace('/preview','/download')}">Download file</a></span>
                    </div>`;
                });
        } else {
            // Gambar langsung
            container.innerHTML = `<div class="d-flex justify-content-center align-items-center" style="width:100%;height:100%;background:#f8f9fa;overflow:auto">
                <img src="${url}" style="max-width:100%;max-height:100%;object-fit:contain" alt="Preview">
            </div>`;
        }

        new bootstrap.Modal(document.getElementById('modalPreview')).show();
    });
});

// Cleanup blob URL saat modal ditutup
document.getElementById('modalPreview').addEventListener('hidden.bs.modal', function () {
    const container = document.getElementById('previewContainer');
    const embed = container.querySelector('embed');
    if (embed) {
        URL.revokeObjectURL(embed.src);
    }
    container.innerHTML = '';
});
</script>
@endpush