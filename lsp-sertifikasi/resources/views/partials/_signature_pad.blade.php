{{--
    ══════════════════════════════════════════════════════════════
    REUSABLE SIGNATURE PAD — dengan dukungan TTD Tersimpan
    ══════════════════════════════════════════════════════════════

    PROPS:
      $padId        — unik ID per halaman, misal 'asesi', 'asesor'   (required)
      $padLabel     — label teks                                       (optional)
      $padHeight    — tinggi canvas px, default 200                   (optional)
      $savedSig     — base64/data-URI TTD tersimpan dari DB           (optional)
                      biasanya: auth()->user()->signature_image

    CARA PAKAI:
      @include('partials._signature_pad', [
          'padId'    => 'asesi',
          'padLabel' => 'Tanda Tangan Asesi',
          'savedSig' => auth()->user()->signature_image,
      ])

    CARA AMBIL DATA DI JS:
      prepareAndGet('asesi')   → base64 PNG string atau null
      SigPadManager.isEmpty('asesi')      → boolean
      SigPadManager.clear('asesi')        → void
--}}

@php
    $padId     = $padId    ?? 'default';
    $padLabel  = $padLabel ?? 'Tanda Tangan';
    $padHeight = $padHeight ?? 200;
    $savedSig  = $savedSig  ?? auth()->user()->signature_image ?? null;
    $hasSaved  = !empty($savedSig);
    $uid       = 'sp-' . $padId;
@endphp

<div class="sig-pad-wrapper" id="{{ $uid }}-wrapper">

    @if($padLabel)
    <label class="form-label fw-semibold mb-2">
        {{ $padLabel }} <span class="text-danger">*</span>
    </label>
    @endif

    {{-- ══════════════════════════════════════════════
         STATE A: TTD TERSIMPAN — preview + konfirmasi
         ══════════════════════════════════════════════ --}}
    <div id="{{ $uid }}-saved-state" style="{{ $hasSaved ? '' : 'display:none;' }}">
        <div style="border:2px solid #22c55e; border-radius:8px; background:#f0fdf4; padding:16px;">

            {{-- Header --}}
            <div class="d-flex align-items-center gap-2 mb-3">
                <svg width="18" height="18" viewBox="0 0 20 20" fill="#22c55e">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                </svg>
                <span class="fw-semibold text-success" style="font-size:.9rem;">Tanda tangan tersimpan di profil Anda</span>
            </div>

            {{-- Preview TTD --}}
            <div style="background:#fff; border:1px solid #bbf7d0; border-radius:6px; padding:10px; margin-bottom:14px; text-align:center;">
                <img id="{{ $uid }}-saved-preview"
                    src="{{ $savedSig ?? '' }}"
                    style="max-height:{{ $padHeight - 60 }}px; max-width:100%; object-fit:contain;"
                    alt="TTD Tersimpan">
            </div>

            {{-- Pilihan: Pakai ini atau Gunakan baru --}}
            <div class="d-flex flex-column gap-2">
                {{-- Tombol "Gunakan TTD ini" --}}
                <button type="button"
                    id="{{ $uid }}-use-saved-btn"
                    class="btn btn-success btn-sm w-100"
                    onclick="SigPadManager._useSaved('{{ $padId }}')"
                    style="font-size:.85rem;">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor" class="me-1">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    Gunakan tanda tangan ini
                </button>

                {{-- Tombol "Gunakan TTD baru" --}}
                <button type="button"
                    class="btn btn-outline-secondary btn-sm w-100"
                    onclick="SigPadManager._switchToNew('{{ $padId }}')"
                    style="font-size:.85rem;">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="currentColor" class="me-1">
                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                    </svg>
                    Gambar / upload tanda tangan baru
                </button>
            </div>
        </div>

        {{-- Konfirmasi: sudah memilih pakai TTD tersimpan --}}
        <div id="{{ $uid }}-confirmed-state" style="display:none; margin-top:10px;">
            <div style="border:2px solid #3b82f6; border-radius:8px; background:#eff6ff; padding:12px 16px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="#3b82f6">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                        </svg>
                        <span style="font-size:.85rem; color:#1d4ed8; font-weight:600;">
                            Tanda tangan tersimpan akan digunakan
                        </span>
                    </div>
                    <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        onclick="SigPadManager._cancelUseSaved('{{ $padId }}')"
                        style="font-size:.75rem; padding:2px 8px;">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         STATE B: INPUT BARU (draw / upload)
         ══════════════════════════════════════════════ --}}
    <div id="{{ $uid }}-new-state" style="{{ $hasSaved ? 'display:none;' : '' }}">

        {{-- Kembali ke TTD tersimpan (hanya muncul kalau hasSaved) --}}
        @if($hasSaved)
        <div class="mb-2">
            <button type="button"
                class="btn btn-sm btn-link text-secondary p-0"
                onclick="SigPadManager._backToSaved('{{ $padId }}')"
                style="font-size:.82rem; text-decoration:none;">
                ← Kembali gunakan tanda tangan tersimpan
            </button>
        </div>
        @endif

        {{-- Mode tabs --}}
        <div class="sig-mode-tabs d-flex gap-0 mb-0"
            style="border:1px solid #dee2e6; border-bottom:none; border-radius:6px 6px 0 0; overflow:hidden; width:fit-content;">
            <button type="button"
                class="sig-tab-btn active btn btn-sm rounded-0"
                id="{{ $uid }}-tab-draw"
                onclick="SigPadManager._switchTab('{{ $padId }}', 'draw')"
                style="font-size:.82rem; padding:5px 14px; border-right:1px solid #dee2e6;">
                Gambar
            </button>
            <button type="button"
                class="sig-tab-btn btn btn-sm rounded-0"
                id="{{ $uid }}-tab-upload"
                onclick="SigPadManager._switchTab('{{ $padId }}', 'upload')"
                style="font-size:.82rem; padding:5px 14px;">
                Upload
            </button>
        </div>

        {{-- Panel Draw --}}
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

        {{-- Panel Upload --}}
        <div id="{{ $uid }}-panel-upload"
            style="display:none; border:2px dashed #94a3b8; border-radius:0 6px 6px 6px; background:#f8fafc; min-height:{{ $padHeight }}px; position:relative;">
            <div id="{{ $uid }}-dropzone"
                style="min-height:{{ $padHeight }}px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; padding:20px; cursor:pointer;"
                onclick="document.getElementById('{{ $uid }}-file-input').click()"
                ondragover="event.preventDefault(); this.style.background='#e0eeff';"
                ondragleave="this.style.background='';"
                ondrop="SigPadManager._onDrop(event, '{{ $padId }}')">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <div style="text-align:center; color:#64748b; font-size:.85rem;">
                    <strong style="color:#3b82f6;">Klik untuk upload</strong> atau drag &amp; drop<br>
                    <span style="font-size:.78rem; color:#94a3b8;">PNG, JPG — maks 2 MB</span>
                </div>
            </div>
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
            <div class="d-flex align-items-center gap-2">
                {{-- Checkbox simpan ke profil --}}
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox"
                        id="{{ $uid }}-save-checkbox" checked>
                    <label class="form-check-label" for="{{ $uid }}-save-checkbox"
                        style="font-size:.8rem; color:#64748b; cursor:pointer;">
                        Simpan ke profil saya
                    </label>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="SigPadManager.clear('{{ $padId }}')">
                🗑️ Hapus
            </button>
        </div>
    </div>

</div>

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
.sig-tab-btn:not(.active):hover { background: #f1f5f9; }
</style>
@endonce

@once
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const SigPadManager = (() => {
    const state = {};
    // { mode, pad, dataURL, usingSaved, savedDataURL }

    function _uid(id) { return 'sp-' + id; }

    function _initCanvas(padId) {
        const uid    = _uid(padId);
        const canvas = document.getElementById(uid + '-canvas');
        if (!canvas) return;

        const ratio  = Math.max(window.devicePixelRatio ?? 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        const h      = parseInt(canvas.style.height) || 200;
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

        pad.addEventListener('beginStroke', () => {
            const ph = document.getElementById(uid + '-placeholder');
            if (ph) ph.style.opacity = '0';
        });
        pad.addEventListener('afterUpdateStroke', () => {
            state[padId].dataURL = pad.toDataURL('image/png');
        });

        state[padId].pad = pad;
    }

    // ── Simpan TTD baru ke profil via AJAX ──
    async function _saveToProfile(padId, dataURL) {
        const checkbox = document.getElementById(_uid(padId) + '-save-checkbox');
        if (!checkbox || !checkbox.checked) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const res = await fetch('/user/signature', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ signature: dataURL }),
            });
            const data = await res.json();
            if (data.success) {
                // Update semua pad lain di halaman dengan TTD baru (opsional)
                console.log('[SigPad] TTD berhasil disimpan ke profil.');
            }
        } catch (e) {
            console.warn('[SigPad] Gagal simpan TTD ke profil:', e);
        }
    }

    return {
        init(padId, savedDataURL = null) {
            state[padId] = {
                mode:       'draw',
                pad:        null,
                dataURL:    null,
                usingSaved: false,
                savedDataURL: savedDataURL,
            };
            // Kalau ada TTD tersimpan, default state = saved (belum dikonfirmasi)
            if (!savedDataURL) {
                _initCanvas(padId);
            }
        },

        // ── Konfirmasi pakai TTD tersimpan ──
        _useSaved(padId) {
            const uid = _uid(padId);
            state[padId].usingSaved = true;
            state[padId].dataURL    = state[padId].savedDataURL;

            // Sembunyikan pilihan, tampilkan konfirmasi
            document.getElementById(uid + '-use-saved-btn').style.display = 'none';
            document.getElementById(uid + '-confirmed-state').style.display = 'block';
        },

        // ── Batal pakai TTD tersimpan ──
        _cancelUseSaved(padId) {
            const uid = _uid(padId);
            state[padId].usingSaved = false;
            state[padId].dataURL    = null;

            document.getElementById(uid + '-use-saved-btn').style.display = 'block';
            document.getElementById(uid + '-confirmed-state').style.display = 'none';
        },

        // ── Beralih ke input TTD baru ──
        _switchToNew(padId) {
            const uid = _uid(padId);
            state[padId].usingSaved = false;
            state[padId].dataURL    = null;

            document.getElementById(uid + '-saved-state').style.display = 'none';
            document.getElementById(uid + '-new-state').style.display   = 'block';

            // Init canvas kalau belum
            if (!state[padId].pad) {
                setTimeout(() => _initCanvas(padId), 50);
            }
        },

        // ── Kembali ke TTD tersimpan ──
        _backToSaved(padId) {
            const uid = _uid(padId);
            state[padId].usingSaved = false;
            state[padId].dataURL    = null;

            // Reset konfirmasi
            document.getElementById(uid + '-use-saved-btn').style.display = 'block';
            document.getElementById(uid + '-confirmed-state').style.display = 'none';

            document.getElementById(uid + '-saved-state').style.display = 'block';
            document.getElementById(uid + '-new-state').style.display   = 'none';
        },

        // ── Switch draw / upload ──
        _switchTab(padId, mode) {
            const uid = _uid(padId);
            if (!state[padId]) this.init(padId);
            state[padId].mode = mode;

            document.getElementById(uid + '-panel-draw').style.display   = mode === 'draw'   ? 'block' : 'none';
            document.getElementById(uid + '-panel-upload').style.display = mode === 'upload' ? 'block' : 'none';
            document.getElementById(uid + '-tab-draw')  ?.classList.toggle('active', mode === 'draw');
            document.getElementById(uid + '-tab-upload')?.classList.toggle('active', mode === 'upload');

            if (mode === 'draw' && !state[padId].pad) {
                setTimeout(() => _initCanvas(padId), 50);
            }
        },

        _onFileChange(event, padId) {
            const file = event.target.files[0];
            if (file) SigPadManager._processFile(file, padId);
        },

        _onDrop(event, padId) {
            event.preventDefault();
            document.getElementById(_uid(padId) + '-dropzone').style.background = '';
            const file = event.dataTransfer.files[0];
            if (file) SigPadManager._processFile(file, padId);
        },

        _processFile(file, padId) {
            const uid = _uid(padId);
            if (!['image/png','image/jpeg','image/jpg'].includes(file.type)) {
                alert('Format tidak didukung. Gunakan PNG atau JPG.');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('File terlalu besar. Maks 2 MB.');
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                const dataURL = e.target.result;
                state[padId].dataURL = dataURL;

                const preview  = document.getElementById(uid + '-preview-img');
                const dropzone = document.getElementById(uid + '-dropzone');
                const prevWrap = document.getElementById(uid + '-preview-wrap');
                const nameEl   = document.getElementById(uid + '-preview-name');
                if (preview)  preview.src           = dataURL;
                if (nameEl)   nameEl.textContent     = file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
                if (dropzone) dropzone.style.display  = 'none';
                if (prevWrap) prevWrap.style.display  = 'flex';
            };
            reader.readAsDataURL(file);
        },

        /**
         * Ambil data URI TTD yang aktif.
         * Juga auto-save ke profil kalau TTD baru & checkbox centang.
         */
        getDataURL(padId) {
            const s = state[padId];
            if (!s) return null;

            // Kalau sedang pakai TTD tersimpan
            if (s.usingSaved) return s.savedDataURL;

            // TTD baru dari draw
            if (s.mode === 'draw') {
                return (s.pad && !s.pad.isEmpty()) ? s.pad.toDataURL('image/png') : null;
            }
            // TTD baru dari upload
            return s.dataURL ?? null;
        },

        isEmpty(padId) {
            return this.getDataURL(padId) === null;
        },

        /**
         * Panggil ini sesaat sebelum submit form/AJAX.
         * Akan auto-save TTD baru ke profil jika checkbox dicentang.
         */
        async prepareAndGet(padId) {
            const dataURL = this.getDataURL(padId);
            const s = state[padId];

            // Auto-save TTD baru ke profil (bukan TTD tersimpan)
            if (dataURL && !s.usingSaved) {
                await _saveToProfile(padId, dataURL);
            }

            return dataURL;
        },

        clear(padId) {
            const uid = _uid(padId);
            const s   = state[padId];
            if (!s) return;

            s.dataURL    = null;
            s.usingSaved = false;

            if (s.mode === 'draw' && s.pad) {
                s.pad.clear();
                const ph = document.getElementById(uid + '-placeholder');
                if (ph) ph.style.opacity = '.25';
            } else if (s.mode === 'upload') {
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