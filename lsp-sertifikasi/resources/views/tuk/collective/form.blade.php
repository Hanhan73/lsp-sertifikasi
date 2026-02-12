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
                    Daftarkan beberapa asesi sekaligus. Sistem akan otomatis membuat akun untuk setiap peserta dan
                    mengirimkan kredensial via email.
                </div>

                <form method="POST" action="{{ route('tuk.collective.store') }}" id="collective-form">
                    @csrf

                    <!-- Pilih Skema -->
                    <div class="mb-4">
                        <label class="form-label">Skema Sertifikasi <span class="text-danger">*</span></label>
                        <select class="form-select @error('skema_id') is-invalid @enderror" name="skema_id" required>
                            <option value="">Pilih Skema</option>
                            @php
                            $skemas = \App\Models\Skema::where('is_active', true)->get();
                            @endphp
                            @foreach($skemas as $skema)
                            <option value="{{ $skema->id }}" data-fee="{{ $skema->fee }}">
                                {{ $skema->name }} - Rp {{ number_format($skema->fee, 0, ',', '.') }}
                            </option>
                            @endforeach
                        </select>
                        @error('skema_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Payment Phases - UPDATED -->
                    <div class="mb-4">
                        <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i>
                            Pilih metode pembayaran kolektif untuk semua peserta dalam batch ini:
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check card p-3 mb-2 payment-phase-option"
                                    onclick="selectPhase('single')" id="phase-single">
                                    <input class="form-check-input" type="radio" name="payment_phases" value="single"
                                        id="phases_single" checked required>
                                    <label class="form-check-label" for="phases_single" style="cursor: pointer;">
                                        <strong><i class="bi bi-cash-stack text-success"></i> 1 Fase (Full
                                            Payment)</strong>
                                        <p class="text-muted mb-0 mt-1 small">
                                            Pembayaran <strong>100%</strong> dilakukan <strong>1 kali</strong> setelah
                                            Admin LSP memverifikasi semua peserta dan menentukan biaya.
                                        </p>
                                        <div class="alert alert-success mt-2 mb-0 py-2">
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check card p-3 mb-2 payment-phase-option"
                                    onclick="selectPhase('two_phase')" id="phase-two">
                                    <input class="form-check-input" type="radio" name="payment_phases" value="two_phase"
                                        id="phases_two" required>
                                    <label class="form-check-label" for="phases_two" style="cursor: pointer;">
                                        <strong><i class="bi bi-cash-coin text-primary"></i> 2 Fase (Split
                                            Payment)</strong>
                                        <p class="text-muted mb-0 mt-1 small">
                                            Pembayaran dibagi <strong>2 tahap</strong>:
                                        </p>
                                        <ul class="small text-muted mb-2 mt-1">
                                            <li><strong>Fase 1 (50%)</strong>: Setelah Admin verifikasi</li>
                                            <li><strong>Fase 2 (50%)</strong>: Setelah asesmen selesai, sebelum download
                                                sertifikat</li>
                                        </ul>
                                        <div class="alert alert-info mt-2 mb-0 py-2">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        @error('payment_phases')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <!-- Participants List -->
                    <!-- Participants List -->
                    <div id="participants-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6>Daftar Peserta</h6>
                            <div>
                                <!-- Upload Button -->
                                <button type="button" class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="bi bi-file-earmark-excel"></i> Upload Excel/CSV
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" id="add-participant">
                                    <i class="bi bi-plus-circle"></i> Tambah Manual
                                </button>
                            </div>
                        </div>

                        <!-- Import Summary (Hidden by default) -->
                        <div class="alert alert-success" id="import-summary" style="display: none;">
                            <i class="bi bi-check-circle"></i>
                            <strong>Data berhasil diimport!</strong>
                            <span id="import-count"></span> peserta telah ditambahkan dari file.
                            <button type="button" class="btn-close float-end" onclick="$('#import-summary').hide()"></button>
                        </div>

                        <div id="participants-list">
                            <!-- Participant items akan muncul di sini -->
                        </div>
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
                                    <!-- Instructions -->
                                    <div class="alert alert-info">
                                        <h6><i class="bi bi-info-circle"></i> Petunjuk Upload:</h6>
                                        <ol class="mb-2">
                                            <li>Download template Excel/CSV terlebih dahulu</li>
                                            <li>Isi data peserta sesuai format yang ada</li>
                                            <li>Upload file yang sudah diisi</li>
                                            <li>Sistem akan otomatis memvalidasi dan menambahkan peserta</li>
                                        </ol>
                                        <strong>Format yang diterima:</strong> .xlsx, .xls, .csv
                                    </div>

                                    <!-- Download Template -->
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

                                    <!-- Upload File -->
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Step 2: Upload File</h6>
                                            <div class="mb-3">
                                                <label class="form-label">Pilih File Excel/CSV</label>
                                                <input type="file" class="form-control" id="participant-file" 
                                                    accept=".xlsx,.xls,.csv">
                                                <small class="text-muted">
                                                    Format: .xlsx, .xls, .csv (Max: 5MB)
                                                </small>
                                            </div>
                                            
                                            <!-- Preview Area -->
                                            <div id="preview-area" style="display: none;">
                                                <h6 class="mt-3">Preview Data:</h6>
                                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                                    <table class="table table-sm table-bordered" id="preview-table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Nama Lengkap</th>
                                                                <th>Email</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="preview-tbody">
                                                            <!-- Preview rows akan muncul di sini -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="alert alert-warning" id="validation-errors" style="display: none;">
                                                    <!-- Validation errors akan muncul di sini -->
                                                </div>
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
                            <h6>Ringkasan:</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="200">Total Peserta:</td>
                                    <td><strong id="total-participants">1</strong> orang</td>
                                </tr>
                                <tr>
                                    <td>Metode Pembayaran:</td>
                                    <td><strong id="payment-method-text">1 Fase (Full Payment)</strong></td>
                                </tr>
                            </table>
                            <div class="alert alert-warning mt-3 mb-0" id="payment-note">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    Biaya akan ditentukan oleh Admin LSP setelah semua peserta mengisi data dan
                                    diverifikasi TUK.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
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
.payment-phase-option {
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid #dee2e6;
}

.payment-phase-option:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.payment-phase-option.selected {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

.payment-phase-option input[type="radio"] {
    width: 18px;
    height: 18px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
let participantCount = 0; // Start from 0
let importedData = [];

$(document).ready(function() {
    // File input change handler
    $('#participant-file').change(function(e) {
        const file = e.target.files[0];
        
        if (!file) {
            return;
        }
        
        // Show loading
        Swal.fire({
            title: 'Memproses...',
            html: 'Membaca file Excel/CSV',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Parse file using SheetJS
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                
                // Get first sheet
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet);
                
                // Validate and preview
                validateAndPreview(jsonData);
                
                Swal.close();
                
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal membaca file: ' + error.message
                });
            }
        };
        
        reader.readAsArrayBuffer(file);
    });
    
    // Import button handler
    $('#import-btn').click(function() {
        importData();
        $('#uploadModal').modal('hide');
    });
    
    // Existing add participant code...
    $('#add-participant').click(function() {
        addParticipant();
    });
    
    $(document).on('click', '.remove-participant', function() {
        $(this).closest('.participant-item').remove();
        participantCount--;
        updateParticipantNumbers();
        updateSummary();
    });
    
    $('input[name="payment_phases"]').change(function() {
        updatePaymentMethodText();
    });
    
    updateSummary();
    updatePaymentMethodText();
});

