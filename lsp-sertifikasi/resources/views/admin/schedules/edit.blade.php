@extends('layouts.app')
@section('title', 'Edit Jadwal #' . $schedule->id)
@section('page-title', 'Edit Jadwal Asesmen')

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

.section-heading {
    font-size: .72rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: var(--sched-muted); margin-bottom: 10px;
}

.info-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: #f1f5f9; border: 1px solid var(--sched-border);
    border-radius: 20px; padding: 3px 10px;
    font-size: .78rem; font-weight: 600; color: #334155;
}

.asesi-list-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
    font-size: .85rem;
}
.asesi-list-item:last-child { border-bottom: none; }
.asesi-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: #e0e7ff; color: #4f46e5;
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 700; flex-shrink: 0;
}

.summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 7px 14px; border-bottom: 1px solid #f1f5f9; font-size: .83rem;
}
.summary-row:last-child { border-bottom: none; }
.summary-row .s-label { color: var(--sched-muted); }
.summary-row .s-value { font-weight: 600; }

.form-control:focus, .form-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.sticky-panel { position: sticky; top: 72px; }

/* ── Location type cards ── */
.loc-type-card { user-select: none; }
</style>
@endpush

@section('content')

{{-- ── Breadcrumb ── --}}
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.index') }}">Jadwal Asesmen</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.schedules.show', $schedule) }}">#{{ $schedule->id }}</a></li>
        <li class="breadcrumb-item active">Edit</li>
    </ol>
</nav>

{{-- ── Status Banner ── --}}
@if($schedule->isRejected())
<div class="alert alert-danger d-flex align-items-start gap-3 shadow-sm mb-4">
    <i class="bi bi-x-circle-fill fs-4 flex-shrink-0 mt-1"></i>
    <div>
        <div class="fw-bold mb-1">Jadwal ini ditolak oleh Direktur</div>
        <div class="small">{{ $schedule->approval_notes }}</div>
        <div class="small text-muted mt-1">
            Setelah Anda menyimpan perubahan, jadwal akan otomatis dikembalikan ke antrian persetujuan Direktur.
        </div>
    </div>
</div>
@elseif($schedule->isPendingApproval())
<div class="alert alert-warning d-flex align-items-start gap-3 shadow-sm mb-4">
    <i class="bi bi-hourglass-split fs-4 flex-shrink-0 mt-1"></i>
    <div>
        <div class="fw-bold mb-1">Jadwal sedang menunggu persetujuan Direktur</div>
        <div class="small text-muted">Anda masih bisa mengedit jadwal sebelum Direktur memproses approval.</div>
    </div>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger shadow-sm mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Terdapat kesalahan:</strong>
    <ul class="mb-0 mt-1">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
</div>
@endif

@php
    $oldType = old('location_type', $schedule->location_type ?? 'offline');
    $oldLoc  = old('location', $schedule->location ?? '');
    $oldLink = old('meeting_link', $schedule->meeting_link ?? '');
@endphp

<form method="POST" action="{{ route('admin.schedules.update', $schedule) }}" id="edit-form">
@csrf
@method('PUT')

<div class="row g-4">

    {{-- ══ KIRI — Peserta (read-only) + Form Edit ══ --}}
    <div class="col-lg-7">

        {{-- Info Peserta (tidak bisa diubah) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:28px;height:28px;background:#eff6ff;">
                    <i class="bi bi-people-fill text-primary" style="font-size:.8rem;"></i>
                </div>
                <span class="fw-semibold">Peserta</span>
                <span class="badge bg-primary ms-1">{{ $schedule->asesmens->count() }} orang</span>
                <div class="ms-auto">
                    <span class="info-chip">
                        <i class="bi bi-lock-fill" style="font-size:.65rem; color:#94a3b8;"></i>
                        Tidak dapat diubah
                    </span>
                </div>
            </div>

            {{-- Skema & TUK --}}
            <div class="px-3 py-2 border-bottom d-flex gap-3 flex-wrap" style="background:#fafafa;">
                <div>
                    <span class="text-muted" style="font-size:.72rem;">Skema</span>
                    <div class="small fw-semibold">{{ $schedule->skema?->name ?? '-' }}</div>
                </div>
                <div>
                    <span class="text-muted" style="font-size:.72rem;">TUK</span>
                    <div class="small fw-semibold">{{ $schedule->tuk?->name ?? '-' }}</div>
                </div>
                @if($schedule->asesor)
                <div>
                    <span class="text-muted" style="font-size:.72rem;">Asesor</span>
                    <div class="small fw-semibold">{{ $schedule->asesor->nama }}</div>
                </div>
                @endif
            </div>

            <div style="max-height:220px; overflow-y:auto;">
                @forelse($schedule->asesmens as $asesmen)
                <div class="asesi-list-item">
                    <div class="asesi-avatar">{{ strtoupper(substr($asesmen->full_name, 0, 1)) }}</div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $asesmen->full_name }}</div>
                        <div class="text-muted" style="font-size:.73rem;">{{ $asesmen->user?->email ?? '-' }}</div>
                    </div>
                    <span class="badge bg-{{ $asesmen->is_collective ? 'primary' : 'success' }}" style="font-size:.6rem;">
                        {{ $asesmen->is_collective ? 'Kolektif' : 'Mandiri' }}
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-people fs-3 d-block mb-2 opacity-25"></i>
                    <p class="small mb-0">Tidak ada peserta.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Form Edit Jadwal --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2 py-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:28px;height:28px;background:#f0fdf4;">
                    <i class="bi bi-pencil-square text-success" style="font-size:.8rem;"></i>
                </div>
                <span class="fw-semibold">Detail Jadwal</span>
                <span class="badge bg-{{ $schedule->approval_status_badge }} ms-auto">
                    {{ $schedule->approval_status_label }}
                </span>
            </div>
            <div class="card-body p-4">

                {{-- Tanggal --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tanggal Asesmen <span class="text-danger">*</span></label>
                    <input type="date" name="assessment_date" id="input-date"
                           class="form-control form-control-sm @error('assessment_date') is-invalid @enderror"
                           value="{{ old('assessment_date', $schedule->assessment_date->format('Y-m-d')) }}"
                           required>
                    @error('assessment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Jam --}}
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" id="input-start"
                               class="form-control form-control-sm @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time', $schedule->start_time) }}" required>
                        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" id="input-end"
                               class="form-control form-control-sm @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time', $schedule->end_time) }}" required>
                        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Jenis Pelaksanaan --}}
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

                {{-- Lokasi --}}
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

                {{-- Meeting Link (online only) --}}
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
                    <label class="form-label small fw-semibold">
                        Catatan <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <textarea name="notes" id="input-notes" class="form-control form-control-sm" rows="2"
                        placeholder="Informasi tambahan untuk peserta atau asesor...">{{ old('notes', $schedule->notes) }}</textarea>
                </div>

            </div>
        </div>

    </div>

    {{-- ══ KANAN — Summary & Actions ══ --}}
    <div class="col-lg-5">
    <div class="sticky-panel">

        {{-- Summary --}}
        <div class="card border-0 shadow-sm mb-4" id="summary-panel">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:28px;height:28px;background:#fdf2f8;">
                    <i class="bi bi-clipboard-check text-danger" style="font-size:.8rem;"></i>
                </div>
                <span class="fw-semibold">Ringkasan Perubahan</span>
            </div>
            <div class="card-body p-0">
                <div id="summary-rows">
                    <div class="summary-row"><span class="s-label">Peserta</span><span class="s-value">{{ $schedule->asesmens->count() }} orang</span></div>
                    <div class="summary-row"><span class="s-label">Skema</span><span class="s-value">{{ $schedule->skema?->name ?? '-' }}</span></div>
                    <div class="summary-row"><span class="s-label">TUK</span><span class="s-value">{{ $schedule->tuk?->name ?? '-' }}</span></div>
                    <div class="summary-row">
                        <span class="s-label">Tanggal</span>
                        <span class="s-value" id="s-date">{{ $schedule->assessment_date->translatedFormat('d M Y') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Waktu</span>
                        <span class="s-value" id="s-time">{{ $schedule->start_time }} – {{ $schedule->end_time }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Jenis</span>
                        <span class="s-value" id="s-loc-type">
                            {{ ($schedule->location_type ?? 'offline') === 'online' ? '🌐 Online' : '🏢 Offline' }}
                        </span>
                    </div>
                    <div class="summary-row">
                        <span class="s-label">Lokasi</span>
                        <span class="s-value" id="s-location">{{ $schedule->location ?? '-' }}</span>
                    </div>
                    @if($schedule->asesor)
                    <div class="summary-row">
                        <span class="s-label">Asesor</span>
                        <span class="s-value">{{ $schedule->asesor->nama }}</span>
                    </div>
                    @endif
                    @if($schedule->isRejected())
                    <div class="summary-row" style="background:#fef2f2;">
                        <span class="s-label text-danger">Status</span>
                        <span class="s-value text-danger small">Setelah disimpan → Pending Approval</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Asesor info (read-only) --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:28px;height:28px;background:#fef9c3;">
                    <i class="bi bi-person-badge text-warning" style="font-size:.8rem;"></i>
                </div>
                <span class="fw-semibold">Asesor</span>
                <span class="ms-auto badge bg-secondary" style="font-size:.65rem;">Tidak berubah</span>
            </div>
            <div class="card-body">
                @if($schedule->asesor)
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#2563eb);
                         display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;flex-shrink:0;">
                        {{ strtoupper(substr($schedule->asesor->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-semibold small">{{ $schedule->asesor->nama }}</div>
                        <div class="text-muted" style="font-size:.73rem;">{{ $schedule->asesor->no_reg_met ?? '-' }}</div>
                        @if($schedule->asesor->email)
                        <div class="text-muted" style="font-size:.73rem;">{{ $schedule->asesor->email }}</div>
                        @endif
                    </div>
                </div>
                <div class="alert alert-light border mt-3 mb-0 py-2 small">
                    <i class="bi bi-info-circle me-1 text-muted"></i>
                    Untuk mengubah asesor, gunakan fitur <strong>Penugasan Asesor</strong> dari halaman detail jadwal.
                </div>
                @else
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-person-dash fs-3 d-block mb-2 opacity-25"></i>
                    <p class="small mb-0">Belum ada asesor yang ditugaskan.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary" id="btn-submit" onclick="confirmSave()">
                <i class="bi bi-floppy me-1"></i>Simpan Perubahan
            </button>
            <a href="{{ route('admin.schedules.show', $schedule) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Batal
            </a>
        </div>

    </div>
    </div>

</div>
</form>

@endsection

@push('scripts')
<script>
// ── Location type toggle ──────────────────────────────────────
function handleLocationType() {
    const type     = document.querySelector('input[name="location_type"]:checked')?.value ?? 'offline';
    const isOnline = type === 'online';

    ['offline', 'online'].forEach(t => {
        const inner  = document.getElementById(`card-${t}-inner`);
        const icon   = document.getElementById(`icon-${t}`);
        const wrap   = document.getElementById(`icon-${t}-wrap`);
        const label  = document.getElementById(`label-${t}`);
        const active = t === type;
        if (!inner) return;

        inner.classList.toggle('border-primary', active);
        inner.classList.toggle('bg-primary',     active);
        inner.classList.toggle('bg-opacity-10',  active);
        inner.classList.toggle('border-secondary-subtle', !active);

        if (icon)  icon.style.color  = active ? '#2563eb' : '#94a3b8';
        if (label) label.style.color = active ? '#1d4ed8' : '#334155';
        if (wrap)  wrap.style.background = active ? '#dbeafe' : '#f1f5f9';
    });

    const locIcon  = document.getElementById('loc-icon');
    const locLabel = document.getElementById('loc-label-text');
    const locInput = document.getElementById('input-location');
    const locHint  = document.getElementById('loc-hint');

    if (locIcon)  locIcon.className   = `bi ${isOnline ? 'bi-camera-video' : 'bi-geo-alt'} text-muted`;
    if (locLabel) locLabel.textContent = isOnline ? 'Nama Platform / Keterangan' : 'Gedung / Ruangan';
    if (locInput) locInput.placeholder = isOnline ? 'cth: Zoom Meeting, Google Meet...' : 'cth: Aula Lantai 2, Gedung A Ruang 301...';
    if (locHint)  locHint.textContent  = isOnline ? 'Isi nama platform atau keterangan singkat.' : 'Isi nama gedung, ruangan, atau alamat tempat asesmen.';

    const wrap = document.getElementById('meeting-link-wrap');
    if (wrap) {
        wrap.style.display = isOnline ? 'block' : 'none';
        const linkInput = document.getElementById('input-meeting-link');
        if (linkInput) linkInput.required = isOnline;
    }

    updateSummary();
}

// ── Platform icon detection ───────────────────────────────────
function detectPlatformIcon(url) {
    const icon    = document.getElementById('meeting-link-icon');
    const badge   = document.getElementById('platform-badge');
    const pname   = document.getElementById('platform-name');
    const testBtn = document.getElementById('btn-test-link');

    if (!url) {
        if (icon)    icon.className = 'bi bi-link-45deg';
        if (badge)   badge.style.display = 'none';
        if (testBtn) testBtn.style.display = 'none';
        return;
    }

    const h = url.toLowerCase();
    let platform = null, iconClass = 'bi bi-link-45deg';
    if (h.includes('zoom'))        { platform = 'Zoom';        iconClass = 'bi bi-camera-video-fill text-primary'; }
    else if (h.includes('meet'))   { platform = 'Google Meet'; iconClass = 'bi bi-google text-danger'; }
    else if (h.includes('teams'))  { platform = 'MS Teams';    iconClass = 'bi bi-microsoft text-info'; }
    else if (h.includes('webex'))  { platform = 'Webex';       iconClass = 'bi bi-camera-video text-warning'; }

    if (icon)  icon.className = iconClass;
    if (badge) badge.style.display = platform ? 'block' : 'none';
    if (pname) pname.textContent = platform ?? '';
    if (testBtn) { testBtn.style.display = url ? 'flex' : 'none'; testBtn.href = url; }

    updateSummary();
}

// ── Live summary update ───────────────────────────────────────
function updateSummary() {
    const dateEl  = document.getElementById('input-date');
    const startEl = document.getElementById('input-start');
    const endEl   = document.getElementById('input-end');
    const locEl   = document.getElementById('input-location');
    const locType = document.querySelector('input[name="location_type"]:checked')?.value ?? 'offline';

    const sDate   = document.getElementById('s-date');
    const sTime   = document.getElementById('s-time');
    const sLocType= document.getElementById('s-loc-type');
    const sLoc    = document.getElementById('s-location');

    if (dateEl.value && sDate) {
        const d = new Date(dateEl.value + 'T00:00:00');
        sDate.textContent = d.toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
    }
    if (startEl.value && endEl.value && sTime) sTime.textContent = `${startEl.value} – ${endEl.value}`;
    if (sLocType) sLocType.textContent = locType === 'online' ? '🌐 Online' : '🏢 Offline';
    if (locEl.value.trim() && sLoc) sLoc.textContent = locEl.value.trim();
}

// ── Confirm save ──────────────────────────────────────────────
async function confirmSave() {
    const dateEl  = document.getElementById('input-date');
    const startEl = document.getElementById('input-start');
    const endEl   = document.getElementById('input-end');
    const locEl   = document.getElementById('input-location');
    const locType = document.querySelector('input[name="location_type"]:checked')?.value ?? 'offline';
    const linkEl  = document.getElementById('input-meeting-link');

    // Validasi minimal
    if (!dateEl.value || !startEl.value || !endEl.value || !locEl.value.trim()) {
        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Isi tanggal, jam mulai, jam selesai, dan lokasi terlebih dahulu.' });
        return;
    }
    if (locType === 'online' && !linkEl.value.trim()) {
        Swal.fire({ icon: 'warning', title: 'Link Meeting Diperlukan', text: 'Isi link meeting untuk jadwal online.' });
        return;
    }

    const isRejected = {{ $schedule->isRejected() ? 'true' : 'false' }};

    const result = await Swal.fire({
        title: 'Simpan Perubahan?',
        html: `<div class="text-start small">
            <div class="mb-3 p-3 rounded bg-light">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Tanggal</span>
                    <strong>${new Date(dateEl.value + 'T00:00:00').toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' })}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Waktu</span>
                    <strong>${startEl.value} – ${endEl.value}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Jenis</span>
                    <strong>${locType === 'online' ? '🌐 Online' : '🏢 Offline'}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Lokasi</span>
                    <strong>${locEl.value.trim()}</strong>
                </div>
            </div>
            ${isRejected ? `<div class="alert alert-warning py-2 mb-0 small"><i class="bi bi-arrow-repeat me-1"></i>Jadwal akan dikembalikan ke antrian <strong>Menunggu Persetujuan Direktur</strong>.</div>` : ''}
        </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-floppy me-1"></i>Ya, Simpan',
        cancelButtonText: 'Periksa Ulang',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...';
    document.getElementById('edit-form').submit();
}

// ── Init ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    handleLocationType();
    @if($oldLink)
    detectPlatformIcon('{{ $oldLink }}');
    @endif

    // Live update summary saat user mengetik
    ['input-date', 'input-start', 'input-end', 'input-location'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateSummary);
        document.getElementById(id)?.addEventListener('change', updateSummary);
    });
});
</script>
@endpush