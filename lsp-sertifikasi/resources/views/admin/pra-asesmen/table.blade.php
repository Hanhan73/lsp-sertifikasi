<div class="table-responsive">
    <table class="table table-hover align-middle" id="verification-table">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NIK</th>
                <th>Email</th>
                <th>Skema</th>
                <th>Jenis</th>
                <th class="text-center">Dokumen</th>
                <th>Tgl Daftar</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $asesmen)
            <tr>
                <td class="text-muted">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $asesmen->full_name ?? $asesmen->user->name }}</strong>
                    @if($asesmen->is_collective)
                    <div class="text-muted small">
                        <i class="bi bi-people"></i> {{ $asesmen->collective_batch_id }}
                    </div>
                    @endif
                </td>
                <td><code>{{ $asesmen->nik ?? '-' }}</code></td>
                <td class="small">{{ $asesmen->user->email ?? $asesmen->email ?? '-' }}</td>
                <td class="small">{{ $asesmen->skema->name ?? '-' }}</td>
                <td>
                    @if($asesmen->is_collective)
                        <span class="badge bg-primary">Kolektif</span>
                    @else
                        <span class="badge bg-success">Mandiri</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($asesmen->photo_path && $asesmen->ktp_path && $asesmen->document_path)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>Lengkap
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-circle me-1"></i>Belum
                        </span>
                    @endif
                </td>
                <td class="small">{{ $asesmen->registration_date->format('d/m/Y') }}</td>
                <td class="text-center">
                    <span class="badge bg-{{ $asesmen->status_badge }}">
                        {{ $asesmen->status_label }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox" style="font-size: 2rem; opacity:.4;"></i>
                    <p class="mt-2 mb-0">Tidak ada data</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>