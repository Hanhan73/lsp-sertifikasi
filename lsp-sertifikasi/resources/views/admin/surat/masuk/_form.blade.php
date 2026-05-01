@if($errors->any())
<div class="alert alert-danger py-2 mb-3">
    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label fw-semibold">No. Urut <span class="text-danger">*</span></label>
        <input type="number" name="nomor_urut" class="form-control @error('nomor_urut') is-invalid @enderror"
            value="{{ old('nomor_urut', $surat->nomor_urut ?? $nextNo ?? '') }}" required>
        @error('nomor_urut')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tanggal Agenda <span class="text-danger">*</span></label>
        <input type="date" name="tanggal_agenda" class="form-control @error('tanggal_agenda') is-invalid @enderror"
            value="{{ old('tanggal_agenda', $surat?->tanggal_agenda?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
        @error('tanggal_agenda')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label fw-semibold">No. Surat <span class="text-danger">*</span></label>
        <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror"
            value="{{ old('nomor_surat', $surat->nomor_surat ?? '') }}" required>
        @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tanggal Surat <span class="text-danger">*</span></label>
        <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror"
            value="{{ old('tanggal_surat', $surat?->tanggal_surat?->format('Y-m-d') ?? '') }}" required>
        @error('tanggal_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-8">
        <label class="form-label fw-semibold">Dari <span class="text-danger">*</span></label>
        <input type="text" name="dari" class="form-control @error('dari') is-invalid @enderror"
            value="{{ old('dari', $surat->dari ?? '') }}" required>
        @error('dari')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Isi Ringkas <span class="text-danger">*</span></label>
        <textarea name="isi_ringkas" rows="3" class="form-control @error('isi_ringkas') is-invalid @enderror"
            required>{{ old('isi_ringkas', $surat->isi_ringkas ?? '') }}</textarea>
        @error('isi_ringkas')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Upload Dokumen/Surat</label>
        @if($surat?->file_path)
        <div class="mb-2 d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-info btn-preview-form"
                data-url="{{ route('admin.surat.masuk.preview', $surat) }}"
                data-mime="{{ Storage::disk('public_html')->mimeType($surat->file_path) }}"
                data-label="Surat Masuk #{{ $surat->nomor_urut }}">
                <i class="bi bi-eye"></i> Preview
            </button>
            <a href="{{ route('admin.surat.masuk.download', $surat) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-download"></i> Download
            </a>
            <small class="text-muted">Upload baru untuk mengganti</small>
        </div>
        @endif
        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
            accept=".pdf,.jpg,.jpeg,.png">
        <div class="form-text">Format: PDF, JPG, PNG. Maks: 10 MB.</div>
        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>