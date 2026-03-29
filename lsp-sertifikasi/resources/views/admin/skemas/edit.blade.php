@extends('layouts.app')

@section('title', 'Edit Skema')
@section('page-title', 'Edit Skema - ' . $skema->name)

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Form Edit Skema
                </h5>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.skemas.update', $skema) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- ── IDENTITAS SKEMA ── --}}
                    <h6 class="fw-bold text-warning border-bottom pb-2 mb-3">
                        <i class="bi bi-file-earmark-text"></i> Identitas Skema
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Kode Skema *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code', $skema->code) }}" required>
                            @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Skema *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $skema->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Jenis Skema *</label>
                            <select name="jenis_skema" class="form-select @error('jenis_skema') is-invalid @enderror"
                                required>
                                <option value="">Pilih Jenis</option>
                                <option value="klaster"
                                    {{ old('jenis_skema', $skema->jenis_skema) == 'klaster' ? 'selected' : '' }}>Klaster
                                </option>
                                <option value="okupasi"
                                    {{ old('jenis_skema', $skema->jenis_skema) == 'okupasi' ? 'selected' : '' }}>Okupasi
                                </option>
                                <option value="kkni"
                                    {{ old('jenis_skema', $skema->jenis_skema) == 'kkni' ? 'selected' : '' }}>KKNI
                                </option>
                            </select>
                            @error('jenis_skema')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nomor Skema</label>
                            <input type="text" name="nomor_skema" class="form-control"
                                value="{{ old('nomor_skema', $skema->nomor_skema) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Biaya (Rp) *</label>
                            <input type="number" name="fee" class="form-control @error('fee') is-invalid @enderror"
                                value="{{ old('fee', $skema->fee) }}" min="0" step="1000" required>
                            @error('fee')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control"
                                rows="3">{{ old('description', $skema->description) }}</textarea>
                        </div>
                    </div>

                    {{-- ── DOKUMEN PENGESAHAN ── --}}
                    <h6 class="fw-bold text-warning border-bottom pb-2 mb-3">
                        <i class="bi bi-file-earmark-check"></i> Dokumen Pengesahan
                    </h6>

                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label">Tanggal Pengesahan</label>
                            <input type="date" name="tanggal_pengesahan" class="form-control"
                                value="{{ old('tanggal_pengesahan', $skema->tanggal_pengesahan) }}">
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">Upload Dokumen</label>
                            <input type="file" name="dokumen_pengesahan" class="form-control" accept=".pdf,.doc,.docx">
                            <small class="text-muted">PDF / Word, maks. 10MB</small>

                            @if($skema->dokumen_pengesahan)
                            <div class="mt-2">
                                <a href="{{ asset('storage/'.$skema->dokumen_pengesahan) }}" target="_blank"
                                    class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-eye"></i> Lihat Dokumen Saat Ini
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                            {{ old('is_active', $skema->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Skema Aktif
                        </label>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update Skema
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