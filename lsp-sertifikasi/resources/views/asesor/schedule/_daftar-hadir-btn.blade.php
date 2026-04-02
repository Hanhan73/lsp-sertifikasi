{{--
    Partial: tombol download daftar hadir + modal TTD asesor
    Di-include dari asesor.schedule.detail (tab Daftar Peserta)

    Variables: $schedule, $asesor
--}}

@php
    $hasSig = !empty(auth()->user()->signature);
@endphp

{{-- Tombol --}}
<a href="#"
   id="btn-download-daftar-hadir"
   class="btn btn-sm btn-outline-danger ms-auto"
   onclick="handleDaftarHadir(event)">
    <i class="bi bi-file-pdf me-1"></i>Download Daftar Hadir
</a>

{{-- Modal TTD (muncul kalau belum ada TTD) --}}
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
                    Tanda tangan diperlukan untuk daftar hadir. Tanda tangan akan otomatis tersimpan ke profil Anda.
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
                <button type="button" class="btn btn-primary btn-sm" id="btn-confirm-ttd-hadir" onclick="confirmTtdAndDownload()">
                    <i class="bi bi-download me-1"></i>Simpan TTD & Download
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const _hasSig       = @json($hasSig);
const _downloadUrl  = '{{ route("asesor.schedule.daftar-hadir", $schedule) }}';
const _saveSigUrl   = '{{ route("user.signature.store") }}';
const _CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Init sig pad saat modal dibuka
document.getElementById('modalTtdDaftarHadir')?.addEventListener('shown.bs.modal', () => {
    SigPadManager.init('daftar-hadir-asesor', @json(auth()->user()->signature_image));
});

async function handleDaftarHadir(e) {
    e.preventDefault();

    if (_hasSig) {
        // Sudah ada TTD — langsung buka PDF
        window.open(_downloadUrl, '_blank');
        return;
    }

    // Belum ada TTD — tampilkan modal
    new bootstrap.Modal(document.getElementById('modalTtdDaftarHadir')).show();
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
        // Simpan TTD ke profil
        const dataURL = await SigPadManager.prepareAndGet('daftar-hadir-asesor');

        const res  = await fetch(_saveSigUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _CSRF },
            body: JSON.stringify({ signature: dataURL }),
        });
        const data = await res.json();

        if (!data.success) throw new Error(data.message ?? 'Gagal menyimpan TTD');

        // Tutup modal, buka PDF
        bootstrap.Modal.getInstance(document.getElementById('modalTtdDaftarHadir')).hide();
        window.open(_downloadUrl, '_blank');

        // Update flag supaya next click langsung download
        window._hasSig = true;

    } catch (err) {
        Swal.fire('Gagal', err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-download me-1"></i>Simpan TTD & Download';
    }
}
</script>
@endpush