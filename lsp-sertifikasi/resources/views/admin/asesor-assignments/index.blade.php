@extends('layouts.app')
@section('title', 'Penugasan Asesor')
@section('page-title', 'Penugasan Asesor')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="--bg-color: #3b82f6; --bg-color-end: #1d4ed8;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:0.8rem;">TOTAL JADWAL</p>
                    <h3>{{ $stats['total'] }}</h3>
                </div>
                <i class="bi bi-calendar-event" style="font-size:2.5rem; opacity:0.4;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="--bg-color: #10b981; --bg-color-end: #047857;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:0.8rem;">SUDAH DITUGASKAN</p>
                    <h3>{{ $stats['assigned'] }}</h3>
                </div>
                <i class="bi bi-person-check" style="font-size:2.5rem; opacity:0.4;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="--bg-color: #f59e0b; --bg-color-end: #b45309;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:0.8rem;">BELUM DITUGASKAN</p>
                    <h3>{{ $stats['unassigned'] }}</h3>
                </div>
                <i class="bi bi-person-x" style="font-size:2.5rem; opacity:0.4;"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Jadwal & Penugasan Asesor</h5>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card-body border-bottom py-3">
        <form method="GET" class="row g-2">
            <div class="col-md-4">
                <select name="tuk_id" class="form-select form-select-sm">
                    <option value="">Semua TUK</option>
                    @foreach($tuks as $tuk)
                    <option value="{{ $tuk->id }}" {{ request('tuk_id') == $tuk->id ? 'selected' : '' }}>
                        {{ $tuk->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Sudah Ditugaskan
                    </option>
                    <option value="unassigned" {{ request('status') === 'unassigned' ? 'selected' : '' }}>Belum
                        Ditugaskan</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.asesor-assignments.index') }}" class="btn btn-secondary btn-sm w-100">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($schedules->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-calendar-x" style="font-size:4rem;color:#ccc;"></i>
            <h5 class="mt-3 text-muted">Tidak Ada Jadwal</h5>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th class="text-center">Asesi</th>
                        <th>Asesor</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                    <tr id="schedule-row-{{ $schedule->id }}">
                        <td>
                            <strong>{{ $schedule->assessment_date->format('d M Y') }}</strong><br>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> {{ $schedule->start_time }} - {{ $schedule->end_time }}
                            </small>
                        </td>
                        <td>{{ $schedule->tuk->name }}</td>
                        <td>
                            <strong>{{ $schedule->skema->name }}</strong><br>
                            <small class="text-muted"><code>{{ $schedule->skema->code }}</code></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info">{{ $schedule->asesmens->count() }} orang</span>
                        </td>
                        <td>
                            <div id="asesor-display-{{ $schedule->id }}">
                                @if($schedule->asesor)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $schedule->asesor->foto_url }}" alt="Foto"
                                        style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;">
                                    <div>
                                        <strong>{{ $schedule->asesor->nama }}</strong><br>
                                        <small class="text-muted">{{ $schedule->asesor->no_reg_met ?? '-' }}</small>
                                    </div>
                                </div>
                                @else
                                <span class="badge bg-warning">Belum Ditugaskan</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($schedule->asesor)
                                {{-- Ganti Asesor --}}
                                <button class="btn btn-sm btn-outline-warning" title="Ganti Asesor"
                                    onclick="showAssignModal({{ $schedule->id }}, {{ $schedule->asesor->id }})">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                                {{-- Batalkan --}}
                                <button class="btn btn-sm btn-outline-danger" title="Batalkan Penugasan"
                                    onclick="unassignAsesor({{ $schedule->id }}, '{{ addslashes($schedule->asesor->nama) }}')">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                                {{-- History --}}
                                <button class="btn btn-sm btn-outline-info" title="Riwayat"
                                    onclick="viewHistory({{ $schedule->id }})">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                                @else
                                {{-- Tugaskan --}}
                                <button class="btn btn-sm btn-primary" title="Tugaskan Asesor"
                                    onclick="showAssignModal({{ $schedule->id }})">
                                    <i class="bi bi-person-plus"></i> Tugaskan
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Modal Assign Asesor --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalTitle">Tugaskan Asesor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assign-schedule-id">

                {{-- Info Jadwal --}}
                <div class="alert alert-info mb-3" id="schedule-info-box">
                    <strong><i class="bi bi-calendar-event"></i> Informasi Jadwal:</strong>
                    <div class="mt-2" id="schedule-info-detail">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Tanggal & Waktu:</small>
                                <strong id="schedule-date-time">-</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">TUK:</small>
                                <strong id="schedule-tuk">-</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Skema:</small>
                                <strong id="schedule-skema">-</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Jumlah Asesi:</small>
                                <strong id="schedule-asesi-count">-</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="asesor-list-loading" class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2 text-muted">Memuat daftar asesor...</p>
                </div>

                <div id="asesor-list-container" style="display:none;">
                    <label class="form-label fw-bold">Pilih Asesor:</label>
                    <div id="asesor-list" class="list-group mb-3" style="max-height: 300px; overflow-y: auto;"></div>

                    <label class="form-label">Catatan (opsional):</label>
                    <textarea id="assign-notes" class="form-control" rows="2"
                        placeholder="Catatan tambahan untuk asesor..."></textarea>
                </div>

                <div id="no-asesor-available" style="display:none;" class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Tidak ada asesor tersedia</strong><br>
                    Semua asesor sudah ditugaskan pada tanggal dan waktu yang sama.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-submit-assign" onclick="submitAssign()" disabled>
                    <i class="bi bi-check-circle"></i> Tugaskan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal History --}}
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-clock-history"></i> Riwayat Penugasan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="history-content">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let selectedAsesorId = null;
let currentScheduleData = null;

async function showAssignModal(scheduleId, currentAsesorId = null) {
    selectedAsesorId = null;
    document.getElementById('assign-schedule-id').value = scheduleId;
    document.getElementById('assign-notes').value = '';
    document.getElementById('btn-submit-assign').disabled = true;

    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();

    document.getElementById('assignModalTitle').textContent =
        currentAsesorId ? 'Ganti Asesor' : 'Tugaskan Asesor';

    // Tampilkan info jadwal dari data yang sudah ada di tabel
    const scheduleRow = document.getElementById(`schedule-row-${scheduleId}`);
    if (scheduleRow) {
        const cells = scheduleRow.getElementsByTagName('td');
        
        // Extract data dari tabel
        const dateTime = cells[0].textContent.trim();
        const tuk = cells[1].textContent.trim();
        const skema = cells[2].querySelector('strong').textContent.trim();
        const asesiCount = cells[3].textContent.trim();

        // Populate info jadwal
        document.getElementById('schedule-date-time').textContent = dateTime;
        document.getElementById('schedule-tuk').textContent = tuk;
        document.getElementById('schedule-skema').textContent = skema;
        document.getElementById('schedule-asesi-count').textContent = asesiCount;
    }

    document.getElementById('asesor-list-loading').style.display = 'block';
    document.getElementById('asesor-list-container').style.display = 'none';
    document.getElementById('no-asesor-available').style.display = 'none';

    try {
        const res = await fetch(`/admin/schedules/${scheduleId}/available-asesors`);
        const data = await res.json();

        if (data.success && data.asesors.length > 0) {
            renderAsesorList(data.asesors, currentAsesorId);
            document.getElementById('asesor-list-loading').style.display = 'none';
            document.getElementById('asesor-list-container').style.display = 'block';
        } else {
            document.getElementById('asesor-list-loading').style.display = 'none';
            document.getElementById('no-asesor-available').style.display = 'block';
        }
    } catch (err) {
        console.error(err);
        document.getElementById('asesor-list-loading').innerHTML =
            '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> Gagal memuat data asesor</div>';
    }
}

function renderAsesorList(asesors, currentAsesorId) {
    const container = document.getElementById('asesor-list');
    container.innerHTML = '';

    asesors.forEach(asesor => {
        const item = document.createElement('div');
        item.className = 'list-group-item list-group-item-action';
        item.style.cursor = 'pointer';
        item.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <img src="${asesor.foto_url}" style="width:45px;height:45px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb;">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong class="d-block">${asesor.nama}</strong>
                            <small class="text-muted">${asesor.no_reg_met || '-'}</small>
                        </div>
                        <i class="bi bi-check-circle-fill text-success" style="font-size:1.5rem;display:none;" data-check="${asesor.id}"></i>
                    </div>
                    <small class="text-muted"><i class="bi bi-envelope"></i> ${asesor.email}</small>
                </div>
            </div>
        `;
        item.addEventListener('click', function() { 
            selectAsesor(asesor.id, item); 
        });
        container.appendChild(item);
    });
}

function selectAsesor(asesorId, clickedElement) {
    selectedAsesorId = asesorId;
    
    // Remove active dari semua item
    document.querySelectorAll('#asesor-list .list-group-item').forEach(el => {
        el.classList.remove('active');
    });
    
    // Hide semua check icon
    document.querySelectorAll('[data-check]').forEach(el => {
        el.style.display = 'none';
    });

    // Set active pada item yang diklik
    clickedElement.classList.add('active');
    
    // Show check icon
    const checkIcon = clickedElement.querySelector(`[data-check="${asesorId}"]`);
    if (checkIcon) {
        checkIcon.style.display = 'block';
    }
    
    // Enable submit button
    document.getElementById('btn-submit-assign').disabled = false;
}

async function submitAssign() {
    if (!selectedAsesorId) return;

    const scheduleId = document.getElementById('assign-schedule-id').value;
    const notes = document.getElementById('assign-notes').value.trim();

    const btn = document.getElementById('btn-submit-assign');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    try {
        const res = await fetch(`/admin/schedules/${scheduleId}/assign-asesor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                asesor_id: selectedAsesorId,
                notes
            }),
        });
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Tugaskan';
        }
    } catch (err) {
        console.error(err);
        Swal.fire('Error', 'Terjadi kesalahan saat menyimpan', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Tugaskan';
    }
}

async function unassignAsesor(scheduleId, asesorName) {
    const result = await Swal.fire({
        title: 'Batalkan Penugasan?',
        html: `Penugasan untuk asesor <strong>${asesorName}</strong> akan dibatalkan.`,
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Alasan pembatalan (opsional)...',
        showCancelButton: true,
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/schedules/${scheduleId}/unassign-asesor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                notes: result.value
            }),
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Terjadi kesalahan', 'error');
    }
}

async function viewHistory(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    modal.show();
    document.getElementById('history-content').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    try {
        const res = await fetch(`/admin/schedules/${scheduleId}/assignment-history`);
        const data = await res.json();
        if (data.success) {
            document.getElementById('history-content').innerHTML = data.html;
        }
    } catch (err) {
        document.getElementById('history-content').innerHTML =
            '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> Gagal memuat riwayat</div>';
    }
}
</script>
@endpush