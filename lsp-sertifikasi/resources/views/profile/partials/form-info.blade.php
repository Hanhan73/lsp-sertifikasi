{{--
    resources/views/profile/partials/form-info.blade.php
    Props: $nameValue, $emailValue
--}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom fw-semibold">
        <i class="bi bi-person me-2 text-primary"></i>Informasi Akun
    </div>
    <div class="card-body p-4">
        <form action="{{ route('profile.update-info') }}" method="POST">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nama</label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $nameValue) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $emailValue) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>