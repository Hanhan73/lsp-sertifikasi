{{--
    Shared form partial.
    Variabel opsional: $item (BiayaOperasional) — diisi saat edit.
--}}
@php $item = $item ?? null; @endphp

<div class="row g-3">

    {{-- Tanggal --}}
    <div class="col-md-4">
        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
            value="{{ old('tanggal', $item?->tanggal?->format('Y-m-d') ?? today()->format('Y-m-d')) }}" required>
        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Uraian --}}
    <div class="col-md-8">
        <label class="form-label">Uraian <span class="text-danger">*</span></label>
        <input type="text" name="uraian" class="form-control @error('uraian') is-invalid @enderror"
            value="{{ old('uraian', $item?->uraian) }}" placeholder="Contoh: Honor Asesor Asesmen Skema OTKP" required>
        @error('uraian')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Tipe Penerima --}}
    <div class="col-12">
        <label class="form-label">Penerima <span class="text-danger">*</span></label>
        <div class="d-flex gap-3 mb-2">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipe_penerima" id="tipe_asesor" value="asesor"
                    {{ old('tipe_penerima', $item?->asesor_id ? 'asesor' : 'manual') === 'asesor' ? 'checked' : '' }}>
                <label class="form-check-label" for="tipe_asesor">Asesor (dari database)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipe_penerima" id="tipe_manual" value="manual"
                    {{ old('tipe_penerima', $item?->asesor_id ? 'asesor' : 'manual') === 'manual' ? 'checked' : '' }}>
                <label class="form-check-label" for="tipe_manual">Manual (pihak luar)</label>
            </div>
        </div>

        {{-- Dropdown asesor --}}
        <div id="wrap_asesor" style="display:none">
            <select name="asesor_id" id="asesor_id" class="form-select @error('asesor_id') is-invalid @enderror">
                <option value="">-- Pilih Asesor --</option>
                @foreach($asesors as $a)
                <option value="{{ $a->id }}" data-nama="{{ $a->nama }}"
                    {{ old('asesor_id', $item?->asesor_id) == $a->id ? 'selected' : '' }}>
                    {{ $a->nama }}
                </option>
                @endforeach
            </select>
            @error('asesor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Input manual --}}
        <div id="wrap_manual" style="display:none">
            <input type="text" id="nama_manual" class="form-control" placeholder="Nama lengkap penerima"
                value="{{ old('nama_penerima', (!$item?->asesor_id) ? $item?->nama_penerima : '') }}">
        </div>

        {{-- Hidden field yang dikirim --}}
        <input type="hidden" name="nama_penerima" id="nama_penerima_hidden"
            value="{{ old('nama_penerima', $item?->nama_penerima) }}">
        @error('nama_penerima')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    {{-- Tarif --}}
    <div class="col-md-4">
        <label class="form-label">Tarif (Rp) <span class="text-danger">*</span></label>
        <input type="text" id="tarif_display" class="form-control @error('tarif') is-invalid @enderror"
            value="{{ old('tarif') ? number_format((int)old('tarif'), 0, ',', '.') : ($item ? number_format($item->tarif, 0, ',', '.') : '') }}"
            placeholder="0" inputmode="numeric" autocomplete="off">
        <input type="hidden" name="tarif" id="tarif_hidden" value="{{ old('tarif', $item?->tarif) }}">
        @error('tarif')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Jumlah --}}
    <div class="col-md-4">
        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
        <input type="number" name="jumlah" id="jumlah" class="form-control @error('jumlah') is-invalid @enderror"
            value="{{ old('jumlah', $item?->jumlah ?? 1) }}" min="1" required>
        @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Total (readonly display) --}}
    <div class="col-md-4">
        <label class="form-label">Total</label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" id="total_display" class="form-control bg-light fw-bold text-danger" readonly
                placeholder="0">
        </div>
    </div>

    {{-- Keterangan --}}
    <div class="col-12">
        <label class="form-label">Keterangan <small class="text-muted">(opsional)</small></label>
        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="2"
            placeholder="Catatan tambahan...">{{ old('keterangan', $item?->keterangan) }}</textarea>
        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Bukti Transaksi --}}
    <div class="col-md-6">
        <label class="form-label">Bukti Transaksi <small class="text-muted">(jpg/png, maks 3MB)</small></label>
        <input type="file" name="bukti_transaksi" id="bukti_transaksi"
            class="form-control @error('bukti_transaksi') is-invalid @enderror" accept="image/jpeg,image/png">
        @error('bukti_transaksi')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div id="preview_transaksi" class="mt-2"></div>
    </div>

    {{-- Bukti Kegiatan --}}
    <div class="col-md-6">
        <label class="form-label">Bukti Kegiatan <small class="text-muted">(jpg/png, maks 3MB)</small></label>
        <input type="file" name="bukti_kegiatan" id="bukti_kegiatan"
            class="form-control @error('bukti_kegiatan') is-invalid @enderror" accept="image/jpeg,image/png">
        @error('bukti_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div id="preview_kegiatan" class="mt-2"></div>
    </div>

</div>