function validateAndPreview(data) {
    if (data.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'File Kosong',
            text: 'File tidak mengandung data peserta'
        });
        return;
    }
    
    let validCount = 0;
    let errors = [];
    let previewHtml = '';
    
    importedData = []; // Reset
    
    data.forEach((row, index) => {
        const rowNumber = index + 2; // +2 karena row 1 header
        const name = row['Nama Lengkap'] || row['nama_lengkap'] || '';
        const email = row['Email'] || row['email'] || '';
        
        let rowErrors = [];
        
        // Validate
        if (!name) {
            rowErrors.push('Nama wajib diisi');
        }
        
        if (!email) {
            rowErrors.push('Email wajib diisi');
        } else if (!isValidEmail(email)) {
            rowErrors.push('Format email tidak valid');
        }
        
        const isValid = rowErrors.length === 0;
        
        if (isValid) {
            validCount++;
            importedData.push({ name, email });
        }
        
        // Build preview row
        const statusClass = isValid ? 'success' : 'danger';
        const statusIcon = isValid ? 'check-circle' : 'x-circle';
        const statusText = isValid ? 'Valid' : rowErrors.join(', ');
        
        previewHtml += `
            <tr class="table-${statusClass}">
                <td>${rowNumber - 1}</td>
                <td>${name || '<em class="text-muted">Kosong</em>'}</td>
                <td>${email || '<em class="text-muted">Kosong</em>'}</td>
                <td>
                    <i class="bi bi-${statusIcon}"></i> ${statusText}
                </td>
            </tr>
        `;
        
        if (!isValid) {
            errors.push({
                row: rowNumber,
                errors: rowErrors
            });
        }
    });
    
    // Show preview
    $('#preview-tbody').html(previewHtml);
    $('#preview-area').show();
    
    // Show errors if any
    if (errors.length > 0) {
        let errorHtml = '<strong>Data tidak valid pada baris:</strong><ul>';
        errors.forEach(err => {
            errorHtml += `<li>Baris ${err.row}: ${err.errors.join(', ')}</li>`;
        });
        errorHtml += '</ul>';
        
        $('#validation-errors').html(errorHtml).show();
        $('#import-btn').prop('disabled', true);
    } else {
        $('#validation-errors').hide();
        $('#import-btn').prop('disabled', false);
    }
    
    // Update import button text
    $('#import-btn').html(
        `<i class="bi bi-check-circle"></i> Import ${validCount} Peserta Valid`
    );
}

