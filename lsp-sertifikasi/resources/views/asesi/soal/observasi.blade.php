{{-- resources/views/asesi/soal/observasi.blade.php --}}
@extends('layouts.app')

@section('title', 'Soal Observasi')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <h5 class="fw-bold mb-1">Soal Observasi</h5>
    <p class="text-muted mb-0" style="font-size:.875rem">
        Lihat paket soal observasi dan upload hasil pekerjaan via Google Drive.
    </p>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 px-3" style="font-size:.875rem">
    <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- ══ PERINGATAN: ada paket belum diisi ══ --}}
@if($belumIsi > 0 && $isReopenActive)
<div class="alert d-flex gap-2 py-2 px-3 mb-3 border
            {{ $isReopenActive ? 'alert-warning' : 'alert-danger' }}"
     style="font-size:.82rem">
    <i class="bi {{ $isReopenActive ? 'bi-exclamation-triangle-fill text-warning' : 'bi-x-circle-fill text-danger' }} flex-shrink-0 mt-1"></i>
    <div>
        @if($isReopenActive)
            <strong>{{ $belumIsi }} dari {{ $totalPaket }} link belum diisi.</strong>
            Segera isi sebelum batas waktu berakhir:
            <strong id="countdown-reopen" class="text-danger ms-1"></strong>
        @else
            <strong>{{ $belumIsi }} dari {{ $totalPaket }} link belum diisi.</strong>
            Waktu pengumpulan sudah berakhir. Hubungi asesor Anda untuk membuka kembali.
        @endif
    </div>
</div>
@endif

{{-- ══ INFO: window reopen aktif ══ --}}
@if($isReopenActive)
<div class="alert alert-success d-flex gap-2 py-2 px-3 mb-3" style="font-size:.82rem">
    <i class="bi bi-unlock-fill text-success flex-shrink-0 mt-1"></i>
    <div>
        <strong>Pengumpulan link dibuka kembali oleh asesor.</strong>
        Anda dapat mengisi atau memperbarui link hingga
        <strong>{{ $reopenUntil->translatedFormat('d F Y, H:i') }} WIB</strong>.
    </div>
</div>
@endif

{{-- Panduan --}}
<div class="alert alert-info d-flex gap-2 py-2 px-3 mb-4" style="font-size:.8rem">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>Cara pengumpulan:</strong>
        Upload hasil pekerjaan Anda ke Google Drive, lalu paste link-nya di kolom yang tersedia di bawah.
        Pastikan link sudah diset ke <em>"Anyone with the link can view"</em>.
    </div>
</div>

@forelse($distribusiObservasi as $dist)
@php $obs = $dist->soalObservasi; @endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex align-items-center gap-2" style="background:#f0f9ff;border-bottom:1px solid #bae6fd">
        <i class="bi bi-eye text-primary"></i>
        <h6 class="fw-bold mb-0">{{ $obs->judul }}</h6>
        <span class="badge bg-primary-subtle text-primary ms-auto" style="font-size:.7rem">
            {{ $obs->paket->count() }} paket
        </span>
    </div>
    <div class="card-body p-0">
        @if($obs->paket->isEmpty())
        <div class="text-center py-4 text-muted" style="font-size:.875rem">
            <i class="bi bi-file-earmark-pdf" style="font-size:2rem;opacity:.3;display:block"></i>
            Belum ada paket dalam soal observasi ini.
        </div>
        @else
        <div class="d-flex flex-column gap-0">
            @foreach($obs->paket as $paket)
            @php
                $jawaban = $jawabanMap[$paket->id] ?? null;
                $hasLink = $jawaban?->hasLink();
                // Input bisa diisi kalau: reopen aktif, atau belum pernah diisi (window normal)
                // Tidak bisa diisi kalau: reopen sudah expired (set tapi masa lalu)
                $reopenExpired = $asesmen->observasi_reopen_until
                    && \Carbon\Carbon::parse($asesmen->observasi_reopen_until)->isPast();
                $canEdit = !$reopenExpired || $isReopenActive;
            @endphp
            <div class="d-flex align-items-center gap-4 px-4 py-3 border-bottom flex-wrap
                         {{ $hasLink ? 'bg-success-subtle' : ($reopenExpired && !$hasLink ? 'bg-danger-subtle' : '') }}">

                {{-- Kode Paket --}}
                <div class="d-flex align-items-center gap-3 flex-shrink-0" style="min-width:200px">
                    <span class="badge rounded-circle fw-bold d-flex align-items-center justify-content-center"
                          style="width:36px;height:36px;font-size:.9rem;background:#2563eb;color:white;flex-shrink:0">
                        {{ $paket->kode_paket }}
                    </span>
                    <div>
                        <div class="fw-semibold" style="font-size:.875rem">Paket {{ $paket->kode_paket }}</div>
                        <div class="d-flex align-items-center gap-1 text-muted" style="font-size:.78rem">
                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                            <a href="{{ route('asesi.soal.observasi.download', $paket) }}"
                               class="text-decoration-none text-muted" target="_blank">
                                {{ $paket->file_name }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex-shrink-0" style="min-width:100px">
                    @if($hasLink)
                    <span class="badge bg-success" style="font-size:.72rem">
                        <i class="bi bi-check-circle me-1"></i>Sudah Upload
                    </span>
                    @elseif($reopenExpired)
                    <span class="badge bg-danger" style="font-size:.72rem">
                        <i class="bi bi-x-circle me-1"></i>Waktu Habis
                    </span>
                    @else
                    <span class="badge bg-warning text-dark" style="font-size:.72rem">
                        <i class="bi bi-clock me-1"></i>Belum Upload
                    </span>
                    @endif
                </div>

                {{-- Input GDrive --}}
                <div class="flex-grow-1">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white">
                            <img src="https://www.gstatic.com/images/branding/product/1x/drive_2020q4_48dp.png"
                                 style="width:16px;height:16px" alt="GDrive">
                        </span>
                        <input type="url"
                               class="form-control gdrive-input"
                               data-dist-id="{{ $dist->id }}"
                               data-paket-id="{{ $paket->id }}"
                               placeholder="{{ $canEdit ? 'Paste link Google Drive...' : 'Tidak dapat diubah (waktu habis)' }}"
                               value="{{ $jawaban?->gdrive_link ?? '' }}"
                               style="font-size:.82rem"
                               {{ $canEdit ? '' : 'readonly' }}>
                        @if($canEdit)
                        <button type="button" class="btn btn-primary btn-sm save-link-btn"
                                data-dist-id="{{ $dist->id }}"
                                data-paket-id="{{ $paket->id }}">
                            <i class="bi bi-save me-1"></i>Simpan
                        </button>
                        @endif
                        @if($hasLink)
                        <a href="{{ $jawaban->gdrive_link }}" target="_blank"
                           class="btn btn-outline-secondary btn-sm" title="Buka di GDrive">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                        @endif
                    </div>
                    @if($hasLink)
                    <small class="text-success mt-1 d-block">
                        <i class="bi bi-check-circle me-1"></i>
                        Disimpan {{ $jawaban->uploaded_at?->diffForHumans() ?? '-' }}
                    </small>
                    @elseif(!$canEdit)
                    <small class="text-danger mt-1 d-block">
                        <i class="bi bi-lock me-1"></i>Waktu pengumpulan sudah berakhir.
                    </small>
                    @else
                    <small class="text-muted mt-1 d-block">
                        Format: https://drive.google.com/...
                    </small>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@empty
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-file-earmark-pdf" style="font-size:3rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
        <p class="fw-semibold mb-0">Soal observasi belum didistribusikan</p>
        <small>Hubungi Manajer Sertifikasi jika ada pertanyaan.</small>
    </div>
</div>
@endforelse

@endsection

@push('scripts')
<script>
const SAVE_LINK_URL = "{{ route('asesi.soal.observasi.save') }}";
const CSRF          = "{{ csrf_token() }}";

// ── Countdown timer reopen ──────────────────────────────────────────────────
@if($isReopenActive)
(function() {
    const until = new Date("{{ $reopenUntil->toIso8601String() }}");
    const el    = document.getElementById('countdown-reopen');
    if (!el) return;

    function update() {
        const diff = Math.floor((until - Date.now()) / 1000);
        if (diff <= 0) {
            el.textContent = '(waktu habis)';
            clearInterval(timer);
            window.location.reload();
            return;
        }
        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;
        el.textContent = `(sisa ${h > 0 ? h+'j ' : ''}${m}m ${s}s)`;
    }
    update();
    const timer = setInterval(update, 1000);
})();
@endif

// ── Save link ──────────────────────────────────────────────────────────────
document.querySelectorAll('.save-link-btn').forEach(btn => {
    btn.addEventListener('click', async function () {
        const distId  = this.dataset.distId;
        const paketId = this.dataset.paketId;
        const input   = document.querySelector(`.gdrive-input[data-paket-id="${paketId}"]`);
        const link    = input.value.trim();

        if (link && !link.match(/^https?:\/\/(drive|docs)\.google\.com\//i)) {
            input.classList.add('is-invalid');
            showToast('Link harus berupa URL Google Drive yang valid.', 'danger');
            return;
        }
        input.classList.remove('is-invalid');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const res = await fetch(SAVE_LINK_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ distribusi_id: distId, paket_id: paketId, gdrive_link: link }),
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Gagal menyimpan link.', 'danger');
            }
        } catch (e) {
            showToast('Terjadi kesalahan.', 'danger');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan';
        }
    });
});

function showToast(msg, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;
    toast.style.cssText = 'z-index:9999;font-size:.875rem;min-width:250px;animation:fadeIn .3s';
    toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'} me-2"></i>${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
</script>
@endpush