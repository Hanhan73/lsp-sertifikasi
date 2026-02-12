@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('sidebar')
@if(auth()->user()->isAdmin())
@include('admin.partials.sidebar')
@elseif(auth()->user()->isTuk())
@include('tuk.partials.sidebar')
@else
@include('asesi.partials.sidebar')
@endif
@endsection

@section('content')
<div class="row">
    <!-- Profile Info Card -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    @if($user->photo_path)
                    <img src="{{ asset('storage/' . $user->photo_path) }}" alt="Profile" class="rounded-circle"
                        style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center"
                        style="width: 150px; height: 150px;">
                        <i class="bi bi-person-circle" style="font-size: 5rem; color: #ccc;"></i>
                    </div>
                    @endif
                </div>

                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>

                <hr>

                <!-- Upload Photo Form -->
                <form action="{{ route('profile.upload-photo') }}" method="POST" enctype="multipart/form-data"
                    id="upload-photo-form">
                    @csrf
                    <div class="mb-3">
                        <label for="photo" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-camera"></i> Ganti Foto Profil
                        </label>
                        <input type="file" class="d-none" id="photo" name="photo" accept="image/*"
                            onchange="document.getElementById('upload-photo-form').submit()">
                    </div>
                </form>

                @if($user->photo_path)
                <form action="{{ route('profile.delete-photo') }}" method="POST"
                    onsubmit="return confirm('Hapus foto profil?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                        <i class="bi bi-trash"></i> Hapus Foto
                    </button>
                </form>
                @endif

                <hr>

                <div class="text-start">
                    <p class="mb-1"><strong>Member Since:</strong></p>
                    <p class="text-muted">{{ $user->created_at->format('d F Y') }}</p>

                    <p class="mb-1"><strong>Status Akun:</strong></p>
                    <p>
                        @if($user->is_active)
                        <span class="badge bg-success">Aktif</span>
                        @else
                        <span class="badge bg-danger">Nonaktif</span>
                        @endif
                    </p>

                    @if($user->isTuk() && $user->tuk)
                    <p class="mb-1"><strong>TUK Code:</strong></p>
                    <p class="text-muted">{{ $user->tuk->code }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Forms -->
    <div class="col-lg-8">
        <!-- Update Info Card -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update-info') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Email digunakan untuk login</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Update Password Card -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Ubah Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update-password') }}" method="POST" id="password-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Password Lama</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                name="current_password" id="current_password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="togglePassword('current_password', 'icon1')">
                                <i class="bi bi-eye" id="icon1"></i>
                            </button>
                        </div>
                        @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" id="password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="togglePassword('password', 'icon2')">
                                <i class="bi bi-eye" id="icon2"></i>
                            </button>
                        </div>
                        @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 8 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password_confirmation"
                                id="password_confirmation" required>
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="togglePassword('password_confirmation', 'icon3')">
                                <i class="bi bi-eye" id="icon3"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>

        <!-- TUK Additional Info (if TUK) -->
        @if($user->isTuk() && $user->tuk)
        <div class="card mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-building"></i> Informasi TUK</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <strong>Kode TUK:</strong><br>
                        {{ $user->tuk->code }}
                    </div>
                    <div class="col-md-6 mb-2">
                        <strong>Nama TUK:</strong><br>
                        {{ $user->tuk->name }}
                    </div>
                    <div class="col-12 mb-2">
                        <strong>Alamat:</strong><br>
                        {{ $user->tuk->address }}
                    </div>
                    @if($user->tuk->email)
                    <div class="col-md-6 mb-2">
                        <strong>Email TUK:</strong><br>
                        {{ $user->tuk->email }}
                    </div>
                    @endif
                    @if($user->tuk->phone)
                    <div class="col-md-6 mb-2">
                        <strong>Telepon:</strong><br>
                        {{ $user->tuk->phone }}
                    </div>
                    @endif
                    @if($user->tuk->manager_name)
                    <div class="col-md-6 mb-2">
                        <strong>Manager:</strong><br>
                        {{ $user->tuk->manager_name }}
                    </div>
                    @endif
                    @if($user->tuk->staff_name)
                    <div class="col-md-6 mb-2">
                        <strong>Staff:</strong><br>
                        {{ $user->tuk->staff_name }}
                    </div>
                    @endif
                </div>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle"></i> Untuk mengubah informasi TUK, silakan hubungi Administrator.
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Password confirmation validation
$('#password-form').on('submit', function(e) {
    const password = $('#password').val();
    const confirmation = $('#password_confirmation').val();

    if (password !== confirmation) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Password Tidak Cocok',
            text: 'Password baru dan konfirmasi password harus sama!'
        });
        return false;
    }
});
</script>
@endpush