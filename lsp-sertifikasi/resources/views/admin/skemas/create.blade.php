@extends('layouts.app')
@section('title', 'Tambah Skema')
@section('page-title', 'Tambah Skema Baru')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Form Tambah Skema</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.skemas.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- ── IDENTITAS SKEMA ── --}}
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-file-earmark-text"></i> Identitas Skema
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Kode Skema <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code') }}" placeholder="SK-001" required>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Skema <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Jenis Skema <span class="text-danger">*</span></label>
                            <select name="jenis_skema" class="form-select @error('jenis_skema') is-invalid @enderror"
                                required>
                                <option value="">Pilih Jenis</option>
                                <option value="klaster" {{ old('jenis_skema') === 'klaster'  ? 'selected' : '' }}>
                                    Klaster</option>
                                <option value="okupasi" {{ old('jenis_skema') === 'okupasi'  ? 'selected' : '' }}>
                                    Okupasi</option>
                                <option value="kkni" {{ old('jenis_skema') === 'kkni'     ? 'selected' : '' }}>KKNI
                                </option>
                            </select>
                            @error('jenis_skema')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nomor Skema</label>
                            <input type="text" name="nomor_skema" class="form-control" value="{{ old('nomor_skema') }}"
                                placeholder="01/LSP-KAP/IX/2025">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Biaya (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="fee" class="form-control @error('fee') is-invalid @enderror"
                                value="{{ old('fee') }}" min="0" step="1000" required>
                            @error('fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Durasi (hari) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_days"
                                class="form-control @error('duration_days') is-invalid @enderror"
                                value="{{ old('duration_days', 3) }}" min="1" required>
                            @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control"
                                rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    {{-- ── DOKUMEN PENGESAHAN ── --}}
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                        <i class="bi bi-file-earmark-check"></i> Dokumen Pengesahan
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label">Tanggal Pengesahan</label>
                            <input type="date" name="tanggal_pengesahan" class="form-control"
                                value="{{ old('tanggal_pengesahan') }}">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Upload Dokumen Pengesahan</label>
                            <input type="file" name="dokumen_pengesahan" class="form-control" accept=".pdf,.doc,.docx">
                            <small class="text-muted">PDF / Word, maks. 10MB</small>
                        </div>
                    </div>

                    {{-- ── STATUS ── --}}
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                            {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Skema Aktif</label>
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Skema
                        </button>
                        <a href="{{ route('admin.skemas') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection