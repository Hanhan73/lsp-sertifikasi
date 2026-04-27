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

                        {{-- Tampilkan validation errors --}}
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <strong><i class="bi bi-exclamation-triangle"></i> Pendaftaran gagal:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            </div>
            @endif
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Pendaftaran Kolektif</strong><br>
                    Daftarkan beberapa asesi sekaligus. Sistem akan otomatis membuat akun untuk setiap peserta.
                    Peserta dapat login dengan password default <strong>password123</strong> dan wajib menggantinya saat
                    pertama login.
                </div>

                <form method="POST" action="{{ route('tuk.collective.store') }}" id="collective-form">
                    @csrf

                    {{-- Hidden: payment_phases selalu single --}}
                    <input type="hidden" name="payment_phases" value="single">
                    <!-- Nama Batch -->
                    <div class="mb-4">
                        <label class="form-label">Nama Batch <span class="text-muted">(opsional)</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('batch_name') is-invalid @enderror"
                                name="batch_name"
                                id="batch_name_input"
                                value="{{ old('batch_name') }}"
                                placeholder="cth: Angkatan Jan 2025, Kelas A SMK 1"
                                maxlength="50">
                            <span class="input-group-text text-muted" id="batch-id-preview" style="font-size:0.8rem; min-width: 180px;">
                                —
                            </span>
                        </div>
                        <small class="text-muted">
                            Nama ini akan menjadi prefix Batch ID. Jika kosong, akan menggunakan "BATCH".
                            Kode TUK dan suffix unik akan ditambahkan otomatis di ujung.
                        </small>
                        @error('batch_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

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
                            name="preferred_date" value="{{ old('preferred_date') }}"
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
                                        <input type="radio" name="training_flag" value="0" id="training-no" checked
                                            required>
                                        <div class="ms-3 flex-grow-1">
                                            <label for="training-no" class="form-label fw-bold mb-1"
                                                style="cursor: pointer;">
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
                                            <label for="training-yes" class="form-label fw-bold mb-1"
                                                style="cursor: pointer;">
                                                <i class="bi bi-check-circle text-success"></i> Dengan Pelatihan
                                            </label>
                                            <p class="text-muted small mb-2">
                                                Semua peserta ikut pelatihan sebelum asesmen
                                            </p>
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
                            <button type="button" class="btn-close float-end"
                                onclick="$('#import-summary').hide()"></button>
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
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
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
                                            <p class="text-muted small">Template Excel dengan format yang sudah sesuai
                                            </p>
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
                                                <div class="table-responsive"
                                                    style="max-height: 300px; overflow-y: auto;">
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
                                                <div class="alert alert-warning" id="validation-errors"
                                                    style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Batal</button>
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
                                    Biaya asesmen akan ditentukan oleh Admin LSP setelah semua peserta mengisi data dan
                                    diverifikasi TUK.
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

{{-- Modal Duplikat --}}
<div class="modal fade" id="modalDuplikat" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Ditemukan Data Duplikat!</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-info-circle me-1"></i>
                    Beberapa peserta yang akan didaftarkan <strong>sudah ada di sistem</strong>.
                    Tinjau data perbandingan, lalu setujui konversi untuk melanjutkan pendaftaran.
                </div>
                <div id="duplikat-list"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-pencil me-1"></i> Kembali & Edit
                </button>
                <button type="button" class="btn btn-success" id="btn-lanjutkan">
                    <i class="bi bi-send me-1"></i> Lanjutkan Pendaftaran
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let participantCount   = 0;
let importedData       = [];
let pendingSubmit      = false;
let detectedDuplicates = [];

$(document).ready(function () {
    addParticipant();
    selectTraining(false);

    $('#participant-file').change(function (e) {
        const file = e.target.files[0];
        if (!file) return;
        Swal.fire({ title: 'Memproses...', html: 'Membaca file Excel/CSV', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                const data     = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const sheet    = workbook.Sheets[workbook.SheetNames[0]];
                validateAndPreview(XLSX.utils.sheet_to_json(sheet));
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

    $('#add-participant').click(function () { addParticipant(); });

    $(document).on('click', '.remove-participant', function () {
        $(this).closest('.participant-item').remove();
        updateParticipantNumbers();
        updateSummary();
    });

    const tukCode      = '{{ strtoupper(auth()->user()->tuk->code ?? "TUK") }}';
    const randomSuffix = generateRandomSuffix(6);

    function generateRandomSuffix(length) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = '';
        for (let i = 0; i < length; i++) result += chars.charAt(Math.floor(Math.random() * chars.length));
        return result;
    }

    function slugify(text) {
        return text.toString().toUpperCase().trim()
            .replace(/\s+/g, '-').replace(/[^A-Z0-9\-]/g, '')
            .replace(/\-+/g, '-').replace(/^-+|-+$/g, '');
    }

    function updateBatchPreview() {
        const nameVal = $('#batch_name_input').val().trim();
        const prefix  = nameVal ? slugify(nameVal) : 'BATCH';
        $('#batch-id-preview').text(prefix + '-' + tukCode + '-' + randomSuffix);
    }

    $('#batch_name_input').on('input', updateBatchPreview);
    updateBatchPreview();

    // ── Tombol lanjutkan duplikat ──
    $('#btn-lanjutkan').on('click', function () {
        bootstrap.Modal.getInstance(document.getElementById('modalDuplikat')).hide();
        pendingSubmit = true;
        $('#collective-form').submit();
    });

    // ── SUBMIT HANDLER UTAMA ──
    $('#collective-form').on('submit', async function (e) {
        e.preventDefault();

        $('.participant-email').each(function () {
            $(this).val($(this).val().trim().toLowerCase());
        });

        if ($('.participant-item').length === 0) {
            Swal.fire({ icon: 'warning', title: 'Peserta Kosong', text: 'Tambahkan minimal 1 peserta.' });
            return;
        }

        // Validasi email
        const emailInvalid = [];
        $('.participant-item').each(function (idx) {
            const nama  = $(this).find('input[name*="[name]"]').val().trim();
            const email = $(this).find('input[name*="[email]"]').val().trim().toLowerCase();
            if (!isValidEmail(email)) {
                emailInvalid.push({ no: idx + 1, nama: nama || '(nama kosong)', email: email || '(kosong)' });
                $(this).find('input[name*="[email]"]').addClass('is-invalid');
            } else {
                $(this).find('input[name*="[email]"]').removeClass('is-invalid');
            }
        });

        if (emailInvalid.length > 0) {
            const listHtml = emailInvalid.map(p =>
                `<tr>
                    <td class="text-center">${p.no}</td>
                    <td>${p.nama}</td>
                    <td><code class="text-danger">${p.email || '<em>kosong</em>'}</code></td>
                </tr>`
            ).join('');
            Swal.fire({
                icon: 'error',
                title: `${emailInvalid.length} Email Tidak Valid`,
                html: `
                    <p class="text-muted small mb-3">Perbaiki email berikut sebelum melanjutkan pendaftaran:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered text-start">
                            <thead class="table-light">
                                <tr><th class="text-center" width="40">No</th><th>Nama</th><th>Email</th></tr>
                            </thead>
                            <tbody>${listHtml}</tbody>
                        </table>
                    </div>`,
                confirmButtonText: 'Oke, saya perbaiki',
                width: '600px',
            });
            return;
        }

        // Re-index
        $('.participant-item').each(function (newIdx) {
            $(this).find('input[name*="[name]"]').attr('name', `participants[${newIdx}][name]`);
            $(this).find('input[name*="[email]"]').attr('name', `participants[${newIdx}][email]`);
        });

        if (pendingSubmit) {
            pendingSubmit = false;
            $(this).find('[type=submit]').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-1"></span> Mendaftarkan...');
            this.submit();
            return;
        }

        // Kumpulkan peserta
        const participants = [];
        $('.participant-item').each(function () {
            participants.push({
                name:  $(this).find('input[name*="[name]"]').val().trim(),
                email: $(this).find('input[name*="[email]"]').val().trim().toLowerCase(),
            });
        });

        // Cek duplikat ke server
        Swal.fire({ title: 'Memeriksa data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const res  = await fetch('{{ route("tuk.collective.check-duplicates") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ participants }),
            });
            const data = await res.json();
            Swal.close();

            if (data.duplicates && data.duplicates.length > 0) {
                detectedDuplicates = data.duplicates;
                tampilkanModalDuplikat(data.duplicates);
            } else {
                pendingSubmit = true;
                $('#collective-form').submit();
            }
        } catch (err) {
            Swal.close();
            pendingSubmit = true;
            $('#collective-form').submit();
        }
    });

}); // end $(document).ready

function tampilkanModalDuplikat(duplicates) {
    let html = '';
    duplicates.forEach((d, i) => {
        const isEmailSama = d.match_type === 'email';
        const canConvert  = d.can_convert;
        const matchTypeBadge = isEmailSama
            ? '<span class="badge bg-danger">Email sama</span>'
            : '<span class="badge bg-warning text-dark">Nama sama</span>';
        const jenisExisting = d.existing.is_collective
            ? `<span class="badge bg-info">Kolektif</span> <small class="text-muted">batch: ${d.existing.batch_id}</small>`
            : '<span class="badge bg-success">Mandiri</span>';
        let actionSection = '';
        if (!isEmailSama) {
            actionSection = `
            <div class="mt-3 p-3 bg-light border rounded">
                <i class="bi bi-info-circle text-secondary me-1"></i>
                <span class="small">Nama mirip tapi email berbeda — peserta ini akan <strong>di-skip</strong>.
                Koreksi email di form jika ini orang yang sama.</span>
            </div>`;
        } else if (canConvert) {
            actionSection = `
            <div class="mt-3 p-3 bg-warning bg-opacity-10 border border-warning rounded">
                <div class="small mb-2">
                    <i class="bi bi-arrow-repeat text-warning me-1"></i>
                    Pendaftaran <strong>${d.existing.is_collective ? 'kolektif' : 'mandiri'}</strong> lama
                    akan dikonversi dan dimasukkan ke batch ini.
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input chk-setuju-convert"
                        id="setuju-${i}" data-index="${i}" data-asesmen-id="${d.existing.id}">
                    <label class="form-check-label small fw-semibold" for="setuju-${i}">
                        Saya setuju — konversi pendaftaran <em>${d.existing.nama}</em> ke batch ini
                    </label>
                </div>
            </div>`;
        } else {
            actionSection = `
            <div class="mt-3 p-3 bg-danger bg-opacity-10 border border-danger rounded">
                <i class="bi bi-x-circle text-danger me-1"></i>
                <span class="small"><strong>Tidak bisa dikonversi.</strong>
                Peserta sudah dalam status <strong>${d.existing.status}</strong> —
                proses sudah terlalu jauh untuk dipindahkan ke batch ini.
                Hubungi admin untuk penanganan lebih lanjut.</span>
            </div>`;
        }
        html += `
        <div class="card mb-3 border-${isEmailSama ? (canConvert ? 'warning' : 'danger') : 'secondary'}">
            <div class="card-header bg-${isEmailSama ? (canConvert ? 'warning' : 'danger') : 'secondary'} ${isEmailSama && !canConvert ? 'text-white' : ''} bg-opacity-25 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Peserta #${d.index + 1}: ${d.input_name}</span>
                ${matchTypeBadge}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="card border-primary h-100">
                            <div class="card-header bg-primary text-white py-2">
                                <small><i class="bi bi-person-plus me-1"></i>Yang akan didaftarkan</small>
                            </div>
                            <div class="card-body py-2">
                                <table class="table table-sm mb-0">
                                    <tr><th class="text-muted" style="width:35%">Nama</th><td>${d.input_name}</td></tr>
                                    <tr><th class="text-muted">Email</th><td>${d.input_email}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="bi bi-arrow-left-right fs-3 text-warning d-block"></i>
                            <small class="text-muted">vs</small>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card border-danger h-100">
                            <div class="card-header bg-danger text-white py-2">
                                <small><i class="bi bi-database me-1"></i>Sudah ada di sistem</small>
                            </div>
                            <div class="card-body py-2">
                                <table class="table table-sm mb-0">
                                    <tr><th class="text-muted" style="width:35%">Nama</th><td>${d.existing.nama}</td></tr>
                                    <tr><th class="text-muted">Email</th><td>${d.existing.email}</td></tr>
                                    <tr><th class="text-muted">Skema</th><td>${d.existing.skema}</td></tr>
                                    <tr><th class="text-muted">TUK</th><td>${d.existing.tuk}</td></tr>
                                    <tr><th class="text-muted">Status</th><td><span class="badge bg-secondary">${d.existing.status}</span></td></tr>
                                    <tr><th class="text-muted">Jenis</th><td>${jenisExisting}</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                ${actionSection}
            </div>
        </div>`;
    });
    $('#duplikat-list').html(html);
    cekTombolLanjutkan();
    $(document).off('change', '.chk-setuju-convert').on('change', '.chk-setuju-convert', function () {
        cekTombolLanjutkan();
    });
    new bootstrap.Modal(document.getElementById('modalDuplikat')).show();
}

function cekTombolLanjutkan() {
    const totalWajib   = $('.chk-setuju-convert').length;
    const totalChecked = $('.chk-setuju-convert:checked').length;
    const semuaSetuju  = totalWajib === 0 || totalChecked === totalWajib;
    const btn = $('#btn-lanjutkan');
    if (semuaSetuju) {
        btn.prop('disabled', false)
           .removeClass('btn-secondary').addClass('btn-success')
           .html(`<i class="bi bi-send me-1"></i> Lanjutkan${totalWajib > 0 ? ' & Konversi ' + totalWajib + ' Peserta' : ''}`);
    } else {
        btn.prop('disabled', true)
           .removeClass('btn-success').addClass('btn-secondary')
           .html(`<i class="bi bi-lock me-1"></i> Setujui konversi dulu (${totalChecked}/${totalWajib})`);
    }
}

function validateAndPreview(data) {
    if (data.length === 0) {
        Swal.fire({ icon: 'warning', title: 'File Kosong', text: 'File tidak mengandung data peserta' });
        return;
    }
    let validCount = 0, errors = [], previewHtml = '';
    importedData = [];
    data.forEach((row, index) => {
        const rowNumber = index + 2;
        const rawEmail  = (row['Email'] || row['email'] || '').toString();
        const name      = (row['Nama Lengkap'] || row['nama_lengkap'] || '').toString().trim();
        const email     = rawEmail.trim().toLowerCase();
        const wasFixed  = rawEmail.trim() !== rawEmail;
        let rowErrors = [];
        if (!name)  rowErrors.push('Nama wajib diisi');
        if (!email) rowErrors.push('Email wajib diisi');
        else if (!isValidEmail(email)) rowErrors.push('Format email tidak valid');
        const isValid = rowErrors.length === 0;
        if (isValid) { validCount++; importedData.push({ name, email }); }
        const statusClass = isValid ? 'success' : 'danger';
        const statusIcon  = isValid ? 'check-circle' : 'x-circle';
        const fixedBadge  = (isValid && wasFixed) ? ' <span class="badge bg-warning text-dark">Spasi dihapus</span>' : '';
        const statusText  = isValid ? ('Valid' + fixedBadge) : rowErrors.join(', ');
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
        let errorHtml = '<strong>Data tidak valid:</strong><ul>';
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
    importedData.forEach(p => addParticipant(p.name.trim(), p.email.trim()));
    $('#import-summary').show();
    $('#import-count').text(importedData.length);
    $('#participant-file').val('');
    $('#preview-area').hide();
    $('#import-btn').prop('disabled', true);
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: `${importedData.length} peserta berhasil diimport`, timer: 2000, showConfirmButton: false });
}

function addParticipant(name = '', email = '') {
    const idx       = participantCount;
    const safeName  = name.trim().replace(/"/g, '&quot;');
    const safeEmail = email.trim().replace(/"/g, '&quot;');
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
                               value="${safeName}" placeholder="Nama lengkap peserta" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="text" class="form-control participant-email" name="participants[${idx}][email]"
                               value="${safeEmail}" placeholder="email@contoh.com"
                               pattern="[^\\s@]+@[^\\s@]+\\.[^\\s@]+" title="Format email tidak valid" required>
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
    items.each(function (index) { $(this).find('.participant-number').text(index + 1); });
    $('.remove-participant').toggle(items.length > 1);
}

function updateSummary() { $('#total-participants').text($('.participant-item').length); }

function selectTraining(withTraining) {
    $('.training-option').removeClass('selected');
    if (withTraining) { $('#option-yes').addClass('selected'); $('#training-yes').prop('checked', true); }
    else { $('#option-no').addClass('selected'); $('#training-no').prop('checked', true); }
}

function isValidEmail(email) { return email.includes('@') && email.includes('.'); }
</script>
@endpush