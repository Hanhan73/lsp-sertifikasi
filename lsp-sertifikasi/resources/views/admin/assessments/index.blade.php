@extends('layouts.app')

@section('title', 'Input Hasil Asesmen')
@section('page-title', 'Input Hasil Asesmen')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-check"></i> Asesi Siap Diases
        </h5>
        <span class="badge bg-primary">{{ $asesmens->count() }} Asesi</span>
    </div>
    <div class="card-body">
        @if($asesmens->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-clipboard-x" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">Tidak Ada Asesi yang Siap Diases</h4>
            <p class="text-muted">Asesi harus menyelesaikan pra-asesmen terlebih dahulu</p>
        </div>
        @else
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all">
                    Semua <span class="badge bg-secondary ms-1">{{ $asesmens->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#scheduled">
                    Terjadwal <span
                        class="badge bg-warning ms-1">{{ $asesmens->where('status', 'scheduled')->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#completed">
                    Pra-Asesmen Selesai <span
                        class="badge bg-success ms-1">{{ $asesmens->where('status', 'pre_assessment_completed')->count() }}</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- All Tab -->
            <div class="tab-pane fade show active" id="all">
                @include('admin.assessments.table', ['data' => $asesmens])
            </div>

            <!-- Scheduled Tab -->
            <div class="tab-pane fade" id="scheduled">
                @include('admin.assessments.table', ['data' => $asesmens->where('status', 'scheduled')])
            </div>

            <!-- Completed Tab -->
            <div class="tab-pane fade" id="completed">
                @include('admin.assessments.table', ['data' => $asesmens->where('status', 'pre_assessment_completed')])
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Input Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resultForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Input Hasil Asesmen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Asesi</label>
                        <input type="text" class="form-control" id="asesi_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Skema</label>
                        <input type="text" class="form-control" id="skema_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hasil Asesmen <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="result" value="kompeten" id="kompeten"
                                required>
                            <label class="form-check-label text-success fw-bold" for="kompeten">
                                <i class="bi bi-check-circle"></i> Kompeten
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="result" value="belum_kompeten"
                                id="belum_kompeten">
                            <label class="form-check-label text-danger fw-bold" for="belum_kompeten">
                                <i class="bi bi-x-circle"></i> Belum Kompeten
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Hasil</label>
                        <textarea name="result_notes" class="form-control" rows="3"
                            placeholder="Catatan hasil asesmen (opsional)"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <small>
                            Jika hasil <strong>Kompeten</strong>, sertifikat akan otomatis dibuat setelah submit.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Hasil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function inputResult(id, name, skema) {
    $('#resultForm').attr('action', `/admin/assessments/${id}/input`);
    $('#asesi_name').val(name);
    $('#skema_name').val(skema);
    $('#resultModal').modal('show');
}

$('#resultForm').on('submit', function(e) {
    e.preventDefault();

    const result = $('input[name="result"]:checked').val();
    const text = result === 'kompeten' ?
        'Asesi akan dinyatakan KOMPETEN dan sertifikat akan dibuat otomatis.' :
        'Asesi akan dinyatakan BELUM KOMPETEN.';

    Swal.fire({
        title: 'Konfirmasi Hasil Asesmen',
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
    }).then((confirm) => {
        if (confirm.isConfirmed) {
            this.submit();
        }
    });
});
</script>
@endpush