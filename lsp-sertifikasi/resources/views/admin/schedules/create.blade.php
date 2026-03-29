@extends('layouts.app')
@section('title', 'Buat Jadwal Asesmen')
@section('page-title', 'Buat Jadwal Asesmen')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
:root {
    --sched-primary:  #2563eb;
    --sched-success:  #16a34a;
    --sched-warning:  #d97706;
    --sched-danger:   #dc2626;
    --sched-muted:    #64748b;
    --sched-border:   #e2e8f0;
    --sched-surface:  #f8fafc;
    --sched-radius:   10px;
}
.step-track { display:flex; align-items:center; margin-bottom:28px; }
.step-item  { display:flex; align-items:center; flex:1; }
.step-item:last-child { flex:none; }
.step-dot {
    width:32px; height:32px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:.78rem; font-weight:700;
    border:2px solid var(--sched-border); background:#fff; color:var(--sched-muted);
    flex-shrink:0; transition:all .25s; position:relative; z-index:1;
}
.step-dot.active { border-color:var(--sched-primary); background:var(--sched-primary); color:#fff; }
.step-dot.done   { border-color:var(--sched-success);  background:var(--sched-success);  color:#fff; }
.step-label { font-size:.75rem; font-weight:600; color:var(--sched-muted); margin-left:8px; white-space:nowrap; }
.step-label.active { color:var(--sched-primary); }
.step-label.done   { color:var(--sched-success); }
.step-line { flex:1; height:2px; background:var(--sched-border); margin:0 8px; }
.step-line.done { background:var(--sched-success); }

.section-heading { font-size:.72rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:var(--sched-muted); margin-bottom:10px; }

.asesi-row { cursor:pointer; transition:background .1s; }
.asesi-row:hover { background:#f0f7ff !important; }
.asesi-row.row-selected { background:#eff6ff; }
.asesi-row.row-disabled { opacity:.45; pointer-events:none; }
.asesi-avatar { width:34px; height:34px; border-radius:50%; background:#e0e7ff; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; color:#4f46e5; flex-shrink:0; }

#chip-container { display:flex; flex-wrap:wrap; gap:6px; min-height:36px; padding:6px; border:1.5px dashed var(--sched-border); border-radius:8px; background:var(--sched-surface); transition:border-color .2s; }
#chip-container:has(.chip) { border-style:solid; border-color:#bfdbfe; background:#f0f7ff; }
.chip { display:inline-flex; align-items:center; gap:5px; background:#dbeafe; color:#1d4ed8; border-radius:20px; padding:3px 10px 3px 8px; font-size:.78rem; font-weight:600; animation:chip-in .2s ease; }
.chip-remove { cursor:pointer; opacity:.6; transition:opacity .15s; width:14px; height:14px; display:flex; align-items:center; justify-content:center; border-radius:50%; }
.chip-remove:hover { opacity:1; background:rgba(0,0,0,.1); }
@keyframes chip-in { from{transform:scale(.7);opacity:0} to{transform:scale(1);opacity:1} }

#summary-panel { border:1.5px solid var(--sched-border); border-radius:var(--sched-radius); background:#fff; overflow:hidden; transition:all .3s; }
#summary-panel.has-data { border-color:#bfdbfe; }
.summary-row { display:flex; justify-content:space-between; align-items:center; padding:7px 14px; border-bottom:1px solid #f1f5f9; font-size:.83rem; }
.summary-row:last-child { border-bottom:none; }
.summary-row .s-label { color:var(--sched-muted); }
.summary-row .s-value { font-weight:600; }

.asesor-option { display:flex; align-items:center; gap:10px; padding:8px 10px; border:1.5px solid var(--sched-border); border-radius:8px; cursor:pointer; transition:border-color .15s, background .15s; margin-bottom:6px; }
.asesor-option:hover  { border-color:#93c5fd; background:#f0f7ff; }
.asesor-option.selected { border-color:var(--sched-primary); background:#eff6ff; }
.asesor-option input[type="radio"] { display:none; }
.asesor-avatar    { width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #e0e7ff; flex-shrink:0; }
.asesor-avatar-placeholder { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#4f46e5,#2563eb); display:flex; align-items:center; justify-content:center; color:#fff; font-size:.75rem; font-weight:700; flex-shrink:0; }

.empty-state { padding:40px 20px; text-align:center; color:var(--sched-muted); }
.empty-state i { font-size:2.5rem; opacity:.25; display:block; margin-bottom:8px; }
.count-pulse { animation:pulse-badge .3s ease; }
@keyframes pulse-badge { 0%{transform:scale(1)} 50%{transform:scale(1.3)} 100%{transform:scale(1)} }
.form-control:focus, .form-select:focus { border-color:#93c5fd; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
.sticky-panel { position:sticky; top:72px; }

/* ── Location type cards ── */
.loc-type-card { user-select:none; }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Jadwal Asesmen</a></li>
        <li class="breadcrumb-item active">Buat Jadwal Baru</li>
    </ol>
</nav>

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

@if($errors->any())
<div class="alert alert-danger shadow-sm mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Terdapat kesalahan:</strong>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger shadow-sm mb-4"><i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}</div>
@endif

<form action="{{ route('admin.schedules.store') }}" method="POST" id="form-jadwal">
@csrf

<div class="row g-4">

{{-- ══ KIRI — Pilih Asesi ══ --}}
<div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#eff6ff;">
                    <i class="bi bi-people-fill text-primary" style="font-size:.8rem;"></i>
                </div>
                <span class="fw-semibold">Pilih Asesi</span>
            </div>
            <span class="badge bg-primary ms-1" id="selected-count">0 dipilih</span>
            <div class="ms-auto"><span class="small text-muted"><strong id="total-available">{{ $availableAsesmens->count() }}</strong> tersedia</span></div>
        </div>
        <div class="card-body p-0">
            <div class="p-3 border-bottom d-flex gap-2">
                <div class="input-group input-group-sm flex-grow-1">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="search-asesi" placeholder="Cari nama, NIK, email, atau skema...">
                </div>
                <select class="form-select form-select-sm" id="filter-tuk" style="max-width:180px;">
                    <option value="">Semua TUK</option>
                    @foreach($tuks as $tuk)<option value="{{ $tuk->id }}">{{ $tuk->name }}</option>@endforeach
                </select>
                <select class="form-select form-select-sm" id="filter-skema" style="max-width:200px;">
                    <option value="">Semua Skema</option>
                    @foreach($skemas as $skema)<option value="{{ $skema->id }}">{{ $skema->name }}</option>@endforeach
                </select>
            </div>

            <div class="alert alert-warning alert-dismissible d-none mx-3 mt-3 mb-0 py-2" id="alert-skema">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Asesi yang dipilih harus memiliki <strong>skema yang sama</strong>.
            </div>

            <div style="max-height:380px; overflow-y:auto;" id="table-wrapper">
                @if($availableAsesmens->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0 small">Tidak ada asesi yang siap dijadwalkan.</p>
                    <p class="small text-muted">Pastikan asesi sudah memenuhi kriteria dan belum memiliki jadwal.</p>
                </div>
                @else
                <table class="table table-hover align-middle mb-0" id="table-asesi">
                    <thead class="table-light" style="position:sticky;top:0;z-index:2;">
                        <tr>
                            <th width="44" class="text-center">
                                <input type="checkbox" id="check-all" class="form-check-input" title="Pilih semua">
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
                                <input type="checkbox" name="asesmen_ids[]" value="{{ $asesmen->id }}"
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
                                    <div class="asesi-avatar">{{ strtoupper(substr($asesmen->full_name, 0, 1)) }}</div>
                                    <div>
                                        <div class="fw-semibold small">{{ $asesmen->full_name }}</div>
                                        <div class="text-muted" style="font-size:.75rem;">
                                            {{ $asesmen->user->email ?? '-' }}
                                            @if($asesmen->nik) &bull; <span class="font-monospace">{{ $asesmen->nik }}</span>@endif
                                        </div>
                                        @if($asesmen->is_collective)
                                        <span class="badge bg-primary" style="font-size:.6rem;"><i class="bi bi-people me-1"></i>Kolektif</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border small">{{ $asesmen->skema->name ?? '-' }}</span></td>
                            <td class="text-end pe-3"><span class="small text-muted">{{ $asesmen->tuk->name ?? '-' }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="empty-state d-none" id="no-result-row">
                    <i class="bi bi-search"></i><p class="mb-0 small">Tidak ada hasil yang cocok.</p>
                </div>
                @endif
            </div>

            <div class="p-3 border-top">
                <div class="section-heading mb-2">Asesi Terpilih</div>
                <div id="chip-container">
                    <span class="small text-muted align-self-center ps-1" id="chip-placeholder">Belum ada asesi yang dipilih</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ KANAN — Detail Jadwal + Asesor + Summary ══ --}}
<div class="col-lg-5">
<div class="sticky-panel">

{{-- Panel 2: Detail Jadwal --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#f0fdf4;">
            <i class="bi bi-calendar3 text-success" style="font-size:.8rem;"></i>
        </div>
        <span class="fw-semibold">Detail Jadwal</span>
    </div>
    <div class="card-body">

        {{-- TUK --}}
        <div class="mb-3">
            <label class="form-label small fw-semibold">TUK Pelaksana <span class="text-danger">*</span></label>
            <select name="tuk_id" id="input-tuk" class="form-select form-select-sm" required>
                <option value="">— Pilih TUK —</option>
                @foreach($tuks as $tuk)
                <option value="{{ $tuk->id }}" {{ old('tuk_id') == $tuk->id ? 'selected' : '' }}>{{ $tuk->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tanggal --}}
        <div class="mb-3">
            <label class="form-label small fw-semibold">Tanggal Asesmen <span class="text-danger">*</span></label>
            <input type="date" name="assessment_date" id="input-date"
                class="form-control form-control-sm @error('assessment_date') is-invalid @enderror"
                value="{{ old('assessment_date') }}" min="{{ date('Y-m-d') }}" required>
            @error('assessment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Waktu --}}
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label small fw-semibold">Mulai <span class="text-danger">*</span></label>
                <input type="time" name="start_time" id="input-start"
                    class="form-control form-control-sm @error('start_time') is-invalid @enderror"
                    value="{{ old('start_time', '08:00') }}" required>
            </div>
            <div class="col-6">
                <label class="form-label small fw-semibold">Selesai <span class="text-danger">*</span></label>
                <input type="time" name="end_time" id="input-end"
                    class="form-control form-control-sm @error('end_time') is-invalid @enderror"
                    value="{{ old('end_time', '16:00') }}" required>
                @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        {{-- ── Jenis Pelaksanaan ── --}}
        @php
            $oldType = old('location_type', 'offline');
            $oldLoc  = old('location', '');
            $oldLink = old('meeting_link', '');
        @endphp
        <div class="mb-3">
            <label class="form-label small fw-semibold">Jenis Pelaksanaan <span class="text-danger">*</span></label>
            <div class="d-flex gap-2">
                <label class="loc-type-card flex-fill" id="card-offline" style="cursor:pointer;">
                    <input type="radio" name="location_type" value="offline" class="d-none loc-type-radio"
                        {{ $oldType === 'offline' ? 'checked' : '' }} onchange="handleLocationType()">
                    <div class="d-flex align-items-center gap-2 p-3 rounded border" id="card-offline-inner" style="transition:all .18s;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;background:#f1f5f9;" id="icon-offline-wrap">
                            <i class="bi bi-building" style="color:#94a3b8;font-size:.95rem;" id="icon-offline"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small" id="label-offline" style="color:#334155;">Offline</div>
                            <div class="text-muted" style="font-size:.7rem;">Tatap muka</div>
                        </div>
                    </div>
                </label>
                <label class="loc-type-card flex-fill" id="card-online" style="cursor:pointer;">
                    <input type="radio" name="location_type" value="online" class="d-none loc-type-radio"
                        {{ $oldType === 'online' ? 'checked' : '' }} onchange="handleLocationType()">
                    <div class="d-flex align-items-center gap-2 p-3 rounded border" id="card-online-inner" style="transition:all .18s;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;background:#f1f5f9;" id="icon-online-wrap">
                            <i class="bi bi-camera-video" style="color:#94a3b8;font-size:.95rem;" id="icon-online"></i>
                        </div>
                        <div>
                            <div class="fw-semibold small" id="label-online" style="color:#334155;">Online</div>
                            <div class="text-muted" style="font-size:.7rem;">Via Zoom / Meet</div>
                        </div>
                    </div>
                </label>
            </div>
            @error('location_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- ── Nama Lokasi / Platform ── --}}
        <div class="mb-3">
            <label class="form-label small fw-semibold">
                <span id="loc-label-text">{{ $oldType === 'online' ? 'Nama Platform / Keterangan' : 'Gedung / Ruangan' }}</span>
                <span class="text-danger">*</span>
            </label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white">
                    <i class="bi {{ $oldType === 'online' ? 'bi-camera-video' : 'bi-geo-alt' }} text-muted" id="loc-icon"></i>
                </span>
                <input type="text" name="location" id="input-location"
                    class="form-control @error('location') is-invalid @enderror"
                    value="{{ $oldLoc }}"
                    placeholder="{{ $oldType === 'online' ? 'cth: Zoom Meeting, Google Meet...' : 'cth: Aula Lantai 2, Gedung A Ruang 301...' }}"
                    required>
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-text text-muted small" id="loc-hint">
                {{ $oldType === 'online' ? 'Isi nama platform atau keterangan singkat.' : 'Isi nama gedung, ruangan, atau alamat tempat asesmen.' }}
            </div>
        </div>

        {{-- ── Meeting Link (hanya online) ── --}}
        <div class="mb-3" id="meeting-link-wrap" style="display:{{ $oldType === 'online' ? 'block' : 'none' }};">
            <label class="form-label small fw-semibold">Link Meeting <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white">
                    <i class="bi bi-link-45deg" id="meeting-link-icon"></i>
                </span>
                <input type="url" name="meeting_link" id="input-meeting-link"
                    class="form-control @error('meeting_link') is-invalid @enderror"
                    value="{{ $oldLink }}"
                    placeholder="https://zoom.us/j/... atau https://meet.google.com/..."
                    oninput="detectPlatformIcon(this.value)">
                <a id="btn-test-link" class="input-group-text bg-white text-primary"
                    style="display:{{ $oldLink ? 'flex' : 'none' }}; text-decoration:none;"
                    title="Buka link di tab baru" target="_blank"
                    href="{{ $oldLink ?: '#' }}">
                    <i class="bi bi-box-arrow-up-right" style="font-size:.8rem;"></i>
                </a>
                @error('meeting_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-text small" style="color:#64748b;">Paste link Zoom, Google Meet, MS Teams, atau platform lainnya.</div>
            <div id="platform-badge" class="mt-2" style="display:none;">
                <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:.72rem;">
                    <i class="bi bi-check-circle me-1"></i><span id="platform-name"></span> terdeteksi
                </span>
            </div>
        </div>

        {{-- Catatan --}}
        <div class="mb-0">
            <label class="form-label small fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
            <textarea name="notes" id="input-notes" class="form-control form-control-sm" rows="2"
                placeholder="Informasi tambahan untuk peserta atau asesor...">{{ old('notes') }}</textarea>
        </div>

    </div>
</div>

{{-- Panel 3: Tugaskan Asesor --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#fef9c3;">
            <i class="bi bi-person-badge text-warning" style="font-size:.8rem;"></i>
        </div>
        <span class="fw-semibold">Tugaskan Asesor</span>
        <span class="ms-auto badge bg-secondary" style="font-size:.65rem;">Opsional</span>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>Asesor bisa ditugaskan sekarang atau nanti dari halaman penugasan.</p>
        <div class="input-group input-group-sm mb-3">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted" style="font-size:.75rem;"></i></span>
            <input type="text" class="form-control border-start-0 ps-0 form-control-sm" id="search-asesor" placeholder="Cari nama atau no. registrasi...">
        </div>
        <div style="max-height:220px; overflow-y:auto; padding-right:2px;" id="asesor-list">
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
                id="asesor-opt-{{ $asesor->id }}" data-id="{{ $asesor->id }}"
                data-search="{{ strtolower($asesor->nama . ' ' . ($asesor->no_reg_met ?? '')) }}">
                <input type="radio" name="asesor_id" value="{{ $asesor->id }}" {{ old('asesor_id') == $asesor->id ? 'checked' : '' }}>
                @if($asesor->foto_url)
                <img src="{{ $asesor->foto_url }}" class="asesor-avatar" alt="">
                @else
                <div class="asesor-avatar-placeholder">{{ strtoupper(substr($asesor->nama, 0, 1)) }}</div>
                @endif
                <div class="flex-grow-1 min-width-0">
                    <div class="small fw-semibold text-truncate">{{ $asesor->nama }}</div>
                    <div class="text-muted" style="font-size:.72rem;">
                        {{ $asesor->no_reg_met ?? 'Tanpa no. registrasi' }}
                        @if($asesor->email) &bull; {{ $asesor->email }}@endif
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    </div>
</div>

{{-- Panel 4: Ringkasan & Submit --}}
<div class="card border-0 shadow-sm mb-3" id="summary-panel">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;background:#fdf2f8;">
            <i class="bi bi-clipboard-check text-danger" style="font-size:.8rem;"></i>
        </div>
        <span class="fw-semibold">Ringkasan Jadwal</span>
    </div>
    <div id="summary-body">
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
            <div class="summary-row"><span class="s-label">Jenis</span><span class="s-value" id="s-loc-type">—</span></div>
            <div class="summary-row"><span class="s-label">Lokasi</span><span class="s-value" id="s-location">—</span></div>
            <div class="summary-row"><span class="s-label">Asesor</span><span class="s-value" id="s-asesor">—</span></div>
        </div>
    </div>
</div>

<div class="d-grid gap-2">
    <button type="submit" class="btn btn-primary" id="btn-submit" disabled>
        <i class="bi bi-calendar-plus me-1"></i>
        <span id="btn-submit-text">Buat Jadwal</span>
    </button>
    <a href="{{ route('admin.schedules.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

</div>{{-- end sticky-panel --}}
</div>

</div>
</form>
@endsection

@push('scripts')
<script>
// ── State ────────────────────────────────────────────────────
const state = { selected: new Map(), firstSkemaId: null };

// ── Toggle row ───────────────────────────────────────────────
function toggleRow(tr) {
    const cb = tr.querySelector('.asesi-check');
    cb.checked = !cb.checked;
    updateState();
}

// ── Master update ─────────────────────────────────────────────
function updateState() {
    state.selected.clear();
    document.querySelectorAll('.asesi-check:checked').forEach(cb => {
        state.selected.set(cb.value, { name: cb.dataset.name, skemaId: cb.dataset.skema, skemaName: cb.dataset.skemaName, tukId: cb.dataset.tuk });
    });

    document.querySelectorAll('.asesi-row').forEach(tr => {
        tr.classList.toggle('row-selected', state.selected.has(tr.dataset.id));
    });

    const count    = state.selected.size;
    const skemaIds = [...new Set([...state.selected.values()].map(v => v.skemaId))];
    const hasConflict = skemaIds.length > 1;

    const badge = document.getElementById('selected-count');
    badge.textContent = count + ' dipilih';
    badge.classList.remove('count-pulse');
    void badge.offsetWidth;
    badge.classList.add('count-pulse');
    badge.className = badge.className.replace(/bg-\w+/g, '') + (count > 0 ? ' bg-primary' : ' bg-secondary');

    document.getElementById('alert-skema').classList.toggle('d-none', !hasConflict);
    renderChips();

    if (count === 1 && !hasConflict) state.firstSkemaId = skemaIds[0];
    document.querySelectorAll('.asesi-row').forEach(tr => {
        const isChecked = state.selected.has(tr.dataset.id);
        const conflicts = count > 0 && !hasConflict && tr.dataset.skema !== state.firstSkemaId;
        tr.classList.toggle('row-disabled', !isChecked && conflicts);
    });
    if (count === 0) {
        state.firstSkemaId = null;
        document.querySelectorAll('.asesi-row').forEach(tr => tr.classList.remove('row-disabled'));
    }

    const formFilled = checkFormFilled();
    document.getElementById('btn-submit').disabled = !(count > 0 && !hasConflict && formFilled);
    document.getElementById('btn-submit-text').textContent = count > 0 ? `Buat Jadwal (${count} Asesi)` : 'Buat Jadwal';

    updateSteps(count, !hasConflict, formFilled);
    updateSummary(skemaIds, hasConflict);
}

// ── Chips ─────────────────────────────────────────────────────
function renderChips() {
    const container   = document.getElementById('chip-container');
    const placeholder = document.getElementById('chip-placeholder');
    container.querySelectorAll('.chip').forEach(c => c.remove());
    if (state.selected.size === 0) { placeholder.style.display = 'inline'; return; }
    placeholder.style.display = 'none';
    state.selected.forEach((data, id) => {
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.innerHTML = `<i class="bi bi-person-fill" style="font-size:.7rem;"></i>${escHtml(data.name)}<span class="chip-remove" onclick="removeChip('${id}')"><i class="bi bi-x" style="font-size:.75rem;"></i></span>`;
        container.appendChild(chip);
    });
}
function removeChip(id) {
    const cb = document.querySelector(`.asesi-check[value="${id}"]`);
    if (cb) cb.checked = false;
    updateState();
}
function escHtml(str) { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }

// ── Steps ─────────────────────────────────────────────────────
function updateSteps(count, skemaOk, formFilled) {
    const step1 = count > 0 && skemaOk;
    const step2 = formFilled;
    const step3 = document.querySelector('input[name="asesor_id"]:checked')?.value !== '';
    const step4 = step1 && step2;
    setStep(1, step1 ? 'done' : 'active', step1);
    setStep(2, step2 ? 'done' : (step1 ? 'active' : 'pending'), step1);
    setStep(3, step3 ? 'done' : (step2 ? 'active' : 'pending'), step2);
    setStep(4, step4 ? 'active' : 'pending', step4);
    setLine(1, step1); setLine(2, step2); setLine(3, step3);
}
function setStep(n, state, isReachable) {
    const dot = document.getElementById(`dot-${n}`);
    const lbl = document.getElementById(`lbl-${n}`);
    dot.className = 'step-dot'; lbl.className = 'step-label';
    if (state === 'done')   { dot.classList.add('done');   lbl.classList.add('done');   dot.innerHTML = '<i class="bi bi-check2" style="font-size:.75rem;"></i>'; }
    else if (state === 'active') { dot.classList.add('active'); lbl.classList.add('active'); dot.textContent = n; }
    else { dot.textContent = n; }
}
function setLine(n, done) { document.getElementById(`line-${n}`)?.classList.toggle('done', done); }

// ── Form filled check ─────────────────────────────────────────
function checkFormFilled() {
    const tuk      = document.getElementById('input-tuk').value;
    const date     = document.getElementById('input-date').value;
    const start    = document.getElementById('input-start').value;
    const end      = document.getElementById('input-end').value;
    const location = document.getElementById('input-location').value.trim();
    const locType  = document.querySelector('input[name="location_type"]:checked')?.value;
    const link     = document.getElementById('input-meeting-link').value.trim();

    if (!tuk || !date || !start || !end || !location) return false;
    if (locType === 'online' && !link) return false;
    return true;
}

// ── Summary ───────────────────────────────────────────────────
function updateSummary(skemaIds, hasConflict) {
    const tukEl    = document.getElementById('input-tuk');
    const dateEl   = document.getElementById('input-date');
    const startEl  = document.getElementById('input-start');
    const endEl    = document.getElementById('input-end');
    const locEl    = document.getElementById('input-location');
    const locType  = document.querySelector('input[name="location_type"]:checked')?.value ?? 'offline';
    const asesorEl = document.querySelector('input[name="asesor_id"]:checked');
    const asesorLbl = asesorEl?.closest('.asesor-option')?.querySelector('.fw-semibold')?.textContent ?? '—';

    const hasData = state.selected.size > 0 || dateEl.value || locEl.value.trim();
    document.getElementById('summary-empty').style.display = hasData ? 'none' : '';
    document.getElementById('summary-rows').style.display  = hasData ? '' : 'none';
    document.getElementById('summary-panel').classList.toggle('has-data', hasData);

    const count = state.selected.size;
    const skemaName = !hasConflict && count > 0 ? ([...state.selected.values()][0]?.skemaName ?? '—') : (hasConflict ? '⚠ Skema berbeda!' : '—');

    document.getElementById('s-asesi').textContent    = count > 0 ? `${count} orang` : '—';
    document.getElementById('s-skema').textContent    = skemaName;
    document.getElementById('s-tuk').textContent      = tukEl.options[tukEl.selectedIndex]?.text ?? '—';
    document.getElementById('s-date').textContent     = dateEl.value ? formatDate(dateEl.value) : '—';
    document.getElementById('s-time').textContent     = startEl.value && endEl.value ? `${startEl.value} – ${endEl.value}` : '—';
    document.getElementById('s-loc-type').textContent = locType === 'online' ? '🌐 Online' : '🏢 Offline';
    document.getElementById('s-location').textContent = locEl.value.trim() || '—';
    document.getElementById('s-asesor').textContent   = asesorLbl;
}
function formatDate(ymd) {
    if (!ymd) return '—';
    return new Date(ymd + 'T00:00:00').toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
}

// ── Location type toggle ──────────────────────────────────────
function handleLocationType() {
    const type = document.querySelector('input[name="location_type"]:checked')?.value ?? 'offline';
    const isOnline = type === 'online';

    ['offline', 'online'].forEach(t => {
        const inner  = document.getElementById(`card-${t}-inner`);
        const icon   = document.getElementById(`icon-${t}`);
        const wrap   = document.getElementById(`icon-${t}-wrap`);
        const label  = document.getElementById(`label-${t}`);
        const active = t === type;
        if (!inner) return;

        // border/bg
        inner.classList.toggle('border-primary', active);
        inner.classList.toggle('bg-primary', active);
        inner.classList.toggle('bg-opacity-10', active);
        inner.classList.toggle('border-secondary-subtle', !active);

        if (icon)  icon.style.color  = active ? '#2563eb' : '#94a3b8';
        if (label) label.style.color = active ? '#1d4ed8' : '#334155';
        if (wrap)  wrap.style.background = active ? '#dbeafe' : '#f1f5f9';
    });

    // Location field
    const locIcon  = document.getElementById('loc-icon');
    const locLabel = document.getElementById('loc-label-text');
    const locInput = document.getElementById('input-location');
    const locHint  = document.getElementById('loc-hint');

    if (locIcon)  locIcon.className  = `bi ${isOnline ? 'bi-camera-video' : 'bi-geo-alt'} text-muted`;
    if (locLabel) locLabel.textContent = isOnline ? 'Nama Platform / Keterangan' : 'Gedung / Ruangan';
    if (locInput) locInput.placeholder = isOnline ? 'cth: Zoom Meeting, Google Meet...' : 'cth: Aula Lantai 2, Gedung A Ruang 301...';
    if (locHint)  locHint.textContent  = isOnline ? 'Isi nama platform atau keterangan singkat.' : 'Isi nama gedung, ruangan, atau alamat tempat asesmen.';

    // Meeting link wrap
    const wrap = document.getElementById('meeting-link-wrap');
    if (wrap) {
        wrap.style.display = isOnline ? 'block' : 'none';
        const linkInput = document.getElementById('input-meeting-link');
        if (linkInput) linkInput.required = isOnline;
    }

    updateState();
}

// ── Platform icon detection ───────────────────────────────────
function detectPlatformIcon(url) {
    const icon    = document.getElementById('meeting-link-icon');
    const badge   = document.getElementById('platform-badge');
    const pname   = document.getElementById('platform-name');
    const testBtn = document.getElementById('btn-test-link');

    if (!url) {
        if (icon)  icon.className = 'bi bi-link-45deg';
        if (badge) badge.style.display = 'none';
        if (testBtn) testBtn.style.display = 'none';
        return;
    }

    const h = url.toLowerCase();
    let platform = null, iconClass = 'bi bi-link-45deg';
    if (h.includes('zoom'))       { platform = 'Zoom';        iconClass = 'bi bi-camera-video-fill text-primary'; }
    else if (h.includes('meet'))  { platform = 'Google Meet'; iconClass = 'bi bi-google text-danger'; }
    else if (h.includes('teams')) { platform = 'MS Teams';    iconClass = 'bi bi-microsoft text-info'; }
    else if (h.includes('webex')) { platform = 'Webex';       iconClass = 'bi bi-camera-video text-warning'; }

    if (icon)  icon.className = iconClass;
    if (badge) badge.style.display = platform ? 'block' : 'none';
    if (pname) pname.textContent = platform ?? '';
    if (testBtn) { testBtn.style.display = url ? 'flex' : 'none'; testBtn.href = url; }

    updateState();
}

// ── Search & filter ───────────────────────────────────────────
function filterTable() {
    const q     = document.getElementById('search-asesi').value.toLowerCase();
    const tukF  = document.getElementById('filter-tuk').value;
    const skemaF= document.getElementById('filter-skema').value;
    let visible = 0;
    document.querySelectorAll('#asesi-tbody tr.asesi-row').forEach(tr => {
        const show = (!q || tr.dataset.search.includes(q)) && (!tukF || tr.dataset.tuk === tukF) && (!skemaF || tr.dataset.skema === skemaF);
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('no-result-row')?.classList.toggle('d-none', visible > 0);
}

document.getElementById('search-asesor')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#asesor-list .asesor-option[data-id]').forEach(opt => {
        if (!opt.dataset.id) return;
        opt.style.display = !q || opt.dataset.search.includes(q) ? '' : 'none';
    });
});

// ── Asesor option click ───────────────────────────────────────
document.querySelectorAll('.asesor-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.asesor-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
        updateState();
    });
});

// ── Check all ─────────────────────────────────────────────────
document.getElementById('check-all')?.addEventListener('change', function() {
    document.querySelectorAll('#asesi-tbody tr.asesi-row:not([style*="display: none"])').forEach(tr => {
        const cb = tr.querySelector('.asesi-check');
        if (!tr.classList.contains('row-disabled') && cb) cb.checked = this.checked;
    });
    updateState();
});

// ── Live form listeners ───────────────────────────────────────
['input-tuk','input-date','input-start','input-end','input-location','input-meeting-link'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', updateState);
    document.getElementById(id)?.addEventListener('input',  updateState);
});
document.getElementById('search-asesi')?.addEventListener('input', filterTable);
document.getElementById('filter-tuk')?.addEventListener('change', filterTable);
document.getElementById('filter-skema')?.addEventListener('change', filterTable);

// ── Form submit confirm ───────────────────────────────────────
document.getElementById('form-jadwal')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const count    = state.selected.size;
    const dateEl   = document.getElementById('input-date');
    const locEl    = document.getElementById('input-location');
    const locType  = document.querySelector('input[name="location_type"]:checked')?.value ?? 'offline';
    const linkEl   = document.getElementById('input-meeting-link');
    const asesorEl = document.querySelector('input[name="asesor_id"]:checked');
    const asesorName = asesorEl?.closest('.asesor-option')?.querySelector('.fw-semibold')?.textContent ?? 'Belum ditugaskan';

    const result = await Swal.fire({
        title: 'Konfirmasi Buat Jadwal',
        html: `<div class="text-start small">
            <div class="mb-3 p-3 rounded bg-light">
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Jumlah Asesi</span><strong>${count} orang</strong></div>
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Tanggal</span><strong>${formatDate(dateEl.value)}</strong></div>
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Jenis</span><strong>${locType === 'online' ? '🌐 Online' : '🏢 Offline'}</strong></div>
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Lokasi</span><strong>${escHtml(locEl.value.trim())}</strong></div>
                ${locType === 'online' && linkEl.value ? `<div class="d-flex justify-content-between mb-1"><span class="text-muted">Link</span><strong style="font-size:.75rem;word-break:break-all;">${escHtml(linkEl.value)}</strong></div>` : ''}
                <div class="d-flex justify-content-between"><span class="text-muted">Asesor</span><strong>${escHtml(asesorName)}</strong></div>
            </div>
            <div class="alert alert-info py-2 mb-0"><i class="bi bi-info-circle me-1"></i>Jadwal akan menunggu persetujuan Direktur sebelum status asesi berubah.</div>
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

    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    this.submit();
});

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    handleLocationType();
    @if(old('meeting_link'))
    detectPlatformIcon('{{ old("meeting_link") }}');
    @endif
    updateState();
    @if(old('asesmen_ids'))
    document.querySelectorAll('.asesi-check').forEach(cb => {
        if (@json(old('asesmen_ids', [])).includes(cb.value)) cb.checked = true;
    });
    updateState();
    @endif
});
</script>
@endpush