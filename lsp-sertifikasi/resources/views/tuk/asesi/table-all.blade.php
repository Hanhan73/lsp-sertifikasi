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
                <th>Status</th>
                <th>Tanggal Daftar</th>
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
                </td>
                <td>{{ $asesmen->skema->name ?? '-' }}</td>
                <td>
                    @if($asesmen->is_collective)
                    <span class="badge bg-primary">
                        <i class="bi bi-people"></i> Kolektif
                    </span>
                    <br>
                    <small class="text-muted">Batch: {{ Str::limit($asesmen->collective_batch_id, 15) }}</small>
                    @else
                    <span class="badge bg-success">
                        <i class="bi bi-person"></i> Mandiri
                    </span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-{{ $asesmen->status_badge }}">
                        {{ $asesmen->status_label }}
                    </span>
                </td>
                <td>
                    <small>{{ $asesmen->registration_date->format('d/m/Y') }}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('tuk.asesi.show', $asesmen) }}" class="btn btn-outline-info" title="Detail">
                            <i class="bi bi-eye"></i>
                        </a>

                        @if($asesmen->is_collective)
                        <a href="{{ route('tuk.batch.detail', $asesmen->collective_batch_id) }}"
                            class="btn btn-outline-primary" title="Lihat Batch">
                            <i class="bi bi-collection"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif