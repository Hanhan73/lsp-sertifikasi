<div class="row">
    <!-- Left Column: Payment Info -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Informasi Pembayaran</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Payment ID</strong></td>
                        <td>: #{{ $payment->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Order ID</strong></td>
                        <td>: <code>{{ $payment->order_id ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Transaction ID</strong></td>
                        <td>:
                            @if($payment->transaction_id)
                            <code>{{ $payment->transaction_id }}</code>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fase Pembayaran</strong></td>
                        <td>:
                            <span class="badge bg-{{ $payment->phase_badge }}">
                                @if($payment->payment_phase === 'phase_1')
                                <i class="bi bi-1-circle"></i> Fase 1 (50%)
                                @elseif($payment->payment_phase === 'phase_2')
                                <i class="bi bi-2-circle"></i> Fase 2 (50%)
                                @else
                                <i class="bi bi-cash"></i> Pembayaran Penuh
                                @endif
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Jumlah</strong></td>
                        <td>: <h5 class="mb-0 text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</h5>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Metode</strong></td>
                        <td>:
                            @if($payment->payment_type)
                            <span class="badge bg-info">{{ strtoupper($payment->payment_type) }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>:
                            @if($payment->status === 'verified')
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Terverifikasi</span>
                            @elseif($payment->status === 'pending')
                            <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Dibuat</strong></td>
                        <td>: {{ $payment->created_at->translatedFormat('d/m/Y H:i') }}</td>
                    </tr>
                    @if($payment->verified_at)
                    <tr>
                        <td><strong>Tanggal Verifikasi</strong></td>
                        <td>: {{ $payment->verified_at->translatedFormat('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Verification Info -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-shield-check"></i> Informasi Verifikasi</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Tipe Verifikasi</strong></td>
                        <td>:
                            @if($payment->is_auto_verified)
                            <span class="badge bg-success">
                                <i class="bi bi-robot"></i> Auto-Verified (Midtrans)
                            </span>
                            @elseif($payment->verified_by)
                            <span class="badge bg-info">
                                <i class="bi bi-person-check"></i> Manual Verification
                            </span>
                            @else
                            <span class="badge bg-secondary">Belum Verifikasi</span>
                            @endif
                        </td>
                    </tr>
                    @if($payment->verifier)
                    <tr>
                        <td><strong>Diverifikasi Oleh</strong></td>
                        <td>: {{ $payment->verifier->name }}</td>
                    </tr>
                    @endif
                    @if($payment->notes)
                    <tr>
                        <td><strong>Catatan</strong></td>
                        <td>: {{ $payment->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Asesi Info -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-person"></i> Informasi Asesi</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>No. Registrasi</strong></td>
                        <td>: #{{ $payment->asesmen->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nama Lengkap</strong></td>
                        <td>: {{ $payment->asesmen->full_name ?? $payment->asesmen->user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>: {{ $payment->asesmen->email ?? $payment->asesmen->user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>No. HP</strong></td>
                        <td>: {{ $payment->asesmen->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>TUK</strong></td>
                        <td>: {{ $payment->asesmen->tuk->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Skema</strong></td>
                        <td>: {{ $payment->asesmen->skema->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jenis Registrasi</strong></td>
                        <td>:
                            @if($payment->asesmen->is_collective)
                            <span class="badge bg-primary"><i class="bi bi-people"></i> Kolektif</span>
                            <br><small class="text-muted">Batch: {{ $payment->asesmen->collective_batch_id }}</small>
                            @else
                            <span class="badge bg-success"><i class="bi bi-person"></i> Mandiri</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status Asesi</strong></td>
                        <td>: <span
                                class="badge bg-{{ $payment->asesmen->status_badge }}">{{ $payment->asesmen->status_label }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Summary (if two-phase) -->
        @if($payment->asesmen->payment_phases === 'two_phase')
        <div class="card mb-3 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart"></i> Ringkasan Pembayaran 2 Fase</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td width="150"><strong>Total Biaya</strong></td>
                        <td>: <strong>Rp {{ number_format($payment->asesmen->fee_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Fase 1 (50%)</strong></td>
                        <td>: Rp {{ number_format($payment->asesmen->phase_1_amount, 0, ',', '.') }}
                            @php
                            $phase1 = $payment->asesmen->payments()->where('payment_phase', 'phase_1')->first();
                            @endphp
                            @if($phase1 && $phase1->status === 'verified')
                            <span class="badge bg-success ms-2"><i class="bi bi-check"></i> Lunas</span>
                            @else
                            <span class="badge bg-warning ms-2"><i class="bi bi-clock"></i> Belum</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fase 2 (50%)</strong></td>
                        <td>: Rp {{ number_format($payment->asesmen->phase_2_amount, 0, ',', '.') }}
                            @php
                            $phase2 = $payment->asesmen->payments()->where('payment_phase', 'phase_2')->first();
                            @endphp
                            @if($phase2 && $phase2->status === 'verified')
                            <span class="badge bg-success ms-2"><i class="bi bi-check"></i> Lunas</span>
                            @else
                            <span class="badge bg-warning ms-2"><i class="bi bi-clock"></i> Belum</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- All Payments (if multiple) -->
        @if($payment->asesmen->payments && $payment->asesmen->payments->count() > 1)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-receipt"></i> Riwayat Semua Pembayaran</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($payment->asesmen->payments->sortBy('created_at') as $p)
                    <div class="list-group-item {{ $p->id === $payment->id ? 'active' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>
                                    @if($p->payment_phase === 'phase_1')
                                    <i class="bi bi-1-circle"></i> Fase 1
                                    @elseif($p->payment_phase === 'phase_2')
                                    <i class="bi bi-2-circle"></i> Fase 2
                                    @else
                                    <i class="bi bi-cash"></i> Full Payment
                                    @endif
                                </strong>
                                <br>
                                <small {{ $p->id === $payment->id ? '' : 'class=text-muted' }}>
                                    Rp {{ number_format($p->amount, 0, ',', '.') }}
                                    <br>
                                    {{ $p->created_at->translatedFormat('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div>
                                @if($p->status === 'verified')
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i></span>
                                @elseif($p->status === 'pending')
                                <span class="badge bg-warning"><i class="bi bi-clock"></i></span>
                                @else
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-tools"></i> Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.asesi.show', $payment->asesmen->id) }}"
                        class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person"></i> Lihat Detail Asesi
                    </a>

                    @if($payment->status === 'pending')
                    <button type="button" class="btn btn-outline-success btn-sm"
                        onclick="manualVerifyFromDetail({{ $payment->id }}, 'verified')">
                        <i class="bi bi-check-circle"></i> Verifikasi Manual
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm"
                        onclick="manualVerifyFromDetail({{ $payment->id }}, 'rejected')">
                        <i class="bi bi-x-circle"></i> Tolak Pembayaran
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Timeline -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Pembayaran</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @if($payment->verified_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <strong>
                                <i class="bi bi-{{ $payment->is_auto_verified ? 'robot' : 'person-check' }}"></i>
                                {{ $payment->is_auto_verified ? 'Auto-Verified' : 'Manual Verification' }}
                            </strong>
                            <br><small class="text-muted">{{ $payment->verified_at->translatedFormat('d/m/Y H:i') }}</small>
                            @if($payment->verifier)
                            <br><span class="badge bg-info">{{ $payment->verifier->name }}</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="timeline-item">
                        <div class="timeline-marker bg-{{ $payment->status === 'verified' ? 'primary' : 'warning' }}">
                        </div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-cash"></i> Pembayaran Dibuat</strong>
                            <br><small class="text-muted">{{ $payment->created_at->translatedFormat('d/m/Y H:i') }}</small>
                            <br><span class="badge bg-success">Rp
                                {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    padding-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 9px;
    top: 20px;
    height: calc(100% - 10px);
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding: 5px 0;
}

.timeline-content strong {
    color: #495057;
    font-size: 14px;
}

.timeline-content small {
    font-size: 12px;
}

.timeline-content .badge {
    margin-top: 5px;
}

.list-group-item.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.list-group-item.active small {
    color: rgba(255, 255, 255, 0.8) !important;
}
</style>

<script>
function manualVerifyFromDetail(paymentId, status) {
    const title = status === 'verified' ? 'Verifikasi Manual' : 'Tolak Pembayaran';
    const text = status === 'verified' ?
        'Apakah Anda yakin ingin memverifikasi pembayaran ini secara manual?' :
        'Apakah Anda yakin ingin menolak pembayaran ini?';

    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: status === 'verified' ? '#28a745' : '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Close current modal
            $('#detailModal').modal('hide');

            // Call parent window function
            if (window.parent && window.parent.manualVerify) {
                window.parent.manualVerify(paymentId, status);
            } else if (typeof manualVerify === 'function') {
                manualVerify(paymentId, status);
            }
        }
    });
}
</script>