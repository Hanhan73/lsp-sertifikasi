{{--
    Partial: tombol download daftar hadir + modal verifikasi + modal TTD asesor
    Di-include dari asesor.schedule.detail (tab Daftar Peserta)

    Variables: $schedule, $asesor
--}}

@php
    $hasSig      = !empty(auth()->user()->signature);
    $isSigned    = $schedule->isDaftarHadirSigned();
@endphp

{{-- Tombol --}}
@if($isSigned)
{{-- Sudah ditandatangani: hanya bisa download, tidak bisa verifikasi ulang --}}
<div class="d-flex align-items-center gap-2 ms-auto">
    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem;">
        <i class="bi bi-check-circle-fill me-1"></i>Daftar Hadir Ditandatangani
        <span class="text-muted fw-normal ms-1">{{ $schedule->daftar_hadir_signed_at->translatedFormat('d M Y, H:i') }}</span>
    </span>
    <a href="{{ route('asesor.schedule.daftar-hadir', $schedule) }}"
       target="_blank"
       class="btn btn-sm btn-outline-danger">
        <i class="bi bi-file-pdf me-1"></i>Download Daftar Hadir
    </a>
</div>
@else
<a href="#"
   id="btn-download-daftar-hadir"
   class="btn btn-sm btn-outline-danger ms-auto"
   onclick="handleDaftarHadir(event)">
    <i class="bi bi-file-pdf me-1"></i>Download Daftar Hadir
</a>
@endif

{{-- Modal 1: Verifikasi Daftar Hadir --}}
<div class="modal fade" id="modalVerifikasiDaftarHadir" tabindex="-1" aria-labelledby="modalVerifLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold" id="modalVerifLabel">
                    <i class="bi bi-clipboard-check me-2"></i>Verifikasi Daftar Hadir
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 fw-semibold">Sebelum menandatangani, pastikan daftar hadir sudah benar.</p>
                <p class="text-muted small mb-3">
                    Setelah ditandatangani, status kehadiran peserta <strong>tidak dapat diubah lagi</strong>.
                    Periksa kembali daftar hadir di bawah sebelum melanjutkan.
                </p>
                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0" style="font-size:.85rem;">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                    Apakah daftar hadir peserta sudah benar dan sesuai?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Periksa Lagi</button>
                <button type="button" class="btn btn-warning btn-sm fw-semibold" id="btn-konfirmasi-verif"
                        onclick="lanjutkanKeTtd()">
                    <i class="bi bi-check-lg me-1"></i>Ya, Sudah Benar — Lanjut TTD
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: TTD Asesor (muncul setelah verifikasi) --}}
<div class="modal fade" id="modalTtdDaftarHadir" tabindex="-1" aria-labelledby="modalTtdLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title" id="modalTtdLabel">
                    <i class="bi bi-pen me-2"></i>Tanda Tangan Asesor
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Tanda tangan akan disimpan ke profil Anda dan muncul di daftar hadir.
                </p>

                @include('partials._signature_pad', [
                    'padId'    => 'daftar-hadir-asesor',
                    'padLabel' => 'Tanda Tangan Asesor',
                    'padHeight' => 180,
                    'savedSig'  => auth()->user()->signature_image,
                ])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btn-confirm-ttd-hadir"
                        onclick="confirmTtdAndDownload()">
                    <i class="bi bi-download me-1"></i>Simpan TTD & Download
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const _hasSig          = @json($hasSig);
const _downloadUrl     = '{{ route("asesor.schedule.daftar-hadir", $schedule) }}';
const _saveSigUrl      = '{{ route("user.signature.store") }}';
const _verifikasiUrl   = '{{ route("asesor.schedule.daftar-hadir.verifikasi", $schedule) }}';
const _CSRF            = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

let _modalVerif = null;
let _modalTtd   = null;

document.addEventListener('DOMContentLoaded', () => {
    _modalVerif = new bootstrap.Modal(document.getElementById('modalVerifikasiDaftarHadir'));
    _modalTtd   = new bootstrap.Modal(document.getElementById('modalTtdDaftarHadir'));
});

// Init sig pad saat modal TTD dibuka
document.getElementById('modalTtdDaftarHadir')?.addEventListener('shown.bs.modal', () => {
    SigPadManager.init('daftar-hadir-asesor', @json(auth()->user()->signature_image));
});

function handleDaftarHadir(e) {
    e.preventDefault();
    // Selalu tampilkan modal verifikasi dulu
    _modalVerif.show();
}

function lanjutkanKeTtd() {
    _modalVerif.hide();

    if (_hasSig) {
        // Sudah ada TTD — langsung verifikasi lalu download
        verifikasiLaluDownload();
    } else {
        // Belum ada TTD — tampilkan modal TTD
        setTimeout(() => _modalTtd.show(), 400);
    }
}

async function verifikasiLaluDownload() {
    try {
        await fetch(_verifikasiUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _CSRF },
        });
        window.open(_downloadUrl, '_blank');
        // Reload halaman agar toggle hadir terkunci
        setTimeout(() => location.reload(), 800);
    } catch (err) {
        Swal.fire('Gagal', 'Terjadi kesalahan. Silakan coba lagi.', 'error');
    }
}

async function confirmTtdAndDownload() {
    if (SigPadManager.isEmpty('daftar-hadir-asesor')) {
        Swal.fire({
            icon: 'warning',
            title: 'Tanda Tangan Diperlukan',
            text: 'Silakan tanda tangan terlebih dahulu.',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
        });
        return;
    }

    const btn = document.getElementById('btn-confirm-ttd-hadir');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    try {
        // 1. Simpan TTD ke profil
        const dataURL = await SigPadManager.prepareAndGet('daftar-hadir-asesor');
        const res     = await fetch(_saveSigUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _CSRF },
            body: JSON.stringify({ signature: dataURL }),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message ?? 'Gagal menyimpan TTD');

        // 2. Tandai daftar hadir sebagai sudah diverifikasi
        await fetch(_verifikasiUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': _CSRF },
        });

        // 3. Tutup modal, buka PDF, reload
        _modalTtd.hide();
        window.open(_downloadUrl, '_blank');
        setTimeout(() => location.reload(), 800);

    } catch (err) {
        Swal.fire('Gagal', err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-download me-1"></i>Simpan TTD & Download';
    }
}
</script>
@endpush
