@extends('layouts.app')
@section('title', 'FR.APL.02 — Asesmen Mandiri')
@section('page-title', 'FR.APL.02 — Asesmen Mandiri')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@push('styles')
<style>
/* ── Unit accordion ── */
.unit-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 12px;
    transition: box-shadow .2s;
}
.unit-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.07); }

.unit-header {
    background: #f8fafc;
    padding: 12px 16px;
    cursor: pointer;
    user-select: none;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.unit-header:hover { background: #f1f5f9; }

.unit-body { padding: 0; }

/* ── Elemen row ── */
.elemen-row {
    border-bottom: 1px solid #f1f5f9;
    padding: 10px 16px;
    transition: background .1s;
}
.elemen-row:last-child { border-bottom: none; }
.elemen-row:hover { background: #fafbff; }
.elemen-row.answered-K  { border-left: 3px solid #10b981; }
.elemen-row.answered-BK { border-left: 3px solid #ef4444; }
.elemen-row.unanswered  { border-left: 3px solid #e2e8f0; }

/* ── K / BK toggle buttons ── */
.jawaban-btn {
    min-width: 52px;
    font-size: .8rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    transition: all .15s;
}
.jawaban-btn.K.active  { background:#10b981; border-color:#10b981; color:#fff; }
.jawaban-btn.BK.active { background:#ef4444; border-color:#ef4444; color:#fff; }
.jawaban-btn:not(.active) { opacity: .55; }

/* ── Sticky progress bar ── */
.sticky-progress {
    position: sticky;
    top: 56px;
    z-index: 100;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px 0;
    margin-bottom: 16px;
}

/* ── Progress ring ── */
.progress-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; }
.progress-ring svg { transform: rotate(-90deg); }
.progress-ring .pct { position: absolute; font-size: .75rem; font-weight: 700; color: #1e293b; }

/* ── KUK mini list ── */
.kuk-list { margin-top: 4px; padding-left: 14px; }
.kuk-list li { font-size: .78rem; color: #6b7280; line-height: 1.5; }

/* ── Bukti textarea ── */
.bukti-area {
    font-size: .8rem;
    resize: vertical;
    min-height: 42px;
    border-color: #e2e8f0;
    background: #fafafa;
}
.bukti-area:focus { background: #fff; border-color: #3b82f6; }

/* ── Read-only view ── */
.ro-badge-K  { background: #d1fae5; color: #065f46; }
.ro-badge-BK { background: #fee2e2; color: #991b1b; }
.ro-badge    { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: .78rem; font-weight: 700; }

/* ── Save indicator ── */
#save-indicator {
    transition: opacity .3s;
    font-size: .8rem;
}

/* ── TTD wrapper ── */
#sig-wrapper-apldua {
    border: 2px dashed #94a3b8;
    border-radius: 8px;
    background: #f0f4f8;
    overflow: hidden;
}
#sig-canvas-apldua {
    display: block;
    width: 100%;
    height: 200px;
    touch-action: none;
    cursor: crosshair;
}
</style>
@endpush

@section('content')

{{-- ── Status banner untuk yang sudah submit ── --}}
@if($apldua && in_array($apldua->status, ['submitted','verified','approved']))
<div class="alert alert-success d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div>
        <strong>APL-02 sudah disubmit</strong>
        pada {{ $apldua->submitted_at?->format('d M Y H:i') }}.
        <span class="badge bg-{{ $apldua->status_badge }} ms-2">{{ $apldua->status_label }}</span>
    </div>
</div>
@endif

{{-- ── Header info ── --}}
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body py-3 px-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-award me-2"></i>{{ $asesmen->skema->name }}
                </h6>
                <small class="text-muted">
                    Skema: {{ $asesmen->skema->nomor_skema ?? $asesmen->skema->code }}
                    &nbsp;|&nbsp; Pemohon: <strong>{{ $asesmen->full_name }}</strong>
                </small>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('asesi.schedule') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Jadwal
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Sticky progress bar ── --}}
@if(!$apldua || $apldua->is_editable)
<div class="sticky-progress px-1" id="sticky-prog">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <div class="progress flex-grow-1" style="height:8px;">
                <div class="progress-bar bg-success" id="prog-bar" style="width:0%;transition:width .4s;"></div>
            </div>
            <span class="small text-muted text-nowrap" id="prog-label">0 / {{ $asesmen->skema->unitKompetensis->flatMap->elemens->count() }} elemen</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="small"><span class="badge bg-success" id="count-K">0</span> Kompeten</span>
            <span class="small"><span class="badge bg-danger"  id="count-BK">0</span> Blm Kompeten</span>
        </div>
        <span id="save-indicator" class="text-muted opacity-0"><i class="bi bi-cloud-check me-1"></i>Tersimpan</span>
    </div>
</div>
@endif

{{-- ═══════════ READ-ONLY (sudah submit) ═══════════ --}}
@if($apldua && !$apldua->is_editable)

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-clipboard-check me-2"></i>
        FR.APL.02 — Asesmen Mandiri (Read-Only)
    </div>
    <div class="card-body p-0">
        @foreach($asesmen->skema->unitKompetensis as $unit)
        <div class="unit-card m-3">
            <div class="unit-header" style="cursor:default;">
                <span class="badge bg-primary">{{ $loop->iteration }}</span>
                <div>
                    <div class="fw-bold small">{{ $unit->kode_unit }}</div>
                    <div class="fw-semibold">{{ $unit->judul_unit }}</div>
                </div>
            </div>
            <div class="unit-body">
                @foreach($unit->elemens as $elemen)
                @php $jaw = $jawabanMap[$elemen->id] ?? null; @endphp
                <div class="elemen-row d-flex align-items-start gap-3 {{ $jaw?->jawaban ? 'answered-'.$jaw->jawaban : 'unanswered' }}">
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $elemen->judul }}</div>
                        @if($elemen->kuks->isNotEmpty())
                        <ul class="kuk-list mb-0">
                            @foreach($elemen->kuks as $kuk)
                            <li>{{ $kuk->kode }} — {{ $kuk->deskripsi }}</li>
                            @endforeach
                        </ul>
                        @endif

                        @if($elemen->hint_bukti)
                        <div class="d-flex align-items-start gap-1 mt-1 px-2 py-1 rounded"
                                style="background:#fffbeb; border:1px solid #fde68a;">
                            <i class="bi bi-lightbulb-fill text-warning" style="font-size:.72rem; flex-shrink:0; margin-top:3px;"></i>
                            <div style="font-size:.75rem; color:#92400e;">{{ $elemen->hint_bukti }}</div>
                        </div>
                        @endif
                        @if($jaw?->bukti)
                        <div class="mt-1 text-muted" style="font-size:.78rem;">
                            <i class="bi bi-chat-left-dots me-1"></i>{{ $jaw->bukti }}
                        </div>
                        @endif
                    </div>
                    <div class="flex-shrink-0 text-end" style="min-width:100px;">
                        @if($jaw?->jawaban)
                        <span class="ro-badge ro-badge-{{ $jaw->jawaban }}">
                            {{ $jaw->jawaban === 'K' ? 'Kompeten' : 'Blm Kompeten' }}
                        </span>
                        @else
                        <span class="badge bg-secondary">Belum diisi</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Rekomendasi asesor jika sudah verified ── --}}
@if($apldua->rekomendasi_asesor)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-{{ $apldua->rekomendasi_asesor === 'lanjut' ? 'success' : 'danger' }} text-white">
        <i class="bi bi-person-check-fill me-2"></i>Rekomendasi Asesor
    </div>
    <div class="card-body">
        <div class="fw-bold mb-2">
            {{ $apldua->rekomendasi_asesor === 'lanjut' ? '✅ Direkomendasikan untuk Lanjut Asesmen' : '❌ Tidak Direkomendasikan untuk Lanjut' }}
        </div>
        @if($apldua->catatan_asesor)
        <div class="text-muted small">{{ $apldua->catatan_asesor }}</div>
        @endif
        <div class="mt-2 d-flex align-items-center gap-3 flex-wrap">
            @if($apldua->ttd_asesor)
            <div>
                <div class="text-muted small mb-1">TTD Asesor:</div>
                <img src="{{ $apldua->ttd_asesor_image }}" style="max-height:50px; border:1px solid #e2e8f0; border-radius:4px; padding:2px;" alt="TTD Asesor">
            </div>
            @endif
            @if($apldua->nama_ttd_asesor)
            <div>
                <div class="text-muted small">Nama Asesor:</div>
                <div class="fw-semibold">{{ $apldua->nama_ttd_asesor }}</div>
                @if($apldua->tanggal_ttd_asesor)
                <div class="text-muted small">{{ $apldua->tanggal_ttd_asesor->format('d M Y') }}</div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ═══════════ EDITABLE FORM ═══════════ --}}
@else

<div class="alert alert-info border-0 shadow-sm mb-4">
    <h6 class="alert-heading mb-1">
        <i class="bi bi-info-circle-fill me-2"></i>Petunjuk Pengisian Asesmen Mandiri
    </h6>
    <p class="mb-0 small">
        Berikan penilaian diri sendiri untuk setiap elemen kompetensi di bawah ini.
        Pilih <strong class="text-success">K (Kompeten)</strong> jika Anda merasa sudah menguasai elemen tersebut,
        atau <strong class="text-danger">BK (Belum Kompeten)</strong> jika belum.
        Anda juga bisa menuliskan bukti atau pengalaman yang mendukung penilaian Anda.
        Data akan <strong>tersimpan otomatis</strong>.
    </p>
</div>

{{-- ── Unit kompetensi accordion ── --}}
@foreach($asesmen->skema->unitKompetensis->load('elemens.kuks') as $unitIdx => $unit)
@php
    $unitElemenIds = $unit->elemens->pluck('id')->toArray(); // ← penting!
    $unitAnswered  = $jawabanMap
                        ->whereIn('elemen_id', $unitElemenIds)
                        ->whereNotNull('jawaban')
                        ->count();
    $unitTotal     = $unit->elemens->count();
    $unitComplete  = $unitAnswered === $unitTotal && $unitTotal > 0;
@endphp
<div class="unit-card" id="unit-card-{{ $unit->id }}">
    <div class="unit-header" onclick="toggleUnit({{ $unit->id }})">
        <i class="bi bi-chevron-right toggle-arrow" id="arrow-{{ $unit->id }}" style="transition:transform .2s;font-size:1rem;color:#94a3b8;"></i>
        <span class="badge bg-{{ $unitComplete ? 'success' : 'secondary' }} me-1">{{ $unitIdx + 1 }}</span>
        <div class="flex-grow-1">
            <div class="small text-muted">{{ $unit->kode_unit }}</div>
            <div class="fw-semibold" style="font-size:.95rem;">{{ $unit->judul_unit }}</div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <span class="small text-muted" id="unit-prog-{{ $unit->id }}">
                {{ $unitAnswered }}/{{ $unitTotal }}
            </span>
            @if($unitComplete)
            <i class="bi bi-check-circle-fill text-success"></i>
            @endif
        </div>
    </div>

    {{-- Elemen list — collapsed by default, expand first unit ── --}}
    <div class="unit-body" id="unit-body-{{ $unit->id }}" style="{{ $unitIdx === 0 ? '' : 'display:none;' }}">
        @foreach($unit->elemens as $elemen)
        @php $jaw = $jawabanMap[$elemen->id] ?? null; @endphp
        <div class="elemen-row {{ $jaw?->jawaban ? 'answered-'.$jaw->jawaban : 'unanswered' }}"
             id="row-{{ $elemen->id }}">
            <div class="d-flex align-items-start gap-3">
                {{-- Elemen info ── --}}
                <div class="flex-grow-1">
                    <div class="fw-semibold small mb-1">{{ $elemen->judul }}</div>

                    {{-- KUK list ── --}}
                    @if($elemen->kuks->isNotEmpty())
                    <ul class="kuk-list mb-2">
                        @foreach($elemen->kuks as $kuk)
                        <li>{{ $kuk->kode }} — {{ $kuk->deskripsi }}</li>
                        @endforeach
                    </ul>
                    @endif

                    @if($elemen->hint_bukti)
                    <div class="d-flex align-items-start gap-2 mb-2 p-2 rounded"
                        style="background:#fffbeb; border:1px solid #fde68a;">
                        <i class="bi bi-lightbulb-fill text-warning mt-1" style="font-size:.85rem; flex-shrink:0;"></i>
                        <div style="font-size:.78rem; color:#92400e; line-height:1.5;">
                            <strong>Panduan bukti:</strong> {{ $elemen->hint_bukti }}
                        </div>
                    </div>
                    @endif

                    {{-- Bukti textarea ── --}}
                    <textarea
                        class="form-control bukti-area mt-1"
                        rows="2"
                        placeholder="{{ $elemen->hint_bukti ? 'Paste link Google Drive atau tuliskan bukti Anda di sini...' : 'Tuliskan bukti atau pengalaman yang Anda miliki terkait elemen ini (opsional)...' }}"
                        data-elemen="{{ $elemen->id }}"
                        onchange="onBuktiChange({{ $elemen->id }}, this.value)">{{ $jaw?->bukti }}</textarea>
                </div>

                {{-- K / BK buttons ── --}}
                <div class="d-flex flex-column gap-1 flex-shrink-0 pt-1" style="min-width:58px;">
                    <button type="button"
                        class="jawaban-btn btn btn-outline-success K {{ $jaw?->jawaban === 'K' ? 'active' : '' }}"
                        data-elemen="{{ $elemen->id }}"
                        onclick="setJawaban({{ $elemen->id }}, 'K', this)">
                        K
                    </button>
                    <button type="button"
                        class="jawaban-btn btn btn-outline-danger BK {{ $jaw?->jawaban === 'BK' ? 'active' : '' }}"
                        data-elemen="{{ $elemen->id }}"
                        onclick="setJawaban({{ $elemen->id }}, 'BK', this)">
                        BK
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

{{-- ── Tanda Tangan & Submit ── --}}
<div class="card shadow-sm border-0 mt-4" id="submit-section">
    <div class="card-header bg-success text-white">
        <i class="bi bi-pen me-2"></i>Tanda Tangan & Submit
    </div>
    <div class="card-body">

        <div class="alert alert-warning mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Perhatian:</strong> Pastikan semua elemen sudah diisi sebelum submit.
            Setelah submit, jawaban tidak dapat diubah.
            Pilih <strong>K</strong> atau <strong>BK</strong> untuk setiap elemen.
        </div>

        {{-- Progress summary ── --}}
        <div class="row g-3 mb-4 text-center">
            <div class="col-4">
                <div class="bg-light rounded p-3">
                    <div class="fw-bold fs-4" id="sum-total">–</div>
                    <div class="small text-muted">Total Elemen</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-success bg-opacity-10 rounded p-3">
                    <div class="fw-bold fs-4 text-success" id="sum-k">–</div>
                    <div class="small text-muted">Kompeten (K)</div>
                </div>
            </div>
            <div class="col-4">
                <div class="bg-danger bg-opacity-10 rounded p-3">
                    <div class="fw-bold fs-4 text-danger" id="sum-bk">–</div>
                    <div class="small text-muted">Blm Kompeten (BK)</div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            @include('partials._signature_pad', [
                'padId'    => 'asesi-apl02',
                'padLabel' => 'Tanda Tangan Pemohon',
                'padHeight' => 180,
                'savedSig' => auth()->user()->signature_image,
            ])
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="apldua-agree">
            <label class="form-check-label" for="apldua-agree">
                Saya menyatakan bahwa penilaian mandiri ini <strong>jujur dan sesuai</strong>
                dengan kondisi kompetensi yang saya miliki saat ini.
            </label>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('asesi.schedule') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <button type="button" class="btn btn-success px-4" id="btn-submit-apldua" onclick="submitApldua()">
                <i class="bi bi-check-circle me-1"></i>Submit APL-02
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const SAVE_URL   = '{{ route("asesi.apldua.save") }}';
const SUBMIT_URL = '{{ route("asesi.apldua.submit") }}';
const TOTAL_EL   = {{ $asesmen->skema->unitKompetensis->flatMap->elemens->count() }};

// ── State ──────────────────────────────────────────────────
// jawaban[elemen_id] = { jawaban: 'K'|'BK'|null, bukti: '' }
const jawaban = {};
@foreach($asesmen->skema->unitKompetensis as $unit)
@foreach($unit->elemens as $elemen)
@php $jaw = $jawabanMap[$elemen->id] ?? null; @endphp
jawaban[{{ $elemen->id }}] = @json([
    'jawaban' => $jaw->jawaban ?? null,
    'bukti'   => $jaw->bukti ?? '',
]);
@endforeach
@endforeach

// Per-unit metadata for progress updates
const unitElemenMap = {};
@foreach($asesmen->skema->unitKompetensis as $unit)
unitElemenMap[{{ $unit->id }}] = [
    @foreach($unit->elemens as $el)
    {{ $el->id }},
    @endforeach
];
@endforeach

window.addEventListener('DOMContentLoaded', () => {
    SigPadManager.init('asesi-apl02', @json(auth()->user()->signature_image));
    updateAllProgress();
});

// ── Toggle unit accordion ──────────────────────────────────
function toggleUnit(unitId) {
    const body  = document.getElementById(`unit-body-${unitId}`);
    const arrow = document.getElementById(`arrow-${unitId}`);
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (arrow) arrow.style.transform = open ? '' : 'rotate(90deg)';
}

// ── Set jawaban (K / BK) ───────────────────────────────────
function setJawaban(elemenId, val, btn) {
    const prev = jawaban[elemenId]?.jawaban;

    // Toggle off if clicking same button
    if (prev === val) {
        jawaban[elemenId].jawaban = null;
        document.querySelectorAll(`.jawaban-btn[data-elemen="${elemenId}"]`)
                .forEach(b => b.classList.remove('active'));
        updateRowStyle(elemenId, null);
    } else {
        jawaban[elemenId] = jawaban[elemenId] ?? {};
        jawaban[elemenId].jawaban = val;

        // Update button states
        document.querySelectorAll(`.jawaban-btn[data-elemen="${elemenId}"]`)
                .forEach(b => {
                    b.classList.remove('active');
                    if (b.classList.contains(val)) b.classList.add('active');
                });
        updateRowStyle(elemenId, val);
    }

    updateAllProgress();
    debouncedSave();
}

function onBuktiChange(elemenId, value) {
    jawaban[elemenId] = jawaban[elemenId] ?? {};
    jawaban[elemenId].bukti = value;
    debouncedSave();
}

function updateRowStyle(elemenId, val) {
    const row = document.getElementById(`row-${elemenId}`);
    if (!row) return;
    row.classList.remove('answered-K', 'answered-BK', 'unanswered');
    if (val === 'K')  row.classList.add('answered-K');
    else if (val === 'BK') row.classList.add('answered-BK');
    else row.classList.add('unanswered');
}

// ── Progress update ─────────────────────────────────────────
function updateAllProgress() {
    let answered = 0, countK = 0, countBK = 0;

    Object.values(jawaban).forEach(j => {
        if (j.jawaban) {
            answered++;
            if (j.jawaban === 'K')  countK++;
            if (j.jawaban === 'BK') countBK++;
        }
    });

    const pct = TOTAL_EL > 0 ? Math.round(answered / TOTAL_EL * 100) : 0;

    const bar   = document.getElementById('prog-bar');
    const label = document.getElementById('prog-label');
    if (bar)   bar.style.width = pct + '%';
    if (label) label.textContent = `${answered} / ${TOTAL_EL} elemen`;

    const cK  = document.getElementById('count-K');
    const cBK = document.getElementById('count-BK');
    if (cK)  cK.textContent  = countK;
    if (cBK) cBK.textContent = countBK;

    // Summary cards
    const sTotal = document.getElementById('sum-total');
    const sK     = document.getElementById('sum-k');
    const sBK    = document.getElementById('sum-bk');
    if (sTotal) sTotal.textContent = TOTAL_EL;
    if (sK)     sK.textContent     = countK;
    if (sBK)    sBK.textContent    = countBK;

    // Per-unit progress badges
    Object.entries(unitElemenMap).forEach(([unitId, elemenIds]) => {
        const ua = elemenIds.filter(id => jawaban[id]?.jawaban).length;
        const ut = elemenIds.length;
        const el = document.getElementById(`unit-prog-${unitId}`);
        if (el) el.textContent = `${ua}/${ut}`;

        // Update unit header badge
        const header = document.getElementById(`unit-card-${unitId}`)?.querySelector('.badge');
        if (header) {
            header.className = `badge ${ua === ut && ut > 0 ? 'bg-success' : 'bg-secondary'} me-1`;
        }
    });
}

// ── Auto-save (debounced) ───────────────────────────────────
let saveTimer = null;

function debouncedSave() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(doSave, 1200);
}

