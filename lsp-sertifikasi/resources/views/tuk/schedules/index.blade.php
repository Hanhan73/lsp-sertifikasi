@extends('layouts.app')

@section('title', 'Penjadwalan Asesmen')
@section('page-title', 'Penjadwalan Asesmen')

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<!-- Asesi yang perlu dijadwalkan -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-calendar-plus"></i> Asesi yang Perlu Dijadwalkan
            <span class="badge bg-warning ms-2">{{ $asesmens->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($asesmens->isEmpty())
        <div class="text-center py-4">
            <i class="bi bi-calendar-check" style="font-size: 3rem; color: #28a745;"></i>
            <p class="text-muted mt-3">Semua asesi sudah dijadwalkan atau belum ada yang siap dijadwalkan</p>
        </div>
        @else
        <form action="{{ route('tuk.schedules.batch-create') }}" method="POST" id="batch-schedule-form">
            @csrf

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Pilih asesi yang ingin dijadwalkan, lalu tentukan tanggal dan waktu asesmen untuk mereka.
            </div>

            <!-- Select All -->
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                    <label class="form-check-label fw-bold" for="select-all-checkbox">
                        Pilih Semua ({{ $asesmens->count() }} asesi)
                    </label>
                </div>
            </div>

            <!-- Asesi List -->
            <div class="table-responsive mb-3">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">
                                <i class="bi bi-check-square"></i>
                            </th>
                            <th>No Reg</th>
                            <th>Nama</th>
                            <th>Skema</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asesmens as $asesmen)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input asesmen-checkbox" name="asesmen_ids[]"
                                    value="{{ $asesmen->id }}">
                            </td>
                            <td><strong>#{{ $asesmen->id }}</strong></td>
                            <td>
                                {{ $asesmen->full_name ?? $asesmen->user->name }}
                                @if($asesmen->is_collective)
                                <br><small class="text-muted"><i class="bi bi-collection"></i>
                                    {{ $asesmen->collective_batch_id }}</small>
                                @endif
                            </td>
                            <td>{{ $asesmen->skema->name ?? '-' }}</td>
                            <td>
                                @if($asesmen->is_collective)
                                <span class="badge bg-primary"><i class="bi bi-people"></i> Kolektif</span>
                                @else
                                <span class="badge bg-success"><i class="bi bi-person"></i> Mandiri</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $asesmen->status_badge }}">
                                    {{ $asesmen->status_label }}
                                </span>
                            </td>
                            <td>
                                {{ $asesmen->payment?->verified_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Schedule Details -->
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-calendar-event"></i> Detail Jadwal</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Asesmen <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('assessment_date') is-invalid @enderror" 
                                    name="assessment_date" required min="{{ date('Y-m-d') }}" value="{{ old('assessment_date') }}">
                                @error('assessment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                    name="start_time" required value="{{ old('start_time') }}">
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                    name="end_time" required value="{{ old('end_time') }}">
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Asesmen <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                    name="location" placeholder="Contoh: Ruang Asesmen TUK, Gedung A Lt. 2" 
                                    required value="{{ old('location') }}">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                    name="notes" rows="3" 
                                    placeholder="Catatan tambahan untuk peserta asesmen">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="alert alert-success mt-3" id="summary-box" style="display: none;">
                <h6><i class="bi bi-info-circle"></i> Ringkasan</h6>
                <p class="mb-0">
                    <strong id="selected-count">0</strong> asesi dipilih untuk dijadwalkan
                </p>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                    <i class="bi bi-calendar-check"></i> Buat Jadwal
                </button>
                <a href="{{ route('tuk.dashboard') }}" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </form>
        @endif
    </div>
</div>

<!-- Jadwal yang Sudah Dibuat -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="bi bi-calendar-event"></i> Jadwal Asesmen
            <span class="badge bg-primary ms-2">{{ $scheduled->count() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($scheduled->isEmpty())
        <div class="text-center py-4">
            <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Belum ada jadwal asesmen yang dibuat</p>
        </div>
        @else
        @php
        // Group schedules by date + time + location
        $groupedSchedules = $scheduled->groupBy(function($schedule) {
            return $schedule->assessment_date->format('Y-m-d') . '|' .
                   $schedule->start_time . '|' .
                   $schedule->end_time . '|' .
                   $schedule->location;
        });
        @endphp

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Tanggal Asesmen</th>
                        <th>Waktu</th>
                        <th>Lokasi</th>
                        <th>Skema</th>
                        <th width="120" class="text-center">Jumlah Peserta</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedSchedules as $groupKey => $schedules)
                    @php
                        list($date, $startTime, $endTime, $location) = explode('|', $groupKey);
                        $firstSchedule = $schedules->first();
                        
                        // Get all asesmens from schedules
                        $allAsesmens = $schedules->flatMap(fn($s) => $s->asesmens);
                        $collectiveCount = $allAsesmens->where('is_collective', true)->count();
                        $mandiriCount = $allAsesmens->where('is_collective', false)->count();
                        
                        // Get unique skema names
                        $skemaNames = $allAsesmens->pluck('skema.name')->unique()->filter()->values();
                        $skemaDisplay = $skemaNames->count() > 1 
                            ? $skemaNames->first() . ' (+' . ($skemaNames->count() - 1) . ')'
                            : ($skemaNames->first() ?? 'N/A');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ \Carbon\Carbon::parse($date)->isoFormat('dddd') }}</strong><br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</small>
                        </td>
                        <td>
                            <i class="bi bi-clock text-primary"></i>
                            {{ $startTime }} - {{ $endTime }}
                        </td>
                        <td>
                            <i class="bi bi-geo-alt text-danger"></i>
                            {{ Str::limit($location, 30) }}
                        </td>
                        <td>
                            <span class="badge bg-info" title="{{ $skemaNames->implode(', ') }}">
                                {{ $skemaDisplay }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6">{{ $allAsesmens->count() }}</span>
                            <br>
                            <small class="text-muted">
                                @if($collectiveCount > 0)
                                    <span class="badge bg-primary bg-opacity-25 text-primary">
                                        {{ $collectiveCount }} Kolektif
                                    </span>
                                @endif
                                @if($mandiriCount > 0)
                                    <span class="badge bg-success bg-opacity-25 text-success">
                                        {{ $mandiriCount }} Mandiri
                                    </span>
                                @endif
                            </small>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" 
                                    onclick="showScheduleDetail('{{ md5($groupKey) }}')" 
                                    title="Lihat Detail">
                                <i class="bi bi-eye"></i> Detail
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Schedule Detail Modal -->
<div class="modal fade" id="scheduleDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white" 
                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="w-100">
                    <h5 class="modal-title mb-2">
                        <i class="bi bi-calendar-event-fill"></i> Detail Jadwal Asesmen
                    </h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span id="modal-date-info"></span>
                            <br>
                            <small id="modal-time-location"></small>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0" id="modal-total-peserta"></h3>
                            <small>Total Peserta</small>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Stats Cards -->
                <div class="row g-3 mb-4" id="modal-stats">
                    <!-- Will be populated by JS -->
                </div>

                <!-- Notes if exists -->
                <div id="modal-notes" style="display: none;" class="alert alert-info mb-3">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>Catatan:</strong> <span id="modal-notes-text"></span>
                </div>

                <!-- Participants Table -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-people"></i> Daftar Peserta Asesmen
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40">No</th>
                                        <th>No Reg</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Skema</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                        <th width="100">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-participants-tbody">
                                    <!-- Will be populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="w-100 d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted" id="modal-created-at">
                            <!-- Will be populated by JS -->
                        </small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Tutup
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="printScheduleFromModal()">
                            <i class="bi bi-printer"></i> Cetak Daftar
                        </button>
                        <button type="button" class="btn btn-success" onclick="exportScheduleFromModal()">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('tuk.partials.schedule-modals')

<!-- Hidden JSON Data for Schedules -->
@if($scheduled->isNotEmpty())
@foreach($groupedSchedules as $groupKey => $schedules)
@php
    list($date, $startTime, $endTime, $location) = explode('|', $groupKey);
    $firstSchedule = $schedules->first();
    
    // Get all asesmens from all schedules in this group
    $allAsesmens = $schedules->flatMap(fn($s) => $s->asesmens);
    $collectiveCount = $allAsesmens->where('is_collective', true)->count();
    $mandiriCount = $allAsesmens->where('is_collective', false)->count();
    $skemas = $allAsesmens->pluck('skema.name')->unique()->filter()->implode(', ');
    
    // Build participants array from asesmens
    $participants = [];
    foreach($schedules as $schedule) {
        foreach($schedule->asesmens->sortBy('full_name') as $asesmen) {
            $participants[] = [
                'scheduleId' => $schedule->id,
                'regNo' => $asesmen->id,
                'name' => $asesmen->full_name ?? $asesmen->user->name ?? 'N/A',
                'email' => $asesmen->user->email ?? $asesmen->email ?? '-',
                'skema' => $asesmen->skema->name ?? '-',
                'isCollective' => $asesmen->is_collective,
                'batchId' => $asesmen->collective_batch_id ?? '-',
                'status' => $asesmen->status_label,
                'statusBadge' => $asesmen->status_badge,
            ];
        }
    }
    
    // Store schedule IDs for export functionality
    $scheduleIds = $schedules->pluck('id')->toArray();
@endphp
<script type="application/json" id="schedule-data-{{ md5($groupKey) }}">
{!! json_encode([
    'groupKey' => $groupKey,
    'scheduleIds' => $scheduleIds,
    'date' => $date,
    'dateFormatted' => \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y'),
    'startTime' => $startTime,
    'endTime' => $endTime,
    'location' => $location,
    'notes' => $firstSchedule->notes ?? '',
    'createdAt' => $firstSchedule->created_at->diffForHumans(),
    'collectiveCount' => $collectiveCount,
    'mandiriCount' => $mandiriCount,
    'totalCount' => $allAsesmens->count(),
    'skemas' => $skemas,
    'participants' => $participants
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
@endforeach
@endif
@endsection

@push('scripts')
<script>
// Store current modal data
let currentScheduleData = null;

window.scheduleGroupData = {
    @if($scheduled->isNotEmpty())
    @foreach($groupedSchedules as $groupKey => $schedules)
    '{{ md5($groupKey) }}': '{{ $groupKey }}',
    @endforeach
    @endif
};

$(document).ready(function() {
    // Select all checkbox
    $('#select-all-checkbox').change(function() {
        $('.asesmen-checkbox').prop('checked', $(this).prop('checked'));
        updateSummary();
    });

    // Individual checkbox
    $('.asesmen-checkbox').change(function() {
        updateSummary();
        const total = $('.asesmen-checkbox').length;
        const checked = $('.asesmen-checkbox:checked').length;
        $('#select-all-checkbox').prop('checked', total === checked);
    });

    function updateSummary() {
        const checked = $('.asesmen-checkbox:checked').length;
        $('#selected-count').text(checked);

        if (checked > 0) {
            $('#summary-box').show();
            $('#submit-btn').prop('disabled', false);
        } else {
            $('#summary-box').hide();
            $('#submit-btn').prop('disabled', true);
        }
    }

    // Form validation with time checking
    $('#batch-schedule-form').submit(function(e) {
        e.preventDefault();
        
        const checked = $('.asesmen-checkbox:checked').length;
        if (checked === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Asesi',
                text: 'Silakan pilih minimal 1 asesi untuk dijadwalkan',
                confirmButtonText: 'OK'
            });
            return false;
        }

        // Validate time
        const startTime = $('input[name="start_time"]').val();
        const endTime = $('input[name="end_time"]').val();
        
        if (startTime && endTime && startTime >= endTime) {
            Swal.fire({
                icon: 'warning',
                title: 'Waktu Tidak Valid',
                text: 'Waktu selesai harus lebih besar dari waktu mulai',
                confirmButtonText: 'OK'
            });
            return false;
        }

        Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Penjadwalan',
            html: `
                <p>Apakah Anda yakin ingin membuat jadwal untuk <strong>${checked} asesi</strong>?</p>
                <small class="text-muted">Pastikan semua data sudah benar sebelum melanjutkan.</small>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat Jadwal',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang membuat jadwal asesmen',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                e.target.submit();
            }
        });
    });

    // Auto-update select all checkbox on page load
    updateSummary();
});

// Show Schedule Detail Modal
function showScheduleDetail(scheduleId) {
    const dataScript = document.getElementById(`schedule-data-${scheduleId}`);
    if (!dataScript) {
        console.error('Schedule data not found for ID:', scheduleId);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Data jadwal tidak ditemukan',
            confirmButtonText: 'OK'
        });
        return;
    }

    try {
        const data = JSON.parse(dataScript.textContent);
        currentScheduleData = data;

        // Populate modal header
        $('#modal-date-info').text(data.dateFormatted);
        $('#modal-time-location').html(`
            <i class="bi bi-clock-fill"></i> ${data.startTime} - ${data.endTime} WIB
            &nbsp;|&nbsp;
            <i class="bi bi-geo-alt-fill"></i> ${data.location}
        `);
        $('#modal-total-peserta').text(data.totalCount);

        // Populate stats
        const statsHtml = `
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                        <h5 class="mt-2 mb-0">${data.collectiveCount}</h5>
                        <small class="text-muted">Kolektif</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="bi bi-person-fill text-success" style="font-size: 2rem;"></i>
                        <h5 class="mt-2 mb-0">${data.mandiriCount}</h5>
                        <small class="text-muted">Mandiri</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-body">
                        <small class="text-muted d-block">Skema Sertifikasi</small>
                        <div class="fw-bold text-truncate" title="${data.skemas}">${data.skemas}</div>
                    </div>
                </div>
            </div>
        `;
        $('#modal-stats').html(statsHtml);

        // Show notes if exists
        if (data.notes && data.notes.trim() !== '') {
            $('#modal-notes-text').text(data.notes);
            $('#modal-notes').show();
        } else {
            $('#modal-notes').hide();
        }

        // Populate participants table
        let participantsHtml = '';
        if (data.participants && data.participants.length > 0) {
            data.participants.forEach((p, index) => {
                participantsHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td><strong>#${p.regNo}</strong></td>
                        <td><strong>${escapeHtml(p.name)}</strong></td>
                        <td><small>${escapeHtml(p.email)}</small></td>
                        <td><small>${escapeHtml(p.skema)}</small></td>
                        <td>
                            <span class="badge bg-${p.isCollective ? 'primary' : 'success'}">
                                <i class="bi bi-${p.isCollective ? 'people' : 'person'}"></i>
                                ${p.isCollective ? 'Kolektif' : 'Mandiri'}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-${p.statusBadge}">${escapeHtml(p.status)}</span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info" onclick="viewSchedule(${p.scheduleId})" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-warning" onclick="editSchedule(${p.scheduleId})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-danger" onclick="deleteSchedule(${p.scheduleId})" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        } else {
            participantsHtml = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i> Tidak ada peserta
                    </td>
                </tr>
            `;
        }
        $('#modal-participants-tbody').html(participantsHtml);

        // Populate footer
        $('#modal-created-at').html(`
            <i class="bi bi-calendar-check"></i> Dibuat: ${data.createdAt}
        `);

        // Show modal
        $('#scheduleDetailModal').modal('show');
        
    } catch (error) {
        console.error('Error parsing schedule data:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal memuat data jadwal: ' + error.message,
            confirmButtonText: 'OK'
        });
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? String(text).replace(/[&<>"']/g, m => map[m]) : '';
}

function printScheduleFromModal() {
    if (!currentScheduleData) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Data schedule tidak ditemukan',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Create printable content
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Daftar Hadir Asesmen - ${currentScheduleData.dateFormatted}</title>
            <meta charset="UTF-8">
            <style>
                @page { 
                    size: A4 landscape;
                    margin: 15mm; 
                }
                body { 
                    margin: 0; 
                    padding: 20px; 
                    font-family: Arial, sans-serif; 
                    font-size: 12px;
                }
                h1 { 
                    font-size: 18px; 
                    text-align: center; 
                    margin-bottom: 5px;
                }
                h2 {
                    font-size: 14px;
                    text-align: center;
                    margin-top: 0;
                    font-weight: normal;
                    color: #666;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 15px; 
                }
                th, td { 
                    border: 1px solid #000; 
                    padding: 8px; 
                    text-align: left; 
                }
                th { 
                    background-color: #f0f0f0; 
                    font-weight: bold;
                }
                .info { 
                    margin-bottom: 20px; 
                    line-height: 1.6;
                }
                .info p {
                    margin: 5px 0;
                }
                @media print {
                    body { 
                        padding: 10px;
                    }
                    button {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <h1>DAFTAR HADIR ASESMEN</h1>
            <h2>Lembaga Sertifikasi Profesi</h2>
            
            <div class="info">
                <table style="border: none; margin-bottom: 20px;">
                    <tr style="border: none;">
                        <td style="border: none; width: 150px;"><strong>Tanggal</strong></td>
                        <td style="border: none;">: ${currentScheduleData.dateFormatted}</td>
                    </tr>
                    <tr style="border: none;">
                        <td style="border: none;"><strong>Waktu</strong></td>
                        <td style="border: none;">: ${currentScheduleData.startTime} - ${currentScheduleData.endTime} WIB</td>
                    </tr>
                    <tr style="border: none;">
                        <td style="border: none;"><strong>Lokasi</strong></td>
                        <td style="border: none;">: ${escapeHtml(currentScheduleData.location)}</td>
                    </tr>
                    <tr style="border: none;">
                        <td style="border: none;"><strong>Total Peserta</strong></td>
                        <td style="border: none;">: ${currentScheduleData.totalCount} orang</td>
                    </tr>
                </table>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th style="width: 100px;">No. Reg</th>
                        <th>Nama Lengkap</th>
                        <th style="width: 200px;">Skema</th>
                        <th style="width: 100px;">Jenis</th>
                        <th style="width: 150px;">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    ${currentScheduleData.participants.map((p, index) => `
                        <tr>
                            <td style="text-align: center;">${index + 1}</td>
                            <td>#${p.regNo}</td>
                            <td>${escapeHtml(p.name)}</td>
                            <td>${escapeHtml(p.skema)}</td>
                            <td style="text-align: center;">${p.isCollective ? 'Kolektif' : 'Mandiri'}</td>
                            <td>&nbsp;</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
            
            ${currentScheduleData.notes ? `
                <div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-left: 4px solid #0d6efd;">
                    <strong>Catatan:</strong> ${escapeHtml(currentScheduleData.notes)}
                </div>
            ` : ''}
            
            <div style="margin-top: 30px;">
                <table style="border: none; width: 100%;">
                    <tr style="border: none;">
                        <td style="border: none; width: 50%; text-align: center;">
                            <p>Mengetahui,</p>
                            <p style="margin-top: 80px; border-top: 1px solid #000; display: inline-block; padding-top: 5px; min-width: 200px;">
                                Pimpinan TUK
                            </p>
                        </td>
                        <td style="border: none; width: 50%; text-align: center;">
                            <p>Asesor,</p>
                            <p style="margin-top: 80px; border-top: 1px solid #000; display: inline-block; padding-top: 5px; min-width: 200px;">
                                Nama Asesor
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <script>
                window.onload = function() {
                    window.print();
                };
            <\/script>
        </body>
        </html>
    `;
    
    // Open print window
    const printWindow = window.open('', '_blank', 'width=1000,height=700');
    if (printWindow) {
        printWindow.document.write(printContent);
        printWindow.document.close();
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal membuka jendela cetak. Pastikan popup tidak diblokir.',
            confirmButtonText: 'OK'
        });
    }
}

function exportScheduleFromModal() {
    if (!currentScheduleData) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Data schedule tidak ditemukan',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Export ke Excel?',
        html: `
            <p class="mb-2">Data yang akan di-export:</p>
            <div class="text-start" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
                <small>
                    <strong>Tanggal:</strong> ${currentScheduleData.dateFormatted}<br>
                    <strong>Waktu:</strong> ${currentScheduleData.startTime} - ${currentScheduleData.endTime} WIB<br>
                    <strong>Lokasi:</strong> ${escapeHtml(currentScheduleData.location)}<br>
                    <strong>Total:</strong> ${currentScheduleData.totalCount} peserta
                </small>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-download"></i> Ya, Export!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#28a745',
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                html: 'Sedang membuat file Excel',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Get the first schedule ID from the group
            const scheduleId = currentScheduleData.scheduleIds && currentScheduleData.scheduleIds.length > 0 
                ? currentScheduleData.scheduleIds[0] 
                : null;

            if (!scheduleId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Schedule ID tidak ditemukan',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/tuk/schedules/export/${scheduleId}`;

            // CSRF Token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrfInput);

            // Group data
            const groupInput = document.createElement('input');
            groupInput.type = 'hidden';
            groupInput.name = 'group_data';
            groupInput.value = currentScheduleData.groupKey;
            form.appendChild(groupInput);

            // Submit form
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            // Show success message after delay
            setTimeout(() => {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'File Excel berhasil diunduh',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 1500);
        }
    });
}

function viewSchedule(id) {
    if (!id) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Schedule ID tidak valid',
            confirmButtonText: 'OK'
        });
        return;
    }

    $.ajax({
        url: `/tuk/schedules/${id}/view`,
        method: 'GET',
        beforeSend: function() {
            Swal.fire({
                title: 'Loading...',
                text: 'Memuat data jadwal',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close();
            if (response.success) {
                const data = response.schedule;
                $('#view-reg-no').text('#' + data.id);
                $('#view-asesi-name').text(data.asesmen_name || 'N/A');
                $('#view-skema').text(data.skema || '-');
                $('#view-status').text(data.status || '-').removeClass().addClass('badge bg-primary');
                $('#view-date').text(data.assessment_date || '-');
                $('#view-time').text((data.start_time || '-') + ' - ' + (data.end_time || '-'));
                $('#view-location').text(data.location || '-');
                $('#view-notes').text(data.notes || 'Tidak ada catatan');
                $('#viewScheduleModal').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Gagal memuat data jadwal',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            const errorMessage = xhr.responseJSON?.message || 'Gagal memuat data jadwal';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
        }
    });
}

function editSchedule(id) {
    if (!id) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Schedule ID tidak valid',
            confirmButtonText: 'OK'
        });
        return;
    }

    $.ajax({
        url: `/tuk/schedules/${id}/edit`,
        method: 'GET',
        beforeSend: function() {
            Swal.fire({
                title: 'Loading...',
                text: 'Memuat data jadwal',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            Swal.close();
            if (response.success) {
                const data = response.schedule;
                $('#edit-schedule-id').val(data.id);
                $('#edit-asesi-name').text(data.asesmen_names || data.asesmen_name || 'N/A');
                $('#edit-assessment-date').val(data.assessment_date || '');
                $('#edit-start-time').val(data.start_time || '');
                $('#edit-end-time').val(data.end_time || '');
                $('#edit-location').val(data.location || '');
                $('#edit-notes').val(data.notes || '');
                $('#editScheduleModal').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Gagal memuat data jadwal',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            const errorMessage = xhr.responseJSON?.message || 'Gagal memuat data jadwal';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
        }
    });
}

function deleteSchedule(id) {
    if (!id) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Schedule ID tidak valid',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Hapus Jadwal?',
        html: `
            <p>Apakah Anda yakin ingin menghapus jadwal ini?</p>
            <small class="text-danger">Status asesi akan dikembalikan ke "Sudah Bayar"</small>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/tuk/schedules/${id}/delete-ajax`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    _method: 'DELETE'
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Sedang menghapus jadwal',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message || 'Jadwal berhasil dihapus',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: response.message || 'Gagal menghapus jadwal',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'Gagal menghapus jadwal';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}
</script>
@endpush