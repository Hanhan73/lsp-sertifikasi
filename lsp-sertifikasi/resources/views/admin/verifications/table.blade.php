<div class="table-responsive">
    <table class="table table-hover data-table">
        <thead>
            <tr>
                <th>No Reg</th>
                <th>Nama</th>
                <th>Email</th>
                <th>TUK</th>
                <th>Skema</th>
                <th>Jenis</th>
                <th>Metode Bayar</th>
                <th>Pelatihan</th>
                <th>Verifikasi TUK</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $asesmen)
            <tr>
                <td><strong>#{{ $asesmen->id }}</strong></td>
                <td>{{ $asesmen->full_name ?? $asesmen->user->name }}</td>
                <td>{{ $asesmen->email }}</td>
                <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                <td>{{ $asesmen->skema->name ?? '-' }}</td>
                <td>
                    @if($asesmen->is_collective)
                    <span class="badge bg-primary">Kolektif</span>
                    <br><small class="text-muted">{{ $asesmen->collective_batch_id }}</small>
                    @else
                    <span class="badge bg-success">Mandiri</span>
                    @endif
                </td>
                <td>
                    @if($asesmen->payment_phases === 'single')
                    <span class="badge bg-success">
                        <i class="bi bi-cash-stack"></i> 1 Fase
                    </span>
                    @else
                    <span class="badge bg-primary">
                        <i class="bi bi-cash-coin"></i> 2 Fase
                    </span>
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
                    @if($asesmen->tuk_verified_at)
                    <span class="badge bg-success">
                        <i class="bi bi-check-circle"></i>
                        {{ $asesmen->tuk_verified_at->format('d/m/Y') }}
                    </span>
                    <br>
                    <small class="text-muted">{{ $asesmen->tukVerifier->name ?? '-' }}</small>
                    @else
                    <span class="badge bg-secondary">Belum</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.verifications.show', $asesmen) }}" class="btn btn-sm btn-warning"
                        data-bs-toggle="tooltip" title="Tetapkan Biaya">
                        <i class="bi bi-cash-coin"></i> Proses
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2">Tidak ada data</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>