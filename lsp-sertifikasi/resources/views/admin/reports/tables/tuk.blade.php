<table class="table table-hover table-sm data-table">
    <thead class="table-light">
        <tr>
            <th>Kode</th>
            <th>Nama TUK</th>
            <th>Alamat</th>
            <th>Manager</th>
            <th>Status</th>
            <th>Total Asesi</th>
            <th>Terverifikasi</th>
            <th>Tersertifikasi</th>
            <th>Total Biaya</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tuks as $tuk)
        <tr>
            <td><strong>{{ $tuk->code }}</strong></td>
            <td>{{ $tuk->name }}</td>
            <td>{{ Str::limit($tuk->address, 50) }}</td>
            <td>{{ $tuk->manager_name ?? '-' }}</td>
            <td>
                @if($tuk->is_active)
                <span class="badge bg-success">Aktif</span>
                @else
                <span class="badge bg-secondary">Nonaktif</span>
                @endif
            </td>
            <td>
                <strong>{{ $tuk->asesmens_count }}</strong>
            </td>
            <td>
                <span class="badge bg-primary">{{ $tuk->verified_count }}</span>
            </td>
            <td>
                <span class="badge bg-success">{{ $tuk->certified_count }}</span>
            </td>
            <td>
                @php
                $totalFee = $tuk->asesmens->sum('fee_amount');
                @endphp
                @if($totalFee > 0)
                <strong class="text-success">Rp {{ number_format($totalFee, 0, ',', '.') }}</strong>
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
            <th><strong>{{ $tuks->sum('asesmens_count') }}</strong></th>
            <th><strong>{{ $tuks->sum('verified_count') }}</strong></th>
            <th><strong>{{ $tuks->sum('certified_count') }}</strong></th>
            <th>
                <strong class="text-success">
                    Rp {{ number_format($tuks->sum(fn($t) => $t->asesmens->sum('fee_amount')), 0, ',', '.') }}
                </strong>
            </th>
        </tr>
    </tfoot>
</table>