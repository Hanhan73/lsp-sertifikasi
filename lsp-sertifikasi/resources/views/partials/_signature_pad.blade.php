{{--
    ══════════════════════════════════════════════════════
    REUSABLE SIGNATURE PAD — Canvas Draw & Upload Image
    ══════════════════════════════════════════════════════

    PROPS (via $attributes / local variables, set before include):
      $padId        — unik ID per halaman, misal 'admin', 'asesi', 'asesor'   (required)
      $padLabel     — label teks, misal 'Tanda Tangan Admin'                   (optional)
      $padHeight    — tinggi canvas px, default 200                            (optional)

    CARA PAKAI DI BLADE:
      @include('partials._signature_pad', ['padId' => 'admin', 'padLabel' => 'Tanda Tangan Admin LSP'])

    CARA AMBIL DATA DI JS:
      SignaturePadManager.getDataURL('admin')   → base64 PNG string atau null
      SignaturePadManager.isEmpty('admin')      → boolean
      SignaturePadManager.clear('admin')        → void

    MENYUNTIK KE FORM:
      const sig = SignaturePadManager.getDataURL('admin');
      // lalu kirim sig ke server (sudah termasuk prefix data:image/png;base64,...)
--}}

@php
    $padId     = $padId     ?? 'default';
    $padLabel  = $padLabel  ?? 'Tanda Tangan';
    $padHeight = $padHeight ?? 200;
    $uid       = 'sp-' . $padId;  // prefix untuk semua element ID
@endphp

<div class="sig-pad-wrapper" id="{{ $uid }}-wrapper">

    {{-- Label --}}
    @if($padLabel)
    <label class="form-label fw-semibold mb-2">
        {{ $padLabel }} <span class="text-danger">*</span>
    </label>
    @endif

    {{-- Mode tabs --}}
    <div class="sig-mode-tabs d-flex gap-0 mb-0" style="border:1px solid #dee2e6; border-bottom:none; border-radius:6px 6px 0 0; overflow:hidden; width:fit-content;">
        <button type="button"
            class="sig-tab-btn active btn btn-sm rounded-0"
            id="{{ $uid }}-tab-draw"
            onclick="SigPadManager._switchTab('{{ $padId }}', 'draw')"
            style="font-size:.82rem; padding:5px 14px; border-right:1px solid #dee2e6;">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor" class="me-1" style="vertical-align:-.1em;">
                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
            </svg>
            Gambar
        </button>
        <button type="button"
            class="sig-tab-btn btn btn-sm rounded-0"
            id="{{ $uid }}-tab-upload"
            onclick="SigPadManager._switchTab('{{ $padId }}', 'upload')"
            style="font-size:.82rem; padding:5px 14px;">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor" class="me-1" style="vertical-align:-.1em;">
                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
            </svg>
            Upload
        </button>
    </div>

    {{-- PANEL DRAW --}}
    <div id="{{ $uid }}-panel-draw"
        style="border:2px dashed #94a3b8; border-radius:0 6px 6px 6px; background:#f0f4f8; overflow:hidden; position:relative;">
        <canvas id="{{ $uid }}-canvas"
            style="display:block; width:100%; height:{{ $padHeight }}px; touch-action:none; cursor:crosshair;">
        </canvas>
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); pointer-events:none; opacity:.25; font-size:.8rem; color:#475569; text-align:center; line-height:1.4;" id="{{ $uid }}-placeholder">
            Tanda tangan di sini<br>
            <small>gunakan mouse atau layar sentuh</small>
        </div>
    </div>

    {{-- PANEL UPLOAD --}}
    <div id="{{ $uid }}-panel-upload"
        style="display:none; border:2px dashed #94a3b8; border-radius:0 6px 6px 6px; background:#f8fafc; min-height:{{ $padHeight }}px; position:relative;">

        {{-- Drop zone --}}
        <div id="{{ $uid }}-dropzone"
            style="min-height:{{ $padHeight }}px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; padding:20px; cursor:pointer;"
            onclick="document.getElementById('{{ $uid }}-file-input').click()"
            ondragover="event.preventDefault(); this.style.background='#e0eeff';"
            ondragleave="this.style.background=''; "
            ondrop="SigPadManager._onDrop(event, '{{ $padId }}')">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
            <div style="text-align:center; color:#64748b; font-size:.85rem;">
                <strong style="color:#3b82f6;">Klik untuk upload</strong> atau drag &amp; drop<br>
                <span style="font-size:.78rem; color:#94a3b8;">PNG, JPG, JPEG — maks 2 MB</span>
            </div>
        </div>

        {{-- Preview after upload --}}
        <div id="{{ $uid }}-preview-wrap"
            style="display:none; min-height:{{ $padHeight }}px; align-items:center; justify-content:center; padding:12px; flex-direction:column; gap:10px;">
            <img id="{{ $uid }}-preview-img"
                style="max-height:{{ $padHeight - 40 }}px; max-width:100%; object-fit:contain; border:1px solid #e2e8f0; border-radius:4px; background:#fff;"
                alt="Preview TTD">
            <div style="font-size:.78rem; color:#64748b;" id="{{ $uid }}-preview-name"></div>
        </div>

        <input type="file" id="{{ $uid }}-file-input" accept="image/png,image/jpeg,image/jpg"
            style="display:none;" onchange="SigPadManager._onFileChange(event, '{{ $padId }}')">
    </div>

    {{-- Footer controls --}}
    <div class="d-flex justify-content-between align-items-center mt-2">
        <small class="text-muted" id="{{ $uid }}-hint">
            Gambar tanda tangan Anda
        </small>
        <button type="button" class="btn btn-sm btn-outline-secondary"
            onclick="SigPadManager.clear('{{ $padId }}')">
            <svg width="13" height="13" viewBox="0 0 16 16" fill="currentColor" class="me-1">
                <path d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828l6.879-6.879zm2.121.707a1 1 0 0 0-1.414 0L4.16 7.547l5.293 5.293 4.633-4.633a1 1 0 0 0 0-1.414l-3.879-3.879zM8.746 13.547 3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293l.16-.16z"/>
            </svg>
            Hapus
        </button>
    </div>

