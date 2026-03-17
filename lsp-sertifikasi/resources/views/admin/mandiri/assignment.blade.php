@extends('layouts.app')

@section('title', 'Assignment Asesi ke TUK')
@section('page-title', 'Assignment Asesi Mandiri ke TUK')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-building"></i> Assignment Asesi ke TUK
        </h5>
        <div>
            <span class="badge bg-primary">{{ $asesmens->count() }} Perlu Assignment</span>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Assignment ke TUK:</strong>
            <p class="mb-0 mt-2">
                Assign asesi mandiri ke TUK yang sudah memiliki jadwal untuk skema yang sesuai.
                Klik tombol "Detail" pada jadwal untuk melihat asesi yang terdaftar.
            </p>
        </div>

        @if($asesmens->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
            <h4 class="mt-3">Semua Asesi Sudah Di-Assign</h4>
            <p class="text-muted">Tidak ada asesi mandiri yang menunggu assignment saat ini.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No Reg</th>
                        <th>Nama</th>
                        <th>Skema</th>
                        <th>Biaya</th>
                        <th>Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesmens as $asesmen)
                    <tr>
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>
                            {{ $asesmen->full_name }}<br>
                            <small class="text-muted">{{ $asesmen->user->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $asesmen->skema->name }}</span>
                        </td>
                        <td>
                            <strong>Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</strong>
                        </td>
                        <td>
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i>
                                {{ $asesmen->admin_verified_at->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                onclick="showAssignModal({{ $asesmen->id }}, '{{ $asesmen->full_name }}', {{ $asesmen->skema_id }})"
                                data-bs-toggle="tooltip" title="Assign ke TUK">
                                <i class="bi bi-building"></i> Assign
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

<!-- TUK List with Schedules - UPDATED: Compact View -->
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> TUK dengan Jadwal Tersedia</h5>
    </div>
    <div class="card-body">
        @if($tuksWithSchedules->isEmpty())
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Belum ada TUK dengan jadwal tersedia.
        </div>
        @else
        <div class="accordion" id="tukAccordion">
            @foreach($tuksWithSchedules as $index => $tukData)
            @php
            $tuk = $tukData['tuk'];
            $schedules = $tukData['schedules'];
            $skemas = $tukData['skemas'];
            @endphp
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#tuk-{{ $tuk->id }}">
                        <strong>{{ $tuk->name }}</strong>
                        <span class="badge bg-info ms-2">{{ $schedules->count() }} Jadwal</span>
                        <span class="badge bg-success ms-2">{{ $skemas->count() }} Skema</span>
                    </button>
                </h2>
                <div id="tuk-{{ $tuk->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                    data-bs-parent="#tukAccordion">
                    <div class="accordion-body">
                        <div class="mb-3">
                            <strong>Alamat:</strong> {{ $tuk->address }}<br>
                            @if($tuk->manager_name)
                            <strong>Manager:</strong> {{ $tuk->manager_name }}<br>
                            @endif
                        </div>

                        <h6>Jadwal Tersedia:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Waktu</th>
                                        <th>Skema</th>
                                        <th>Lokasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->assessment_date->format('d/m/Y') }}</td>
                                        <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $schedule->skema->name }}
                                            </span>
                                        </td>
                                        <td>{{ $schedule->location }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info"
                                                onclick="showScheduleDetail({{ $schedule->id }}, '{{ $schedule->assessment_date->format('d/m/Y') }}', '{{ $schedule->start_time }} - {{ $schedule->end_time }}', '{{ $schedule->location }}', '{{ $schedule->skema->name }}', '{{ $schedule->asesmens->pluck('full_name')->join(', ') ?: 'Belum ada peserta' }}')"
                                                data-bs-toggle="tooltip" title="Lihat detail jadwal">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="assign-form">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-building"></i> Assign Asesi ke TUK & Jadwal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" id="asesi-info">
                        <!-- Will be filled by JS -->
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih TUK <span class="text-danger">*</span></label>
                        <select class="form-select" name="tuk_id" id="tuk-select" required>
                            <option value="">-- Pilih TUK --</option>
                            @foreach($tuksWithSchedules as $tukData)
                            @php
                            $tuk = $tukData['tuk'];
                            $skemas = $tukData['skemas'];
                            @endphp
                            <option value="{{ $tuk->id }}" data-skemas="{{ $skemas->pluck('id')->join(',') }}">
                                {{ $tuk->name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="tuk-info"></small>
                    </div>

                    <!-- Schedule Selection -->
                    <div class="mb-3" id="schedule-selection" style="display: none;">
                        <label class="form-label">Pilih Jadwal <span class="text-danger">*</span></label>
                        <select class="form-select" name="schedule_id" id="schedule-select" required>
                            <option value="">-- Pilih Jadwal --</option>
                        </select>
                        <small class="text-muted">Pilih jadwal yang akan digabung dengan asesi ini</small>

                        <!-- Schedule Details -->
                        <div id="schedule-details" class="mt-3" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="mb-2">Detail Jadwal:</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td width="100"><strong>Tanggal:</strong></td>
                                            <td id="detail-date">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Waktu:</strong></td>
                                            <td id="detail-time">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lokasi:</strong></td>
                                            <td id="detail-location">-</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Asesi Lain:</strong></td>
                                            <td id="detail-asesi">-</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="no-schedule-warning" style="display: none;">
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Tidak Ada Jadwal:</strong> TUK ini belum memiliki jadwal untuk skema ini.
                            Silakan pilih TUK lain atau minta TUK membuat jadwal terlebih dahulu.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Catatan assignment"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                        <i class="bi bi-check-circle"></i> Assign ke Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Schedule Detail Modal - NEW -->
<div class="modal fade" id="scheduleDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check"></i> Detail Jadwal Asesmen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-primary mb-3"><i class="bi bi-info-circle"></i> Informasi Jadwal</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="120"><strong>Tanggal:</strong></td>
                                        <td id="modal-date">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Waktu:</strong></td>
                                        <td id="modal-time">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Lokasi:</strong></td>
                                        <td id="modal-location">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Skema:</strong></td>
                                        <td><span class="badge bg-primary" id="modal-skema">-</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="text-success mb-3"><i class="bi bi-people"></i> Asesi Terdaftar</h6>
                                <div id="modal-asesi-list">
                                    <!-- Will be filled by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentAsesmenId = null;
let currentSkemaId = null;
let currentSchedules = [];

function showAssignModal(asesmenId, asesmenName, skemaId) {
    currentAsesmenId = asesmenId;
    currentSkemaId = skemaId;

    $('#asesi-info').html(`
        <strong>Asesi:</strong> ${asesmenName}<br>
        <strong>No Registrasi:</strong> #${asesmenId}
    `);

    $('#assign-form').attr('action', `/admin/mandiri/assign/${asesmenId}`);
    $('#tuk-select').val('').trigger('change');
    $('#schedule-select').html('<option value="">-- Pilih Jadwal --</option>');
    $('#schedule-selection').hide();
    $('#schedule-details').hide();
    $('#no-schedule-warning').hide();
    $('#tuk-info').text('');
    $('#submit-btn').prop('disabled', true);

    $('#assignModal').modal('show');
}

// NEW: Show schedule detail in modal
function showScheduleDetail(scheduleId, date, time, location, skema, asesiNames) {
    $('#modal-date').text(date);
    $('#modal-time').text(time);
    $('#modal-location').text(location);
    $('#modal-skema').text(skema);

    // Parse asesi names
    if (asesiNames && asesiNames !== 'Belum ada peserta') {
        const names = asesiNames.split(', ');
        let html = '<ul class="list-group">';
        names.forEach(name => {
            html += `<li class="list-group-item"><i class="bi bi-person"></i> ${name}</li>`;
        });
        html += '</ul>';
        $('#modal-asesi-list').html(html);
    } else {
        $('#modal-asesi-list').html('<p class="text-muted">Belum ada peserta terdaftar</p>');
    }

    $('#scheduleDetailModal').modal('show');
}

// When TUK is selected, fetch schedules
$('#tuk-select').on('change', function() {
    const tukId = $(this).val();
    const selectedOption = $(this).find('option:selected');
    const tukSkemas = selectedOption.data('skemas');

    $('#schedule-selection').hide();
    $('#schedule-details').hide();
    $('#no-schedule-warning').hide();
    $('#submit-btn').prop('disabled', true);
    $('#schedule-select').html('<option value="">-- Pilih Jadwal --</option>');

    if (!tukId) {
        $('#tuk-info').text('');
        return;
    }

    // Check if TUK has this skema
    if (tukSkemas) {
        const skemaArray = String(tukSkemas).split(',').map(Number);
        const hasSkema = skemaArray.includes(currentSkemaId);

        if (!hasSkema) {
            $('#tuk-info').html(
                '<i class="bi bi-exclamation-triangle text-warning"></i> TUK ini tidak memiliki jadwal untuk skema ini'
            );
            $('#no-schedule-warning').show();
            return;
        }
    }

    // Fetch schedules for this TUK and skema
    $('#tuk-info').html('<i class="bi bi-hourglass-split"></i> Memuat jadwal...');

    $.ajax({
        url: `/admin/mandiri/tuk/${tukId}/schedules/${currentSkemaId}`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.schedules.length > 0) {
                currentSchedules = response.schedules;

                $('#tuk-info').html(
                    `<i class="bi bi-check-circle text-success"></i> ${response.schedules.length} jadwal tersedia`
                );

                // Populate schedule dropdown
                response.schedules.forEach(function(schedule) {
                    $('#schedule-select').append(
                        `<option value="${schedule.id}" 
                                 data-date="${schedule.assessment_date_formatted}" 
                                 data-time="${schedule.start_time} - ${schedule.end_time}"
                                 data-location="${schedule.location}"
                                 data-count="${schedule.asesmens_count}">
                            ${schedule.assessment_date_formatted} - ${schedule.start_time} (${schedule.location}) - ${schedule.asesmens_count} peserta
                        </option>`
                    );
                });

                $('#schedule-selection').show();
            } else {
                $('#tuk-info').html(
                    '<i class="bi bi-exclamation-triangle text-warning"></i> Belum ada jadwal tersedia'
                );
                $('#no-schedule-warning').show();
            }
        },
        error: function(xhr) {
            console.error('Error fetching schedules:', xhr);
            $('#tuk-info').html('<i class="bi bi-x-circle text-danger"></i> Error memuat jadwal');
            $('#no-schedule-warning').show();
        }
    });
});

// When schedule is selected, show details
$('#schedule-select').on('change', function() {
    const scheduleId = $(this).val();

    if (!scheduleId) {
        $('#schedule-details').hide();
        $('#submit-btn').prop('disabled', true);
        return;
    }

    // Find schedule data
    const schedule = currentSchedules.find(s => s.id == scheduleId);

    $('#detail-date').text(schedule.assessment_date_formatted);
    $('#detail-time').text(`${schedule.start_time} - ${schedule.end_time}`);
    $('#detail-location').text(schedule.location);
    $('#detail-asesi').text(`${schedule.asesmens_count} asesi: ${schedule.asesmens_names || 'Belum ada'}`);

    $('#schedule-details').show();
    $('#submit-btn').prop('disabled', false);
});

$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush