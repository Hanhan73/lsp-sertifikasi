@extends('layouts.app')
@section('title', 'Upload Sertifikat Fisik')
@section('page-title', 'Upload Sertifikat Fisik')
@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-cloud-upload text-primary me-2"></i>Upload Sertifikat Fisik
            </div>
            <div class="card-body">
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>
                    Sertifikat fisik Anda sudah didistribusikan oleh LSP. Silakan upload foto/scan sertifikat
                    beserta nomor sertifikat dan nomor adm untuk melengkapi arsip data Anda.
                </div>

                <form action="{{ route('asesi.sertifikat-fisik.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. Sertifikat <span class="text-danger">*</span></label>
                        <input type="text" name="no_sertifikat" class="form-control @error('no_sertifikat') is-invalid @enderror"
                            value="{{ old('no_sertifikat') }}" required>
                        @error('no_sertifikat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No. Adm <span class="text-danger">*</span></label>
                        <input type="text" name="no_adm" class="form-control @error('no_adm') is-invalid @enderror"
                            value="{{ old('no_adm') }}" required>
                        @error('no_adm')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Sertifikat (Foto/Scan) <span class="text-danger">*</span></label>
                        <input type="file" name="file_sertifikat" class="form-control @error('file_sertifikat') is-invalid @enderror"
                            accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text">Format PDF/JPG/PNG, maks 10MB.</div>
                        @error('file_sertifikat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i>Upload Sertifikat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection