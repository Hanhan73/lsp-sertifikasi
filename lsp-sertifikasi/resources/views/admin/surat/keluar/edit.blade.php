@extends('layouts.app')
@section('title', 'Edit Surat Keluar')
@section('page-title', 'Edit Surat Keluar')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Surat Keluar</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.surat.keluar.update', $surat) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.surat.keluar._form', ['surat' => $surat])
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Perbarui</button>
                <a href="{{ route('admin.surat.keluar.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
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
document.querySelectorAll('.btn-preview-form').forEach(btn => {
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
                        <span>Gagal memuat PDF. <a href="${url.replace('/preview','/download')}">Download file</a></span>
                    </div>`;
                });
        } else {
            container.innerHTML = `<div class="d-flex justify-content-center align-items-center" style="width:100%;height:100%;background:#f8f9fa;overflow:auto">
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