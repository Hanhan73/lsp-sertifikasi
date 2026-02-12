<div class="table-responsive">
    <table class="table table-hover data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>Email</th>
                <th>Skema</th>
                <th>Type</th>
                <th>Dokumen</th>
                <th>Tgl Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $asesmen)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                    @if($asesmen->is_collective)
                    <br><small class="text-muted">
                        <i class="bi bi-people"></i> {{ $asesmen->collective_batch_id }}
                    </small>
                    @endif
                </td>
                <td>{{ $asesmen->nik ?? '-' }}</td>
                <td>{{ $asesmen->email }}</td>
                <td>{{ $asesmen->skema->name ?? '-' }}</td>
                <td>
                    @if($asesmen->is_collective)
                    <span class="badge bg-primary">Kolektif</span>
                    <br>
                    <small
                        class="badge bg-{{ $asesmen->collective_payment_timing === 'before' ? 'warning' : 'success' }}">
                        Bayar: {{ $asesmen->collective_payment_timing === 'before' ? 'Sebelum' : 'Setelah' }}
                    </small>
                    @else
                    <span class="badge bg-secondary">Mandiri</span>
                    @endif
                </td>
                <td>
                    @if($asesmen->photo_path && $asesmen->ktp_path && $asesmen->document_path)
                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Lengkap</span>
                    @else
                    <span class="badge bg-warning"><i class="bi bi-exclamation-circle"></i> Belum Lengkap</span>
                    @endif
                </td>
                <td>{{ $asesmen->registration_date->format('d/m/Y') }}</td>
                <td>
                    <a href="{{ route('tuk.verifications.show', $asesmen) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i> Lihat & Verifikasi
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 2rem; color: #ccc;"></i>
                    <p class="text-muted mb-0 mt-2">Tidak ada data</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>