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
                <h5 class="mb-0">
                    <i class="bi bi-pencil"></i> Form Edit TUK
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.tuks.update', $tuk) }}" method="POST" enctype="multipart/form-data" id="tuk-form">
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
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Manajer TUK</label>
                            <input type="text" class="form-control @error('manager_name') is-invalid @enderror"
                                name="manager_name" value="{{ old('manager_name', $tuk->manager_name) }}"
                                placeholder="Nama manajer">
                            @error('manager_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ✅ NEW: Treasurer -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Bendahara TUK</label>
                            <input type="text" class="form-control @error('treasurer_name') is-invalid @enderror"
                                name="treasurer_name" value="{{ old('treasurer_name', $tuk->treasurer_name) }}"
                                placeholder="Nama bendahara">
                            @error('treasurer_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Staff -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Staff TUK</label>
                            <input type="text" class="form-control @error('staff_name') is-invalid @enderror"
                                name="staff_name" value="{{ old('staff_name', $tuk->staff_name) }}"
                                placeholder="Nama staff">
                            @error('staff_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- ✅ NEW: Phone -->
                    <div class="mb-3">
                        <label class="form-label">Nomor HP TUK</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone"
                            value="{{ old('phone', $tuk->phone) }}" placeholder="Contoh: 081234567890">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nomor HP yang dapat dihubungi untuk koordinasi</small>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3"><i class="bi bi-image"></i> Logo & Dokumen</h6>

                    <div class="row">
                        <!-- Logo -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Logo TUK</label>

                            @if($tuk->logo_path)
                            <div class="mb-2">
                                <img src="{{ $tuk->logo_url }}" alt="Current Logo" class="img-thumbnail"
                                    style="max-width: 150px;">
                                <p class="small text-muted mb-0 mt-1">Logo saat ini</p>
                            </div>
                            @endif

                            <input type="file" class="form-control @error('logo') is-invalid @enderror" name="logo"
                                accept="image/*" onchange="previewImage(this, 'logo-preview')">
                            @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Upload logo baru untuk mengganti (JPG, PNG. Max 2MB). Kosongkan jika
                                tidak ingin mengubah.</small>

                            <!-- Preview -->
                            <div id="logo-preview" class="mt-2" style="display: none;">
                                <p class="small text-success mb-1">Preview logo baru:</p>
                                <img id="logo-preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 150px;">
                            </div>
                        </div>

                        <!-- ✅ NEW: SK Document -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SK Penetapan TUK</label>

                            @if($tuk->hasSkDocument())
                            <div class="mb-2">
                                <a href="{{ $tuk->sk_document_url }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-file-earmark-pdf"></i> Lihat SK Saat Ini
                                </a>
                                <p class="small text-muted mb-0 mt-1">
                                    SK sudah diupload
                                </p>
                            </div>
                            @else
                            <div class="alert alert-info small mb-2">
                                <i class="bi bi-info-circle"></i> Belum ada SK yang diupload
                            </div>
                            @endif

                            <input type="file" class="form-control @error('sk_document') is-invalid @enderror" 
                                name="sk_document" accept=".pdf,.doc,.docx" 
                                onchange="showFileName(this, 'sk-filename')">
                            @error('sk_document')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Upload SK baru untuk mengganti (PDF, DOC, DOCX. Max 5MB). Kosongkan jika tidak ingin mengubah.</small>

                            <div id="sk-filename" class="mt-1 text-success small" style="display: none;">
                                <i class="bi bi-file-earmark-pdf"></i> <span id="sk-filename-text"></span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Account Info -->
                    <h6 class="mb-3"><i class="bi bi-person-lock"></i> Informasi Akun</h6>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Email Login:</strong> {{ $tuk->user->email ?? '-' }}
                        <br>
                        <small>Untuk mengubah email atau password, silakan hubungi admin sistem</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $tuk->is_active) ? 'checked' : '' }} id="is_active">
                            <label class="form-check-label" for="is_active">
                                <strong>TUK Aktif</strong>
                            </label>
                        </div>
                        <small class="text-muted">Nonaktifkan TUK jika sudah tidak beroperasi</small>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Update TUK
                        </button>
                        <a href="{{ route('admin.tuks') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="button" class="btn btn-danger ms-auto" 
                                onclick="confirmDelete({{ $tuk->id }}, '{{ $tuk->name }}')">
                            <i class="bi bi-trash"></i> Hapus TUK
                        </button>
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
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="120"><strong>Total Asesi:</strong></td>
                        <td><span class="badge bg-info">{{ $tuk->asesmens->count() }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Dibuat:</strong></td>
                        <td>{{ $tuk->created_at->translatedFormat('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Update Terakhir:</strong></td>
                        <td>{{ $tuk->updated_at->translatedFormat('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
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

        @if($tuk->asesmens->count() > 0)
        <div class="card bg-info text-white mt-3">
            <div class="card-body">
                <h6><i class="bi bi-people"></i> Statistik Asesi</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <h3 class="mb-0">{{ $tuk->asesmens->where('is_collective', false)->count() }}</h3>
                        <small>Mandiri</small>
                    </div>
                    <div class="col-6">
                        <h3 class="mb-0">{{ $tuk->asesmens->where('is_collective', true)->count() }}</h3>
                        <small>Kolektif</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card bg-warning mt-3">
            <div class="card-body">
                <h6><i class="bi bi-exclamation-triangle"></i> Perhatian</h6>
                <p class="small mb-0">
                    Perubahan informasi TUK akan mempengaruhi semua data asesi yang terkait.
                    Pastikan data yang diinput sudah benar.
                </p>
            </div>
        </div>

        <div class="card border-danger mt-3">
            <div class="card-body">
                <h6 class="text-danger"><i class="bi bi-shield-exclamation"></i> Hapus TUK</h6>
                <p class="small text-muted mb-2">
                    Menghapus TUK akan menghapus:
                </p>
                <ul class="small mb-2">
                    <li>Data TUK</li>
                    <li>Akun login</li>
                    <li>Logo & SK Document</li>
                </ul>
                @if($tuk->asesmens->whereIn('status', ['registered', 'data_completed', 'verified', 'paid', 'scheduled'])->count() > 0)
                <div class="alert alert-danger small mb-0">
                    <i class="bi bi-x-circle"></i> TUK tidak dapat dihapus karena masih memiliki 
                    <strong>{{ $tuk->asesmens->whereIn('status', ['registered', 'data_completed', 'verified', 'paid', 'scheduled'])->count() }} asesi aktif</strong>
                </div>
                @else
                <button type="button" class="btn btn-danger btn-sm w-100" 
                        onclick="confirmDelete({{ $tuk->id }}, '{{ $tuk->name }}')">
                    <i class="bi bi-trash"></i> Hapus TUK Ini
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#' + previewId).show();
            $('#' + previewId + '-img').attr('src', e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function showFileName(input, targetId) {
    if (input.files && input.files[0]) {
        $('#' + targetId).show();
        $('#' + targetId + '-text').text(input.files[0].name);
    }
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus TUK?',
        html: `
            <p>Anda akan menghapus TUK:</p>
            <strong class="text-danger">${name}</strong>
            <p class="mt-2 small text-muted">Data TUK, logo, SK document, dan akun login akan dihapus permanen!</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/tuks/${id}`;
            form.submit();
        }
    });
}
</script>
@endpush