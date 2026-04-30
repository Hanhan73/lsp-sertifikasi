@php
    $buktiPath = $honor->bukti_transfer_path ?? null;
    $ext = $buktiPath ? strtolower(pathinfo($buktiPath, PATHINFO_EXTENSION)) : null;
    $isPdf = $ext === 'pdf';

    // Sesuaikan route berdasarkan role yang mengakses
    $downloadRoute = request()->routeIs('bendahara.*')
        ? route('bendahara.honor.payment.bukti.download', $honor)
        : route('asesor.honor.bukti.download', $honor);
@endphp

@if($buktiPath && !$isPdf)
<div class="border rounded-3 overflow-hidden mb-3">
    <div style="cursor:zoom-in;"
         onclick="bukaBuktiModal('{{ $downloadRoute }}')">
        <img src="{{ $downloadRoute }}"
             class="w-100"
             style="max-height:260px;object-fit:cover;transition:opacity .2s;"
             onmouseover="this.style.opacity='.85'"
             onmouseout="this.style.opacity='1'"
             alt="Bukti Transfer">
    </div>
    <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between"
         style="font-size:.75rem;">
        <span class="text-muted"><i class="bi bi-receipt me-1"></i>Bukti Transfer</span>
        <div class="d-flex gap-1">
            <button class="btn btn-outline-secondary py-0 px-1"
                    style="font-size:.7rem;"
                    onclick="bukaBuktiModal('{{ $downloadRoute }}')"
                    title="Perbesar">
                <i class="bi bi-zoom-in"></i>
            </button>
            <a href="{{ $downloadRoute }}?download=1"
               class="btn btn-outline-primary py-0 px-1"
               style="font-size:.7rem;"
               title="Download">
                <i class="bi bi-download"></i>
            </a>
        </div>
    </div>
</div>

{{-- Modal Lightbox --}}
<div class="modal fade" id="modalBuktiTransfer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-header border-0 pb-1 px-0">
                <span class="text-white fw-semibold" style="font-size:.9rem;">Bukti Transfer</span>
                <button type="button" class="btn-close btn-close-white ms-auto"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="modalBuktiImg" src=""
                     alt="Bukti Transfer"
                     class="img-fluid rounded-3 shadow"
                     style="max-height:85vh;object-fit:contain;">
            </div>
            <div class="modal-footer border-0 justify-content-center py-2">
                <a id="modalBuktiDownload" href="{{ $downloadRoute }}?download=1"
                   class="btn btn-sm btn-outline-light">
                    <i class="bi bi-download me-1"></i>Download
                </a>
                <button type="button" class="btn btn-sm btn-secondary"
                        data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
#modalBuktiTransfer .modal-dialog { max-width: 90vw; }
#modalBuktiTransfer { background: rgba(0,0,0,.85); }
</style>

<script>
function bukaBuktiModal(src) {
    document.getElementById('modalBuktiImg').src = src;
    bootstrap.Modal.getOrCreateInstance(
        document.getElementById('modalBuktiTransfer')
    ).show();
}
</script>

@elseif($buktiPath && $isPdf)
<div class="border rounded-3 overflow-hidden mb-3">
    <div class="p-3 text-center bg-light">
        <i class="bi bi-file-earmark-pdf text-danger" style="font-size:3rem;"></i>
        <div class="small text-muted mt-1">Bukti dalam format PDF</div>
    </div>
    <div class="px-2 py-1 bg-light d-flex align-items-center justify-content-between border-top"
         style="font-size:.75rem;">
        <span class="text-muted"><i class="bi bi-receipt me-1"></i>Bukti Transfer</span>
        <a href="{{ $downloadRoute }}?download=1"
           class="btn btn-outline-primary py-0 px-1"
           style="font-size:.7rem;">
            <i class="bi bi-download me-1"></i>Download PDF
        </a>
    </div>
</div>
@endif