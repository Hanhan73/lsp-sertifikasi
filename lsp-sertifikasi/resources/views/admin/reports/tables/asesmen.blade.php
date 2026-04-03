<table class="table table-hover table-sm data-table">
    <thead class="table-light">
        <tr>
            <th>No Reg</th>
            <th>Nama</th>
            <th>TUK</th>
            <th>Skema</th>
            <th>Jenis</th>
            <th>Tgl Daftar</th>
            <th>Status</th>
            <th>Biaya</th>
            <th>Pelatihan</th>
            <th>Tgl Verifikasi</th>
            <th>Tgl Bayar</th>
            <th>Tgl Asesmen</th>
            <th>Hasil</th>
            <th>Sertifikat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($asesmens as $asesmen)
        <tr>
            <td><strong>#{{ $asesmen->id }}</strong></td>
            <td>
                {{ $asesmen->full_name ?? $asesmen->user->name }}<br>
                <small class="text-muted">{{ $asesmen->user->email }}</small>
            </td>
            <td>{{ $asesmen->tuk->name ?? '-' }}</td>
            <td>
                <span class="badge bg-primary">{{ $asesmen->skema->name ?? '-' }}</span>
            </td>
            <td>
                @if($asesmen->is_collective)
                <span class="badge bg-info">Kolektif</span>
                @else
                <span class="badge bg-success">Mandiri</span>
                @endif
            </td>
            <td>{{ $asesmen->registration_date ? $asesmen->registration_date->translatedFormat('d/m/Y') : '-' }}</td>
            <td>
                <span class="badge bg-{{ $asesmen->status_badge }}">
                    {{ $asesmen->status_label }}
                </span>
            </td>
            <td>
                @if($asesmen->fee_amount)
                <strong>Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</strong>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($asesmen->training_flag)
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-mortarboard-fill"></i> Ya
                </span>
                @else
                <span class="badge bg-secondary">Tidak</span>
                @endif
            </td>
            <td>
                @if($asesmen->admin_verified_at)
                {{ $asesmen->admin_verified_at->translatedFormat('d/m/Y') }}
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($asesmen->payment && $asesmen->payment->verified_at)
                {{ $asesmen->payment->verified_at->translatedFormat('d/m/Y') }}
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($asesmen->schedule)
                {{ $asesmen->schedule->assessment_date->translatedFormat('d/m/Y') }}
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($asesmen->result)
                @if($asesmen->result === 'kompeten')
                <span class="badge bg-success">Kompeten</span>
                @else
                <span class="badge bg-danger">Belum Kompeten</span>
                @endif
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($asesmen->certificate)
                <span class="badge bg-success">
                    <i class="bi bi-file-earmark-check"></i> {{ $asesmen->certificate->certificate_number }}
                </span>
                @else
                <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>