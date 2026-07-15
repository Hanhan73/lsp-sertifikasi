@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('sidebar')
    @include('asesor.partials.sidebar')
@endsection

@section('content')
@php $asesor = $user->asesor; @endphp
@include('profile.partials.alerts')

<div class="row g-4">

    {{-- Kartu Identitas Asesor --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                {{-- Foto --}}
                <div class="mb-3">
                    @if($asesor?->foto_path)
                    <img src="{{ $asesor->foto_url }}"
                        class="rounded-circle border shadow-sm"
                        style="width:90px;height:90px;object-fit:cover;" alt="Foto">
                    @else
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold mx-auto"
                        style="width:90px;height:90px;font-size:2rem;">
                        {{ strtoupper(substr($asesor?->nama ?? $user->name, 0, 1)) }}
                    </div>
                    @endif
                </div>

                <h6 class="fw-bold mb-0">{{ $asesor?->nama ?? $user->name }}</h6>
                <div class="text-muted small mb-2">{{ $user->email }}</div>
                <span class="badge bg-{{ $asesor?->status_badge ?? 'secondary' }} mb-3">
                    {{ $asesor?->status_label ?? 'Asesor' }}
                </span>

                {{-- Upload foto --}}
                <form action="{{ route('profile.upload-foto-asesor') }}" method="POST" enctype="multipart/form-data"
                      id="form-foto-asesor">
                    @csrf
                    <input type="file" name="foto" id="foto-asesor" class="d-none"
                           accept="image/jpeg,image/png,image/jpg"
                           onchange="document.getElementById('form-foto-asesor').submit()">
                </form>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100"
                            onclick="document.getElementById('foto-asesor').click()">
                        <i class="bi bi-camera me-1"></i> Ganti Foto
                    </button>

                    @if($asesor?->foto_path)
                    <form action="{{ route('profile.delete-foto-asesor') }}" method="POST"
                          onsubmit="return confirm('Hapus foto profil ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Foto">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                    @endif
                </div>

                @error('foto')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                <hr>

                {{-- Data identitas: view / edit toggle --}}
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-semibold small text-primary">
                        <i class="bi bi-person-vcard me-1"></i>Data Identitas
                    </span>
                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" onclick="toggleEditAsesor()">
                        <span id="edit-asesor-label"><i class="bi bi-pencil"></i> Edit</span>
                    </button>
                </div>

                {{-- Mode: view --}}
                <div id="asesor-view" class="text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">NIK</span>
                        <span class="fw-semibold font-monospace">{{ $asesor?->nik ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">TTL</span>
                        <span>{{ $asesor?->tempat_lahir ?? '-' }}, {{ $asesor?->tanggal_lahir?->translatedFormat('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Jenis Kelamin</span>
                        <span>{{ $asesor?->jenis_kelamin === 'L' ? 'Laki-laki' : ($asesor?->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Alamat</span>
                        <span class="text-end">{{ $asesor?->alamat ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Kota / Provinsi</span>
                        <span>{{ $asesor?->kota ?? '-' }}, {{ $asesor?->provinsi ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Telepon</span>
                        <span>{{ $asesor?->telepon ?? '-' }}</span>
                    </div>
                    <div class="text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">No. Reg. Met.</span>
                        <span class="fw-semibold">{{ $asesor?->no_reg_met ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">No. Blanko</span>
                        <span class="fw-semibold">{{ $asesor?->no_blanko ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">SIAPKerja</span>
                        @if($asesor?->siap_kerja === 'Memiliki')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Memiliki</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Tidak</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Status Registrasi</span>
                        <span class="badge bg-{{ $asesor?->status_badge ?? 'secondary' }}">{{ $asesor?->status_label ?? '-' }}</span>
                    </div>
                </div>
                </div>

{{-- Mode: edit --}}
                <form id="asesor-edit" action="{{ route('profile.update-asesor-data') }}" method="POST"
                      class="text-start small d-none">
                    @csrf @method('PUT')

                    <div class="mb-2">
                        <label class="form-label small mb-1">Nama Lengkap</label>
                        <input type="text" name="nama"
                               class="form-control form-control-sm @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $asesor?->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label small mb-1">NIK</label>
                        <input type="text" name="nik" maxlength="16"
                               class="form-control form-control-sm @error('nik') is-invalid @enderror"
                               value="{{ old('nik', $asesor?->nik) }}" required>
                        @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-7">
                            <label class="form-label small mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir"
                                   class="form-control form-control-sm @error('tempat_lahir') is-invalid @enderror"
                                   value="{{ old('tempat_lahir', $asesor?->tempat_lahir) }}" required>
                            @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-5">
                            <label class="form-label small mb-1">Tgl Lahir</label>
                            <input type="date" name="tanggal_lahir"
                                   class="form-control form-control-sm @error('tanggal_lahir') is-invalid @enderror"
                                   value="{{ old('tanggal_lahir', $asesor?->tanggal_lahir?->format('Y-m-d')) }}" required>
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select form-select-sm" required>
                            <option value="L" {{ old('jenis_kelamin', $asesor?->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $asesor?->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small mb-1">Alamat</label>
                        <textarea name="alamat" rows="2" class="form-control form-control-sm">{{ old('alamat', $asesor?->alamat) }}</textarea>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Kota</label>
                            <input type="text" name="kota" class="form-control form-control-sm"
                                   value="{{ old('kota', $asesor?->kota) }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control form-control-sm"
                                   value="{{ old('provinsi', $asesor?->provinsi) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small mb-1">No. Telepon</label>
                        <input type="text" name="telepon" class="form-control form-control-sm"
                               value="{{ old('telepon', $asesor?->telepon) }}">
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">No. Reg. Met.</label>
                            <input type="text" name="no_reg_met" class="form-control form-control-sm @error('no_reg_met') is-invalid @enderror"
                                   value="{{ old('no_reg_met', $asesor?->no_reg_met) }}">
                            @error('no_reg_met')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">No. Blanko</label>
                            <input type="text" name="no_blanko" class="form-control form-control-sm @error('no_blanko') is-invalid @enderror"
                                   value="{{ old('no_blanko', $asesor?->no_blanko) }}">
                            @error('no_blanko')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small mb-1">SIAPKerja</label>
                        <select name="siap_kerja" class="form-select form-select-sm @error('siap_kerja') is-invalid @enderror" required>
                            <option value="Memiliki" {{ old('siap_kerja', $asesor?->siap_kerja) === 'Memiliki' ? 'selected' : '' }}>✅ Memiliki</option>
                            <option value="Tidak" {{ old('siap_kerja', $asesor?->siap_kerja) === 'Tidak' ? 'selected' : '' }}>❌ Tidak Memiliki</option>
                        </select>
                        @error('siap_kerja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label small mb-1">Status Registrasi</label>
                        <select name="status_reg" id="status_reg" class="form-select form-select-sm @error('status_reg') is-invalid @enderror" required>
                            <option value="aktif" {{ old('status_reg', $asesor?->status_reg) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="expire" {{ old('status_reg', $asesor?->status_reg) === 'expire' ? 'selected' : '' }}>Expire</option>
                            <option value="nonaktif" {{ old('status_reg', $asesor?->status_reg) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('status_reg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3" id="expire-date-wrapper"
                         style="display: {{ old('status_reg', $asesor?->status_reg) === 'expire' ? 'block' : 'none' }};">
                        <label class="form-label small mb-1">Tanggal Expire</label>
                        <input type="date" name="expire_date"
                               class="form-control form-control-sm @error('expire_date') is-invalid @enderror"
                               value="{{ old('expire_date', $asesor?->expire_date?->format('Y-m-d')) }}">
                        @error('expire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleEditAsesor()">
                            Batal
                        </button>
                    </div>
                </form>

                <hr>

                {{-- Data registrasi — view mode, TETAP tampil di sini juga untuk quick-glance --}}
                <div class="text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">No. Reg. Met.</span>
                        <span class="fw-semibold">{{ $asesor?->no_reg_met ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">No. Blanko</span>
                        <span class="fw-semibold">{{ $asesor?->no_blanko ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">SIAPKerja</span>
                        @if($asesor?->siap_kerja === 'Memiliki')
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Memiliki</span>
                        @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Tidak</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kolom kanan --}}
    <div class="col-lg-8">

        {{-- Info akun (email saja, nama sinkron dari tabel asesors) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="bi bi-person me-2 text-primary"></i>Informasi Akun
            </div>
            <div class="card-body p-4">
                <form action="{{ route('profile.update-info') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Nama (sinkron dengan data asesor)</label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Nama ini hanya mengubah tampilan akun. Data resmi tetap dikelola admin.</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold">Email Akun</label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-save me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Form rekening asesor --}}
        @include('profile.partials.rekening-asesor')

        {{-- Dokumen Pendukung Asesor --}}
        <div class="mb-4">
            @include('partials.asesor-documents', ['asesor' => $asesor, 'context' => 'asesor'])
        </div>

        {{-- Form Password --}}
        @include('profile.partials.form-password')

    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleEditAsesor() {
    document.getElementById('asesor-view').classList.toggle('d-none');
    document.getElementById('asesor-edit').classList.toggle('d-none');
    const isEditing = !document.getElementById('asesor-edit').classList.contains('d-none');
    document.getElementById('edit-asesor-label').innerHTML = isEditing
        ? '<i class="bi bi-x-lg"></i> Batal'
        : '<i class="bi bi-pencil"></i> Edit';
}

@if($errors->has('nama') || $errors->has('nik') || $errors->has('tempat_lahir') || $errors->has('tanggal_lahir') || $errors->has('jenis_kelamin') || $errors->has('no_reg_met') || $errors->has('no_blanko') || $errors->has('siap_kerja'))
document.addEventListener('DOMContentLoaded', toggleEditAsesor);
@endif
</script>
@endpush