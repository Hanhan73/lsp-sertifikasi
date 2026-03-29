@extends('layouts.app')
@section('title', 'Buat Jadwal Asesmen')
@section('page-title', 'Buat Jadwal Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
/* ── Design variables ──────────────────────────────────────── */
:root {
    --sched-primary:   #2563eb;
    --sched-success:   #16a34a;
    --sched-warning:   #d97706;
    --sched-danger:    #dc2626;
    --sched-muted:     #64748b;
    --sched-border:    #e2e8f0;
    --sched-surface:   #f8fafc;
    --sched-radius:    10px;
}

/* ── Step indicator ────────────────────────────────────────── */
.step-track {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 28px;
}
.step-item {
    display: flex;
    align-items: center;
    flex: 1;
    position: relative;
}
.step-item:last-child { flex: none; }
.step-dot {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .78rem; font-weight: 700;
    border: 2px solid var(--sched-border);
    background: #fff;
    color: var(--sched-muted);
    flex-shrink: 0;
    transition: all .25s;
    position: relative;
    z-index: 1;
}
.step-dot.active  { border-color: var(--sched-primary); background: var(--sched-primary); color: #fff; }
.step-dot.done    { border-color: var(--sched-success); background: var(--sched-success); color: #fff; }
.step-label {
    font-size: .75rem; font-weight: 600;
    color: var(--sched-muted);
    margin-left: 8px;
    white-space: nowrap;
}
.step-label.active { color: var(--sched-primary); }
.step-label.done   { color: var(--sched-success); }
.step-line {
    flex: 1;
    height: 2px;
    background: var(--sched-border);
    margin: 0 8px;
    position: relative;
}
.step-line.done { background: var(--sched-success); }

/* ── Section headings ──────────────────────────────────────── */
.section-heading {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--sched-muted);
    margin-bottom: 10px;
}

/* ── Asesi table ───────────────────────────────────────────── */
.asesi-row { cursor: pointer; transition: background .1s; }
.asesi-row:hover { background: #f0f7ff !important; }
.asesi-row.row-selected { background: #eff6ff; }
.asesi-row.row-disabled { opacity: .45; pointer-events: none; }

.asesi-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: #e0e7ff;
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; color: #4f46e5;
    flex-shrink: 0;
}

/* ── Selected chips ────────────────────────────────────────── */
#chip-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    min-height: 36px;
    padding: 6px;
    border: 1.5px dashed var(--sched-border);
    border-radius: 8px;
    background: var(--sched-surface);
    transition: border-color .2s;
}
#chip-container:has(.chip) { border-style: solid; border-color: #bfdbfe; background: #f0f7ff; }
.chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: #dbeafe; color: #1d4ed8;
    border-radius: 20px;
    padding: 3px 10px 3px 8px;
    font-size: .78rem; font-weight: 600;
    animation: chip-in .2s ease;
}
.chip-remove {
    cursor: pointer; opacity: .6; transition: opacity .15s;
    width: 14px; height: 14px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
}
.chip-remove:hover { opacity: 1; background: rgba(0,0,0,.1); }
@keyframes chip-in {
    from { transform: scale(.7); opacity: 0; }
    to   { transform: scale(1);  opacity: 1; }
}

/* ── Info cards (right panel) ──────────────────────────────── */
.info-band {
    display: flex; gap: 6px; align-items: flex-start;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: .82rem;
    background: var(--sched-surface);
    border: 1px solid var(--sched-border);
    margin-bottom: 8px;
    transition: border-color .2s, background .2s;
}
.info-band.filled { border-color: #bfdbfe; background: #eff6ff; }
.info-band-icon { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

/* ── Summary card ──────────────────────────────────────────── */
#summary-panel {
    border: 1.5px solid var(--sched-border);
    border-radius: var(--sched-radius);
    background: #fff;
    overflow: hidden;
    transition: all .3s;
}
#summary-panel.has-data { border-color: #bfdbfe; }

.summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 7px 14px;
    border-bottom: 1px solid #f1f5f9;
    font-size: .83rem;
}
.summary-row:last-child { border-bottom: none; }
.summary-row .s-label { color: var(--sched-muted); }
.summary-row .s-value { font-weight: 600; }

/* ── Asesor card ───────────────────────────────────────────── */
.asesor-option {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px;
    border: 1.5px solid var(--sched-border);
    border-radius: 8px;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    margin-bottom: 6px;
}
.asesor-option:hover { border-color: #93c5fd; background: #f0f7ff; }
.asesor-option.selected { border-color: var(--sched-primary); background: #eff6ff; }
.asesor-option input[type="radio"] { display: none; }
.asesor-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e0e7ff;
    flex-shrink: 0;
}
.asesor-avatar-placeholder {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5, #2563eb);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: .75rem; font-weight: 700;
    flex-shrink: 0;
}

/* ── Alert skema ───────────────────────────────────────────── */
#alert-skema {
    animation: slide-down .25s ease;
}
@keyframes slide-down {
    from { transform: translateY(-8px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

/* ── Empty state ───────────────────────────────────────────── */
.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: var(--sched-muted);
}
.empty-state i { font-size: 2.5rem; opacity: .25; display: block; margin-bottom: 8px; }

/* ── Count badge animate ───────────────────────────────────── */
.count-pulse {
    animation: pulse-badge .3s ease;
}
@keyframes pulse-badge {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.3); }
    100% { transform: scale(1); }
}

/* ── Form inputs ───────────────────────────────────────────── */
.form-control:focus, .form-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

/* ── Sticky panel ──────────────────────────────────────────── */
.sticky-panel {
    position: sticky;
    top: 72px;
}
</style>
@endpush

@section('content')

{{-- ── Breadcrumb ──────────────────────────────────────────── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Jadwal Asesmen</a></li>
        <li class="breadcrumb-item active">Buat Jadwal Baru</li>
    </ol>
</nav>

{{-- ── Step indicator ─────────────────────────────────────── --}}
<div class="step-track">
    <div class="step-item">
        <div class="step-dot active" id="dot-1">1</div>
        <span class="step-label active" id="lbl-1">Pilih Asesi</span>
    </div>
    <div class="step-line" id="line-1"></div>
    <div class="step-item">
        <div class="step-dot" id="dot-2">2</div>
        <span class="step-label" id="lbl-2">Atur Jadwal</span>
    </div>
    <div class="step-line" id="line-2"></div>
    <div class="step-item">
        <div class="step-dot" id="dot-3">3</div>
        <span class="step-label" id="lbl-3">Tugaskan Asesor</span>
    </div>
    <div class="step-line" id="line-3"></div>
    <div class="step-item">
        <div class="step-dot" id="dot-4">4</div>
        <span class="step-label" id="lbl-4">Konfirmasi</span>
    </div>
</div>

{{-- ── Error global ────────────────────────────────────────── --}}
@if($errors->any())
<div class="alert alert-danger shadow-sm mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Terdapat kesalahan:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger shadow-sm mb-4">
    <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
</div>
@endif

<form action="{{ route('admin.schedules.store') }}" method="POST" id="form-jadwal">
@csrf

<div class="row g-4">

    {{-- ══════════════════════════════════════════════════
         KOLOM KIRI — Pemilihan Asesi
    ══════════════════════════════════════════════════ --}}
    <div class="col-lg-7">

        {{-- ── Panel 1: Pilih Asesi ───────────────────── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:28px;height:28px;background:#eff6ff;">
                        <i class="bi bi-people-fill text-primary" style="font-size:.8rem;"></i>
                    </div>
                    <span class="fw-semibold">Pilih Asesi</span>
                </div>
                <span class="badge bg-primary ms-1" id="selected-count">0 dipilih</span>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <span class="small text-muted">
                        <strong id="total-available">{{ $availableAsesmens->count() }}</strong> tersedia
                    </span>
                </div>
            </div>

            <div class="card-body p-0">

                {{-- Search & filter bar --}}
                <div class="p-3 border-bottom d-flex gap-2">
                    <div class="input-group input-group-sm flex-grow-1">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="search-asesi"
                               placeholder="Cari nama, NIK, email, atau skema...">
                    </div>
                    <select class="form-select form-select-sm" id="filter-tuk" style="max-width:180px;">
                        <option value="">Semua TUK</option>
                        @foreach($tuks as $tuk)
                        <option value="{{ $tuk->id }}">{{ $tuk->name }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm" id="filter-skema" style="max-width:200px;">
                        <option value="">Semua Skema</option>
                        @foreach($skemas as $skema)
                        <option value="{{ $skema->id }}">{{ $skema->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Alert: skema berbeda --}}
                <div class="alert alert-warning alert-dismissible d-none mx-3 mt-3 mb-0 py-2" id="alert-skema">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Asesi yang dipilih harus memiliki <strong>skema yang sama</strong>.
                    Hapus salah satu asesi dari pilihan Anda.
                </div>

                {{-- Tabel asesi --}}
                <div style="max-height:380px; overflow-y:auto;" id="table-wrapper">
                    @if($availableAsesmens->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0 small">Tidak ada asesi yang siap dijadwalkan.</p>
                        <p class="small text-muted">Pastikan asesi sudah berstatus <code>asesmen_started</code> dan belum memiliki jadwal.</p>
                    </div>
                    @else
                    <table class="table table-hover align-middle mb-0" id="table-asesi">
                        <thead class="table-light" style="position:sticky;top:0;z-index:2;">
                            <tr>
                                <th width="44" class="text-center">
                                    <input type="checkbox" id="check-all" class="form-check-input" title="Pilih semua yang terlihat">
                                </th>
                                <th>Asesi</th>
                                <th>Skema</th>
                                <th class="text-end pe-3">TUK</th>
                            </tr>
                        </thead>
                        <tbody id="asesi-tbody">
                            @foreach($availableAsesmens as $asesmen)
                            <tr class="asesi-row {{ in_array($asesmen->id, old('asesmen_ids', [])) ? 'row-selected' : '' }}"
                                data-id="{{ $asesmen->id }}"
                                data-skema="{{ $asesmen->skema_id }}"
                                data-skema-name="{{ $asesmen->skema->name ?? '' }}"
                                data-tuk="{{ $asesmen->tuk_id }}"
                                data-tuk-name="{{ $asesmen->tuk->name ?? '' }}"
                                data-name="{{ $asesmen->full_name }}"
                                data-search="{{ strtolower($asesmen->full_name . ' ' . ($asesmen->nik ?? '') . ' ' . ($asesmen->user->email ?? '') . ' ' . ($asesmen->skema->name ?? '')) }}"
                                onclick="toggleRow(this)">
                                <td class="text-center" onclick="event.stopPropagation()">
                                    <input type="checkbox" name="asesmen_ids[]"
                                           value="{{ $asesmen->id }}"
                                           class="form-check-input asesi-check"
                                           data-skema="{{ $asesmen->skema_id }}"
                                           data-tuk="{{ $asesmen->tuk_id }}"
                                           data-skema-name="{{ $asesmen->skema->name ?? '' }}"
                                           data-name="{{ $asesmen->full_name }}"
                                           {{ in_array($asesmen->id, old('asesmen_ids', [])) ? 'checked' : '' }}
                                           onchange="updateState()">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="asesi-avatar">
                                            {{ strtoupper(substr($asesmen->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ $asesmen->full_name }}</div>
                                            <div class="text-muted" style="font-size:.75rem;">
                                                {{ $asesmen->user->email ?? '-' }}
                                                @if($asesmen->nik)
                                                &bull; <span class="font-monospace">{{ $asesmen->nik }}</span>
                                                @endif
                                            </div>
                                            @if($asesmen->is_collective)
                                            <span class="badge bg-primary" style="font-size:.6rem;">
                                                <i class="bi bi-people me-1"></i>Kolektif
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        {{ $asesmen->skema->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="small text-muted">{{ $asesmen->tuk->name ?? '-' }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="empty-state d-none" id="no-result-row">
                        <i class="bi bi-search"></i>
                        <p class="mb-0 small">Tidak ada hasil yang cocok.</p>
                    </div>
                    @endif
                </div>

                {{-- Footer: Selected chips --}}
                <div class="p-3 border-top">
                    <div class="section-heading mb-2">Asesi Terpilih</div>
                    <div id="chip-container">
                        <span class="small text-muted align-self-center ps-1" id="chip-placeholder">
                            Belum ada asesi yang dipilih
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════
         KOLOM KANAN — Detail Jadwal + Asesor + Summary
    ══════════════════════════════════════════════════ --}}
    <div class="col-lg-5">
        <div class="sticky-panel">

            {{-- ── Panel 2: Detail Jadwal ─────────────── --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:28px;height:28px;background:#f0fdf4;">
                        <i class="bi bi-calendar3 text-success" style="font-size:.8rem;"></i>
                    </div>
                    <span class="fw-semibold">Detail Jadwal</span>
                </div>
                <div class="card-body">

                    {{-- TUK --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            TUK Pelaksana <span class="text-danger">*</span>
                        </label>
                        <select name="tuk_id" id="input-tuk" class="form-select form-select-sm" required>
                            <option value="">— Pilih TUK —</option>
                            @foreach($tuks as $tuk)
                            <option value="{{ $tuk->id }}" {{ old('tuk_id') == $tuk->id ? 'selected' : '' }}>
                                {{ $tuk->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Tanggal Asesmen <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="assessment_date" id="input-date"
                               class="form-control form-control-sm @error('assessment_date') is-invalid @enderror"
                               value="{{ old('assessment_date') }}"
                               min="{{ date('Y-m-d') }}" required>
                        @error('assessment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Waktu --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">
                                Mulai <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="start_time" id="input-start"
                                   class="form-control form-control-sm @error('start_time') is-invalid @enderror"
                                   value="{{ old('start_time', '08:00') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">
                                Selesai <span class="text-danger">*</span>
                            </label>
                            <input type="time" name="end_time" id="input-end"
                                   class="form-control form-control-sm @error('end_time') is-invalid @enderror"
                                   value="{{ old('end_time', '16:00') }}" required>
                            @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Lokasi --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Lokasi / Ruang <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="location" id="input-location"
                               class="form-control form-control-sm @error('location') is-invalid @enderror"
                               value="{{ old('location') }}"
                               placeholder="cth: Aula Lt.2 / Zoom / Google Meet..."
                               required>
                        @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="notes" id="input-notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Informasi tambahan untuk peserta atau asesor...">{{ old('notes') }}</textarea>
                    </div>

                </div>
            </div>

            {{-- ── Panel 3: Tugaskan Asesor ────────────── --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:28px;height:28px;background:#fef9c3;">
                        <i class="bi bi-person-badge text-warning" style="font-size:.8rem;"></i>
                    </div>
                    <span class="fw-semibold">Tugaskan Asesor</span>
                    <span class="ms-auto badge bg-secondary" style="font-size:.65rem;">Opsional</span>
                </div>
                <div class="card-body">

                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Asesor bisa ditugaskan sekarang atau nanti dari halaman penugasan.
                    </p>

                    {{-- Search asesor --}}
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted" style="font-size:.75rem;"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0 form-control-sm"
                               id="search-asesor" placeholder="Cari nama atau no. registrasi...">
                    </div>

                    <div style="max-height:220px; overflow-y:auto; padding-right:2px;" id="asesor-list">
                        {{-- Opsi: tidak tugaskan --}}
                        <label class="asesor-option selected" id="asesor-opt-none" data-id="">
                            <input type="radio" name="asesor_id" value="" checked>
                            <div class="asesor-avatar-placeholder" style="background:linear-gradient(135deg,#94a3b8,#64748b);">
                                <i class="bi bi-dash" style="font-size:.9rem;"></i>
                            </div>
                            <div>
                                <div class="small fw-semibold">Tugaskan Nanti</div>
                                <div class="text-muted" style="font-size:.72rem;">Asesor belum dipilih</div>
                            </div>
                        </label>

                        @foreach(\App\Models\Asesor::where('is_active', true)->where('status_reg', 'aktif')->orderBy('nama')->get() as $asesor)
                        <label class="asesor-option {{ old('asesor_id') == $asesor->id ? 'selected' : '' }}"
                               id="asesor-opt-{{ $asesor->id }}"
                               data-id="{{ $asesor->id }}"
                               data-search="{{ strtolower($asesor->nama . ' ' . ($asesor->no_reg_met ?? '')) }}">
                            <input type="radio" name="asesor_id" value="{{ $asesor->id }}"
                                   {{ old('asesor_id') == $asesor->id ? 'checked' : '' }}>
                            @if($asesor->foto_url)
                            <img src="{{ $asesor->foto_url }}" class="asesor-avatar" alt="">
                            @else
                            <div class="asesor-avatar-placeholder">
                                {{ strtoupper(substr($asesor->nama, 0, 1)) }}
                            </div>
                            @endif
                            <div class="flex-grow-1 min-width-0">
                                <div class="small fw-semibold text-truncate">{{ $asesor->nama }}</div>
                                <div class="text-muted" style="font-size:.72rem;">
                                    {{ $asesor->no_reg_met ?? 'Tanpa no. registrasi' }}
                                    @if($asesor->email)
                                    &bull; {{ $asesor->email }}
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                </div>
            </div>

            {{-- ── Panel 4: Ringkasan & Submit ─────────── --}}
            <div class="card border-0 shadow-sm mb-3" id="summary-panel">
                <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:28px;height:28px;background:#fdf2f8;">
                        <i class="bi bi-clipboard-check text-danger" style="font-size:.8rem;"></i>
                    </div>
                    <span class="fw-semibold">Ringkasan Jadwal</span>
                </div>
                <div id="summary-body">
                    {{-- Diisi JS --}}
                    <div class="empty-state py-4" id="summary-empty">
                        <i class="bi bi-layout-text-sidebar-reverse" style="font-size:1.8rem;"></i>
                        <p class="small mb-0">Isi detail jadwal untuk melihat ringkasan.</p>
                    </div>
                    <div id="summary-rows" style="display:none;">
                        <div class="summary-row"><span class="s-label">Asesi</span><span class="s-value" id="s-asesi">—</span></div>
                        <div class="summary-row"><span class="s-label">Skema</span><span class="s-value" id="s-skema">—</span></div>
                        <div class="summary-row"><span class="s-label">TUK</span><span class="s-value" id="s-tuk">—</span></div>
                        <div class="summary-row"><span class="s-label">Tanggal</span><span class="s-value" id="s-date">—</span></div>
                        <div class="summary-row"><span class="s-label">Waktu</span><span class="s-value" id="s-time">—</span></div>
                        <div class="summary-row"><span class="s-label">Lokasi</span><span class="s-value" id="s-location">—</span></div>
                        <div class="summary-row"><span class="s-label">Asesor</span><span class="s-value" id="s-asesor">—</span></div>
                    </div>
                </div>
            </div>

            {{-- CTA Buttons --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" id="btn-submit" disabled>
                    <i class="bi bi-calendar-plus me-1"></i>
                    <span id="btn-submit-text">Buat Jadwal</span>
                </button>
                <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>

            {{-- Hint --}}
            <div class="text-center mt-3" id="btn-hint" style="font-size:.75rem; color:var(--sched-muted); display:none!important;">
                <i class="bi bi-check2-circle text-success me-1"></i>
                Semua data sudah lengkap. Jadwal siap dibuat.
            </div>

        </div>{{-- end sticky-panel --}}
    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════════
//  State
// ══════════════════════════════════════════════════════════════
const state = {
    selected: new Map(),   // id => { name, skemaId, skemaName, tukId }
    firstSkemaId: null,
};

// ══════════════════════════════════════════════════════════════
//  Toggle row click
// ══════════════════════════════════════════════════════════════
function toggleRow(tr) {
    const cb = tr.querySelector('.asesi-check');
    cb.checked = !cb.checked;
    updateState();
}

// ══════════════════════════════════════════════════════════════
//  Master update state
// ══════════════════════════════════════════════════════════════
function updateState() {
    // Collect all checked
    state.selected.clear();
    document.querySelectorAll('.asesi-check:checked').forEach(cb => {
        state.selected.set(cb.value, {
            name:      cb.dataset.name,
            skemaId:   cb.dataset.skema,
            skemaName: cb.dataset.skemaName,
            tukId:     cb.dataset.tuk,
        });
    });

    // Sync row highlight
    document.querySelectorAll('.asesi-row').forEach(tr => {
        const id = tr.dataset.id;
        tr.classList.toggle('row-selected', state.selected.has(id));
    });

    const count      = state.selected.size;
    const skemaIds   = [...new Set([...state.selected.values()].map(v => v.skemaId))];
    const hasConflict = skemaIds.length > 1;

    // Count badge
    const badge = document.getElementById('selected-count');
    badge.textContent = count + ' dipilih';
    badge.classList.remove('count-pulse');
    void badge.offsetWidth;
    badge.classList.add('count-pulse');
    badge.className = badge.className.replace(/bg-\w+/g, '') + (count > 0 ? ' bg-primary' : ' bg-secondary');

    // Alert skema
    document.getElementById('alert-skema').classList.toggle('d-none', !hasConflict);

    // Chips
    renderChips();

    // Disable rows with different skema once one is locked
    if (count === 1 && !hasConflict) {
        state.firstSkemaId = skemaIds[0];
    }
    document.querySelectorAll('.asesi-row').forEach(tr => {
        const isChecked = state.selected.has(tr.dataset.id);
        const conflicts  = count > 0 && !hasConflict && tr.dataset.skema !== state.firstSkemaId;
        tr.classList.toggle('row-disabled', !isChecked && conflicts);
    });
    if (count === 0) {
        state.firstSkemaId = null;
        document.querySelectorAll('.asesi-row').forEach(tr => tr.classList.remove('row-disabled'));
    }

    // Submit button
    const formFilled = checkFormFilled();
    const canSubmit  = count > 0 && !hasConflict && formFilled;
    document.getElementById('btn-submit').disabled = !canSubmit;

    // Button text
    document.getElementById('btn-submit-text').textContent =
        count > 0 ? `Buat Jadwal (${count} Asesi)` : 'Buat Jadwal';

    // Steps
    updateSteps(count, !hasConflict, formFilled);

    // Summary
    updateSummary(skemaIds, hasConflict);
}

// ══════════════════════════════════════════════════════════════
//  Render chips
// ══════════════════════════════════════════════════════════════
function renderChips() {
    const container   = document.getElementById('chip-container');
    const placeholder = document.getElementById('chip-placeholder');
    // Remove old chips
    container.querySelectorAll('.chip').forEach(c => c.remove());

    if (state.selected.size === 0) {
        placeholder.style.display = 'inline';
        return;
    }
    placeholder.style.display = 'none';

    state.selected.forEach((data, id) => {
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.innerHTML = `
            <i class="bi bi-person-fill" style="font-size:.7rem;"></i>
            ${escHtml(data.name)}
            <span class="chip-remove" onclick="removeChip('${id}')">
                <i class="bi bi-x" style="font-size:.75rem;"></i>
            </span>`;
        container.appendChild(chip);
    });
}

function removeChip(id) {
    const cb = document.querySelector(`.asesi-check[value="${id}"]`);
    if (cb) { cb.checked = false; }
    updateState();
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// ══════════════════════════════════════════════════════════════
//  Step indicator
// ══════════════════════════════════════════════════════════════
function updateSteps(count, skemaOk, formFilled) {
    const step1 = count > 0 && skemaOk;
    const step2 = formFilled;
    const step3 = document.querySelector('input[name="asesor_id"]:checked')?.value !== '';
    const step4 = step1 && step2;

    setStep(1, step1 ? 'done' : 'active', step1);
    setStep(2, step2 ? 'done' : (step1 ? 'active' : 'pending'), step1);
    setStep(3, step3 ? 'done' : (step2 ? 'active' : 'pending'), step2);
    setStep(4, step4 ? 'active' : 'pending', step4);

    setLine(1, step1);
    setLine(2, step2);
    setLine(3, step3);
}

function setStep(n, state, isReachable) {
    const dot = document.getElementById(`dot-${n}`);
    const lbl = document.getElementById(`lbl-${n}`);
    dot.className = 'step-dot';
    lbl.className = 'step-label';
    if (state === 'done') {
        dot.classList.add('done');
        lbl.classList.add('done');
        dot.innerHTML = '<i class="bi bi-check2" style="font-size:.75rem;"></i>';
    } else if (state === 'active') {
        dot.classList.add('active');
        lbl.classList.add('active');
        dot.textContent = n;
    } else {
        dot.textContent = n;
    }
}

function setLine(n, done) {
    const line = document.getElementById(`line-${n}`);
    if (line) line.classList.toggle('done', done);
}

// ══════════════════════════════════════════════════════════════
//  Check form filled
// ══════════════════════════════════════════════════════════════
function checkFormFilled() {
    const tuk      = document.getElementById('input-tuk').value;
    const date     = document.getElementById('input-date').value;
    const start    = document.getElementById('input-start').value;
    const end      = document.getElementById('input-end').value;
    const location = document.getElementById('input-location').value.trim();
    return !!(tuk && date && start && end && location);
}

// ══════════════════════════════════════════════════════════════
//  Summary panel
// ══════════════════════════════════════════════════════════════
function updateSummary(skemaIds, hasConflict) {
    const tukEl     = document.getElementById('input-tuk');
    const dateEl    = document.getElementById('input-date');
    const startEl   = document.getElementById('input-start');
    const endEl     = document.getElementById('input-end');
    const locEl     = document.getElementById('input-location');
    const asesorEl  = document.querySelector('input[name="asesor_id"]:checked');
    const asesorLbl = asesorEl?.closest('.asesor-option')?.querySelector('.fw-semibold')?.textContent ?? '—';

    const hasData = state.selected.size > 0 || dateEl.value || locEl.value.trim();

    document.getElementById('summary-empty').style.display = hasData ? 'none' : '';
    document.getElementById('summary-rows').style.display  = hasData ? '' : 'none';
    document.getElementById('summary-panel').classList.toggle('has-data', hasData);

    const count = state.selected.size;
    const skemaName = !hasConflict && count > 0
        ? ([...state.selected.values()][0]?.skemaName ?? '—')
        : (hasConflict ? '⚠ Skema berbeda!' : '—');

    document.getElementById('s-asesi').textContent    = count > 0 ? `${count} orang` : '—';
    document.getElementById('s-skema').textContent    = skemaName;
    document.getElementById('s-tuk').textContent      = tukEl.options[tukEl.selectedIndex]?.text ?? '—';
    document.getElementById('s-date').textContent     = dateEl.value ? formatDate(dateEl.value) : '—';
    document.getElementById('s-time').textContent     = startEl.value && endEl.value
        ? `${startEl.value} – ${endEl.value}` : '—';
    document.getElementById('s-location').textContent = locEl.value.trim() || '—';
    document.getElementById('s-asesor').textContent   = asesorLbl;
}

function formatDate(ymd) {
    if (!ymd) return '—';
    const d = new Date(ymd + 'T00:00:00');
    return d.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
}

// ══════════════════════════════════════════════════════════════
//  Search & filter asesi
// ══════════════════════════════════════════════════════════════
function filterTable() {
    const q       = document.getElementById('search-asesi').value.toLowerCase();
    const tukF    = document.getElementById('filter-tuk').value;
    const skemaF  = document.getElementById('filter-skema').value;
    let visible   = 0;

    document.querySelectorAll('#asesi-tbody tr.asesi-row').forEach(tr => {
        const matchSearch = !q || tr.dataset.search.includes(q);
        const matchTuk    = !tukF  || tr.dataset.tuk   === tukF;
        const matchSkema  = !skemaF || tr.dataset.skema === skemaF;
        const show = matchSearch && matchTuk && matchSkema;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    document.getElementById('no-result-row')?.classList.toggle('d-none', visible > 0);
}

// ══════════════════════════════════════════════════════════════
//  Search asesor
// ══════════════════════════════════════════════════════════════
document.getElementById('search-asesor')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#asesor-list .asesor-option[data-id]').forEach(opt => {
        if (!opt.dataset.id) return; // skip "none"
        opt.style.display = !q || opt.dataset.search.includes(q) ? '' : 'none';
    });
});

// ══════════════════════════════════════════════════════════════
//  Asesor option click
// ══════════════════════════════════════════════════════════════
document.querySelectorAll('.asesor-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.asesor-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
        updateState();
    });
});

// ══════════════════════════════════════════════════════════════
//  Check all
// ══════════════════════════════════════════════════════════════
document.getElementById('check-all')?.addEventListener('change', function() {
    document.querySelectorAll('#asesi-tbody tr.asesi-row:not([style*="display: none"])').forEach(tr => {
        const cb = tr.querySelector('.asesi-check');
        if (!tr.classList.contains('row-disabled') && cb) {
            cb.checked = this.checked;
        }
    });
    updateState();
});

// ══════════════════════════════════════════════════════════════
//  Live update summary on form change
// ══════════════════════════════════════════════════════════════
['input-tuk','input-date','input-start','input-end','input-location'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', updateState);
    document.getElementById(id)?.addEventListener('input', updateState);
});

// ══════════════════════════════════════════════════════════════
//  Search/filter event listeners
// ══════════════════════════════════════════════════════════════
document.getElementById('search-asesi')?.addEventListener('input', filterTable);
document.getElementById('filter-tuk')?.addEventListener('change', filterTable);
document.getElementById('filter-skema')?.addEventListener('change', filterTable);

// ══════════════════════════════════════════════════════════════
//  Form submit: confirm
// ══════════════════════════════════════════════════════════════
document.getElementById('form-jadwal')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const count   = state.selected.size;
    const dateEl  = document.getElementById('input-date');
    const locEl   = document.getElementById('input-location');
    const asesorEl= document.querySelector('input[name="asesor_id"]:checked');
    const asesorName = asesorEl?.closest('.asesor-option')?.querySelector('.fw-semibold')?.textContent ?? 'Belum ditugaskan';

    const result = await Swal.fire({
        title: 'Konfirmasi Buat Jadwal',
        html: `
            <div class="text-start small">
                <div class="mb-3 p-3 rounded bg-light">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Jumlah Asesi</span>
                        <strong>${count} orang</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Tanggal</span>
                        <strong>${formatDate(dateEl.value)}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Lokasi</span>
                        <strong>${escHtml(locEl.value.trim())}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Asesor</span>
                        <strong>${escHtml(asesorName)}</strong>
                    </div>
                </div>
                <div class="alert alert-info py-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Status asesi akan berubah menjadi <strong>Terjadwal</strong> setelah disimpan.
                </div>
            </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-calendar-plus me-1"></i>Ya, Buat Jadwal',
        cancelButtonText: 'Periksa Ulang',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    // Loading state
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';

    this.submit();
});

// ══════════════════════════════════════════════════════════════
//  Init
// ══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    updateState();

    // Pre-select from old() input
    @if(old('asesmen_ids'))
    document.querySelectorAll('.asesi-check').forEach(cb => {
        if ({{ json_encode(old('asesmen_ids', [])) }}.includes(cb.value)) {
            cb.checked = true;
        }
    });
    updateState();
    @endif
});
</script>
@endpush