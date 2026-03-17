<table class="table table-hover table-sm data-table">
    <thead class="table-light">
        <tr>
            <th>No Reg</th>
            <th>Nama Asesi</th>
            <th>TUK</th>
            <th>Skema</th>
            <th>Jenis</th>
            <th>Fase</th>
            <th>Jumlah</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Tgl Bayar</th>
            <th>Tgl Verifikasi</th>
            <th>Verifikasi Oleh</th>
            <th>Transaction ID</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td><strong>#{{ $payment->asesmen->id }}</strong></td>
            <td>
                {{ $payment->asesmen->full_name ?? $payment->asesmen->user->name }}<br>
                <small class="text-muted">{{ $payment->asesmen->user->email }}</small>
            </td>
            <td>{{ $payment->asesmen->tuk->name ?? '-' }}</td>
            <td>
                <span class="badge bg-primary">{{ $payment->asesmen->skema->name ?? '-' }}</span>
            </td>
            <td>
                @if($payment->asesmen->is_collective)
                <span class="badge bg-info">Kolektif</span>
                @else
                <span class="badge bg-success">Mandiri</span>
                @endif
            </td>
            <td>
                @if($payment->payment_phase === 'full')
                <span class="badge bg-primary">Full</span>
                @elseif($payment->payment_phase === 'phase_1')
                <span class="badge bg-info">Fase 1</span>
                @else
                <span class="badge bg-success">Fase 2</span>
                @endif
            </td>
            <td>
                <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
            </td>
            <td>{{ ucfirst($payment->method) }}</td>
            <td>
                <span class="badge bg-{{ $payment->status_badge }}">
                    {{ $payment->status_label }}
                </span>
            </td>
            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
            <td>
                @if($payment->verified_at)
                {{ $payment->verified_at->format('d/m/Y H:i') }}
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($payment->is_auto_verified)
                <span class="badge bg-success">
                    <i class="bi bi-robot"></i> Otomatis
                </span>
                @elseif($payment->verifier)
                <span class="badge bg-info">
                    <i class="bi bi-person-check"></i> {{ $payment->verifier->name }}
                </span>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($payment->transaction_id)
                <small class="font-monospace">{{ Str::limit($payment->transaction_id, 20) }}</small>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>