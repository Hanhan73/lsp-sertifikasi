@extends('layouts.app')

@section('title', 'Edit Skema')
@section('page-title', 'Edit Skema - ' . $skema->name)

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Form Edit Skema</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.skemas.update', $skema) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Kode Skema -->
                    <div class="mb-3">
                        <label class="form-label">Kode Skema <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                            value="{{ old('code', $skema->code) }}" required>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kode unik untuk skema sertifikasi</small>
                    </div>

                    <!-- Nama Skema -->
                    <div class="mb-3">
                        <label class="form-label">Nama Skema <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $skema->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description"
                            rows="4">{{ old('description', $skema->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Biaya -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Biaya Sertifikasi (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control rupiah @error('fee') is-invalid @enderror"
                                    name="fee" value="{{ old('fee', $skema->fee) }}" required>
                            </div>
                            @error('fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Biaya default (tanpa pelatihan)</small>
                        </div>

                        <!-- Durasi -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi Proses (Hari) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('duration_days') is-invalid @enderror"
                                    name="duration_days" value="{{ old('duration_days', $skema->duration_days) }}"
                                    min="1" required>
                                <span class="input-group-text">hari</span>
                            </div>
                            @error('duration_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $skema->is_active) ? 'checked' : '' }} id="is_active">
                            <label class="form-check-label" for="is_active">
                                Skema Aktif
                            </label>
                        </div>
                        <small class="text-muted">Nonaktifkan skema jika tidak lagi digunakan</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update Skema
                        </button>
                        <a href="{{ route('admin.skemas') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6><i class="bi bi-info-circle"></i> Informasi Skema</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="120">Total Asesi:</td>
                        <td><strong>{{ $skema->asesmens->count() }}</strong></td>
                    </tr>
                    <tr>
                        <td>Tersertifikasi:</td>
                        <td>
                            <strong class="text-success">
                                {{ $skema->asesmens->where('status', 'certified')->count() }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Dibuat:</td>
                        <td>{{ $skema->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Update Terakhir:</td>
                        <td>{{ $skema->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>
                            @if($skema->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card bg-warning mt-3">
            <div class="card-body">
                <h6><i class="bi bi-exclamation-triangle"></i> Perhatian</h6>
                <p class="small mb-2">
                    Perubahan biaya hanya mempengaruhi asesi baru. Asesi yang sudah terdaftar tetap menggunakan biaya
                    lama.
                </p>
                <p class="small mb-0">
                    Jika skema dinonaktifkan, asesi tidak dapat memilih skema ini saat pendaftaran baru.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection