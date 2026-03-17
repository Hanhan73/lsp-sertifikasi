@extends('layouts.app')

@section('title', 'Tambah Asesor')
@section('page-title', 'Tambah Asesor Baru')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus"></i> Form Tambah Asesor</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.asesors.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

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
                                        value="{{ old('nama') }}" required>
                                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">NIK <span class="text-danger">*</span></label>
                                    <input type="text" name="nik"
                                        class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}"
                                        maxlength="16" placeholder="16 digit NIK" required>
                                    @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="jenis_kelamin"
                                        class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                        <option value="">Pilih</option>
                                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki
                                        </option>
                                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan
                                        </option>
                                    </select>
                                    @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                    <input type="text" name="tempat_lahir"
                                        class="form-control @error('tempat_lahir') is-invalid @enderror"
                                        value="{{ old('tempat_lahir') }}" required>
                                    @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_lahir"
                                        class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                        value="{{ old('tanggal_lahir') }}" required>
                                    @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="2"
                                        placeholder="Jl. ...">{{ old('alamat') }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Kota</label>
                                    <input type="text" name="kota" class="form-control" value="{{ old('kota') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Provinsi</label>
                                    <input type="text" name="provinsi" class="form-control"
                                        value="{{ old('provinsi') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" name="telepon" class="form-control" value="{{ old('telepon') }}"
                                        placeholder="08xx">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- REGISTRASI / METODOLOGI --}}
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-card-checklist"></i> Data Registrasi Metodologi
                            </h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">No. Reg. Metodologi</label>
                                    <input type="text" name="no_reg_met" class="form-control"
                                        value="{{ old('no_reg_met') }}" placeholder="000.011004 2018">
                                    <small class="text-muted">Nomor registrasi asesor metodologi</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">No. Blanko</label>
                                    <input type="text" name="no_blanko" class="form-control"
                                        value="{{ old('no_blanko') }}" placeholder="4280232">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">SIAPKerja <span class="text-danger">*</span></label>
                                    <select name="siap_kerja" class="form-select" required>
                                        <option value="Memiliki"
                                            {{ old('siap_kerja', 'Memiliki') === 'Memiliki' ? 'selected' : '' }}>
                                            ✅ Memiliki
                                        </option>
                                        <option value="Tidak" {{ old('siap_kerja') === 'Tidak' ? 'selected' : '' }}>
                                            ❌ Tidak Memiliki
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status Registrasi <span
                                            class="text-danger">*</span></label>
                                    <select name="status_reg" class="form-select" id="status_reg" required>
                                        <option value="aktif"
                                            {{ old('status_reg') === 'aktif' ? 'selected' : 'selected' }}>Aktif</option>
                                        <option value="expire" {{ old('status_reg') === 'expire' ? 'selected' : '' }}>
                                            Expire</option>
                                        <option value="nonaktif"
                                            {{ old('status_reg') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="expire-date-wrapper"
                                    style="display: {{ old('status_reg') === 'expire' ? 'block' : 'none' }};">
                                    <label class="form-label">Tanggal Expire</label>
                                    <input type="date" name="expire_date"
                                        class="form-control @error('expire_date') is-invalid @enderror"
                                        value="{{ old('expire_date') }}">
                                    @error('expire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="keterangan" class="form-control" rows="2"
                                        placeholder="Catatan tambahan...">{{ old('keterangan') }}</textarea>
                                </div>
                            </div>

                            {{-- AKUN SISTEM --}}
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-person-lock"></i> Akun Sistem
                            </h6>

                            <div class="card bg-light border-0 p-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="buat_akun" id="buat_akun"
                                        value="1" {{ old('buat_akun') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="buat_akun">
                                        <strong>Buat akun login untuk asesor ini</strong>
                                    </label>
                                </div>
                                <small class="text-muted mt-1">
                                    Jika dicentang, sistem akan membuat akun login dengan password default:
                                    <code>asesor123</code>
                                </small>
                            </div>
                        </div>

                        {{-- ======== KOLOM KANAN - FOTO ======== --}}
                        <div class="col-md-4">
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                                <i class="bi bi-image"></i> Foto Asesor
                            </h6>

                            <div class="text-center">
                                <div id="foto-preview-container"
                                    style="width:180px; height:200px; border:2px dashed #dee2e6; border-radius:8px; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; overflow:hidden; background:#f8f9fa; cursor:pointer;"
                                    onclick="document.getElementById('foto').click()">
                                    <img id="foto-preview" src="" alt="Preview"
                                        style="width:100%; height:100%; object-fit:cover; display:none;">
                                    <div id="foto-placeholder">
                                        <i class="bi bi-person-circle" style="font-size:4rem; color:#adb5bd;"></i>
                                        <p class="small text-muted mt-2">Klik untuk upload</p>
                                    </div>
                                </div>

                                <input type="file" name="foto" id="foto" class="d-none"
                                    accept="image/jpeg,image/png,image/jpg">
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="document.getElementById('foto').click()">
                                    <i class="bi bi-upload"></i> Pilih Foto
                                </button>
                                <br>
                                <small class="text-muted">JPG/PNG, max 2MB</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Asesor
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
// Toggle expire date field
document.getElementById('status_reg').addEventListener('change', function() {
    document.getElementById('expire-date-wrapper').style.display =
        this.value === 'expire' ? 'block' : 'none';
});

// Preview foto
document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(ev) {
        const preview = document.getElementById('foto-preview');
        const placeholder = document.getElementById('foto-placeholder');
        preview.src = ev.target.result;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
@endpush