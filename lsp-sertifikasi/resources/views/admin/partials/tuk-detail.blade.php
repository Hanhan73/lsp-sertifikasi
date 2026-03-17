<div class="row">
    <!-- Left Column: Logo & Basic Info -->
    <div class="col-md-4">
        <!-- Logo -->
        <div class="card mb-3">
            <div class="card-body text-center">
                @if($tuk->logo_path)
                <img src="{{ $tuk->logo_url }}" alt="Logo {{ $tuk->name }}" class="img-fluid rounded mb-3"
                    style="max-width: 200px; max-height: 200px; object-fit: contain;">
                @else
                <div class="bg-light d-flex align-items-center justify-content-center rounded mb-3"
                    style="width: 200px; height: 200px; margin: 0 auto;">
                    <i class="bi bi-building text-muted" style="font-size: 4rem;"></i>
                </div>
                @endif
                <h5 class="mb-0">{{ $tuk->name }}</h5>
                <p class="text-muted mb-2"><small>{{ $tuk->code }}</small></p>
                @if($tuk->is_active)
                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aktif</span>
                @else
                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Tidak Aktif</span>
                @endif
            </div>
        </div>

        <!-- SK Document -->
        @if($tuk->hasSkDocument())
        <div class="card mb-3 border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-file-earmark-check-fill"></i> SK Penetapan TUK</h6>
            </div>
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                <p class="mb-2 mt-2"><small class="text-muted">SK Penetapan Tersedia</small></p>
                <a href="{{ $tuk->sk_document_url }}" target="_blank" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-download"></i> Download SK
                </a>
                <a href="{{ $tuk->sk_document_url }}" target="_blank" class="btn btn-outline-success btn-sm w-100 mt-2">
                    <i class="bi bi-eye"></i> Lihat SK
                </a>
            </div>
        </div>
        @else
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> SK Penetapan TUK</h6>
            </div>
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-x text-muted" style="font-size: 3rem;"></i>
                <p class="mb-0 mt-2 text-muted"><small>SK belum diupload</small></p>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Statistik</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Asesi</span>
                    <span class="badge bg-info fs-6">{{ $tuk->asesmens->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Asesi Mandiri</span>
                    <span class="badge bg-success">{{ $tuk->asesmens->where('is_collective', false)->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Asesi Kolektif</span>
                    <span class="badge bg-primary">{{ $tuk->asesmens->where('is_collective', true)->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Details -->
    <div class="col-md-8">
        <!-- Contact Information -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi TUK</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="150"><strong>Kode TUK</strong></td>
                        <td>: <span class="badge bg-primary">{{ $tuk->code }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Nama TUK</strong></td>
                        <td>: {{ $tuk->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Alamat</strong></td>
                        <td>: {{ $tuk->address }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>:
                            @if($tuk->email)
                            <a href="mailto:{{ $tuk->email }}">
                                <i class="bi bi-envelope"></i> {{ $tuk->email }}
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>No. HP</strong></td>
                        <td>:
                            @if($tuk->phone)
                            <a href="tel:{{ $tuk->phone }}">
                                <i class="bi bi-telephone"></i> {{ $tuk->phone }}
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Personnel Information -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-people"></i> Personil TUK</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-person-badge text-primary" style="font-size: 2rem;"></i>
                            <h6 class="mt-2 mb-1">Manager</h6>
                            <p class="mb-0">{{ $tuk->manager_name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-cash-coin text-success" style="font-size: 2rem;"></i>
                            <h6 class="mt-2 mb-1">Bendahara</h6>
                            <p class="mb-0">{{ $tuk->treasurer_name ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bi bi-person-workspace text-info" style="font-size: 2rem;"></i>
                            <h6 class="mt-2 mb-1">Staff</h6>
                            <p class="mb-0">{{ $tuk->staff_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-person-lock"></i> Informasi Akun</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="150"><strong>Email Login</strong></td>
                        <td>: {{ $tuk->user->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nama User</strong></td>
                        <td>: {{ $tuk->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status Akun</strong></td>
                        <td>:
                            @if($tuk->user && $tuk->user->is_active)
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aktif</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- System Information -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Informasi Sistem</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="150"><strong>ID TUK</strong></td>
                        <td>: #{{ $tuk->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Dibuat</strong></td>
                        <td>: {{ $tuk->created_at->format('d/m/Y H:i') }} ({{ $tuk->created_at->diffForHumans() }})</td>
                    </tr>
                    <tr>
                        <td><strong>Update Terakhir</strong></td>
                        <td>: {{ $tuk->updated_at->format('d/m/Y H:i') }} ({{ $tuk->updated_at->diffForHumans() }})</td>
                    </tr>
                    <tr>
                        <td><strong>Status TUK</strong></td>
                        <td>:
                            @if($tuk->is_active)
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aktif</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.tuks.edit', $tuk) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit TUK
            </a>
            @if($tuk->asesmens->whereIn('status', ['registered', 'data_completed', 'verified', 'paid',
            'scheduled'])->count() === 0)
            <button type="button" class="btn btn-danger"
                onclick="confirmDeleteFromModal({{ $tuk->id }}, '{{ $tuk->name }}')">
                <i class="bi bi-trash"></i> Hapus TUK
            </button>
            @else
            <button type="button" class="btn btn-danger" disabled data-bs-toggle="tooltip"
                title="TUK tidak dapat dihapus karena masih memiliki {{ $tuk->asesmens->whereIn('status', ['registered', 'data_completed', 'verified', 'paid', 'scheduled'])->count() }} asesi aktif">
                <i class="bi bi-trash"></i> Hapus TUK
            </button>
            @endif
        </div>
    </div>
</div>

<script>
function confirmDeleteFromModal(id, name) {
    // Close the detail modal first
    $('#detailModal').modal('hide');

    // Show confirmation
    Swal.fire({
        title: 'Hapus TUK?',
        html: `
            <p>Anda akan menghapus TUK:</p>
            <strong class="text-danger">${name}</strong>
            <p class="mt-2 small text-muted">Data TUK, logo, SK document, dan akun login akan dihapus permanen!</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="bi bi-x-circle"></i> Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Call parent window's delete function
            if (window.parent && typeof window.parent.confirmDelete === 'function') {
                window.parent.confirmDelete(id, name);
            } else {
                // If no parent function, submit form directly
                const form = document.getElementById('delete-form') || window.parent.document.getElementById(
                    'delete-form');
                if (form) {
                    form.action = `/admin/tuks/${id}`;
                    form.submit();
                }
            }
        }
    });
}

// Initialize tooltips in modal
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>