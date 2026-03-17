@extends('layouts.app')

@section('title', 'Edit Asesor')
@section('page-title', 'Edit Asesor - ' . $asesor->nama)

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Data Asesor</h5>
                <span class="badge bg-dark">ID: {{ $asesor->id }}</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.asesors.update', $asesor) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row">
                        {{-- ======== KOLOM KIRI ======== --}}
                        <div class="col-md-8">

                            {{-- IDENTITAS --}}
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-person-vcard"></i> Data Identitas
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="nama"
                                        class="form-control @error('nama') is-invalid @enderror"
                                        value="{{ old('nama', $asesor->nama) }}" required>
                                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                                    <input type="text" name="nik"
                                        class="form-control @error('nik') is-invalid @enderror"
                                        value="{{ old('nik', $asesor->nik) }}" maxlength="16" required>
                                    @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="jenis_kelamin" class="form-select" required>
                                        <option value="L"
                                            {{ old('jenis_kelamin', $asesor->jenis_kelamin) === 'L' ? 'selected' : '' }}>
                                            Laki-laki</option>
                                        <option value="P"
                                            {{ old('jenis_kelamin', $asesor->jenis_kelamin) === 'P' ? 'selected' : '' }}>
                                            Perempuan</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                    <input type="text" name="tempat_lahir"
                                        class="form-control @error('tempat_lahir') is-invalid @enderror"
                                        value="{{ old('tempat_lahir', $asesor->tempat_lahir) }}" required>
                                    @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_lahir" class="form-control"
                                        value="{{ old('tanggal_lahir', $asesor->tanggal_lahir->format('Y-m-d')) }}"
                                        required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control"
                                        rows="2">{{ old('alamat', $asesor->alamat) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Kota</label>
                                    <input type="text" name="kota" class="form-control"
                                        value="{{ old('kota', $asesor->kota) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Provinsi</label>
                                    <input type="text" name="provinsi" class="form-control"
                                        value="{{ old('provinsi', $asesor->provinsi) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="telepon" class="form-control"
                                        value="{{ old('telepon', $asesor->telepon) }}">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $asesor->email) }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- REGISTRASI --}}
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-card-checklist"></i> Data Registrasi Metodologi
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">No. Reg. Metodologi</label>
                                    <input type="text" name="no_reg_met" class="form-control"
                                        value="{{ old('no_reg_met', $asesor->no_reg_met) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">No. Blanko</label>
                                    <input type="text" name="no_blanko" class="form-control"
                                        value="{{ old('no_blanko', $asesor->no_blanko) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">SIAPKerja <span class="text-danger">*</span></label>
                                    <select name="siap_kerja" class="form-select" required>
                                        <option value="Memiliki"
                                            {{ old('siap_kerja', $asesor->siap_kerja) === 'Memiliki' ? 'selected' : '' }}>
                                            ✅ Memiliki</option>
                                        <option value="Tidak"
                                            {{ old('siap_kerja', $asesor->siap_kerja) === 'Tidak' ? 'selected' : '' }}>❌
                                            Tidak Memiliki</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status Registrasi <span
                                            class="text-danger">*</span></label>
                                    <select name="status_reg" class="form-select" id="status_reg" required>
                                        <option value="aktif"
                                            {{ old('status_reg', $asesor->status_reg) === 'aktif' ? 'selected' : '' }}>
                                            Aktif</option>
                                        <option value="expire"
                                            {{ old('status_reg', $asesor->status_reg) === 'expire' ? 'selected' : '' }}>
                                            Expire</option>
                                        <option value="nonaktif"
                                            {{ old('status_reg', $asesor->status_reg) === 'nonaktif' ? 'selected' : '' }}>
                                            Nonaktif</option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="expire-date-wrapper"
                                    style="display: {{ old('status_reg', $asesor->status_reg) === 'expire' ? 'block' : 'none' }};">
                                    <label class="form-label">Tanggal Expire</label>
                                    <input type="date" name="expire_date" class="form-control"
                                        value="{{ old('expire_date', $asesor->expire_date?->format('Y-m-d')) }}">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control"
                                        rows="2">{{ old('keterangan', $asesor->keterangan) }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                            {{ old('is_active', $asesor->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Asesor Aktif</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ======== KOLOM KANAN - FOTO ======== --}}
                        <div class="col-md-4">
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-image"></i> Foto Asesor
                            </h6>

                            <div class="text-center">
                                <div id="foto-preview-container"
                                    style="width:180px; height:200px; border:2px dashed #dee2e6; border-radius:8px; overflow:hidden; margin:0 auto 1rem; cursor:pointer; background:#f8f9fa;"
                                    onclick="document.getElementById('foto').click()">
                                    <img id="foto-preview"
                                        src="{{ $asesor->foto_path ? asset('storage/' . $asesor->foto_path) : asset('images/default-avatar.png') }}"
                                        alt="Foto" style="width:100%; height:100%; object-fit:cover;">
                                </div>

                                <input type="file" name="foto" id="foto" class="d-none"
                                    accept="image/jpeg,image/png,image/jpg">
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="document.getElementById('foto').click()">
                                    <i class="bi bi-upload"></i> Ganti Foto
                                </button>
                                <br>
                                <small class="text-muted">JPG/PNG, max 2MB</small>
                            </div>

                            {{-- Info Akun --}}
                            @if($asesor->user)
                            <div class="mt-4">
                                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-lock"></i> Info Akun
                                </h6>
                                <div class="card bg-success-subtle border-success-subtle">
                                    <div class="card-body p-2 small">
                                        <i class="bi bi-check-circle text-success"></i>
                                        <strong>Memiliki Akun Login</strong>
                                        <br>
                                        <span class="text-muted">{{ $asesor->user->email }}</span>
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="mt-4">
                                <div class="card bg-warning-subtle border-warning-subtle">
                                    <div class="card-body p-2 small">
                                        <i class="bi bi-exclamation-triangle text-warning"></i>
                                        Belum memiliki akun login
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('admin.asesors.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('status_reg').addEventListener('change', function() {
    document.getElementById('expire-date-wrapper').style.display =
        this.value === 'expire' ? 'block' : 'none';
});

document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('foto-preview').src = ev.target.result;
    };
    reader.readAsDataURL(file);
});
</script>
@endpush