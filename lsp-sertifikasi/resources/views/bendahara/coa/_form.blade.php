{{-- resources/views/bendahara/coa/_form.blade.php --}}
@php $item = $item ?? null; $isSystem = $item?->is_system ?? false; @endphp

<div class="row g-3">

    <div class="col-md-4">
        <label class="form-label">Kode Akun <span class="text-danger">*</span></label>
        <input type="text" name="kode" id="kode"
               class="form-control @error('kode') is-invalid @enderror"
               value="{{ old('kode', $item?->kode) }}"
               placeholder="1-001"
               {{ $isSystem ? 'readonly' : '' }}>
        <div class="form-text">Format: X-NNN (contoh: 1-001)</div>
        @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label class="form-label">Nama Akun <span class="text-danger">*</span></label>
        <input type="text" name="nama"
               class="form-control @error('nama') is-invalid @enderror"
               value="{{ old('nama', $item?->nama) }}"
               placeholder="contoh: Kas">
        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tipe <span class="text-danger">*</span></label>
        <select name="tipe" id="tipe"
                class="form-select @error('tipe') is-invalid @enderror"
                {{ $isSystem ? 'disabled' : '' }}>
            @foreach($tipeList as $key => $label)
            <option value="{{ $key }}" {{ old('tipe', $item?->tipe) == $key ? 'selected' : '' }}>
                {{ $label }}
            </option>
            @endforeach
        </select>
        @if($isSystem)
        <input type="hidden" name="tipe" value="{{ $item->tipe }}">
        @endif
        @error('tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Sub Tipe</label>
        <select name="sub_tipe" class="form-select @error('sub_tipe') is-invalid @enderror">
            <option value="">— Tidak ada —</option>
            @foreach($subTipeList as $key => $label)
            <option value="{{ $key }}" {{ old('sub_tipe', $item?->sub_tipe) == $key ? 'selected' : '' }}>
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
               value="{{ old('urutan', $item?->urutan ?? 99) }}"
               min="0">
        <div class="form-text">Urutan di laporan.</div>
        @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-9">
        <label class="form-label">Keterangan</label>
        <input type="text" name="keterangan"
               class="form-control @error('keterangan') is-invalid @enderror"
               value="{{ old('keterangan', $item?->keterangan) }}"
               placeholder="Deskripsi singkat akun ini...">
        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active"
                   id="is_active" value="1"
                   {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Akun Aktif</label>
        </div>
    </div>

</div>