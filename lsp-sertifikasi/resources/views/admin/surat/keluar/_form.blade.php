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
        <label class="form-label fw-semibold">Kepada <span class="text-danger">*</span></label>
        <input type="text" name="kepada" class="form-control @error('kepada') is-invalid @enderror"
            value="{{ old('kepada', $surat->kepada ?? '') }}" required>
        @error('kepada')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Kode Klasifikasi</label>
        <div class="row g-2">
            {{-- Step 1: Grup --}}
            <div class="col-md-4">
                <select id="selGrup" class="form-select" style="font-size:.85rem">
                    <option value="">— Pilih Grup —</option>
                    <option value="ADM">ADM — Administrasi</option>
                    <option value="OG">OG — Keorganisasian</option>
                    <option value="KU">KU — Keuangan</option>
                    <option value="SER">SER — Sertifikasi</option>
                    <option value="MT">MT — Mutu</option>
                </select>
            </div>
            {{-- Step 2: Sub grup --}}
            <div class="col-md-4">
                <select id="selSub" class="form-select" style="font-size:.85rem" disabled>
                    <option value="">— Pilih Sub —</option>
                </select>
            </div>
            {{-- Step 3: Item --}}
            <div class="col-md-4">
                <select id="selItem" class="form-select" style="font-size:.85rem" disabled>
                    <option value="">— Pilih Item (opsional) —</option>
                </select>
            </div>
        </div>
        {{-- Hasil kode --}}
        <div class="mt-2 d-flex align-items-center gap-2">
            <span class="text-muted small">Kode:</span>
            <span id="kodePreview" class="badge px-3 py-2" style="background:#eff6ff;color:#1d4ed8;font-size:.85rem;min-width:80px">—</span>
            <button type="button" id="btnResetKode" class="btn btn-sm btn-outline-secondary py-0 px-2" style="display:none">
                <i class="bi bi-x"></i> Reset
            </button>
        </div>
        <input type="hidden" name="kode_klasifikasi" id="inputKode"
            value="{{ old('kode_klasifikasi', $surat->kode_klasifikasi ?? '') }}">
        @error('kode_klasifikasi')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
                data-url="{{ route('admin.surat.keluar.preview', $surat) }}"
                data-mime="{{ Storage::disk('public_html')->mimeType($surat->file_path) }}"
                data-label="Surat Keluar #{{ $surat->nomor_urut }}">
                <i class="bi bi-eye"></i> Preview
            </button>
            <a href="{{ route('admin.surat.keluar.download', $surat) }}" class="btn btn-sm btn-outline-primary">
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