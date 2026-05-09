{{--
    resources/views/bendahara/honor/_form-bukti.blade.php
    Partial untuk form upload/ganti bukti transfer + opsional cicilan hutang.

    Variables:
    - $honor        : HonorPayment
    - $hutangAsesor : Collection<OtherReceivable> (hutang aktif asesor)
    - $isReplace    : bool (true = ganti bukti, false = upload pertama)

    Suffix ID untuk menghindari konflik duplikat ID saat dua form muncul bersamaan:
    - Upload pertama  : id="deduction_receivable_id",  id="deduction-panel"
    - Ganti bukti     : id="deduction_receivable_id_r", id="deduction-panel-replace"
--}}

@php $suffix = $isReplace ? '_r' : ''; @endphp

@if($errors->any())
<div class="alert alert-danger py-2 small mb-2">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- File upload --}}
<div class="mb-3">
    <label class="form-label fw-semibold small">
        {{ $isReplace ? 'File Bukti Baru' : 'Upload Bukti Transfer' }}
        <span class="text-danger">*</span>
    </label>
    <input type="file" name="bukti_transfer"
           class="form-control form-control-sm @error('bukti_transfer') is-invalid @enderror"
           accept=".jpg,.jpeg,.png,.pdf" required>
    <div class="form-text">Format: JPG, PNG, atau PDF. Maks 5MB.</div>
    @error('bukti_transfer')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Pilih hutang untuk dicicil (tampil hanya kalau ada hutang) --}}
@if($hutangAsesor->isNotEmpty())
<div class="mb-2">
    <label class="form-label fw-semibold small">
        <i class="bi bi-cash-coin text-warning me-1"></i>Cicil Hutang Asesor
        <span class="text-muted fw-normal">(opsional)</span>
    </label>
    <select name="deduction_receivable_id"
            id="deduction_receivable_id{{ $suffix }}"
            class="form-select form-select-sm @error('deduction_receivable_id') is-invalid @enderror">
        <option value="">— Tidak ada cicilan —</option>
        @foreach($hutangAsesor as $hutang)
        <option value="{{ $hutang->id }}"
                data-sisa="{{ $hutang->sisa }}"
                {{ old('deduction_receivable_id') == $hutang->id ? 'selected' : '' }}>
            {{ $hutang->uraian ?? $hutang->jenis_label }}
            — Sisa Rp {{ number_format($hutang->sisa, 0, ',', '.') }}
        </option>
        @endforeach
    </select>
    @error('deduction_receivable_id')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Panel detail cicilan --}}
<div id="{{ $isReplace ? 'deduction-panel-replace' : 'deduction-panel' }}"
     style="display:{{ old('deduction_receivable_id') ? 'block' : 'none' }}"
     class="border rounded p-3 mb-3 bg-warning bg-opacity-10">

    <div class="mb-2">
        <label class="form-label small fw-semibold">
            Nominal Cicilan <span class="text-danger">*</span>
        </label>
        <div class="input-group input-group-sm">
            <span class="input-group-text">Rp</span>
            <input type="number" name="deduction_amount"
                   class="form-control @error('deduction_amount') is-invalid @enderror"
                   value="{{ old('deduction_amount') }}"
                   min="1000" step="1000" placeholder="0">
        </div>
        <div class="form-text deduction-max-label">
            @if(old('deduction_receivable_id'))
            @php $h = $hutangAsesor->find(old('deduction_receivable_id')); @endphp
            @if($h) Sisa hutang: Rp {{ number_format($h->sisa, 0, ',', '.') }} @endif
            @endif
        </div>
        @error('deduction_amount')
        <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-2">
        <label class="form-label small fw-semibold">Catatan Cicilan</label>
        <input type="text" name="deduction_note" class="form-control form-control-sm"
               value="{{ old('deduction_note') }}"
               placeholder="cth: cicilan kasbon bulan Mei 2026">
    </div>

    {{-- Preview kalkulasi --}}
    <div class="alert alert-warning py-2 mb-0 small">
        <strong>Total honor:</strong>
        Rp {{ number_format($honor->total, 0, ',', '.') }}<br>
        <strong>Dikurangi cicilan:</strong>
        <span class="text-danger">- Rp <span class="preview-deduction">0</span></span><br>
        <strong>Transfer bersih:</strong>
        <span class="text-success fw-bold">
            Rp <span class="preview-transfer">{{ number_format($honor->total, 0, ',', '.') }}</span>
        </span>
    </div>
</div>
@else
<p class="text-muted small mb-3">
    <i class="bi bi-info-circle me-1"></i>Tidak ada hutang aktif atas nama asesor ini.
</p>
@endif

<button type="submit" class="btn btn-success w-100 {{ $isReplace ? 'btn-sm' : '' }}">
    <i class="bi bi-upload me-1"></i>
    {{ $isReplace ? 'Ganti & Simpan' : 'Upload & Tandai Sudah Dibayar' }}
</button>