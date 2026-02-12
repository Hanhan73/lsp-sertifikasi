<!-- View Schedule Modal -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-eye"></i> Detail Jadwal Asesmen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewScheduleContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editScheduleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Edit Jadwal Asesmen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="editScheduleContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewSchedule(id) {
    $('#viewScheduleModal').modal('show');
    
    $.ajax({
        url: `/tuk/schedules/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const s = response.schedule;
                $('#viewScheduleContent').html(`
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Nama Asesi</label>
                            <p class="form-control-plaintext">${s.asesmen_name}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Skema</label>
                            <p class="form-control-plaintext">${s.skema}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Tanggal Asesmen</label>
                            <p class="form-control-plaintext">${s.assessment_date}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Waktu Mulai</label>
                            <p class="form-control-plaintext">${s.start_time}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Waktu Selesai</label>
                            <p class="form-control-plaintext">${s.end_time}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Lokasi</label>
                            <p class="form-control-plaintext">${s.location}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-primary">${s.status}</span>
                            </p>
                        </div>
                        ${s.notes ? `
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Catatan</label>
                            <p class="form-control-plaintext">${s.notes}</p>
                        </div>
                        ` : ''}
                    </div>
                `);
            }
        },
        error: function() {
            $('#viewScheduleContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> Gagal memuat data jadwal
                </div>
            `);
        }
    });
}

function editSchedule(id) {
    $('#editScheduleModal').modal('show');
    
    $.ajax({
        url: `/tuk/schedules/${id}/edit`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const s = response.schedule;
                
                // Set form action
                $('#editScheduleForm').attr('action', `/tuk/schedules/${id}`);
                
                $('#editScheduleContent').html(`
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Asesi</label>
                        <input type="text" class="form-control" value="${s.asesmen_name}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Asesmen <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="assessment_date" 
                            value="${s.assessment_date}" min="${new Date().toISOString().split('T')[0]}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" 
                                value="${s.start_time}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" 
                                value="${s.end_time}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="location" 
                            value="${s.location}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="notes" rows="3">${s.notes || ''}</textarea>
                    </div>
                `);
            }
        },
        error: function() {
            $('#editScheduleContent').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i> Gagal memuat data jadwal
                </div>
            `);
        }
    });
}

function deleteSchedule(id) {
    Swal.fire({
        icon: 'warning',
        title: 'Hapus Jadwal?',
        text: 'Jadwal akan dihapus dan status asesi dikembalikan ke "Sudah Bayar"',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/tuk/schedules/${id}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>