function importData() {
    // Clear existing participants
    $('#participants-list').empty();
    participantCount = 0;
    
    // Add imported participants
    importedData.forEach((participant, index) => {
        addParticipant(participant.name, participant.email);
    });
    
    // Show success message
    $('#import-summary').show();
    $('#import-count').text(importedData.length);
    
    // Reset modal
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
    const html = `
        <div class="participant-item card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Peserta #<span class="participant-number">${participantCount + 1}</span></h6>
                    <button type="button" class="btn btn-sm btn-danger remove-participant">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="participants[${participantCount}][name]" 
                               value="${name}" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="participants[${participantCount}][email]" 
                               value="${email}" required>
                    </div>
                </div>
            </div>
        </div>
    `;
    $('#participants-list').append(html);
    participantCount++;
    updateParticipantNumbers();
    updateSummary();
}

function updateParticipantNumbers() {
    $('.participant-number').each(function(index) {
        $(this).text(index + 1);
    });
    $('.remove-participant').toggle(participantCount > 0);
}

function updateSummary() {
    const count = $('.participant-item').length;
    $('#total-participants').text(count);
}

function updatePaymentMethodText() {
    const phase = $('input[name="payment_phases"]:checked').val();
    $('#payment-method-text').text(
        phase === 'single' ? '1 Fase (Full Payment)' : '2 Fase (50% + 50%)'
    );
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function selectPhase(phase) {
    $('.payment-phase-option').removeClass('selected');
    if (phase === 'single') {
        $('#phase-single').addClass('selected');
        $('#phases_single').prop('checked', true);
    } else {
        $('#phase-two').addClass('selected');
        $('#phases_two').prop('checked', true);
    }
    $('input[name="payment_phases"]:checked').trigger('change');
}

// Initialize
$(document).ready(function() {
    const selectedPhase = $('input[name="payment_phases"]:checked').val();
    selectPhase(selectedPhase);
});
</script>
@endpush