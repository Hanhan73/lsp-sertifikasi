<div class="table-responsive">
    <table class="table table-hover datatable">
        <thead>
            <tr>
                <th>No Reg</th>
                <th>Nama</th>
                <th>TUK</th>
                <th>Skema</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Jadwal Asesmen</th>
                <th>Pra-Asesmen</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $asesmen)
            <tr>
                <td><strong>#{{ $asesmen->id }}</strong></td>
                <td>
                    {{ $asesmen->full_name ?? $asesmen->user->name }}
                    @if($asesmen->is_collective)
                    <br><small class="text-muted">{{ $asesmen->collective_batch_id }}</small>
                    @endif
                </td>
                <td>{{ $asesmen->tuk->name ?? '-' }}</td>
                <td>{{ $asesmen->skema->name ?? '-' }}</td>
                <td>
                    @if($asesmen->is_collective)
                    <span class="badge bg-primary">Kolektif</span>
                    @else
                    <span class="badge bg-success">Mandiri</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-{{ $asesmen->status_badge }}">
                        {{ $asesmen->status_label }}
                    </span>
                </td>
                <td>
                    @if($asesmen->schedule)
                    <i class="bi bi-calendar-check text-success"></i>
                    {{ $asesmen->schedule->assessment_date->format('d/m/Y') }}
                    <br>
                    <small class="text-muted">{{ $asesmen->schedule->start_time }} -
                        {{ $asesmen->schedule->end_time }}</small>
                    @else
                    <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($asesmen->pre_assessment_file)
                    <a href="{{ asset('storage/' . $asesmen->pre_assessment_file) }}" target="_blank"
                        class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Lihat File Pra-Asesmen">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </a>
                    @endif
                    @if($asesmen->pre_assessment_data)
                    <button class="btn btn-sm btn-secondary"
                        onclick="viewPreAssessment('{{ $asesmen->pre_assessment_data }}')" data-bs-toggle="tooltip"
                        title="Lihat Data Pra-Asesmen">
                        <i class="bi bi-eye"></i>
                    </button>
                    @endif
                    @if(!$asesmen->pre_assessment_file && !$asesmen->pre_assessment_data)
                    <span class="text-muted">Belum ada</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-primary"
                        onclick="inputResult({{ $asesmen->id }}, '{{ $asesmen->full_name }}', '{{ $asesmen->skema->name ?? '' }}')"
                        data-bs-toggle="tooltip" title="Input Hasil">
                        <i class="bi bi-clipboard-check"></i> Input Hasil
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2">Tidak ada data</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
function viewPreAssessment(data) {
    Swal.fire({
        title: 'Data Pra-Asesmen',
        html: `<div class="text-start"><pre>${data}</pre></div>`,
        width: '600px',
        confirmButtonText: 'Tutup'
    });
}
</script>
@endpush