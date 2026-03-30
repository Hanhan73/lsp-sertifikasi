@extends('layouts.app')
@section('title', 'Tambah Portofolio')
@section('page-title', 'Portofolio')
@section('sidebar') @include('manajer-sertifikasi.partials.sidebar') @endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('manajer-sertifikasi.portofolio.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <h5 class="fw-bold mb-1">Tambah Portofolio</h5>
</div>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong><i class="bi bi-briefcase me-2" style="color:#7c3aed"></i>Form Portofolio</strong></div>
            <div class="card-body">
                <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.8rem">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Format file portofolio belum ditentukan (TBD). Bisa simpan tanpa file dulu.
                </div>
                <form method="POST" action="{{ route('manajer-sertifikasi.portofolio.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Skema Sertifikasi <span class="text-danger">*</span></label>
                        <select name="skema_id" class="form-select @error('skema_id') is-invalid @enderror" required>
                            <option value="">— Pilih Skema —</option>
                            @foreach($skemas as $sk)
                                <option value="{{ $sk->id }}" {{ old('skema_id') == $sk->id ? 'selected' : '' }}>{{ $sk->name }} ({{ $sk->code }})</option>
                            @endforeach
                        </select>
                        @error('skema_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="judul" value="{{ old('judul') }}"
                               class="form-control @error('judul') is-invalid @enderror"
                               placeholder="cth: Portofolio Kompetensi" required>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Opsional...">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">File <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror">
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Format TBD. Maks. 20 MB.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                        <a href="{{ route('manajer-sertifikasi.portofolio.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection