<table class="table table-hover table-sm data-table">
    <thead class="table-light">
        <tr>
            <th>Kode</th>
            <th>Nama Skema</th>
            <th>Biaya Standar</th>
            <th>Durasi (Hari)</th>
            <th>Status</th>
            <th>Total Asesi</th>
            <th>Terverifikasi</th>
            <th>Tersertifikasi</th>
            <th>Total Pendapatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($skemas as $skema)
        <tr>
            <td><strong>{{ $skema->code }}</strong></td>
            <td>{{ $skema->name }}</td>
            <td>
                <strong>Rp {{ number_format($skema->fee, 0, ',', '.') }}</strong>
            </td>
            <td>{{ $skema->duration_days }} hari</td>
            <td>
                @if($skema->is_active)
                <span class="badge bg-success">Aktif</span>
                @else
                <span class="badge bg-secondary">Nonaktif</span>
                @endif
            </td>
            <td>
                <strong>{{ $skema->asesmens_count }}</strong>
            </td>
            <td>
                <span class="badge bg-primary">{{ $skema->verified_count }}</span>
            </td>
            <td>
                <span class="badge bg-success">{{ $skema->certified_count }}</span>
            </td>
            <td>
                @php
                // Calculate actual revenue (sum of fee_amount from asesmens)
                $totalRevenue = \App\Models\Asesmen::where('skema_id', $skema->id)
                ->whereNotNull('fee_amount')
                ->sum('fee_amount');
                @endphp
                @if($totalRevenue > 0)
                <strong class="text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="table-light">
        <tr>
            <th colspan="5" class="text-end">TOTAL:</th>
            <th><strong>{{ $skemas->sum('asesmens_count') }}</strong></th>
            <th><strong>{{ $skemas->sum('verified_count') }}</strong></th>
            <th><strong>{{ $skemas->sum('certified_count') }}</strong></th>
            <th>
                @php
                $grandTotal = 0;
                foreach($skemas as $skema) {
                $grandTotal += \App\Models\Asesmen::where('skema_id', $skema->id)
                ->whereNotNull('fee_amount')
                ->sum('fee_amount');
                }
                @endphp
                <strong class="text-success">Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
            </th>
        </tr>
    </tfoot>
</table>