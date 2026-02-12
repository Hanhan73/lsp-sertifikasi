@extends('layouts.app')

@section('title', 'Tambah TUK')
@section('page-title', 'Tambah TUK Baru')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Form Tambah TUK</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tuks.store') }}" method="POST" enctype="multipart/form-data"
                    id="tuk-form">
                    @csrf

                    <!-- Kode TUK -->
                    <div class="mb-3">
                        <label class="form-label">Kode TUK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                            value="{{ old('code') }}" placeholder="Contoh: TUK-001" required>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kode unik untuk TUK (akan digunakan dalam sistem)</small>
                    </div>

                    <!-- Nama TUK -->
                    <div class="mb-3">
                        <label class="form-label">Nama TUK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name') }}" placeholder="Contoh: TUK Teknologi Informasi Jakarta" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Alamat -->
                    <div class="mb-3">
                        <label class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3"
                            placeholder="Alamat lengkap TUK" required>{{ old('address') }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Manager -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Manager</label>
                            <input type="text" class="form-control @error('manager_name') is-invalid @enderror"
                                name="manager_name" value="{{ old('manager_name') }}" placeholder="Nama manager TUK">
                            @error('manager_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Staff -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Staff</label>
                            <input type="text" class="form-control @error('staff_name') is-invalid @enderror"
                                name="staff_name" value="{{ old('staff_name') }}" placeholder="Nama staff TUK">
                            @error('staff_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="mb-3">
                        <label class="form-label">Logo TUK</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" name="logo"
                            accept="image/*" onchange="previewImage(this)">
                        @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>

                        <!-- Preview -->
                        <div id="logo-preview" class="mt-2" style="display: none;">
                            <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>

                    <hr>

                    <!-- Account Info -->
                    <h6 class="mb-3"><i class="bi bi-person-lock"></i> Akun Login TUK</h6>

                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email') }}" placeholder="email@tuk.com" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Email ini akan digunakan untuk login</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" id="password" placeholder="Minimal 8 karakter" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggle-icon"></i>
                            </button>
                        </div>
                        @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password_confirmation"
                            placeholder="Ulangi password" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked
                                id="is_active">
                            <label class="form-check-label" for="is_active">
                                Aktifkan TUK
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan TUK
                        </button>
                        <a href="{{ route('admin.tuks') }}" class="btn btn-secondary">
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
                    <li>Kode TUK harus unik dan tidak boleh diubah setelah dibuat</li>
                    <li>Logo TUK akan ditampilkan di sertifikat dan dokumen resmi</li>
                    <li>Email akan digunakan untuk login ke sistem TUK</li>
                    <li>Password minimal 8 karakter untuk keamanan</li>
                    <li>Manager dan Staff bersifat opsional, dapat diisi nanti</li>
                </ul>
            </div>
        </div>

        <div class="card bg-primary text-white mt-3">
            <div class="card-body">
                <h6><i class="bi bi-shield-check"></i> Keamanan</h6>
                <p class="small mb-0">
                    Informasi login akan dikirim ke email TUK setelah akun dibuat.
                    Pastikan email yang dimasukkan valid dan dapat diakses.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#logo-preview').show();
            $('#preview-img').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('toggle-icon');

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Form validation
$('#tuk-form').on('submit', function(e) {
    const password = $('[name="password"]').val();
    const confirmation = $('[name="password_confirmation"]').val();

    if (password !== confirmation) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Password Tidak Cocok',
            text: 'Password dan konfirmasi password harus sama!'
        });
        return false;
    }
});
</script>
@endpush