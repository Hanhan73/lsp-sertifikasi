@if($data->isEmpty())
<div class="text-center py-4">
    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
    <p class="text-muted mt-2">Tidak ada data asesi</p>
</div>
@else
<div class="table-responsive">
    <table class="table table-hover data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Skema</th>
                <th>Jenis</th>
                <th>Metode Bayar</th>
                <th>Status Bayar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $asesmen)
            <tr>
                <td>#{{ $asesmen->id }}</td>
                <td>
                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                    <br>
                    <small class="text-muted">{{ $asesmen->email }}</small>
                    @if($asesmen->is_collective)
                    <br>
                    <small class="badge bg-secondary">{{ $asesmen->collective_batch_id }}</small>
                    @endif
                </td>
                <td>{{ $asesmen->skema->name ?? '-' }}</td>
                <td>
                    @if($asesmen->is_collective)
                    <span class="badge bg-primary">Kolektif</span>
                    @else
                    <span class="badge bg-success">Mandiri</span>
                    @endif
                </td>
                <td>
                    @if($asesmen->is_collective)
                    @if($asesmen->payment_phases === 'single')
                    <span class="badge bg-success">
                        <i class="bi bi-1-circle"></i> 1 Fase
                    </span>
                    @else
                    <span class="badge bg-primary">
                        <i class="bi bi-2-circle"></i> 2 Fase
                    </span>
                    @endif
                    @else
                    <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($asesmen->is_collective)
                    @php
                    $paymentStatus = $asesmen->getBatchPaymentStatus();
                    @endphp

                    @if($paymentStatus === 'paid' || $paymentStatus === 'fully_paid')
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i> Lunas
                    </span>
                    @elseif($paymentStatus === 'phase_1_paid')
                    <span class="badge bg-warning">
                        <i class="bi bi-hourglass-split"></i> Fase 1 Lunas
                    </span>
                    <br>
                    <small class="text-muted">Fase 2 pending</small>
                    @elseif($paymentStatus === 'pending')
                    <span class="badge bg-warning">
                        <i class="bi bi-hourglass-split"></i> Pending
                    </span>
                    @else
                    <span class="badge bg-secondary">Belum Bayar</span>
                    @endif
                    @else
                    @if($asesmen->payment)
                    <span class="badge bg-{{ $asesmen->payment->status === 'verified' ? 'success' : 'warning' }}">
                        {{ $asesmen->payment->status_label }}
                    </span>
                    @else
                    <span class="badge bg-secondary">Belum Bayar</span>
                    @endif
                    @endif
                </td>
                <td>
                    <span class="badge bg-{{ $asesmen->status_badge }}">
                        {{ $asesmen->status_label }}
                    </span>
                    @if($asesmen->training_flag)
                    <br>
                    <small class="badge bg-warning text-dark mt-1">
                        <i class="bi bi-mortarboard-fill"></i> Pelatihan
                    </small>
                    @endif
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('tuk.asesi.show', $asesmen) }}" class="btn btn-outline-primary"
                            title="Detail">
                            <i class="bi bi-eye"></i>
                        </a>

                        @if($asesmen->status === 'data_completed' && !$asesmen->tuk_verified_at)
                        <a href="{{ route('tuk.verifications', $asesmen) }}" class="btn btn-outline-success"
                            title="Verifikasi">
                            <i class="bi bi-check-circle"></i>
                        </a>
                        @endif

                        @if($asesmen->status === 'paid' && !$asesmen->schedule)
                        <a href="{{ route('tuk.schedules.batch-create', ['asesmen_id' => $asesmen->id]) }}"
                            class="btn btn-outline-warning" title="Jadwalkan">
                            <i class="bi bi-calendar-plus"></i>
                        </a>
                        @endif

                        @if($asesmen->is_collective && $asesmen->collective_batch_id)
                        @php
                        // Check if need payment
                        $needPayment = false;
                        if ($asesmen->payment_phases === 'single') {
                        $needPayment = $asesmen->status === 'verified' &&
                        !$asesmen->payments()->where('payment_phase', 'full')->where('status',
                        'verified')->exists();
                        } else {
                        $phase1Paid = $asesmen->payments()->where('payment_phase', 'phase_1')->where('status',
                        'verified')->exists();
                        $phase2Paid = $asesmen->payments()->where('payment_phase', 'phase_2')->where('status',
                        'verified')->exists();

                        if ($asesmen->status === 'verified' && !$phase1Paid) {
                        $needPayment = true;
                        } elseif (in_array($asesmen->status, ['assessed', 'certified']) && $phase1Paid &&
                        !$phase2Paid) {
                        $needPayment = true;
                        }
                        }
                        @endphp

                        @if($needPayment)
                        <a href="{{ route('tuk.collective.payment', $asesmen->collective_batch_id) }}"
                            class="btn btn-outline-warning" title="Bayar">
                            <i class="bi bi-cash-coin"></i>
                        </a>
                        @endif
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif