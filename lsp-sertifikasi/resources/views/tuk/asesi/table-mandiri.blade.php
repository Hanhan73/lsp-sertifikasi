@if($data->isEmpty())
<div class="text-center py-4">
    <i class="bi bi-person" style="font-size: 3rem; color: #ccc;"></i>
    <h5 class="mt-3 text-muted">Belum Ada Asesi Mandiri</h5>
    <p class="text-muted">Asesi mandiri akan muncul di sini</p>
</div>
@else
<div class="table-responsive">
    <table class="table table-hover data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Skema</th>
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
                </td>
                <td>
                    <small>{{ $asesmen->email }}</small>
                </td>
                <td>{{ $asesmen->skema->name ?? '-' }}</td>
                <td>
                    <span class="badge bg-{{ $asesmen->status_badge }}">
                        {{ $asesmen->status_label }}
                    </span>
                    @if($asesmen->training_flag)
                    <br>
                    <small class="badge bg-warning text-dark mt-1">
                        <i class="bi bi-mortarboard"></i> Pelatihan
                    </small>
                    @endif
                </td>
                <td>
                    <small>{{ $asesmen->registration_date->format('d/m/Y H:i') }}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('tuk.asesi.show', $asesmen) }}" class="btn btn-outline-info" title="Detail">
                            <i class="bi bi-eye"></i>
                        </a>

                        @if($asesmen->status === 'data_completed' && !$asesmen->tuk_verified_at)
                        <a href="{{ route('tuk.verifications.show', $asesmen) }}" class="btn btn-outline-success"
                            title="Verifikasi">
                            <i class="bi bi-check-circle"></i>
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