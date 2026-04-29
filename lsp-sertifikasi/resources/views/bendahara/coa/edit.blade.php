@extends('layouts.app')
@section('title', 'Edit Akun CoA')
@section('page-title', 'Edit Akun CoA')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
        <span><i class="bi bi-pencil-square text-primary me-2"></i>Edit Akun — <code>{{ $coa->kode }}</code></span>
        <a href="{{ route('bendahara.coa.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">

        @if($coa->is_system)
        <div class="alert alert-warning small mb-3">
            <i class="bi bi-shield-lock me-1"></i>
            Ini adalah akun sistem. Kode dan tipe tidak dapat diubah.
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('bendahara.coa.update', $coa) }}" method="POST">
            @csrf
            @method('PUT')

            @php $item = $coa; $isSystem = $coa->is_system; @endphp

            {{-- Kode Akun --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Kode Akun <span class="text-danger">*</span></label>
                    <input type="text" name="kode" id="kode"
                           class="form-control @error('kode') is-invalid @enderror"
                           value="{{ old('kode', $coa->kode) }}"
                           placeholder="1-001"
                           {{ $isSystem ? 'readonly' : '' }}>
                    <div class="form-text">Format: X-NNN (contoh: 1-001)</div>
                    @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label">Nama Akun <span class="text-danger">*</span></label>
                    <input type="text" name="nama"
                           class="form-control @error('nama') is-invalid @enderror"
                           value="{{ old('nama', $coa->nama) }}"
                           placeholder="contoh: Kas">
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tipe <span class="text-danger">*</span></label>
                    <select name="tipe" id="tipe"
                            class="form-select @error('tipe') is-invalid @enderror"
                            {{ $isSystem ? 'disabled' : '' }}>
                        @foreach($tipeList as $key => $label)
                        <option value="{{ $key }}" {{ old('tipe', $coa->tipe) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @if($isSystem)
                    <input type="hidden" name="tipe" value="{{ $coa->tipe }}">
                    @endif
                    @error('tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sub Tipe</label>
                    <select name="sub_tipe" class="form-select @error('sub_tipe') is-invalid @enderror">
                        <option value="">— Tidak ada —</option>
                        @foreach($subTipeList as $key => $label)
                        <option value="{{ $key }}" {{ old('sub_tipe', $coa->sub_tipe) == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @error('sub_tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Urutan Tampil</label>
                    <input type="number" name="urutan"
                           class="form-control @error('urutan') is-invalid @enderror"
                           value="{{ old('urutan', $coa->urutan) }}"
                           min="0">
                    <div class="form-text">Urutan di laporan.</div>
                    @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-9">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan"
                           class="form-control @error('keterangan') is-invalid @enderror"
                           value="{{ old('keterangan', $coa->keterangan) }}"
                           placeholder="Deskripsi singkat akun ini...">
                    @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active"
                               id="is_active" value="1"
                               {{ old('is_active', $coa->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Akun Aktif</label>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Perbarui
                </button>
                <a href="{{ route('bendahara.coa.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection