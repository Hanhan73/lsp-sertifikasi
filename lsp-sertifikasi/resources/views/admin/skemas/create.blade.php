@extends('layouts.app')

@section('title', 'Tambah Skema')
@section('page-title', 'Tambah Skema Sertifikasi Baru')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Form Tambah Skema</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.skemas.store') }}" method="POST">
                    @csrf

                    <!-- Kode Skema -->
                    <div class="mb-3">
                        <label class="form-label">Kode Skema <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                            value="{{ old('code') }}" placeholder="Contoh: SKM-TI-001" required>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kode unik untuk skema sertifikasi</small>
                    </div>

                    <!-- Nama Skema -->
                    <div class="mb-3">
                        <label class="form-label">Nama Skema <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description"
                            rows="4"
                            placeholder="Deskripsi lengkap tentang skema sertifikasi ini">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Jelaskan kompetensi yang akan diuji dalam skema ini</small>
                    </div>

                    <div class="row">
                        <!-- Biaya -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Biaya Sertifikasi (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control rupiah @error('fee') is-invalid @enderror"
                                    name="fee" value="{{ old('fee', isset($skema) ? $skema->fee : '') }}" required>
                            </div>
                            @error('fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Biaya default untuk skema ini (tidak termasuk pelatihan)</small>
                        </div>

                        <!-- Durasi -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi Proses (Hari) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                    name="duration_days" value="{{ old('duration_days') }}" min="1" placeholder="30"
                                    required>
                                <span class="input-group-text">hari</span>
                            </div>
                            @error('duration_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Estimasi waktu penyelesaian sertifikasi</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked
                                id="is_active">
                            <label class="form-check-label" for="is_active">
                                Aktifkan Skema
                            </label>
                        </div>
                        <small class="text-muted">Skema aktif dapat dipilih saat pendaftaran</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Skema
                        </button>
                        <a href="{{ route('admin.skemas') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6><i class="bi bi-info-circle"></i> Panduan</h6>
                <ul class="small mb-0">
                    <li><strong>Kode Skema:</strong> Harus unik, gunakan format yang konsisten (contoh: SKM-TI-001)</li>
                    <li><strong>Nama Skema:</strong> Nama resmi sesuai SKKNI atau standar yang digunakan</li>
                    <li><strong>Biaya:</strong> Biaya default tanpa pelatihan. Admin dapat menyesuaikan saat verifikasi
                    </li>
                    <li><strong>Durasi:</strong> Estimasi waktu dari pendaftaran hingga sertifikat terbit</li>
                </ul>
            </div>
        </div>

        <div class="card bg-primary text-white mt-3">
            <div class="card-body">
                <h6><i class="bi bi-lightbulb"></i> Tips</h6>
                <p class="small mb-0">
                    Pastikan kode dan nama skema sesuai dengan dokumen resmi SKKNI atau standar kompetensi yang
                    digunakan.
                </p>
            </div>
        </div>

        <div class="card bg-success text-white mt-3">
            <div class="card-body">
                <h6><i class="bi bi-calculator"></i> Biaya Tambahan</h6>
                <p class="small mb-0">
                    Biaya pelatihan (Rp 1.500.000) akan ditambahkan otomatis jika asesi memilih mengikuti pelatihan.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection