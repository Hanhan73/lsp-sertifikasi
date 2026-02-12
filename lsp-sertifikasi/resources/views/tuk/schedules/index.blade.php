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
                                <input type="date" class="form-control" name="assessment_date" required
                                    min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Lokasi Asesmen <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="location"
                                    placeholder="Contoh: Ruang Asesmen TUK, Gedung A Lt. 2" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" name="notes" rows="3"
                                    placeholder="Catatan tambahan untuk peserta asesmen"></textarea>
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
        // Group by date + time + location
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
                        
                        // Count collective vs mandiri
                        $collectiveCount = $schedules->where('asesmen.is_collective', true)->count();
                        $mandiriCount = $schedules->where('asesmen.is_collective', false)->count();
                        
                        // Get unique skemas
                        $skemas = $schedules->pluck('asesmen.skema.name')->unique()->filter();
                        $skemaDisplay = $skemas->count() > 1 
                            ? $skemas->first() . ' +' . ($skemas->count() - 1) 
                            : $skemas->first();
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
                            <span class="badge bg-info">{{ $skemaDisplay }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6">{{ $schedules->count() }}</span>
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
    $collectiveCount = $schedules->where('asesmen.is_collective', true)->count();
    $mandiriCount = $schedules->where('asesmen.is_collective', false)->count();
    $skemas = $schedules->pluck('asesmen.skema.name')->unique()->filter()->implode(', ');
    
    // Build participants array
    $participants = [];
    foreach($schedules->sortBy('asesmen.full_name') as $schedule) {
        $participants[] = [
            'id' => $schedule->id,
            'regNo' => $schedule->asesmen->id,
            'name' => $schedule->asesmen->full_name ?? $schedule->asesmen->user->name,
            'email' => $schedule->asesmen->user->email ?? $schedule->asesmen->email ?? '-',
            'skema' => $schedule->asesmen->skema->name ?? '-',
            'isCollective' => $schedule->asesmen->is_collective,
            'batchId' => $schedule->asesmen->collective_batch_id ?? '-',
            'status' => $schedule->asesmen->status_label,
            'statusBadge' => $schedule->asesmen->status_badge,
        ];
    }
@endphp
<script type="application/json" id="schedule-data-{{ md5($groupKey) }}">
{!! json_encode([
    'groupKey' => $groupKey,
    'date' => $date,
    'dateFormatted' => \Carbon\Carbon::parse($date)->isoFormat('dddd, D MMMM Y'),
    'startTime' => $startTime,
    'endTime' => $endTime,
    'location' => $location,
    'notes' => $firstSchedule->notes ?? '',
    'createdAt' => $firstSchedule->created_at->diffForHumans(),
    'collectiveCount' => $collectiveCount,
    'mandiriCount' => $mandiriCount,
    'totalCount' => $schedules->count(),
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

    // Form validation
    $('#batch-schedule-form').submit(function(e) {
        const checked = $('.asesmen-checkbox:checked').length;

        if (checked === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Asesi',
                text: 'Silakan pilih minimal 1 asesi untuk dijadwalkan',
                confirmButtonText: 'OK'
            });
            return false;
        }

        e.preventDefault();
        Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Penjadwalan',
            text: `Apakah Anda yakin ingin membuat jadwal untuk ${checked} asesi?`,
            showCancelButton: true,
            confirmButtonText: 'Ya, Buat Jadwal',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Edit Schedule Form Submit
    $('#editScheduleForm').submit(function(e) {
        e.preventDefault();

        const scheduleId = $('#edit-schedule-id').val();
        const formData = {
            assessment_date: $('#edit-assessment-date').val(),
            start_time: $('#edit-start-time').val(),
            end_time: $('#edit-end-time').val(),
            location: $('#edit-location').val(),
            notes: $('#edit-notes').val(),
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT'
        };

        $.ajax({
            url: `/tuk/schedules/${scheduleId}/update-ajax`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#editScheduleModal').modal('hide');
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Gagal mengupdate jadwal',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});

// Show Schedule Detail Modal
function showScheduleDetail(scheduleId) {
    // Get data from hidden script tag
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
                        <div class="fw-bold">${data.skemas}</div>
                    </div>
                </div>
            </div>
        `;
        $('#modal-stats').html(statsHtml);

        // Show notes if exists
        if (data.notes) {
            $('#modal-notes-text').text(data.notes);
            $('#modal-notes').show();
        } else {
            $('#modal-notes').hide();
        }

        // Populate participants table
        let participantsHtml = '';
        data.participants.forEach((p, index) => {
            participantsHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td><strong>#${p.regNo}</strong></td>
                    <td><strong>${p.name}</strong></td>
                    <td><small>${p.email}</small></td>
                    <td><small>${p.skema}</small></td>
                    <td>
                        <span class="badge bg-${p.isCollective ? 'primary' : 'success'}">
                            <i class="bi bi-${p.isCollective ? 'people' : 'person'}"></i>
                            ${p.isCollective ? 'Kolektif' : 'Mandiri'}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${p.statusBadge}">${p.status}</span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info" onclick="viewSchedule(${p.id})" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-warning" onclick="editSchedule(${p.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger" onclick="deleteSchedule(${p.id})" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        $('#modal-participants-tbody').html(participantsHtml);

        // Show created at
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

// Print from modal
function printScheduleFromModal() {
    Swal.fire({
        icon: 'info',
        title: 'Fitur Cetak',
        text: 'Fitur cetak daftar hadir sedang dalam pengembangan',
        confirmButtonText: 'OK'
    });
}

// Export from modal
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
            <div class="text-start">
                <small>
                    <strong>Tanggal:</strong> ${currentScheduleData.dateFormatted}<br>
                    <strong>Waktu:</strong> ${currentScheduleData.startTime} - ${currentScheduleData.endTime}<br>
                    <strong>Lokasi:</strong> ${currentScheduleData.location}<br>
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
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                html: 'Sedang membuat file Excel',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Find the schedule ID from the groupKey
            const scheduleId = Object.keys(window.scheduleGroupData).find(
                key => window.scheduleGroupData[key] === currentScheduleData.groupKey
            );

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/tuk/schedules/export/${scheduleId}`;

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrfInput);

            // Add group data
            const groupInput = document.createElement('input');
            groupInput.type = 'hidden';
            groupInput.name = 'group_data';
            groupInput.value = currentScheduleData.groupKey;
            form.appendChild(groupInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            // Close loading after a delay
            setTimeout(() => {
                Swal.close();
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'File Excel berhasil diunduh',
                    timer: 2000,
                    showConfirmButton: false
                });
            }, 2000);
        }
    });
}

// View Schedule
function viewSchedule(id) {
    $.ajax({
        url: `/tuk/schedules/${id}/view`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.schedule;

                $('#view-reg-no').text('#' + data.id);
                $('#view-asesi-name').text(data.asesmen_name);
                $('#view-skema').text(data.skema);
                $('#view-status').text(data.status).removeClass().addClass('badge bg-primary');
                $('#view-date').text(data.assessment_date);
                $('#view-time').text(data.start_time + ' - ' + data.end_time);
                $('#view-location').text(data.location);
                $('#view-notes').text(data.notes || 'Tidak ada catatan');

                $('#viewScheduleModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat data jadwal',
                confirmButtonText: 'OK'
            });
        }
    });
}

// Edit Schedule
function editSchedule(id) {
    $.ajax({
        url: `/tuk/schedules/${id}/edit`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.schedule;

                $('#edit-schedule-id').val(data.id);
                $('#edit-asesi-name').text(data.asesmen_name);
                $('#edit-assessment-date').val(data.assessment_date);
                $('#edit-start-time').val(data.start_time);
                $('#edit-end-time').val(data.end_time);
                $('#edit-location').val(data.location);
                $('#edit-notes').val(data.notes);

                $('#editScheduleModal').modal('show');
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memuat data jadwal',
                confirmButtonText: 'OK'
            });
        }
    });
}

// Delete Schedule
function deleteSchedule(id) {
    Swal.fire({
        title: 'Hapus Jadwal?',
        text: 'Status asesi akan dikembalikan ke "Sudah Bayar"',
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
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'Gagal menghapus jadwal',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}
</script>
@endpush