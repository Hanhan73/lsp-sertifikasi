{{--
    PARTIAL: admin/asesmen/partials/biodata-reject-panel.blade.php
    
    Diinclude di tab-biodata kolom kanan (admin/asesmen/show.blade.php).
    Menampilkan panel verifikasi biodata dengan 3 state:
    1. Belum diverifikasi       → tombol Verifikasi + Kembalikan
    2. Sudah diverifikasi       → badge verified + tombol reset (batalkan verifikasi)
    3. Sedang menunggu revisi   → info catatan + tombol Setujui Revisi + Kembalikan lagi
--}}

{{-- Hanya tampil jika status sudah pra_asesmen_started ke atas --}}
@if(in_array($asesmen->status, ['pra_asesmen_started', 'scheduled', 'pra_asesmen_completed', 'assessed']))

<div class="card border-0 shadow-sm mt-4" id="biodata-action-panel">
    <div class="card-header bg-white fw-semibold border-bottom d-flex align-items-center gap-2">
        <i class="bi bi-shield-check text-primary"></i>
        Verifikasi Biodata
    </div>
    <div class="card-body">

        @if($asesmen->biodata_needs_revision)
        {{-- ══ STATE 3: Menunggu Revisi Asesi ══ --}}
        <div class="alert alert-warning d-flex gap-3 align-items-start mb-3">
            <i class="bi bi-hourglass-split fs-4 flex-shrink-0"></i>
            <div>
                <strong>Menunggu Revisi Asesi</strong>
                <p class="small mb-2 mt-1">
                    Dikembalikan pada {{ $asesmen->biodata_rejected_at?->translatedFormat('d M Y H:i') ?? '-' }}.
                </p>
                <div class="bg-white border rounded p-2 small">
                    <strong>Catatan Admin:</strong><br>
                    {{ $asesmen->biodata_rejection_notes }}
                </div>
            </div>
        </div>
        <div class="d-grid gap-2">
            <button class="btn btn-success" onclick="verifyBiodata()">
                <i class="bi bi-check-circle me-1"></i> Setujui Revisi & Verifikasi
            </button>
            <button class="btn btn-outline-danger btn-sm" onclick="openRejectBiodataModal()">
                <i class="bi bi-arrow-return-left me-1"></i> Kembalikan Lagi dengan Catatan Baru
            </button>
        </div>

        @elseif($asesmen->biodata_verified_at)
        {{-- ══ STATE 2: Sudah Diverifikasi ══ --}}
        <div class="alert alert-success d-flex gap-2 align-items-center mb-3">
            <i class="bi bi-patch-check-fill fs-5"></i>
            <div>
                <strong>Biodata Terverifikasi</strong>
                <div class="small text-muted mt-1">
                    {{ \Carbon\Carbon::parse($asesmen->biodata_verified_at)->translatedFormat('d M Y H:i') }}
                    @if($asesmen->biodata_verified_by)
                    — oleh <strong>{{ \App\Models\User::find($asesmen->biodata_verified_by)?->name ?? '-' }}</strong>
                    @endif
                </div>
            </div>
        </div>
        <button class="btn btn-outline-danger btn-sm w-100" onclick="openRejectBiodataModal()">
            <i class="bi bi-arrow-return-left me-1"></i> Batalkan & Kembalikan untuk Direvisi
        </button>

        @else
        {{-- ══ STATE 1: Belum Diverifikasi ══ --}}
        <p class="small text-muted mb-3">
            Periksa data pribadi, NIK, dan dokumen pendaftaran (foto, KTP, ijazah) asesi ini.
            Setelah sesuai, verifikasi atau kembalikan jika ada yang perlu diperbaiki.
        </p>
        <div class="d-grid gap-2">
            <button class="btn btn-success" onclick="verifyBiodata()">
                <i class="bi bi-check-circle me-1"></i> Verifikasi Biodata
            </button>
            <button class="btn btn-outline-danger" onclick="openRejectBiodataModal()">
                <i class="bi bi-arrow-return-left me-1"></i> Kembalikan untuk Direvisi
            </button>
        </div>
        @endif

    </div>
</div>

@endif

{{-- ══ MODAL REJECT BIODATA ══ --}}
<div class="modal fade" id="modalRejectBiodata" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-return-left me-2"></i>Kembalikan Biodata
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    <small>Asesi akan dapat mengedit biodata & dokumen, lalu submit ulang.
                    Status asesmen tetap <strong>{{ $asesmen->status_label }}</strong>.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Alasan Pengembalian <span class="text-danger">*</span>
                    </label>
                    <textarea id="biodata-rejection-notes" class="form-control" rows="4"
                        placeholder="Jelaskan apa yang perlu diperbaiki. Contoh: Foto KTP tidak terbaca, NIK tidak sesuai KTP, foto tidak formal, dsb."
                        maxlength="1000"></textarea>
                    <div class="form-text">Min. 10 karakter. Asesi akan melihat catatan ini.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-reject-biodata"
                    onclick="submitRejectBiodata()">
                    <i class="bi bi-send me-1"></i> Kembalikan ke Asesi
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const BIODATA_CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const BIODATA_ASESMEN_ID = {{ $asesmen->id }};

// ── Verify Biodata ──────────────────────────────────────────
async function verifyBiodata() {
    const result = await Swal.fire({
        title: 'Verifikasi Biodata?',
        html : `Biodata dan dokumen <strong>{{ $asesmen->full_name }}</strong> sudah sesuai dan dapat dilanjutkan.`,
        icon : 'question',
        showCancelButton  : true,
        confirmButtonText : '<i class="bi bi-check-circle me-1"></i> Verifikasi',
        cancelButtonText  : 'Batal',
        confirmButtonColor: '#198754',
    });
    if (!result.isConfirmed) return;

    try {
        const res  = await fetch(`/admin/asesi/${BIODATA_ASESMEN_ID}/verify-biodata`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': BIODATA_CSRF, Accept: 'application/json' },
            body   : JSON.stringify({}),
        });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1800, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
    }
}

// ── Open Reject Modal ───────────────────────────────────────
function openRejectBiodataModal() {
    document.getElementById('biodata-rejection-notes').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalRejectBiodata')).show();
}

// ── Submit Reject Biodata ───────────────────────────────────
async function submitRejectBiodata() {
    const notes = document.getElementById('biodata-rejection-notes').value.trim();
    if (notes.length < 10) {
        Swal.fire('Perhatian', 'Catatan penolakan minimal 10 karakter.', 'warning');
        return;
    }

    const btn = document.getElementById('btn-confirm-reject-biodata');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';

    try {
        const res  = await fetch(`/admin/asesi/${BIODATA_ASESMEN_ID}/reject-biodata`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': BIODATA_CSRF, Accept: 'application/json' },
            body   : JSON.stringify({ rejection_notes: notes }),
        });
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalRejectBiodata'))?.hide();
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1800, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i> Kembalikan ke Asesi';
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Kembalikan ke Asesi';
    }
}
</script>
@endpush