@extends('layouts.app')

@section('title', $viewOnly ? 'Lihat Data Pribadi' : ($asesmen->biodata_needs_revision ? 'Revisi Data Pribadi' : 'Lengkapi Data Pribadi'))
@section('page-title', $viewOnly ? 'Lihat Data Pribadi' : ($asesmen->biodata_needs_revision ? 'Revisi Data Pribadi' : 'Lengkapi Data Pribadi'))

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
.locked-field option { color: #6c757d; }
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
.training-option:hover { border-color: #0d6efd; background-color: #e7f1ff; }
.training-option.selected { border-color: #0d6efd; background-color: #cfe2ff; }
.training-option input[type="radio"] { width: 20px; height: 20px; cursor: pointer; }
.price-badge { font-size: 1.2rem; font-weight: bold; color: #198754; }

/* Preview dokumen existing */
.doc-existing {
    border: 1px solid #d1fae5;
    background: #f0fdf4;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 8px;
}
.doc-existing.rejected { border-color: #fecaca; background: #fef2f2; }
</style>
@endpush

@section('content')

@php
    $maxBirthDate = now()->subYears(12)->translatedFormat('Y-m-d');
    $minBirthDate = now()->subYears(80)->translatedFormat('Y-m-d');
    $isRevision   = $asesmen->biodata_needs_revision ?? false;
@endphp

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header {{ $viewOnly ? 'bg-info' : ($isRevision ? 'bg-warning' : 'bg-primary') }} text-{{ $isRevision ? 'dark' : 'white' }}">
                <h5 class="mb-0">
                    <i class="bi {{ $viewOnly ? 'bi-eye' : ($isRevision ? 'bi-pencil-square' : 'bi-clipboard-data') }}"></i>
                    {{ $viewOnly ? 'Data Pribadi Asesi (View Only)' : ($isRevision ? 'Revisi Data Pribadi' : 'Form Data Pribadi Asesi') }}
                </h5>
            </div>
            <div class="card-body">

            {{-- Alert Rejection --}}
            @if($isRevision)
            <div class="alert alert-danger border-0 shadow-sm d-flex gap-3 align-items-start mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-3 flex-shrink-0 mt-1"></i>
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1">Biodata Dikembalikan oleh Admin LSP</h6>
                    <p class="mb-2 small">Perbaiki sesuai catatan di bawah, lalu submit ulang. Data yang sudah benar tidak perlu diubah.</p>
                    <div class="bg-white border border-danger rounded p-3 mb-2">
                        <strong class="small text-danger"><i class="bi bi-chat-left-text me-1"></i>Catatan Admin:</strong>
                        <p class="mb-0 mt-1">{{ $asesmen->biodata_rejection_notes }}</p>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>Dikembalikan pada {{ $asesmen->biodata_rejected_at?->translatedFormat('d M Y H:i') ?? '-' }}
                    </small>
                </div>
            </div>
            @endif

            @if($viewOnly)
                {{-- ── VIEW-ONLY MODE ──────────────────────────────── --}}
                @if($asesmen->biodata_verified_at)
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="bi bi-patch-check-fill fs-5"></i>
                    <div>
                        <strong>Biodata Terverifikasi</strong>
                        <span class="text-muted small ms-2">
                            {{ \Carbon\Carbon::parse($asesmen->biodata_verified_at)->translatedFormat('d M Y H:i') }}
                        </span>
                    </div>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Data Anda sudah disubmit dan sedang dalam proses verifikasi.
                </div>
                @endif

                {{-- Data Pribadi --}}
                <div class="card mb-3">
                    <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-person"></i> Data Pribadi</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><td width="150"><strong>Nama Lengkap</strong></td><td>: {{ $asesmen->full_name }}</td></tr>
                                    <tr><td><strong>NIK</strong></td><td>: {{ $asesmen->nik }}</td></tr>
                                    <tr><td><strong>Tempat Lahir</strong></td><td>: {{ $asesmen->birth_place }}</td></tr>
                                    <tr><td><strong>Tanggal Lahir</strong></td><td>: {{ \Carbon\Carbon::parse($asesmen->birth_date)->translatedFormat('d F Y') }}</td></tr>
                                    <tr><td><strong>Jenis Kelamin</strong></td><td>: {{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                                    <tr><td><strong>Email</strong></td><td>: {{ auth()->user()->email }}</td></tr>
                                    <tr><td><strong>Telepon</strong></td><td>: {{ $asesmen->phone }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><td width="150"><strong>Alamat</strong></td><td>: {{ $asesmen->address }}</td></tr>
                                    <tr><td><strong>Kode Provinsi</strong></td><td>: {{ $asesmen->province_code }}</td></tr>
                                    <tr><td><strong>Kode Kota</strong></td><td>: {{ $asesmen->city_code }}</td></tr>
                                    <tr><td><strong>Pendidikan</strong></td><td>: {{ $asesmen->education }}</td></tr>
                                    <tr><td><strong>Pekerjaan</strong></td><td>: {{ $asesmen->occupation }}</td></tr>
                                    <tr><td><strong>Sumber Anggaran</strong></td><td>: {{ $asesmen->budget_source }}</td></tr>
                                    <tr><td><strong>Asal Lembaga</strong></td><td>: {{ $asesmen->institution }}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Sertifikasi --}}
                <div class="card mb-3">
                    <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-award"></i> Data Sertifikasi</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr><td width="150"><strong>Skema Sertifikasi</strong></td><td>: {{ $asesmen->skema->name ?? '-' }}</td></tr>
                                    <tr><td><strong>TUK</strong></td><td>: {{ $asesmen->tuk->name ?? '-' }}</td></tr>
                                    <tr><td><strong>Tanggal Dipilih</strong></td><td>: {{ $asesmen->preferred_date ? \Carbon\Carbon::parse($asesmen->preferred_date)->translatedFormat('d F Y') : '-' }}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td width="150"><strong>Jenis Pendaftaran</strong></td>
                                        <td>: @if($asesmen->is_collective)<span class="badge bg-primary">Kolektif</span>@else<span class="badge bg-success">Mandiri</span>@endif</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pelatihan</strong></td>
                                        <td>: @if($asesmen->training_flag)<span class="badge bg-success">Ya (+ Rp 1.500.000)</span>@else<span class="badge bg-secondary">Tidak</span>@endif</td>
                                    </tr>
                                    @if($asesmen->fee_amount)
                                    <tr><td><strong>Total Biaya</strong></td><td>: <span class="text-success fw-bold">Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</span></td></tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dokumen --}}
                <div class="card mb-3">
                    <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-file-earmark"></i> Dokumen</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <h6>Pas Foto</h6>
                                @if($asesmen->photo_path)
                                    <img src="{{ asset('storage/' . $asesmen->photo_path) }}" alt="Foto" class="img-thumbnail mb-2" style="max-height:200px;">
                                    <br><a href="{{ asset('storage/' . $asesmen->photo_path) }}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> Lihat</a>
                                @else<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Belum upload</div>@endif
                            </div>
                            <div class="col-md-4 text-center">
                                <h6>KTP</h6>
                                @if($asesmen->ktp_path)
                                    <iframe src="{{ asset('storage/' . $asesmen->ktp_path) }}" style="width:100%;height:200px;border:1px solid #ddd;" class="mb-2"></iframe>
                                    <br><a href="{{ asset('storage/' . $asesmen->ktp_path) }}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-download"></i> Download</a>
                                @else<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Belum upload</div>@endif
                            </div>
                            <div class="col-md-4 text-center">
                                <h6>Ijazah/Transkrip</h6>
                                @if($asesmen->document_path)
                                    <iframe src="{{ asset('storage/' . $asesmen->document_path) }}" style="width:100%;height:200px;border:1px solid #ddd;" class="mb-2"></iframe>
                                    <br><a href="{{ asset('storage/' . $asesmen->document_path) }}" target="_blank" class="btn btn-sm btn-primary"><i class="bi bi-download"></i> Download</a>
                                @else<div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> Belum upload</div>@endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('asesi.dashboard') }}" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    <a href="{{ route('asesi.tracking') }}" class="btn btn-info btn-lg">
                        <i class="bi bi-clock-history"></i> Lihat Timeline
                    </a>
                </div>

            @else
                {{-- ── EDITABLE FORM MODE (termasuk revision) ─────── --}}
                @if(!$isRevision)
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Lengkapi semua data dengan benar sesuai dokumen resmi (KTP/Ijazah).
                </div>
                @endif

                <form method="POST" action="{{ route('asesi.store-data') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Data Pribadi -->
                    <h6 class="border-bottom pb-2 mb-3">Data Pribadi</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror"
                                name="full_name"
                                value="{{ old('full_name', $asesmen->full_name ?? '') }}" required>
                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIK KTP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror"
                                name="nik" maxlength="16" pattern="\d{16}"
                                value="{{ old('nik', $asesmen->nik ?? '') }}" required>
                            <small class="text-muted">16 digit</small>
                            @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('birth_place') is-invalid @enderror"
                                name="birth_place"
                                value="{{ old('birth_place', $asesmen->birth_place ?? '') }}" required>
                            @error('birth_place')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('birth_date') is-invalid @enderror"
                                name="birth_date" id="input-birth-date"
                                value="{{ old('birth_date', $asesmen->birth_date ? \Carbon\Carbon::parse($asesmen->birth_date)->format('Y-m-d') : '') }}"
                                placeholder="Pilih tanggal lahir" readonly required>
                            @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" name="gender" required>
                                <option value="">Pilih</option>
                                <option value="L" {{ old('gender', $asesmen->gender ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $asesmen->gender ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon/HP <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                name="phone"
                                value="{{ old('phone', $asesmen->phone ?? '') }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Alamat Tempat Tinggal <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                name="address" rows="3" required>{{ old('address', $asesmen->address ?? '') }}</textarea>
                            <small class="text-muted">Sesuai KTP</small>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Provinsi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control locked-field @error('province_code') is-invalid @enderror"
                                name="province_code" maxlength="2"
                                value="{{ old('province_code', $asesmen->province_code ?? '') }}" required readonly>
                            <small class="text-muted">2 digit awal dari NIK</small>
                            @error('province_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Kota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control locked-field @error('city_code') is-invalid @enderror"
                                name="city_code" maxlength="4"
                                value="{{ old('city_code', $asesmen->city_code ?? '') }}" required readonly>
                            <small class="text-muted">4 digit setelah kode provinsi NIK</small>
                            @error('city_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <!-- Data Pendidikan & Pekerjaan -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Data Pendidikan & Pekerjaan</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-select @error('education') is-invalid @enderror" name="education" required>
                                <option value="">Pilih</option>
                                @foreach(['SD','SMP','SMA/SMK','D3','S1','S2','S3'] as $edu)
                                <option value="{{ $edu }}" {{ old('education', $asesmen->education ?? '') == $edu ? 'selected' : '' }}>{{ $edu }}</option>
                                @endforeach
                            </select>
                            @error('education')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pekerjaan <span class="text-danger">*</span></label>
                            <select class="form-select @error('occupation') is-invalid @enderror" name="occupation" required>
                                <option value="">Pilih</option>
                                @foreach(['Siswa','Mahasiswa','Guru','Dosen','Karyawan Swasta','PNS','Lainnya'] as $occ)
                                <option value="{{ $occ }}" {{ old('occupation', $asesmen->occupation ?? '') == $occ ? 'selected' : '' }}>{{ $occ }}</option>
                                @endforeach
                            </select>
                            @error('occupation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sumber Anggaran <span class="text-danger">*</span></label>
                            <select class="form-select @error('budget_source') is-invalid @enderror" name="budget_source" required>
                                <option value="">Pilih</option>
                                @foreach(['Mandiri','APBN/APBD','Lembaga'] as $bs)
                                <option value="{{ $bs }}" {{ old('budget_source', $asesmen->budget_source ?? '') == $bs ? 'selected' : '' }}>{{ $bs }}</option>
                                @endforeach
                            </select>
                            @error('budget_source')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asal Lembaga <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('institution') is-invalid @enderror"
                                name="institution"
                                value="{{ old('institution', $asesmen->institution ?? '') }}" required>
                            @error('institution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <!-- Data Sertifikasi -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Data Sertifikasi</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Skema Sertifikasi <span class="text-danger">*</span></label>
                            @if($asesmen->skema_id)
                            <select class="form-select locked-field" name="skema_id" disabled style="pointer-events:none;background-color:#e9ecef;appearance:none;" required>
                                <option selected>{{ $asesmen->skema->name ?? 'Skema terkunci' }}</option>
                            </select>
                            <input type="hidden" name="skema_id" value="{{ $asesmen->skema_id }}" required>
                            @else
                            <select class="form-select @error('skema_id') is-invalid @enderror" name="skema_id" required>
                                <option value="">Pilih Skema</option>
                                @foreach($skemas as $skema)
                                <option value="{{ $skema->id }}" {{ old('skema_id') == $skema->id ? 'selected' : '' }}>{{ $skema->name }}</option>
                                @endforeach
                            </select>
                            @endif
                            @error('skema_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi Ujian (TUK) <span class="text-danger">*</span></label>
                            @if($asesmen->tuk_id)
                            <select class="form-select locked-field" name="tuk_id" disabled style="pointer-events:none;background-color:#e9ecef;appearance:none;">
                                <option selected>{{ $asesmen->tuk->name ?? 'TUK terkunci' }}</option>
                            </select>
                            <input type="hidden" name="tuk_id" value="{{ $asesmen->tuk_id }}">
                            @else
                            <select class="form-select @error('tuk_id') is-invalid @enderror" name="tuk_id" required>
                                <option value="">Pilih TUK</option>
                                @foreach($tuks as $tuk)
                                <option value="{{ $tuk->id }}" {{ old('tuk_id') == $tuk->id ? 'selected' : '' }}>{{ $tuk->name }} - {{ $tuk->address }}</option>
                                @endforeach
                            </select>
                            @endif
                            @error('tuk_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if(!$isCollective)
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Sertifikasi yang Dipilih <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('preferred_date') is-invalid @enderror"
                                name="preferred_date" id="input-preferred-date"
                                value="{{ old('preferred_date', $asesmen->preferred_date ? \Carbon\Carbon::parse($asesmen->preferred_date)->format('Y-m-d') : '') }}"
                                placeholder="Pilih tanggal" readonly required>
                            @error('preferred_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @else
                        <div class="col-12 mb-3">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Pendaftaran Kolektif:</strong> Tanggal asesmen dan opsi pelatihan sudah ditentukan oleh TUK Anda.
                            </div>
                        </div>
                        @endif
                    </div>

                    @if(!$isCollective)
                    <!-- SECTION PELATIHAN -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="bi bi-mortarboard-fill"></i> Pelatihan (Opsional)</h6>
                    <div class="training-section">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="training-option" onclick="selectTraining(false)" id="option-no">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="training_flag" value="0" id="training-no"
                                            {{ old('training_flag', $asesmen->training_flag ?? '0') == '0' ? 'checked' : '' }}>
                                        <div class="ms-3 flex-grow-1">
                                            <label for="training-no" class="form-label fw-bold mb-1" style="cursor:pointer;">
                                                <i class="bi bi-x-circle text-danger"></i> Tidak, Hanya Asesmen
                                            </label>
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
                                            <label for="training-yes" class="form-label fw-bold mb-1" style="cursor:pointer;">
                                                <i class="bi bi-check-circle text-success"></i> Ya, Ikut Pelatihan
                                            </label>
                                            <div class="alert alert-warning mb-0 py-2">
                                                <small><strong>Biaya Tambahan:</strong> <span class="price-badge ms-2">Rp 1.500.000</span></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Upload Dokumen -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Upload Dokumen</h6>

                    @if($isRevision)
                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <small>Dokumen yang sudah ada <strong>tidak perlu di-upload ulang</strong> jika masih sesuai.
                        Upload file baru hanya jika dokumen tersebut yang perlu diganti.</small>
                    </div>
                    @endif

                    <div class="row">
                        {{-- Pas Foto --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                Pas Foto Formal (3x4)
                                @if(!$asesmen->photo_path)<span class="text-danger">*</span>@endif
                            </label>
                            @if($asesmen->photo_path && $isRevision)
                            <div class="doc-existing mb-2 text-center">
                                <img src="{{ asset('storage/' . $asesmen->photo_path) }}"
                                     class="img-thumbnail mb-2" style="max-height:120px; object-fit:cover;">
                                <div class="small text-success"><i class="bi bi-check-circle me-1"></i>Foto sudah ada</div>
                                <a href="{{ asset('storage/' . $asesmen->photo_path) }}" target="_blank" class="btn btn-xs btn-outline-secondary btn-sm mt-1">
                                    <i class="bi bi-eye me-1"></i>Lihat
                                </a>
                            </div>
                            <div class="small text-muted mb-1">Ganti foto (opsional):</div>
                            @endif
                            <input type="file" class="form-control @error('photo') is-invalid @enderror"
                                name="photo" accept="image/*"
                                {{ !$asesmen->photo_path ? 'required' : '' }}>
                            <small class="text-muted">JPG/PNG, Max: 10MB, Latar merah</small>
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- KTP --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                KTP
                                @if(!$asesmen->ktp_path)<span class="text-danger">*</span>@endif
                            </label>
                            @if($asesmen->ktp_path && $isRevision)
                            <div class="doc-existing mb-2 text-center">
                                <i class="bi bi-file-earmark-pdf text-danger d-block" style="font-size:2.5rem;"></i>
                                <div class="small text-success mt-1"><i class="bi bi-check-circle me-1"></i>KTP sudah ada</div>
                                <a href="{{ asset('storage/' . $asesmen->ktp_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                    <i class="bi bi-eye me-1"></i>Lihat
                                </a>
                            </div>
                            <div class="small text-muted mb-1">Ganti KTP (opsional):</div>
                            @endif
                            <input type="file" class="form-control @error('ktp') is-invalid @enderror"
                                name="ktp" accept="application/pdf"
                                {{ !$asesmen->ktp_path ? 'required' : '' }}>
                            <small class="text-muted">PDF, Max: 10MB</small>
                            @error('ktp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Ijazah --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                Ijazah/Transkrip
                                @if(!$asesmen->document_path)<span class="text-danger">*</span>@endif
                            </label>
                            @if($asesmen->document_path && $isRevision)
                            <div class="doc-existing mb-2 text-center">
                                <i class="bi bi-file-earmark-pdf text-danger d-block" style="font-size:2.5rem;"></i>
                                <div class="small text-success mt-1"><i class="bi bi-check-circle me-1"></i>Ijazah sudah ada</div>
                                <a href="{{ asset('storage/' . $asesmen->document_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                    <i class="bi bi-eye me-1"></i>Lihat
                                </a>
                            </div>
                            <div class="small text-muted mb-1">Ganti ijazah (opsional):</div>
                            @endif
                            <input type="file" class="form-control @error('document') is-invalid @enderror"
                                name="document" accept="application/pdf"
                                {{ !$asesmen->document_path ? 'required' : '' }}>
                            <small class="text-muted">PDF, Max: 10MB</small>
                            @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-{{ $isRevision ? 'warning' : 'primary' }} btn-lg"
                            data-bs-toggle="modal" data-bs-target="#confirmSubmitModal">
                            <i class="bi bi-{{ $isRevision ? 'arrow-repeat' : 'save' }}"></i>
                            {{ $isRevision ? 'Submit Revisi' : 'Submit Data' }}
                        </button>
                        <a href="{{ route('asesi.dashboard') }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            @endif

            <!-- Modal Konfirmasi Submit -->
            <div class="modal fade" id="confirmSubmitModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-{{ $isRevision ? 'warning' : 'warning' }}">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                {{ $isRevision ? 'Konfirmasi Submit Revisi' : 'Konfirmasi Pengiriman Data' }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @if($isRevision)
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Anda akan mengirim ulang data yang sudah direvisi sesuai catatan admin.
                            </div>
                            @endif
                            <p class="mb-2">Pastikan semua data sudah benar sesuai KTP/Ijazah.</p>
                            <div id="training-confirmation" style="display:none;">
                                <div class="alert alert-warning mb-3">
                                    <i class="bi bi-mortarboard-fill"></i>
                                    <strong>Anda memilih: IKUT PELATIHAN</strong><br>
                                    <small>Biaya tambahan Rp 1.500.000 akan ditambahkan.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-arrow-left"></i> Periksa Ulang
                            </button>
                            <button type="button" class="btn btn-danger" onclick="submitAsesiForm()">
                                <i class="bi bi-check-circle-fill"></i> Ya, Kirim
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            </div>{{-- card-body --}}
        </div>{{-- card --}}
    </div>{{-- col --}}
</div>{{-- row --}}

@push('scripts')
<script>
function selectTraining(wantTraining) {
    document.getElementById('option-no')?.classList.remove('selected');
    document.getElementById('option-yes')?.classList.remove('selected');
    if (wantTraining) {
        document.getElementById('option-yes')?.classList.add('selected');
        document.getElementById('training-yes').checked = true;
    } else {
        document.getElementById('option-no')?.classList.add('selected');
        document.getElementById('training-no').checked = true;
    }
    updateModalConfirmation();
}

function updateModalConfirmation() {
    const trainingYes = document.getElementById('training-yes');
    const confirmDiv  = document.getElementById('training-confirmation');
    if (trainingYes && trainingYes.checked && confirmDiv) {
        confirmDiv.style.display = 'block';
    } else if (confirmDiv) {
        confirmDiv.style.display = 'none';
    }
}

function submitAsesiForm() {
    // Validasi file sebelum submit untuk cegah ERR_UPLOAD_FILE_CHANGED
    const fileInputs = document.querySelectorAll('input[type="file"]');
    for (const input of fileInputs) {
        if (input.files && input.files.length > 0 && input.files[0].size === 0) {
            bootstrap.Modal.getInstance(document.getElementById('confirmSubmitModal'))?.hide();
            Swal.fire({
                icon: 'error',
                title: 'File Tidak Dapat Dibaca',
                html: `File yang Anda pilih tidak dapat diakses lagi oleh browser.<br><br>
                       <strong>Solusi:</strong> Pilih ulang file dokumen Anda, lalu submit kembali.`,
                confirmButtonText: 'OK, Pilih Ulang',
            });
            return;
        }
    }

    const form = document.querySelector('form[action="{{ route('asesi.store-data') }}"]');
    if (form) form.submit(); // ← null check, aman di view-only mode
}

document.addEventListener('DOMContentLoaded', function () {
    // Auto-fill province & city dari NIK
    const nikInput      = document.querySelector('input[name="nik"]');
    const provinceInput = document.querySelector('input[name="province_code"]');
    const cityInput     = document.querySelector('input[name="city_code"]');
    if (nikInput) {
        nikInput.addEventListener('input', function () {
            const nik = this.value.replace(/\D/g, '');
            if (provinceInput) provinceInput.value = nik.length >= 2 ? nik.substring(0, 2) : '';
            if (cityInput)     cityInput.value     = nik.length >= 4 ? nik.substring(0, 4) : '';
        });
    }

    // Validasi tanggal lahir
    const birthInput = document.querySelector('input[name="birth_date"]');
    if (birthInput) {
        birthInput.addEventListener('change', function () {
            const val = this.value;
            if (!val) return;
            const ageYears = (new Date() - new Date(val)) / (1000 * 60 * 60 * 24 * 365.25);
            if (ageYears < 12) {
                this.setCustomValidity('Usia minimal 12 tahun.');
                this.reportValidity();
            } else if (ageYears > 80) {
                this.setCustomValidity('Usia maksimal 80 tahun.');
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Set initial training highlight
    const trainingYes = document.getElementById('training-yes');
    const trainingNo  = document.getElementById('training-no');
    if (trainingYes?.checked) document.getElementById('option-yes')?.classList.add('selected');
    else if (trainingNo)      document.getElementById('option-no')?.classList.add('selected');

    // Modal open
    const confirmModal = document.getElementById('confirmSubmitModal');
    if (confirmModal) confirmModal.addEventListener('show.bs.modal', updateModalConfirmation);

    if (trainingYes) trainingYes.addEventListener('change', () => selectTraining(true));
    if (trainingNo)  trainingNo.addEventListener('change',  () => selectTraining(false));
});

// Flatpickr locale Indonesia
const fpLocale = {
    months: {
        shorthand: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'],
        longhand:  ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
    },
    weekdays: {
        shorthand: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
        longhand:  ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
    },
    firstDayOfWeek: 1,
};

// Tanggal lahir
flatpickr('#input-birth-date', {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'd F Y',
    locale: fpLocale,
    maxDate: 'today',
});

// Preferred date (hanya untuk mandiri)
const prefEl = document.getElementById('input-preferred-date');
if (prefEl) {
    flatpickr('#input-preferred-date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd F Y',
        locale: fpLocale,
        minDate: new Date().fp_incr(3),
    });
}
</script>
@endpush
@endsection