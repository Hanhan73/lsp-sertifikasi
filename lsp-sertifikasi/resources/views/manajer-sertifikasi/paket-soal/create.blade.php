@extends('layouts.app')

@section('title', 'Upload Paket Soal')
@section('breadcrumb', 'Bank Soal › Paket Soal › Upload')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <a href="{{ route('manajer-sertifikasi.paket-soal.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h4 class="fw-bold mb-1">Upload Paket Soal</h4>
    <p class="text-muted mb-0" style="font-size:.875rem">Upload file PDF paket soal untuk skema tertentu.</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-cloud-upload text-warning me-2"></i>Form Upload</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('manajer-sertifikasi.paket-soal.store') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Skema Sertifikasi <span
                                class="text-danger">*</span></label>
                        <select name="skema_id" class="form-select @error('skema_id') is-invalid @enderror" required>
                            <option value="">— Pilih Skema —</option>
                            @foreach($skemas as $sk)
                            <option value="{{ $sk->id }}" {{ old('skema_id') == $sk->id ? 'selected' : '' }}>
                                {{ $sk->name }} ({{ $sk->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('skema_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Paket Soal <span
                                class="text-danger">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul') }}"
                            class="form-control @error('judul') is-invalid @enderror" placeholder="cth: Paket Soal A"
                            required>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">File PDF <span class="text-danger">*</span></label>
                        <div class="border rounded-3 p-4 text-center bg-light"
                            onclick="document.getElementById('fileInputPaket').click()"
                            style="cursor:pointer;border-style:dashed!important">
                            <i class="bi bi-folder2-open text-warning" style="font-size:2rem"></i>
                            <div class="mt-2 fw-semibold" style="font-size:.875rem" id="dropzoneLabelPaket">
                                Klik untuk pilih file PDF
                            </div>
                            <small class="text-muted">Format: PDF · Maks. 10 MB</small>
                            <input type="file" name="file" id="fileInputPaket" accept=".pdf" class="d-none"
                                onchange="previewFilePaket(this)" required>
                        </div>
                        @error('file') <div class="text-danger mt-1" style="font-size:.875rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-upload me-1"></i> Upload & Simpan
                        </button>
                        <a href="{{ route('manajer-sertifikasi.paket-soal.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function previewFilePaket(input) {
    const label = document.getElementById('dropzoneLabelPaket');
    if (input.files && input.files[0]) {
        const f = input.files[0];
        label.innerHTML =
            `<i class="bi bi-check-circle-fill text-success me-1"></i>${f.name} (${(f.size/1024/1024).toFixed(2)} MB)`;
    }
}
</script>
@endpush@extends('layouts.app')

@section('title', 'Upload Paket Soal')
@section('breadcrumb', 'Bank Soal › Paket Soal › Upload')

@section('content')

<div class="mb-4">
    <a href="{{ route('manajer-sertifikasi.paket-soal.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h4 class="fw-bold mb-1">Upload Paket Soal</h4>
    <p class="text-muted mb-0" style="font-size:.875rem">Upload file PDF paket soal untuk skema tertentu.</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="bi bi-cloud-upload text-warning me-2"></i>Form Upload</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('manajer-sertifikasi.paket-soal.store') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Skema Sertifikasi <span
                                class="text-danger">*</span></label>
                        <select name="skema_id" class="form-select @error('skema_id') is-invalid @enderror" required>
                            <option value="">— Pilih Skema —</option>
                            @foreach($skemas as $sk)
                            <option value="{{ $sk->id }}" {{ old('skema_id') == $sk->id ? 'selected' : '' }}>
                                {{ $sk->name }} ({{ $sk->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('skema_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Paket Soal <span
                                class="text-danger">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul') }}"
                            class="form-control @error('judul') is-invalid @enderror" placeholder="cth: Paket Soal A"
                            required>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">File PDF <span class="text-danger">*</span></label>
                        <div class="border rounded-3 p-4 text-center bg-light"
                            onclick="document.getElementById('fileInputPaket').click()"
                            style="cursor:pointer;border-style:dashed!important">
                            <i class="bi bi-folder2-open text-warning" style="font-size:2rem"></i>
                            <div class="mt-2 fw-semibold" style="font-size:.875rem" id="dropzoneLabelPaket">
                                Klik untuk pilih file PDF
                            </div>
                            <small class="text-muted">Format: PDF · Maks. 10 MB</small>
                            <input type="file" name="file" id="fileInputPaket" accept=".pdf" class="d-none"
                                onchange="previewFilePaket(this)" required>
                        </div>
                        @error('file') <div class="text-danger mt-1" style="font-size:.875rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-upload me-1"></i> Upload & Simpan
                        </button>
                        <a href="{{ route('manajer-sertifikasi.paket-soal.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function previewFilePaket(input) {
    const label = document.getElementById('dropzoneLabelPaket');
    if (input.files && input.files[0]) {
        const f = input.files[0];
        label.innerHTML =
            `<i class="bi bi-check-circle-fill text-success me-1"></i>${f.name} (${(f.size/1024/1024).toFixed(2)} MB)`;
    }
}
</script>
@endpush