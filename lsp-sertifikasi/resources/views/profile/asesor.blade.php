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
                    <img src="{{ asset('storage/' . $asesor->foto_path) }}"
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
                    <button type="button" class="btn btn-sm btn-outline-primary w-100"
                            onclick="document.getElementById('foto-asesor').click()">
                        <i class="bi bi-camera me-1"></i> Ganti Foto
                    </button>
                </form>
                @error('foto')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                <hr>

                {{-- Data asesor (readonly) --}}
                <div class="text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">NIK</span>
                        <span class="fw-semibold font-monospace">{{ $asesor?->nik ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">No. Reg. Met.</span>
                        <span class="fw-semibold">{{ $asesor?->no_reg_met ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">No. Blanko</span>
                        <span class="fw-semibold">{{ $asesor?->no_blanko ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Jenis Kelamin</span>
                        <span>{{ $asesor?->jenis_kelamin === 'L' ? 'Laki-laki' : ($asesor?->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Telepon</span>
                        <span>{{ $asesor?->telepon ?? '-' }}</span>
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

                <div class="alert alert-light border mt-3 mb-0 text-start small">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    Data identitas dikelola oleh Admin LSP. Hubungi admin untuk perubahan data.
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

        {{-- Form Password --}}
        @include('profile.partials.form-password')

    </div>
</div>
@endsection