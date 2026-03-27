@extends('layouts.app')

@section('title', 'Pendaftaran Kolektif')
@section('page-title', 'Pendaftaran Asesi Kolektif')

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Form Pendaftaran Kolektif Asesi</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Pendaftaran Kolektif</strong><br>
                    Daftarkan beberapa asesi sekaligus. Sistem akan otomatis membuat akun untuk setiap peserta.
                    Peserta dapat login dengan password default <strong>password123</strong> dan wajib menggantinya saat pertama login.
                </div>

                <form method="POST" action="{{ route('tuk.collective.store') }}" id="collective-form">
                    @csrf

                    {{-- Hidden: payment_phases selalu single --}}
                    <input type="hidden" name="payment_phases" value="single">

                    <!-- Pilih Skema -->
                    <div class="mb-4">
                        <label class="form-label">Skema Sertifikasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('skema_id') is-invalid @enderror" name="skema_id" required>
                            <option value="">Pilih Skema</option>
                            @php
                                $skemas = \App\Models\Skema::where('is_active', true)->get();
                            @endphp
                            @foreach($skemas as $skema)
                            <option value="{{ $skema->id }}" {{ old('skema_id') == $skema->id ? 'selected' : '' }}>
                                {{ $skema->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('skema_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Asesmen -->
                    <div class="mb-4">
                        <label class="form-label">Tanggal Asesmen <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('preferred_date') is-invalid @enderror"
                            name="preferred_date"
                            value="{{ old('preferred_date') }}"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        <small class="text-muted">Tanggal perkiraan untuk semua peserta dalam batch ini</small>
                        @error('preferred_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Pelatihan -->
                    <div class="mb-4">
                        <label class="form-label">Pelatihan <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="training-option" onclick="selectTraining(false)" id="option-no">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="training_flag" value="0" id="training-no" checked required>
                                        <div class="ms-3 flex-grow-1">
                                            <label for="training-no" class="form-label fw-bold mb-1" style="cursor: pointer;">
                                                <i class="bi bi-x-circle text-danger"></i> Tanpa Pelatihan
                                            </label>
                                            <p class="text-muted small mb-0">
                                                Semua peserta langsung asesmen tanpa pelatihan
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="training-option" onclick="selectTraining(true)" id="option-yes">
                                    <div class="d-flex align-items-start">
                                        <input type="radio" name="training_flag" value="1" id="training-yes" required>
                                        <div class="ms-3 flex-grow-1">
                                            <label for="training-yes" class="form-label fw-bold mb-1" style="cursor: pointer;">
                                                <i class="bi bi-check-circle text-success"></i> Dengan Pelatihan
                                            </label>
                                            <p class="text-muted small mb-2">
                                                Semua peserta ikut pelatihan sebelum asesmen
                                            </p>
                                            <div class="alert alert-warning mb-0 py-2">
                                                <small>
                                                    <i class="bi bi-info-circle-fill"></i>
                                                    <strong>Biaya Tambahan:</strong>
                                                    <span class="price-badge ms-2">Rp 1.500.000 / peserta</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('training_flag')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Participants List -->
                    <div id="participants-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Daftar Peserta</h6>
                            <div>
                                <button type="button" class="btn btn-sm btn-success me-2" data-bs-toggle="modal"
                                    data-bs-target="#uploadModal">
                                    <i class="bi bi-file-earmark-excel"></i> Upload Excel/CSV
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="add-participant">
                                    <i class="bi bi-plus-circle"></i> Tambah Manual
                                </button>
                            </div>
                        </div>

                        <div class="alert alert-success" id="import-summary" style="display: none;">
                            <i class="bi bi-check-circle"></i>
                            <strong>Data berhasil diimport!</strong>
                            <span id="import-count"></span> peserta telah ditambahkan dari file.
                            <button type="button" class="btn-close float-end" onclick="$('#import-summary').hide()"></button>
                        </div>

                        <div id="participants-list"></div>
                    </div>

                    <!-- Upload Modal -->
                    <div class="modal fade" id="uploadModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">
                                        <i class="bi bi-file-earmark-excel"></i> Upload Data Peserta
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle"></i> Petunjuk Upload:</h6>
                                        <ol class="mb-2">
                                            <li>Download template Excel/CSV terlebih dahulu</li>
                                            <li>Isi data peserta sesuai format (Nama Lengkap & Email)</li>
                                            <li>Upload file yang sudah diisi</li>
                                            <li>Sistem akan otomatis memvalidasi dan menambahkan peserta</li>
                                        </ol>
                                        <strong>Format yang diterima:</strong> .xlsx, .xls, .csv
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-body text-center">
                                            <h6>Step 1: Download Template</h6>
                                            <p class="text-muted small">Template Excel dengan format yang sudah sesuai</p>
                                            <div class="btn-group">
                                                <a href="{{ route('tuk.collective.download-template', 'excel') }}"
                                                    class="btn btn-outline-success">
                                                    <i class="bi bi-file-earmark-excel"></i> Download Template Excel
                                                </a>
                                                <a href="{{ route('tuk.collective.download-template', 'csv') }}"
                                                    class="btn btn-outline-primary">
                                                    <i class="bi bi-file-earmark-text"></i> Download Template CSV
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Step 2: Upload File</h6>
                                            <div class="mb-3">
                                                <label class="form-label">Pilih File Excel/CSV</label>
                                                <input type="file" class="form-control" id="participant-file"
                                                    accept=".xlsx,.xls,.csv">
                                                <small class="text-muted">Format: .xlsx, .xls, .csv (Max: 5MB)</small>
                                            </div>

                                            <div id="preview-area" style="display: none;">
                                                <h6 class="mt-3">Preview Data:</h6>
                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Nama Lengkap</th>
                                                                <th>Email</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="preview-tbody"></tbody>
                                                    </table>
                                                </div>
                                                <div class="alert alert-warning" id="validation-errors" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-success" id="import-btn" disabled>
                                        <i class="bi bi-check-circle"></i> Import Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="card bg-light mt-4">
                        <div class="card-body">
                            <h6>Ringkasan Pendaftaran:</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="200">Total Peserta:</td>
                                    <td><strong id="total-participants">0</strong> orang</td>
                                </tr>
                            </table>
                            <div class="alert alert-warning mt-3 mb-0">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    Biaya asesmen akan ditentukan oleh Admin LSP setelah semua peserta mengisi data dan diverifikasi TUK.
                                    Pembayaran dilakukan secara manual (Transfer Bank / QRIS) di akhir proses.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-save"></i> Daftarkan Peserta
                        </button>
                        <a href="{{ route('tuk.asesi') }}" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .training-option {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        background: white;
    }
    .training-option:hover {
        border-color: #28a745;
        background-color: #f8f9fa;
    }
    .training-option.selected {
        border-color: #28a745;
        background-color: #d4edda;
    }
    .training-option input[type="radio"] {
        width: 18px;
        height: 18px;
        margin-top: 5px;
    }
    .price-badge {
        background-color: #fff3cd;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: bold;
    }
    .participant-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    let participantCount = 0;
    let importedData = [];

    $(document).ready(function () {
        addParticipant();
        selectTraining(false);

        $('#participant-file').change(function (e) {
            const file = e.target.files[0];
            if (!file) return;

            Swal.fire({
                title: 'Memproses...',
                html: 'Membaca file Excel/CSV',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet);
                    validateAndPreview(jsonData);
                    Swal.close();
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal membaca file: ' + error.message });
                }
            };
            reader.readAsArrayBuffer(file);
        });

        $('#import-btn').click(function () {
            importData();
            $('#uploadModal').modal('hide');
        });

        $('#add-participant').click(function () {
            addParticipant();
        });

        $(document).on('click', '.remove-participant', function () {
            $(this).closest('.participant-item').remove();
            updateParticipantNumbers();
            updateSummary();
        });
    });

    function validateAndPreview(data) {
        if (data.length === 0) {
            Swal.fire({ icon: 'warning', title: 'File Kosong', text: 'File tidak mengandung data peserta' });
            return;
        }

        let validCount = 0;
        let errors = [];
        let previewHtml = '';
        importedData = [];

        data.forEach((row, index) => {
            const rowNumber = index + 2;
            const name  = row['Nama Lengkap'] || row['nama_lengkap'] || '';
            const email = row['Email'] || row['email'] || '';
            let rowErrors = [];

            if (!name)  rowErrors.push('Nama wajib diisi');
            if (!email) rowErrors.push('Email wajib diisi');
            else if (!isValidEmail(email)) rowErrors.push('Format email tidak valid');

            const isValid = rowErrors.length === 0;
            if (isValid) { validCount++; importedData.push({ name, email }); }

            const statusClass = isValid ? 'success' : 'danger';
            const statusIcon  = isValid ? 'check-circle' : 'x-circle';
            const statusText  = isValid ? 'Valid' : rowErrors.join(', ');

            previewHtml += `
                <tr class="table-${statusClass}">
                    <td>${index + 1}</td>
                    <td>${name  || '<em class="text-muted">Kosong</em>'}</td>
                    <td>${email || '<em class="text-muted">Kosong</em>'}</td>
                    <td><i class="bi bi-${statusIcon}"></i> ${statusText}</td>
                </tr>`;

            if (!isValid) errors.push({ row: rowNumber, errors: rowErrors });
        });

        $('#preview-tbody').html(previewHtml);
        $('#preview-area').show();

        if (errors.length > 0) {
            let errorHtml = '<strong>Data tidak valid pada baris:</strong><ul>';
            errors.forEach(err => { errorHtml += `<li>Baris ${err.row}: ${err.errors.join(', ')}</li>`; });
            errorHtml += '</ul>';
            $('#validation-errors').html(errorHtml).show();
            $('#import-btn').prop('disabled', true);
        } else {
            $('#validation-errors').hide();
            $('#import-btn').prop('disabled', false);
        }

        $('#import-btn').html(`<i class="bi bi-check-circle"></i> Import ${validCount} Peserta Valid`);
    }

    function importData() {
        $('#participants-list').empty();
        participantCount = 0;

        importedData.forEach(p => addParticipant(p.name, p.email));

        $('#import-summary').show();
        $('#import-count').text(importedData.length);
        $('#participant-file').val('');
        $('#preview-area').hide();
        $('#import-btn').prop('disabled', true);

        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: `${importedData.length} peserta berhasil diimport`,
            timer: 2000,
            showConfirmButton: false
        });
    }

    function addParticipant(name = '', email = '') {
        const idx = participantCount;
        const html = `
            <div class="participant-item card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Peserta #<span class="participant-number">${idx + 1}</span></h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-participant">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="participants[${idx}][name]"
                                   value="${name}" placeholder="Nama lengkap peserta" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="participants[${idx}][email]"
                                   value="${email}" placeholder="email@contoh.com" required>
                        </div>
                    </div>
                </div>
            </div>`;

        $('#participants-list').append(html);
        participantCount++;
        updateParticipantNumbers();
        updateSummary();
    }

    function updateParticipantNumbers() {
        const items = $('.participant-item');
        items.each(function (index) {
            $(this).find('.participant-number').text(index + 1);
        });
        // Sembunyikan tombol hapus jika hanya 1 peserta
        $('.remove-participant').toggle(items.length > 1);
    }

    function updateSummary() {
        $('#total-participants').text($('.participant-item').length);
    }

    function selectTraining(withTraining) {
        $('.training-option').removeClass('selected');
        if (withTraining) {
            $('#option-yes').addClass('selected');
            $('#training-yes').prop('checked', true);
        } else {
            $('#option-no').addClass('selected');
            $('#training-no').prop('checked', true);
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
</script>
@endpush