async function doSave() {
    const rows = Object.entries(jawaban).map(([elemenId, data]) => ({
        elemen_id: parseInt(elemenId),
        jawaban:   data.jawaban ?? null,
        bukti:     data.bukti ?? '',
    }));

    try {
        const res = await fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ rows }),
        });
        const data = await res.json();
        if (data.success) {
            showSaveIndicator();
        }
    } catch (e) {
        console.error('[APL02] save error:', e);
    }
}

function showSaveIndicator() {
    const el = document.getElementById('save-indicator');
    if (!el) return;
    el.style.opacity = '1';
    setTimeout(() => { el.style.opacity = '0'; }, 2000);
}

// ── Submit ─────────────────────────────────────────────────
async function submitApldua() {
    // Cek semua terisi
    const answered = Object.values(jawaban).filter(j => j.jawaban).length;
    if (answered < TOTAL_EL) {
        Object.entries(unitElemenMap).forEach(([unitId, elemenIds]) => {
            const adaUnanswered = elemenIds.some(id => !jawaban[id]?.jawaban);
            if (adaUnanswered) {
                const body  = document.getElementById(`unit-body-${unitId}`);
                const arrow = document.getElementById(`arrow-${unitId}`);
                if (body && body.style.display === 'none') {
                    body.style.display = 'block';
                    if (arrow) arrow.style.transform = 'rotate(90deg)';
                }
            }
        });

        const firstUnanswered = document.querySelector('.elemen-row.unanswered');
        if (firstUnanswered) firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        Swal.fire({
            icon: 'warning',
            title: 'Belum Semua Elemen Diisi',
            text: `Masih ada ${TOTAL_EL - answered} elemen yang belum dijawab. Isi semua elemen terlebih dahulu.`,
        });
        return;
    }

    // Cek apakah ada jawaban BK
    const countBKCheck = Object.values(jawaban).filter(j => j.jawaban === 'BK').length;
    if (countBKCheck > 0) {
        // Cari nama elemen yang BK untuk ditampilkan
        const bkElemen = [];
        document.querySelectorAll('.jawaban-btn.BK.active').forEach(btn => {
            const elemenId = btn.dataset.elemen;
            const row = document.getElementById(`row-${elemenId}`);
            const judulEl = row?.querySelector('.fw-semibold.small');
            if (judulEl) bkElemen.push(judulEl.textContent.trim());
        });

        const bkList = bkElemen.slice(0, 5).map(j => `<li>${j}</li>`).join('');
        const sisanya = bkElemen.length > 5 ? `<li><em>...dan ${bkElemen.length - 5} elemen lainnya</em></li>` : '';

    // Auto-expand unit yang mengandung BK
    Object.entries(unitElemenMap).forEach(([unitId, elemenIds]) => {
        const adaBK = elemenIds.some(id => jawaban[id]?.jawaban === 'BK');
        if (adaBK) {
            const body  = document.getElementById(`unit-body-${unitId}`);
            const arrow = document.getElementById(`arrow-${unitId}`);
            if (body && body.style.display === 'none') {
                body.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(90deg)';
            }
        }
    });

    // Scroll ke elemen BK pertama
    const firstBKRow = document.querySelector('.elemen-row.answered-BK');
    if (firstBKRow) firstBKRow.scrollIntoView({ behavior: 'smooth', block: 'center' });

        Swal.fire({
            icon: 'error',
            title: 'Terdapat Elemen Belum Kompeten',
            html: `
                <p class="mb-2">Anda memiliki <strong>${countBKCheck} elemen</strong> yang dinilai <span class="text-danger fw-bold">Belum Kompeten (BK)</span>.</p>
                <ul class="text-start small ps-3 mb-3" style="max-height:150px;overflow-y:auto;">
                    ${bkList}${sisanya}
                </ul>
                <div class="alert alert-warning py-2 mb-0 small text-start">
                    <i class="bi bi-info-circle me-1"></i>
                    Silakan tinjau kembali penilaian Anda.
                </div>`,
            confirmButtonText: 'Tinjau Kembali',
            confirmButtonColor: '#dc3545',
        });
        return;
    }

    // Cek agreement
    if (!document.getElementById('apldua-agree')?.checked) {
        Swal.fire({ icon: 'warning', title: 'Persetujuan Diperlukan', text: 'Centang pernyataan persetujuan terlebih dahulu.' });
        return;
    }

    // Cek TTD
    if (SigPadManager.isEmpty('asesi-apl02')) {
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Diperlukan', text: 'Tanda tangan di kotak yang tersedia.' });
        return;
    }

    const countK  = Object.values(jawaban).filter(j => j.jawaban === 'K').length;
    const countBK = Object.values(jawaban).filter(j => j.jawaban === 'BK').length;

    const confirm = await Swal.fire({
        title: 'Konfirmasi Submit APL-02',
        html: `
            <div class="text-start">
                <p class="mb-3">Ringkasan penilaian mandiri Anda:</p>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-success bg-opacity-10 rounded p-2 text-center">
                            <div class="fw-bold fs-5 text-success">${countK}</div>
                            <div class="small">Kompeten (K)</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-danger bg-opacity-10 rounded p-2 text-center">
                            <div class="fw-bold fs-5 text-danger">${countBK}</div>
                            <div class="small">Blm Kompeten (BK)</div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-danger py-2 mb-0 small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Setelah submit, jawaban tidak dapat diubah!</strong>
                </div>
            </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Ya, Submit',
        cancelButtonText: 'Periksa Ulang',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    });

    if (!confirm.isConfirmed) return;

    const btn = document.getElementById('btn-submit-apldua');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...'; }

    // Save dulu sebelum submit
    await doSave();

    try {
        const res = await fetch(SUBMIT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: (() => {
                const fd = new FormData();
                const signature = await SigPadManager.getSignatureImage('asesi-apl02');
                fd.append('signature', signature);
                return fd;
            })(),
        });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'APL-02 Berhasil Disubmit!',
                text: 'Formulir asesmen mandiri Anda telah terkirim.',
                confirmButtonText: 'OK',
            });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Submit APL-02'; }
        }
    } catch (e) {
        console.error('[APL02] submit error:', e);
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Submit APL-02'; }
    }
}
</script>
@endpush