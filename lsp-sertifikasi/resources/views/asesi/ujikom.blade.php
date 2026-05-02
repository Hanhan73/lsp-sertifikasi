@extends('layouts.app')
@section('title', 'Dokumen Ujikom')
@section('page-title', 'Dokumen Ujikom')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1">Dokumen Hasil Ujikom / Portofolio</h5>
    <p class="text-muted mb-0" style="font-size:.875rem">
        Lampirkan link Google Drive berisi dokumen hasil ujian kompetensi Anda.
        Dokumen ini akan diverifikasi asesor sebelum asesmen dimulai.
    </p>
</div>

@if(!$apldua)
<div class="alert alert-warning d-flex align-items-center gap-3">
    <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
    <div>
        <strong>APL-02 belum dibuat.</strong>
        Silakan isi APL-02 terlebih dahulu sebelum melampirkan dokumen ujikom.
        <div class="mt-2">
            <a href="{{ route('asesi.apldua') }}" class="btn btn-sm btn-warning">
                <i class="bi bi-arrow-right me-1"></i>Ke APL-02
            </a>
        </div>
    </div>
</div>
@else

{{-- Panduan --}}
<div class="alert alert-info small mb-4">
    <i class="bi bi-info-circle-fill me-1"></i>
    <strong>Cara melampirkan:</strong>
    Upload dokumen hasil ujikom ke Google Drive → klik kanan folder →
    <em>Share</em> → ubah akses ke <strong>"Anyone with the link can view"</strong> → salin link dan paste di bawah.
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center gap-2" style="background:#eff6ff;">
        <img src="https://www.gstatic.com/images/branding/product/1x/drive_2020q4_48dp.png"
             style="width:18px;height:18px;" alt="GDrive">
        <h6 class="fw-bold mb-0 text-primary">Link Google Drive</h6>
        @if($apldua->gdrive_ujikom)
        <span class="badge bg-success ms-auto" style="font-size:.68rem;">
            <i class="bi bi-check-circle me-1"></i>Sudah Diisi
        </span>
        @else
        <span class="badge bg-warning text-dark ms-auto" style="font-size:.68rem;">
            Belum Diisi
        </span>
        @endif
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label fw-semibold">Link Folder Google Drive</label>
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-link-45deg"></i>
                </span>
                <input type="url" id="gdrive-ujikom-input" class="form-control"
                       placeholder="https://drive.google.com/drive/folders/..."
                       value="{{ $apldua->gdrive_ujikom ?? '' }}">
                <a href="{{ $apldua->gdrive_ujikom ?? '#' }}"
                   target="_blank"
                   id="gdrive-ujikom-preview"
                   class="btn btn-outline-secondary {{ $apldua->gdrive_ujikom ? '' : 'd-none' }}">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
            <div id="gdrive-ujikom-hint" class="form-text text-muted mt-1">
                Pastikan link diset ke "Anyone with the link can view"
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="button" id="btn-simpan-ujikom" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Simpan
            </button>
            @if($apldua->gdrive_ujikom)
            <small class="text-muted">
                <i class="bi bi-clock me-1"></i>
                Terakhir diupdate: {{ $apldua->updated_at->translatedFormat('d M Y H:i') }}
            </small>
            @endif
        </div>
    </div>
</div>

{{-- Info pre-fill --}}
<div class="card border-0 shadow-sm mt-3" style="border-left:3px solid #3b82f6 !important;">
    <div class="card-body py-3 d-flex align-items-start gap-3">
        <i class="bi bi-magic text-primary flex-shrink-0 mt-1"></i>
        <div class="small text-muted">
            Link ini akan <strong>otomatis terisi</strong> ke semua paket soal observasi
            saat soal didistribusikan oleh Manajer Sertifikasi.
            Anda tetap bisa mengganti link per paket jika diperlukan.
        </div>
    </div>
</div>

@endif

@endsection

@push('scripts')
<script>
const UJIKOM_URL = "{{ route('asesi.ujikom.simpan') }}";
const CSRF       = "{{ csrf_token() }}";

const input   = document.getElementById('gdrive-ujikom-input');
const preview = document.getElementById('gdrive-ujikom-preview');
const hint    = document.getElementById('gdrive-ujikom-hint');

function isGDriveUrl(v) {
    return v.startsWith('https://drive.google.com/') || v.startsWith('https://docs.google.com/');
}

input?.addEventListener('input', function () {
    const v = this.value.trim();
    if (!v) {
        this.classList.remove('is-valid', 'is-invalid');
        hint.textContent = 'Pastikan link diset ke "Anyone with the link can view"';
        preview.classList.add('d-none');
        return;
    }
    if (isGDriveUrl(v)) {
        this.classList.add('is-valid'); this.classList.remove('is-invalid');
        hint.textContent = '✓ Link Google Drive valid';
        preview.href = v; preview.classList.remove('d-none');
    } else {
        this.classList.add('is-invalid'); this.classList.remove('is-valid');
        hint.textContent = '❌ Link harus berasal dari Google Drive';
        preview.classList.add('d-none');
    }
});

document.getElementById('btn-simpan-ujikom')?.addEventListener('click', async function () {
    const link = input.value.trim();

    if (link && !isGDriveUrl(link)) {
        input.classList.add('is-invalid');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    try {
        const res  = await fetch(UJIKOM_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ gdrive_ujikom: link }),
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success', title: 'Tersimpan!',
                text: data.message, timer: 1500, showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan.' });
    } finally {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-save me-1"></i>Simpan';
    }
});
</script>
@endpush