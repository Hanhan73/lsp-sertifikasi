@extends('layouts.app')

@section('title', 'Monitor Pembayaran')
@section('page-title', 'Monitor & Verifikasi Pembayaran')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-credit-card"></i> Monitor Pembayaran
        </h5>
        <div class="alert alert-info mb-0 py-2 px-3">
            <small>
                <i class="bi bi-info-circle"></i>
                <strong>Auto-Verification:</strong> Pembayaran via Midtrans terverifikasi otomatis oleh sistem.
                Manual verification hanya untuk backup/troubleshooting.
            </small>
        </div>
    </div>
    <div class="card-body">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="--bg-color: #f093fb; --bg-color-end: #f5576c;">
                    <div class="text-center">
                        <h4>{{ $payments->where('status', 'pending')->count() }}</h4>
                        <p class="mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="--bg-color: #43e97b; --bg-color-end: #38f9d7;">
                    <div class="text-center">
                        <h4>{{ $payments->where('status', 'verified')->where('verified_by', null)->count() }}</h4>
                        <p class="mb-0">Auto-Verified</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="--bg-color: #4facfe; --bg-color-end: #00f2fe;">
                    <div class="text-center">
                        <h4>{{ $payments->where('status', 'verified')->whereNotNull('verified_by')->count() }}</h4>
                        <p class="mb-0">Manual Verified</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="--bg-color: #fa709a; --bg-color-end: #fee140;">
                    <div class="text-center">
                        <h4>{{ $payments->where('status', 'rejected')->count() }}</h4>
                        <p class="mb-0">Rejected</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filter-status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter-verification" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="auto">Auto-Verified</option>
                    <option value="manual">Manual Verified</option>
                </select>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="table-responsive">
            <table class="table table-hover datatable" id="payments-table">
                <thead>
                    <tr>
                        <th>No Reg</th>
                        <th>Nama Asesi</th>
                        <th>Skema</th>
                        <th>TUK</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Verification</th>
                        <th>Transaction ID</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr data-status="{{ $payment->status }}"
                        data-verification="{{ $payment->is_auto_verified ? 'auto' : 'manual' }}">
                        <td><strong>#{{ $payment->asesmen->id }}</strong></td>
                        <td>
                            {{ $payment->asesmen->full_name }}<br>
                            <small class="text-muted">{{ $payment->asesmen->email }}</small>
                        </td>
                        <td>{{ $payment->asesmen->skema->name ?? '-' }}</td>
                        <td>{{ $payment->asesmen->tuk->name ?? '-' }}</td>
                        <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $payment->status_badge }} badge-status">
                                {{ $payment->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($payment->status === 'verified')
                            @if($payment->is_auto_verified)
                            <span class="badge bg-success">
                                <i class="bi bi-robot"></i> Auto
                            </span>
                            @else
                            <span class="badge bg-info">
                                <i class="bi bi-person-check"></i> Manual
                            </span>
                            @endif
                            @else
                            <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if($payment->transaction_id)
                            <small class="font-monospace">{{ Str::limit($payment->transaction_id, 15) }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            {{ $payment->created_at->format('d/m/Y H:i') }}<br>
                            @if($payment->verified_at)
                            <small class="text-success">
                                <i class="bi bi-check"></i> {{ $payment->verified_at->format('d/m/Y H:i') }}
                            </small>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewDetail({{ $payment->id }})"
                                data-bs-toggle="tooltip" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </button>

                            @if($payment->status === 'pending')
                            <button class="btn btn-sm btn-warning"
                                onclick="manualVerify({{ $payment->id }}, 'verified')" data-bs-toggle="tooltip"
                                title="Manual Verify (Backup)">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="manualVerify({{ $payment->id }}, 'rejected')"
                                data-bs-toggle="tooltip" title="Reject">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Manual Verify Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="verify-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Manual Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Peringatan:</strong> Gunakan manual verification hanya jika auto-verification gagal atau
                        untuk troubleshooting.
                    </div>

                    <input type="hidden" name="status" id="verify-status">

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Alasan manual verification..."
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses Verification</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize tooltips
$(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});

// Filter functionality
$('#filter-status, #filter-verification').on('change', function() {
    const status = $('#filter-status').val();
    const verification = $('#filter-verification').val();

    $('#payments-table tbody tr').each(function() {
        const row = $(this);
        let show = true;

        if (status && row.data('status') !== status) {
            show = false;
        }

        if (verification && row.data('verification') !== verification) {
            show = false;
        }

        row.toggle(show);
    });
});

// View detail
function viewDetail(paymentId) {
    $('#detailModal').modal('show');
    $('#detail-content').html('<div class="text-center"><div class="spinner-border"></div></div>');

    // Load payment details via AJAX
    $.get('/admin/payments/' + paymentId + '/detail', function(data) {
        $('#detail-content').html(data);
    }).fail(function() {
        $('#detail-content').html('<div class="alert alert-danger">Gagal memuat detail</div>');
    });
}

// Manual verify
function manualVerify(paymentId, status) {
    const title = status === 'verified' ? 'Verifikasi Manual' : 'Tolak Pembayaran';
    const text = status === 'verified' ?
        'Apakah Anda yakin ingin memverifikasi pembayaran ini secara manual? Ini hanya untuk backup jika auto-verification gagal.' :
        'Apakah Anda yakin ingin menolak pembayaran ini?';

    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $('#verify-form').attr('action', '/admin/payments/' + paymentId + '/verify');
            $('#verify-status').val(status);
            $('#verifyModal').modal('show');
        }
    });
}

// Submit verify form
$('#verify-form').on('submit', function(e) {
    e.preventDefault();

    const form = $(this);
    const btn = form.find('button[type="submit"]');

    btn.prop('disabled', true);
    btn.html('<span class="spinner-border spinner-border-sm"></span> Processing...');

    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Pembayaran berhasil diverifikasi manual',
                timer: 2000
            }).then(() => {
                window.location.reload();
            });
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan'
            });
            btn.prop('disabled', false);
            btn.html('Proses Verification');
        }
    });
});

// Auto-refresh pending payments every 30 seconds
setInterval(function() {
    const hasPending = $('#payments-table tbody tr[data-status="pending"]').length > 0;
    if (hasPending) {
        console.log('Auto-checking payment status...');
        window.location.reload();
    }
}, 30000);
</script>
@endpush