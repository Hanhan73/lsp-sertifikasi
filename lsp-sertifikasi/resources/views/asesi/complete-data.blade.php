@extends('layouts.app')

@section('title', $viewOnly ? 'Lihat Data Pribadi' : 'Lengkapi Data Pribadi')
@section('page-title', $viewOnly ? 'Lihat Data Pribadi' : 'Lengkapi Data Pribadi')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@push('styles')
<style>
.locked-field {
    background-color: #f1f3f5 !important;
    color: #6c757d;
    cursor: not-allowed;
}

.locked-field option {
    color: #6c757d;
}

.training-section {
    border: 2px solid #0d6efd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 20px;
    background-color: #f8f9fa;
}

.training-option {
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
}

.training-option:hover {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

.training-option.selected {
    border-color: #0d6efd;
    background-color: #cfe2ff;
}

.training-option input[type="radio"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.price-badge {
    font-size: 1.2rem;
    font-weight: bold;
    color: #198754;
}
</style>
@endpush
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header {{ $viewOnly ? 'bg-info' : 'bg-primary' }} text-white">
                <h5 class="mb-0">
                    <i class="bi {{ $viewOnly ? 'bi-eye' : 'bi-clipboard-data' }}"></i> 
                    {{ $viewOnly ? 'Data Pribadi Asesi (View Only)' : 'Form Data Pribadi Asesi' }}
                </h5>
            </div>
            <div class="card-body">
            @if($viewOnly)
                {{-- ✅ VIEW-ONLY MODE --}}
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    Data Anda sudah disubmit dan sedang dalam proses verifikasi. 
                    Data tidak dapat diubah setelah diverifikasi oleh Admin LSP.
                </div>

                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> 
                    <strong>Status:</strong> {{ $asesmen->status_label }}
                </div>

                {{-- Personal Data --}}
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Data Pribadi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td width="150"><strong>Nama Lengkap</strong></td>
                                        <td>: {{ $asesmen->full_name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>NIK</strong></td>
                                        <td>: {{ $asesmen->nik }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tempat Lahir</strong></td>
                                        <td>: {{ $asesmen->birth_place }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Lahir</strong></td>
                                        <td>: {{ \Carbon\Carbon::parse($asesmen->birth_date)->format('d F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Jenis Kelamin</strong></td>
                                        <td>: {{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>: {{ auth()->user()->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Telepon</strong></td>
                                        <td>: {{ $asesmen->phone }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td width="150"><strong>Alamat</strong></td>
                                        <td>: {{ $asesmen->address }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kode Provinsi</strong></td>
                                        <td>: {{ $asesmen->province_code }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Kode Kota</strong></td>
                                        <td>: {{ $asesmen->city_code }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pendidikan</strong></td>
                                        <td>: {{ $asesmen->education }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pekerjaan</strong></td>
                                        <td>: {{ $asesmen->occupation }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sumber Anggaran</strong></td>
                                        <td>: {{ $asesmen->budget_source }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Asal Lembaga</strong></td>
                                        <td>: {{ $asesmen->institution }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Sertifikasi --}}
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-award"></i> Data Sertifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td width="150"><strong>Skema Sertifikasi</strong></td>
                                        <td>: {{ $asesmen->skema->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>TUK</strong></td>
                                        <td>: {{ $asesmen->tuk->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Dipilih</strong></td>
                                        <td>: {{ \Carbon\Carbon::parse($asesmen->preferred_date)->format('d F Y') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td width="150"><strong>Jenis Pendaftaran</strong></td>
                                        <td>: 
                                            @if($asesmen->is_collective)
                                                <span class="badge bg-primary">Kolektif</span>
                                            @else
                                                <span class="badge bg-success">Mandiri</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pelatihan</strong></td>
                                        <td>: 
                                            @if($asesmen->training_flag)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Ya (+ Rp 1.500.000)
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-x-circle"></i> Tidak
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($asesmen->fee_amount)
                                    <tr>
                                        <td><strong>Total Biaya</strong></td>
                                        <td>: <span class="text-success fw-bold">Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</span></td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark"></i> Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <h6>Pas Foto</h6>
                                @if($asesmen->photo_path)
                                    <img src="{{ asset('storage/' . $asesmen->photo_path) }}" alt="Foto" class="img-thumbnail mb-2"
                                        style="max-height: 200px;">
                                    <br>
                                    <a href="{{ asset('storage/' . $asesmen->photo_path) }}" target="_blank"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Belum upload
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 text-center">
                                <h6>KTP</h6>
                                @if($asesmen->ktp_path)
                                    <iframe src="{{ asset('storage/' . $asesmen->ktp_path) }}"
                                        style="width: 100%; height: 200px; border: 1px solid #ddd;" class="mb-2"></iframe>
                                    <br>
                                    <a href="{{ asset('storage/' . $asesmen->ktp_path) }}" target="_blank"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Belum upload
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4 text-center">
                                <h6>Ijazah/Transkrip</h6>
                                @if($asesmen->document_path)
                                    <iframe src="{{ asset('storage/' . $asesmen->document_path) }}"
                                        style="width: 100%; height: 200px; border: 1px solid #ddd;" class="mb-2"></iframe>
                                    <br>
                                    <a href="{{ asset('storage/' . $asesmen->document_path) }}" target="_blank"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> Belum upload
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4">
                    <a href="{{ route('asesi.dashboard') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    <a href="{{ route('asesi.tracking') }}" class="btn btn-info btn-lg">
                        <i class="bi bi-clock-history"></i> Lihat Timeline
                    </a>
                </div>

                @else
                    {{-- ✅ EDITABLE FORM MODE --}}
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Lengkapi semua data dengan benar sesuai dokumen resmi
                    (KTP/Ijazah).
                    Data yang sudah disubmit akan diverifikasi.
                </div>

                <form method="POST" action="{{ route('asesi.store-data') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Data Pribadi -->
                    <h6 class="border-bottom pb-2 mb-3">Data Pribadi</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                name="full_name" value="{{ old('full_name', $asesmen->full_name ?? '') }}" required>
                            <small class="text-muted">Sesuai KTP/Ijazah</small>
                            @error('full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror" name="nik"
                                maxlength="16" pattern="\d{16}" value="{{ old('nik', $asesmen->nik ?? '') }}" required>
                            <small class="text-muted">16 digit</small>
                            @error('nik')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('birth_place') is-invalid @enderror"
                                name="birth_place" value="{{ old('birth_place', $asesmen->birth_place ?? '') }}"
                                required>
                            @error('birth_place')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                name="birth_date" value="{{ old('birth_date', $asesmen->birth_date ?? '') }}" required>
                            @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" name="gender" required>
                                <option value="">Pilih</option>
                                <option value="L" {{ old('gender', $asesmen->gender ?? '') == 'L' ? 'selected' : '' }}>
                                    Laki-laki</option>
                                <option value="P" {{ old('gender', $asesmen->gender ?? '') == 'P' ? 'selected' : '' }}>
                                    Perempuan</option>
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon/HP <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" name="phone"
                                value="{{ old('phone', $asesmen->phone ?? '') }}" required>
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Alamat Tempat Tinggal <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" name="address"
                                rows="3" required>{{ old('address', $asesmen->address ?? '') }}</textarea>
                            <small class="text-muted">Sesuai KTP</small>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Provinsi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control locked-field @error('province_code') is-invalid @enderror"
                                name="province_code" maxlength="2"
                                value="{{ old('province_code', $asesmen->province_code ?? '') }}" required readonly>
                            <small class="text-muted">2 digit awal dari NIK</small>
                            @error('province_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Kota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control locked-field @error('city_code') is-invalid @enderror"
                                name="city_code" maxlength="2" value="{{ old('city_code', $asesmen->city_code ?? '') }}"
                                required readonly>
                            <small class="text-muted">2 digit setelah kode provinsi NIK</small>
                            @error('city_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Data Pendidikan & Pekerjaan -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Data Pendidikan & Pekerjaan</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-select @error('education') is-invalid @enderror" name="education"
                                required>
                                <option value="">Pilih</option>
                                <option value="SD" {{ old('education') == 'SD' ? 'selected' : '' }}>SD</option>
                                <option value="SMP" {{ old('education') == 'SMP' ? 'selected' : '' }}>SMP</option>
                                <option value="SMA/SMK" {{ old('education') == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK
                                </option>
                                <option value="D3" {{ old('education') == 'D3' ? 'selected' : '' }}>D3</option>
                                <option value="S1" {{ old('education') == 'S1' ? 'selected' : '' }}>S1</option>
                                <option value="S2" {{ old('education') == 'S2' ? 'selected' : '' }}>S2</option>
                                <option value="S3" {{ old('education') == 'S3' ? 'selected' : '' }}>S3</option>
                            </select>
                            @error('education')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('occupation') is-invalid @enderror"
                                name="occupation" value="{{ old('occupation') }}" required>
                            @error('occupation')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sumber Anggaran <span class="text-danger">*</span></label>
                            <select class="form-select @error('budget_source') is-invalid @enderror"
                                name="budget_source" required>
                                <option value="">Pilih</option>
                                <option value="Mandiri">Mandiri</option>
                                <option value="APBN/APBD">APBN/APBD</option>
                                <option value="Lembaga">Lembaga</option>
                            </select>
                            @error('budget_source')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asal Lembaga <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('institution') is-invalid @enderror"
                                name="institution" value="{{ old('institution') }}" required>
                            @error('institution')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Data Sertifikasi -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Data Sertifikasi</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Skema Sertifikasi<span class="text-danger">*</span></label>

                            @if($asesmen->skema_id != null)
                            <select class="form-select locked-field" name="skema_id" disabled
                                style="pointer-events: none; background-color: #e9ecef; appearance: none;" required>
                                <option selected>
                                    {{ $asesmen->skema->name ?? 'Skema terkunci' }}
                                </option>
                            </select>

                            <input type="hidden" name="skema_id" value="{{ $asesmen->skema_id }}" required>
                            @else
                            <select class="form-select @error('skema_id') is-invalid @enderror" name="skema_id"
                                required>
                                <option value="">Pilih Skema</option>
                                @foreach($skemas as $skema)
                                <option value="{{ $skema->id }}" {{ old('skema_id') == $skema->id ? 'selected' : '' }}>
                                    {{ $skema->name }}
                                </option>
                                @endforeach
                            </select>
                            @endif

                            @error('skema_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi Ujian (TUK)<span class="text-danger">*</span></label>

                            @if($asesmen->tuk_id != null)
                            <select class="form-select locked-field" name="tuk_id" disabled
                                style="pointer-events: none; background-color: #e9ecef; appearance: none;">
                                <option selected required>
                                    {{ $asesmen->tuk->name ?? 'TUK terkunci' }}
                                </option>
                            </select>

                            <input type="hidden" name="tuk_id" value="{{ $asesmen->tuk_id }}" required>
                            @else
                            <select class="form-select @error('tuk_id') is-invalid @enderror" name="tuk_id" required>
                                <option value="">Pilih TUK</option>
                                @foreach($tuks as $tuk)
                                <option value="{{ $tuk->id }}" {{ old('tuk_id') == $tuk->id ? 'selected' : '' }}>
                                    {{ $tuk->name }} - {{ $tuk->address }}
                                </option>
                                @endforeach
                            </select>
                            @endif

                            @error('tuk_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($asesmen->is_collective || $asesmen->lock_tuk)
                        <div class="col-12">
                            <div class="alert alert-warning mt-2">
                                <i class="bi bi-lock-fill"></i>
                                Data Skema & TUK sudah ditentukan dan tidak dapat diubah.
                            </div>
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Sertifikasi yang Dipilih <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('preferred_date') is-invalid @enderror"
                                name="preferred_date" value="{{ old('preferred_date') }}" required>
                            @error('preferred_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- SECTION PELATIHAN (NEW) -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="bi bi-mortarboard-fill"></i> Pelatihan (Opsional)
                    </h6>

                    <div class="training-section">
                        <div class="mb-3">
                            <h6 class="text-primary">
                                <i class="bi bi-question-circle-fill"></i>
                                Apakah Anda ingin mengikuti pelatihan sebelum asesmen?
                            </h6>
                            <p class="text-muted mb-3">
                                Pelatihan akan membantu Anda mempersiapkan diri dengan lebih baik untuk menghadapi
                                proses asesmen.
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="training-option" onclick="selectTraining(false)" id="option-no">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="training_flag" value="0" id="training-no"
                                            {{ old('training_flag', $asesmen->training_flag ?? '0') == '0' ? 'checked' : '' }}>
                                        <div class="ms-3 flex-grow-1">
                                            <label for="training-no" class="form-label fw-bold mb-1"
                                                style="cursor: pointer;">
                                                <i class="bi bi-x-circle text-danger"></i> Tidak, Hanya Asesmen
                                            </label>
                                            <p class="text-muted small mb-0">
                                                Saya siap untuk langsung mengikuti asesmen.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="training-option" onclick="selectTraining(true)" id="option-yes">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="training_flag" value="1" id="training-yes"
                                            {{ old('training_flag', $asesmen->training_flag ?? '0') == '1' ? 'checked' : '' }}>
                                        <div class="ms-3 flex-grow-1">
                                            <label for="training-yes" class="form-label fw-bold mb-1"
                                                style="cursor: pointer;">
                                                <i class="bi bi-check-circle text-success"></i> Ya, Ikut Pelatihan
                                            </label>
                                            <p class="text-muted small mb-2">
                                                Saya ingin mengikuti pelatihan.
                                            </p>
                                            <div class="alert alert-warning mb-0 py-2">
                                                <small>
                                                    <i class="bi bi-info-circle-fill"></i>
                                                    <strong>Biaya Tambahan:</strong>
                                                    <span class="price-badge ms-2">Rp 1.500.000</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <i class="bi bi-lightbulb-fill"></i>
                                <strong>Catatan:</strong>
                                Biaya pelatihan akan ditambahkan ke total biaya sertifikasi Anda dan akan diinformasikan
                                setelah verifikasi.
                            </small>
                        </div>
                    </div>

                    <!-- Upload Dokumen -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Upload Dokumen</h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pas Foto Formal (3x4) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" name="photo"
                                accept="image/*" required>
                            <small class="text-muted">Format: JPG/PNG, Max: 10MB, Latar merah</small>
                            @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">KTP <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('ktp') is-invalid @enderror" name="ktp"
                                accept="application/pdf" required>
                            <small class="text-muted">Format: PDF, Max: 10MB</small>
                            @error('ktp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ijazah/Transkrip <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('document') is-invalid @enderror"
                                name="document" accept="application/pdf" required>
                            <small class="text-muted">Format: PDF, Max: 10MB</small>
                            @error('document')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal"
                            data-bs-target="#confirmSubmitModal">
                            <i class="bi bi-save"></i> Submit Data
                        </button>
                        <a href="{{ route('asesi.dashboard') }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
                @endif

                <!-- Modal Konfirmasi Submit -->
                <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title" id="confirmSubmitLabel">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Pengiriman Data
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <p class="mb-2">
                                    Apakah Anda yakin semua data yang diisi sudah:
                                </p>
                                <ul>
                                    <li>Sesuai dengan <strong>KTP / Ijazah</strong></li>
                                    <li>Tidak ada kesalahan penulisan</li>
                                    <li>Dokumen yang diunggah sudah benar</li>
                                    <li>Pilihan pelatihan sudah sesuai keinginan</li>
                                </ul>

                                <div id="training-confirmation" style="display: none;">
                                    <div class="alert alert-warning mb-3">
                                        <i class="bi bi-mortarboard-fill"></i>
                                        <strong>Anda memilih: IKUT PELATIHAN</strong><br>
                                        <small>Biaya tambahan Rp 1.500.000 akan ditambahkan ke total biaya
                                            sertifikasi.</small>
                                    </div>
                                </div>

                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <strong>Perhatian:</strong>
                                    Data yang sudah dikirim <u>tidak dapat diubah</u> setelah diverifikasi oleh Admin
                                    LSP.
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-arrow-left"></i> Periksa Ulang
                                </button>

                                <!-- INI YANG BENAR-BENAR SUBMIT -->
                                <button type="button" class="btn btn-danger" onclick="submitAsesiForm()">
                                    <i class="bi bi-check-circle-fill"></i> Ya, Kirim Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ✅ Fungsi global untuk select training
function selectTraining(wantTraining) {
    // Remove selected class from both
    document.getElementById('option-no').classList.remove('selected');
    document.getElementById('option-yes').classList.remove('selected');

    // Add selected class to clicked option
    if (wantTraining) {
        document.getElementById('option-yes').classList.add('selected');
        document.getElementById('training-yes').checked = true;
    } else {
        document.getElementById('option-no').classList.add('selected');
        document.getElementById('training-no').checked = true;
    }

    // Update modal confirmation
    updateModalConfirmation();
}

// ✅ Fungsi global untuk update modal
function updateModalConfirmation() {
    const trainingYes = document.getElementById('training-yes');
    const confirmationDiv = document.getElementById('training-confirmation');

    if (trainingYes && trainingYes.checked) {
        confirmationDiv.style.display = 'block';
    } else {
        confirmationDiv.style.display = 'none';
    }
}

// ✅ Fungsi global untuk submit form
function submitAsesiForm() {
    document.querySelector('form[action="{{ route('asesi.store-data') }}"]').submit();
}

// ✅ DOM Ready untuk inisialisasi
document.addEventListener('DOMContentLoaded', function() {
    const nikInput = document.querySelector('input[name="nik"]');
    const provinceInput = document.querySelector('input[name="province_code"]');
    const cityInput = document.querySelector('input[name="city_code"]');

    // Auto-fill province & city code from NIK
    if (nikInput) {
        nikInput.addEventListener('input', function() {
            const nik = this.value.replace(/\D/g, ''); // hanya angka

            if (nik.length >= 2) {
                provinceInput.value = nik.substring(0, 2);
            } else {
                provinceInput.value = '';
            }

            if (nik.length >= 4) {
                cityInput.value = nik.substring(2, 4);
            } else {
                cityInput.value = '';
            }
        });
    }

    // Set initial selected state based on checked radio
    const trainingYesRadio = document.getElementById('training-yes');
    const trainingNoRadio = document.getElementById('training-no');
    
    if (trainingYesRadio && trainingYesRadio.checked) {
        document.getElementById('option-yes').classList.add('selected');
    } else if (trainingNoRadio) {
        document.getElementById('option-no').classList.add('selected');
    }

    // Update modal when modal is shown
    const confirmModal = document.getElementById('confirmSubmitModal');
    if (confirmModal) {
        confirmModal.addEventListener('show.bs.modal', function() {
            updateModalConfirmation();
        });
    }

    // Add click event to radio buttons (backup jika onclick div tidak jalan)
    if (trainingYesRadio) {
        trainingYesRadio.addEventListener('change', function() {
            if (this.checked) {
                selectTraining(true);
            }
        });
    }

    if (trainingNoRadio) {
        trainingNoRadio.addEventListener('change', function() {
            if (this.checked) {
                selectTraining(false);
            }
        });
    }
});
</script>
@endpush
@endsection