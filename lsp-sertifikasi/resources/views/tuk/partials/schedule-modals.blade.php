<!-- View Schedule Modal -->
<div class="modal fade" id="viewScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-eye"></i> Detail Jadwal Asesmen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">No. Registrasi</label>
                            <p class="fw-bold" id="view-reg-no">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Nama Asesi</label>
                            <p class="fw-bold" id="view-asesi-name">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Skema</label>
                            <p id="view-skema">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Status</label>
                            <p><span class="badge" id="view-status">-</span></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Tanggal Asesmen</label>
                            <p class="fw-bold" id="view-date">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Waktu</label>
                            <p id="view-time">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Lokasi</label>
                            <p id="view-location">-</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Catatan</label>
                            <p id="view-notes" class="text-muted fst-italic">-</p>
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

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editScheduleForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-schedule-id">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Edit Jadwal Asesmen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Asesi Info (Read-only) -->
                    <div class="alert alert-info">
                        <strong>Asesi:</strong> <span id="edit-asesi-name">-</span>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Asesmen <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit-assessment-date" name="assessment_date"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="edit-start-time" name="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="edit-end-time" name="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" id="edit-notes" name="notes" rows="3"></textarea>
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