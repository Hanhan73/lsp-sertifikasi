{{-- resources/views/profile/partials/_form-rekening.blade.php --}}
{{-- $data = AsesorRekening|null --}}
<div class="row g-3">
    <div class="col-12">
        <label class="form-label small fw-semibold">Nama Bank <span class="text-danger">*</span></label>
        <select name="nama_bank" class="form-select" required>
            <option value="">-- Pilih Bank --</option>
            @foreach(['BCA','BRI','BNI','Mandiri','BSI','CIMB Niaga','Permata','Danamon','BTN','Maybank','OCBC','Bank Jateng','Bank Jabar','Bank DKI','Bank Jatim','Lainnya'] as $bank)
                <option value="{{ $bank }}" @selected(($data->nama_bank ?? '') === $bank)>{{ $bank }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
        <input type="text" name="nomor_rekening" class="form-control font-monospace"
            value="{{ $data->nomor_rekening ?? '' }}"
            placeholder="Contoh: 1234567890" required maxlength="50">
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold">Nama Pemilik Rekening <span class="text-danger">*</span></label>
        <input type="text" name="nama_pemilik" class="form-control"
            value="{{ $data->nama_pemilik ?? '' }}"
            placeholder="Sesuai buku tabungan" required maxlength="255">
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold">Cabang <span class="text-muted fw-normal">(opsional)</span></label>
        <input type="text" name="cabang" class="form-control"
            value="{{ $data->cabang ?? '' }}"
            placeholder="Contoh: KCP Bandung Dago" maxlength="150">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_utama" value="1" id="is_utama_{{ $data->id ?? 'new' }}"
                @checked(!empty($data->is_utama))>
            <label class="form-check-label small" for="is_utama_{{ $data->id ?? 'new' }}">
                Jadikan rekening utama
            </label>
        </div>
    </div>
</div>