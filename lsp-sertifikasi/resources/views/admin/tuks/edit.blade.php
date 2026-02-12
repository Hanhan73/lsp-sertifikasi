@extends('layouts.app')

@section('title', 'Edit TUK')
@section('page-title', 'Edit TUK - ' . $tuk->name)

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Form Edit TUK</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tuks.update', $tuk) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Kode TUK -->
                    <div class="mb-3">
                        <label class="form-label">Kode TUK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                            value="{{ old('code', $tuk->code) }}" required>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Kode unik untuk TUK</small>
                    </div>

                    <!-- Nama TUK -->
                    <div class="mb-3">
                        <label class="form-label">Nama TUK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name', $tuk->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Alamat -->
                    <div class="mb-3">
                        <label class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3"
                            required>{{ old('address', $tuk->address) }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Manager -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Manager</label>
                            <input type="text" class="form-control @error('manager_name') is-invalid @enderror"
                                name="manager_name" value="{{ old('manager_name', $tuk->manager_name) }}">
                            @error('manager_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Staff -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Staff</label>
                            <input type="text" class="form-control @error('staff_name') is-invalid @enderror"
                                name="staff_name" value="{{ old('staff_name', $tuk->staff_name) }}">
                            @error('staff_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="mb-3">
                        <label class="form-label">Logo TUK</label>

                        @if($tuk->logo_path)
                        <div class="mb-2">
                            <img src="{{ $tuk->logo_url }}" alt="Current Logo" class="img-thumbnail"
                                style="max-width: 150px;">
                            <p class="small text-muted mb-0">Logo saat ini</p>
                        </div>
                        @endif

                        <input type="file" class="form-control @error('logo') is-invalid @enderror" name="logo"
                            accept="image/*" onchange="previewImage(this)">
                        @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Upload logo baru untuk mengganti (JPG, PNG. Max 2MB). Kosongkan jika
                            tidak ingin mengubah.</small>

                        <!-- Preview -->
                        <div id="logo-preview" class="mt-2" style="display: none;">
                            <p class="small text-success">Preview logo baru:</p>
                            <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>

                    <hr>

                    <!-- Account Info -->
                    <h6 class="mb-3"><i class="bi bi-person-lock"></i> Informasi Akun</h6>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Email Login:</strong> {{ $tuk->user->email ?? '-' }}
                        <br>
                        <small>Untuk mengubah email atau password, silakan hubungi admin sistem</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $tuk->is_active) ? 'checked' : '' }} id="is_active">
                            <label class="form-check-label" for="is_active">
                                TUK Aktif
                            </label>
                        </div>
                        <small class="text-muted">Nonaktifkan TUK jika sudah tidak beroperasi</small>
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update TUK
                        </button>
                        <a href="{{ route('admin.tuks') }}" class="btn btn-secondary">
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
                <h6><i class="bi bi-info-circle"></i> Informasi TUK</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="120">Total Asesi:</td>
                        <td><strong>{{ $tuk->asesmens->count() }}</strong></td>
                    </tr>
                    <tr>
                        <td>Dibuat:</td>
                        <td>{{ $tuk->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Update Terakhir:</td>
                        <td>{{ $tuk->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>
                            @if($tuk->is_active)
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
                <p class="small mb-0">
                    Perubahan informasi TUK akan mempengaruhi semua data asesi yang terkait.
                    Pastikan data yang diinput sudah benar.
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
</script>
@endpush