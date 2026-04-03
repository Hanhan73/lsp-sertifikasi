<table class="table table-hover table-sm data-table">
    <thead class="table-light">
        <tr>
            <th>Tanggal</th>
            <th>No Reg</th>
            <th>Nama Asesi</th>
            <th>TUK</th>
            <th>Skema</th>
            <th>Jenis</th>
            <th>Fase</th>
            <th>Pemasukan</th>
            <th>Metode</th>
            <th>Transaction ID</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td>{{ $payment->verified_at ? $payment->verified_at->translatedFormat('d/m/Y') : $payment->created_at->translatedFormat('d/m/Y') }}
            </td>
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
                @if($payment->asesmen->collective_batch_id)
                <br><small class="text-muted">{{ Str::limit($payment->asesmen->collective_batch_id, 15) }}</small>
                @endif
                @else
                <span class="badge bg-success">Mandiri</span>
                @endif
            </td>
            <td>
                @if($payment->payment_phase === 'full')
                <span class="badge bg-primary">Full</span>
                @elseif($payment->payment_phase === 'phase_1')
                <span class="badge bg-info">Fase 1 (50%)</span>
                @else
                <span class="badge bg-success">Fase 2 (50%)</span>
                @endif
            </td>
            <td>
                <strong class="text-success">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
            </td>
            <td>
                @if($payment->is_auto_verified)
                <span class="badge bg-success">
                    <i class="bi bi-robot"></i> Auto
                </span>
                @else
                <span class="badge bg-info">
                    <i class="bi bi-person-check"></i> Manual
                </span>
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
    <tfoot class="table-light">
        <tr>
            <th colspan="7" class="text-end">TOTAL PEMASUKAN:</th>
            <th colspan="3">
                <strong class="text-success">Rp {{ number_format($payments->sum('amount'), 0, ',', '.') }}</strong>
            </th>
        </tr>
    </tfoot>
</table>