</div>

{{-- ── CSS (hanya sekali jika belum di-render) ── --}}
@once
<style>
.sig-tab-btn {
    background: #fff;
    color: #64748b;
    border: none;
    transition: background .15s, color .15s;
}
.sig-tab-btn.active {
    background: #3b82f6;
    color: #fff;
}
.sig-tab-btn:not(.active):hover {
    background: #f1f5f9;
}
</style>
@endonce

{{-- ── JS Manager (singleton, hanya sekali) ── --}}
@once
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
/**
 * SigPadManager — singleton yang mengelola semua signature pad di halaman.
 * Setiap pad diidentifikasi dengan padId (string).
 */
const SigPadManager = (() => {
    const state = {};   // padId → { mode, pad, dataURL }

    function _uid(padId) { return 'sp-' + padId; }

    function _initCanvas(padId) {
        const uid    = _uid(padId);
        const canvas = document.getElementById(uid + '-canvas');
        if (!canvas) return;

        const ratio  = Math.max(window.devicePixelRatio ?? 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        const h = parseInt(canvas.style.height) || 200;
        canvas.height = h * ratio;
        canvas.getContext('2d').scale(ratio, ratio);

        const pad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255,255,255)',
            penColor: 'rgb(0,0,180)',
            minWidth: 1.5,
            maxWidth: 3.5,
        });

        ['touchstart','touchmove'].forEach(ev =>
            canvas.addEventListener(ev, e => e.preventDefault(), { passive: false })
        );

        // Hide placeholder on draw start
        pad.addEventListener('beginStroke', () => {
            const ph = document.getElementById(uid + '-placeholder');
            if (ph) ph.style.opacity = '0';
        });
        pad.addEventListener('afterUpdateStroke', () => {
            state[padId].dataURL = pad.toDataURL('image/png');
        });

        state[padId].pad = pad;
    }

    return {
        /** Panggil ini setelah DOM siap untuk setiap padId yang ada di halaman */
        init(padId) {
            const uid = _uid(padId);
            state[padId] = { mode: 'draw', pad: null, dataURL: null };
            _initCanvas(padId);
            // Set hint default
            const hint = document.getElementById(uid + '-hint');
            if (hint) hint.textContent = 'Gambar tanda tangan Anda menggunakan mouse atau layar sentuh.';
        },

        /** Switch antara mode 'draw' dan 'upload' */
        _switchTab(padId, mode) {
            const uid = _uid(padId);

            // AUTO INIT kalau belum ada
            if (!state[padId]) {
                SigPadManager.init(padId);
            }

            state[padId].mode = mode;

            // Panel visibility
            const drawPanel   = document.getElementById(uid + '-panel-draw');
            const uploadPanel = document.getElementById(uid + '-panel-upload');
            if (drawPanel)   drawPanel.style.display   = mode === 'draw'   ? 'block' : 'none';
            if (uploadPanel) uploadPanel.style.display = mode === 'upload' ? 'block' : 'none';

            // Tab active state
            document.getElementById(uid + '-tab-draw')  ?.classList.toggle('active', mode === 'draw');
            document.getElementById(uid + '-tab-upload')?.classList.toggle('active', mode === 'upload');

            // Hint text
            const hint = document.getElementById(uid + '-hint');
            if (hint) {
                hint.textContent = mode === 'draw'
                    ? 'Gambar tanda tangan Anda menggunakan mouse atau layar sentuh.'
                    : 'Upload gambar tanda tangan (PNG/JPG, maks 2 MB). Gunakan background putih agar terbaca.';
            }

            // Re-init canvas jika switch ke draw (ukuran mungkin berubah)
            if (mode === 'draw' && state[padId].pad === null) {
                setTimeout(() => _initCanvas(padId), 50);
            }
            if (mode === 'draw' && state[padId].pad) {
                // Re-render jika perlu resize
                setTimeout(() => {
                    const canvas = document.getElementById(uid + '-canvas');
                    if (canvas && canvas.width === 0) _initCanvas(padId);
                }, 50);
            }
        },

        /** File input change handler */
        _onFileChange(event, padId) {
            const file = event.target.files[0];
            if (file) SigPadManager._processFile(file, padId);
        },

        /** Drag & drop handler */
        _onDrop(event, padId) {
            event.preventDefault();
            const uid = _uid(padId);
            const dz  = document.getElementById(uid + '-dropzone');
            if (dz) dz.style.background = '';
            const file = event.dataTransfer.files[0];
            if (file) SigPadManager._processFile(file, padId);
        },

        /** Proses file gambar → base64 */
        _processFile(file, padId) {
            const uid = _uid(padId);

            if (!['image/png','image/jpeg','image/jpg'].includes(file.type)) {
                alert('Format file tidak didukung. Gunakan PNG atau JPG.');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maks 2 MB.');
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                const dataURL = e.target.result;
                state[padId].dataURL = dataURL;

                // Show preview
                const preview  = document.getElementById(uid + '-preview-img');
                const dropzone = document.getElementById(uid + '-dropzone');
                const prevWrap = document.getElementById(uid + '-preview-wrap');
                const nameEl   = document.getElementById(uid + '-preview-name');

                if (preview)  preview.src          = dataURL;
                if (nameEl)   nameEl.textContent    = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                if (dropzone) dropzone.style.display = 'none';
                if (prevWrap) prevWrap.style.display = 'flex';
            };
            reader.readAsDataURL(file);
        },

        /** Ambil base64 data URL dari pad aktif. null jika kosong. */
        getDataURL(padId) {
            const s = state[padId];
            if (!s) return null;
            if (s.mode === 'draw') {
                return (s.pad && !s.pad.isEmpty()) ? s.pad.toDataURL('image/png') : null;
            }
            return s.dataURL ?? null;
        },

        /** Cek apakah pad kosong */
        isEmpty(padId) {
            return SigPadManager.getDataURL(padId) === null;
        },

        /** Hapus / reset pad */
        clear(padId) {
            const uid = _uid(padId);
            const s   = state[padId];
            if (!s) return;
            if (s.mode === 'draw' && s.pad) {
                s.pad.clear();
                s.dataURL = null;
                const ph = document.getElementById(uid + '-placeholder');
                if (ph) ph.style.opacity = '.25';
            } else if (s.mode === 'upload') {
                s.dataURL = null;
                const fileInput = document.getElementById(uid + '-file-input');
                if (fileInput) fileInput.value = '';
                const dropzone  = document.getElementById(uid + '-dropzone');
                const prevWrap  = document.getElementById(uid + '-preview-wrap');
                if (dropzone) dropzone.style.display = 'flex';
                if (prevWrap) prevWrap.style.display = 'none';
            }
        },
    };
})();
</script>
@endonce