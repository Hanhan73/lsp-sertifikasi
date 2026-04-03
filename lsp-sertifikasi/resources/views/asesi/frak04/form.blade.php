@extends('layouts.app')
@section('title', 'FR.AK.04 - Banding Asesmen')
@section('page-title', 'FR.AK.04 - Banding Asesmen')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@push('styles')
<style>
.banding-warning-box {
    background: linear-gradient(135deg, #fff7ed 0%, #fff3cd 100%);
    border: 2px solid #f59e0b;
    border-radius: 10px;
    padding: 18px 20px;
}
.banding-info-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #fde68a;
}
.banding-info-item:last-child { border-bottom: none; }
.pertanyaan-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 10px;
    background: #fff;
    transition: border-color .2s, background .2s;
}
.pertanyaan-card.answered { border-color: #3b82f6; background: #f0f7ff; }
.pertanyaan-label { font-size: .95rem; color: #1e293b; }
.radio-group { display: flex; gap: 20px; margin-top: 10px; }
.radio-group label {
    display: flex; align-items: center; gap: 6px;
    cursor: pointer; font-weight: 600; font-size: .9rem;
}
.radio-ya  { color: #16a34a; }
.radio-tdk { color: #dc2626; }
.radio-group input[type="radio"] { width: 16px; height: 16px; cursor: pointer; }
</style>
@endpush

@section('content')

@php $isSubmitted = $frak04->status === 'submitted'; @endphp

{{-- ── Status Banner ── --}}
@if($isSubmitted)
<div class="alert alert-success d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-check-circle-fill fs-4 flex-shrink-0"></i>
    <div class="flex-grow-1">
        <strong>Banding berhasil diajukan</strong> pada {{ $frak04->submitted_at?->translatedFormat('d M Y H:i') }}.<br>
        <span class="text-muted small">Dokumen banding Anda sedang dalam proses peninjauan.</span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
        <a href="{{ route('asesi.frak04.pdf', ['preview' => 1]) }}" target="_blank"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye me-1"></i>Preview PDF
        </a>
        <a href="{{ route('asesi.frak04.pdf') }}" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i>Download PDF
        </a>
    </div>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark d-flex align-items-center gap-2">
        <i class="bi bi-megaphone-fill fs-5"></i>
        <h5 class="mb-0">FR.AK.04 — BANDING ASESMEN</h5>
        @if($isSubmitted)
        <span class="badge bg-success ms-auto">Sudah Diajukan</span>
        @else
        <span class="badge bg-secondary ms-auto">Belum Diajukan</span>
        @endif
    </div>
    <div class="card-body">

        {{-- ── Warning Box (hanya tampil kalau belum submit) ── --}}
        @if(!$isSubmitted)
        <div class="banding-warning-box mb-4">
            <div class="d-flex align-items-start gap-3 mb-3">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-3 flex-shrink-0"></i>
                <div>
                    <h6 class="fw-bold mb-1">Perhatikan sebelum mengajukan Banding</h6>
                    <p class="mb-0 small text-muted">
                        Banding asesmen adalah hak Anda, namun pastikan Anda memahami hal-hal berikut:
                    </p>
                </div>
            </div>
            <div>
                <div class="banding-info-item">
                    <i class="bi bi-info-circle-fill text-primary flex-shrink-0 mt-1"></i>
                    <span class="small">Banding hanya dapat diajukan jika Anda menilai <strong>proses asesmen tidak sesuai SOP</strong> dan tidak memenuhi Prinsip Asesmen.</span>
                </div>
                <div class="banding-info-item">
                    <i class="bi bi-chat-dots-fill text-warning flex-shrink-0 mt-1"></i>
                    <span class="small">Disarankan untuk <strong>mendiskusikan alasan banding dengan asesor</strong> terlebih dahulu sebelum mengajukan secara formal.</span>
                </div>
                <div class="banding-info-item">
                    <i class="bi bi-lock-fill text-danger flex-shrink-0 mt-1"></i>
                    <span class="small">Setelah disubmit, dokumen banding <strong>tidak dapat diubah</strong>. Pastikan seluruh isian sudah benar.</span>
                </div>
                <div class="banding-info-item">
                    <i class="bi bi-file-earmark-check-fill text-success flex-shrink-0 mt-1"></i>
                    <span class="small">Dokumen ini bersifat <strong>opsional</strong> — hanya isi jika Anda benar-benar ingin mengajukan banding.</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top border-warning-subtle">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirm-understand" onchange="toggleForm(this)">
                    <label class="form-check-label fw-semibold small" for="confirm-understand">
                        Saya memahami ketentuan di atas dan ingin melanjutkan pengisian formulir banding.
                    </label>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Info Data Asesmen ── --}}
        <div class="card border-0 bg-light mb-4">
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted small" style="width:35%; padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">Nama Asesi</td>
                            <td class="fw-semibold small" style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">{{ $frak04->nama_asesi ?? $asesmen->full_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small" style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">Nama Asesor</td>
                            <td class="fw-semibold small" style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">{{ $frak04->nama_asesor ?? $asesmen->schedule?->asesor?->nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small" style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">Tanggal Asesmen</td>
                            <td class="fw-semibold small" style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">{{ $frak04->tanggal_asesmen ?? $asesmen->schedule?->assessment_date?->translatedFormat('l, d F Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small" style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">Skema Sertifikasi</td>
                            <td class="fw-semibold small" style="padding: 7px 10px; border-bottom: 1px solid #f1f5f9;">{{ $frak04->skema_sertifikasi ?? $asesmen->skema?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small" style="padding: 7px 10px;">No. Skema</td>
                            <td class="small" style="padding: 7px 10px;">{{ $frak04->no_skema_sertifikasi ?? $asesmen->skema?->nomor_skema ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Form Banding ── --}}
        <div id="form-banding" style="{{ $isSubmitted ? '' : 'opacity: .4; pointer-events: none;' }}">

            <h6 class="fw-bold mb-3">Jawablah pertanyaan berikut dengan Ya atau Tidak :</h6>

            {{-- Q1 --}}
            <div class="pertanyaan-card {{ $frak04->proses_banding_dijelaskan !== null ? 'answered' : '' }}" id="card-q1">
                <div class="pertanyaan-label">Apakah Proses Banding telah dijelaskan kepada Anda?</div>
                <div class="radio-group">
                    <label class="radio-ya">
                        <input type="radio" name="q_proses_banding" value="1"
                               {{ $frak04->proses_banding_dijelaskan === true ? 'checked' : '' }}
                               {{ $isSubmitted ? 'disabled' : '' }}
                               onchange="markAnswered('card-q1')"> YA
                    </label>
                    <label class="radio-tdk">
                        <input type="radio" name="q_proses_banding" value="0"
                               {{ $frak04->proses_banding_dijelaskan === false ? 'checked' : '' }}
                               {{ $isSubmitted ? 'disabled' : '' }}
                               onchange="markAnswered('card-q1')"> TIDAK
                    </label>
                </div>
            </div>

            {{-- Q2 --}}
            <div class="pertanyaan-card {{ $frak04->sudah_diskusi_dengan_asesor !== null ? 'answered' : '' }}" id="card-q2">
                <div class="pertanyaan-label">Apakah Anda telah mendiskusikan Banding dengan Asesor?</div>
                <div class="radio-group">
                    <label class="radio-ya">
                        <input type="radio" name="q_diskusi" value="1"
                               {{ $frak04->sudah_diskusi_dengan_asesor === true ? 'checked' : '' }}
                               {{ $isSubmitted ? 'disabled' : '' }}
                               onchange="markAnswered('card-q2')"> YA
                    </label>
                    <label class="radio-tdk">
                        <input type="radio" name="q_diskusi" value="0"
                               {{ $frak04->sudah_diskusi_dengan_asesor === false ? 'checked' : '' }}
                               {{ $isSubmitted ? 'disabled' : '' }}
                               onchange="markAnswered('card-q2')"> TIDAK
                    </label>
                </div>
            </div>

            {{-- Q3 --}}
            <div class="pertanyaan-card {{ $frak04->melibatkan_orang_lain !== null ? 'answered' : '' }}" id="card-q3">
                <div class="pertanyaan-label">Apakah Anda mau melibatkan "orang lain" membantu Anda dalam Proses Banding?</div>
                <div class="radio-group">
                    <label class="radio-ya">
                        <input type="radio" name="q_orang_lain" value="1"
                               {{ $frak04->melibatkan_orang_lain === true ? 'checked' : '' }}
                               {{ $isSubmitted ? 'disabled' : '' }}
                               onchange="markAnswered('card-q3')"> YA
                    </label>
                    <label class="radio-tdk">
                        <input type="radio" name="q_orang_lain" value="0"
                               {{ $frak04->melibatkan_orang_lain === false ? 'checked' : '' }}
                               {{ $isSubmitted ? 'disabled' : '' }}
                               onchange="markAnswered('card-q3')"> TIDAK
                    </label>
                </div>
            </div>

            {{-- Alasan Banding --}}
            <div class="mb-4 mt-3">
                <label class="form-label fw-semibold">
                    Alasan Banding <span class="text-danger">*</span>
                </label>
                <p class="text-muted small mb-2">
                    Jelaskan alasan Anda mengajukan banding secara rinci. Anda mempunyai hak mengajukan banding
                    jika Anda menilai Proses Asesmen tidak sesuai SOP dan tidak memenuhi Prinsip Asesmen.
                </p>
                <textarea id="alasan-banding" class="form-control" rows="5"
                          placeholder="Tuliskan alasan banding Anda di sini..."
                          maxlength="2000"
                          {{ $isSubmitted ? 'disabled' : '' }}>{{ $frak04->alasan_banding }}</textarea>
                <div class="d-flex justify-content-between mt-1">
                    <div class="form-text text-muted">Minimal 10 karakter.</div>
                    <div class="form-text text-muted"><span id="char-count">{{ strlen($frak04->alasan_banding ?? '') }}</span>/2000</div>
                </div>
            </div>

            {{-- TTD / Submit (hanya tampil kalau belum submit) --}}
            @if(!$isSubmitted)
            <div class="card mb-4 border-0 bg-light">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="bi bi-pen me-2"></i>Tanda Tangan Asesi</h6>
                </div>
                <div class="card-body">
                    @include('partials._signature_pad', [
                        'padId'    => 'asesi-banding',
                        'padLabel' => 'Tanda Tangan Pengaju Banding',
                        'padHeight' => 180,
                        'savedSig' => auth()->user()->signature_image,
                    ])

                    <div class="form-check mt-3 mb-3">
                        <input class="form-check-input" type="checkbox" id="banding-agree">
                        <label class="form-check-label small" for="banding-agree">
                            Saya menyatakan bahwa informasi yang saya berikan dalam formulir banding ini
                            adalah <strong>benar dan dapat dipertanggungjawabkan</strong>.
                        </label>
                    </div>

                    <button type="button" class="btn btn-warning" id="btn-submit-banding" onclick="submitBanding()">
                        <i class="bi bi-send me-1"></i> Ajukan Banding
                    </button>
                </div>
            </div>
            @else
            {{-- View TTD setelah submit --}}
            <div class="card mb-4">
                <div class="card-header bg-light small fw-bold">Tanda Tangan Pengaju Banding</div>
                <div class="card-body text-center">
                    @if($frak04->ttd_asesi)
                    <img src="{{ $frak04->ttd_asesi_image }}" style="max-height:70px; max-width:100%;" alt="TTD">
                    <div class="small text-muted mt-2">{{ $frak04->nama_ttd_asesi }}</div>
                    <div class="small text-muted">{{ $frak04->tanggal_ttd_asesi?->translatedFormat('d M Y H:i') }}</div>
                    @endif
                </div>
            </div>
            @endif

        </div>{{-- end #form-banding --}}

        <div class="d-flex justify-content-between mt-2">
            <a href="{{ route('asesi.schedule') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Jadwal
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const CSRF_BANDING = document.querySelector('meta[name="csrf-token"]')?.content;

function toggleForm(checkbox) {
    const form = document.getElementById('form-banding');
    if (!form) return;
    if (checkbox.checked) {
        form.style.opacity = '1';
        form.style.pointerEvents = 'auto';
        SigPadManager.init('asesi-banding, @json(auth()->user()->signature_image));
    } else {
        form.style.opacity = '.4';
        form.style.pointerEvents = 'none';
    }
}

function markAnswered(cardId) {
    const card = document.getElementById(cardId);
    if (card) card.classList.add('answered');
}

const alasanEl = document.getElementById('alasan-banding');
const charCount = document.getElementById('char-count');
if (alasanEl && charCount) {
    alasanEl.addEventListener('input', () => {
        charCount.textContent = alasanEl.value.length;
    });
}

async function submitBanding() {
    const q1 = document.querySelector('input[name="q_proses_banding"]:checked')?.value;
    const q2 = document.querySelector('input[name="q_diskusi"]:checked')?.value;
    const q3 = document.querySelector('input[name="q_orang_lain"]:checked')?.value;
    const alasan = document.getElementById('alasan-banding')?.value?.trim() ?? '';
    const agree  = document.getElementById('banding-agree')?.checked;

    if (q1 === undefined || q2 === undefined || q3 === undefined) {
        Swal.fire({ icon: 'warning', title: 'Pertanyaan Belum Dijawab', text: 'Harap jawab semua pertanyaan (Ya/Tidak) terlebih dahulu.' });
        return;
    }
    if (alasan.length < 10) {
        Swal.fire({ icon: 'warning', title: 'Alasan Diperlukan', text: 'Mohon tuliskan alasan banding Anda (minimal 10 karakter).' });
        return;
    }
    if (SigPadManager.isEmpty('asesi-banding')) {
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Diperlukan', text: 'Mohon tanda tangan di kotak yang tersedia.' });
        return;
    }
    if (!agree) {
        Swal.fire({ icon: 'warning', title: 'Persetujuan Diperlukan', text: 'Centang pernyataan persetujuan terlebih dahulu.' });
        return;
    }

    const result = await Swal.fire({
        title: 'Konfirmasi Pengajuan Banding',
        html: `<p class="text-muted small mb-0">Setelah diajukan, banding <strong>tidak dapat diubah</strong>.<br>Pastikan seluruh isian sudah benar.</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Ajukan Banding',
        cancelButtonText: 'Periksa Ulang',
        confirmButtonColor: '#d97706',
        reverseButtons: true,
    });
    if (!result.isConfirmed) return;

    const btn = document.getElementById('btn-submit-banding');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengajukan...';

    const signature = await SigPadManager.prepareAndGet('asesi');

    const formData = new FormData();
    formData.append('proses_banding_dijelaskan',   q1);
    formData.append('sudah_diskusi_dengan_asesor', q2);
    formData.append('melibatkan_orang_lain',       q3);
    formData.append('alasan_banding',              alasan);
    formData.append('signature',                   signature);
    formData.append('nama_asesi',                  '{{ $asesmen->full_name }}');

    try {
        const res  = await fetch('{{ route("asesi.frak04.submit") }}', {
            method: 'POST', body: formData,
            headers: { 'X-CSRF-TOKEN': CSRF_BANDING, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Banding Berhasil Diajukan!', text: data.message })
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i> Ajukan Banding';
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Ajukan Banding';
    }
}
</script>
@endpush