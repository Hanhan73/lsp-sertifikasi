@extends('layouts.app')

@section('title', 'Verifikasi Asesi - TUK')
@section('page-title', 'Verifikasi Data Asesi')

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list-check"></i> Asesi Menunggu Verifikasi TUK</h5>
        <div>
            <span class="badge bg-warning">{{ $asesmens->count() }} Perlu Verifikasi</span>
        </div>
    </div>
    <div class="card-body">
        @if($asesmens->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
            <h4 class="mt-3">Semua Asesi Sudah Terverifikasi</h4>
            <p class="text-muted">Tidak ada data asesi yang menunggu verifikasi TUK saat ini.</p>
        </div>
        @else
        <!-- Filter Tabs -->
        <ul class="nav nav-tabs mb-3" id="filterTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button">
                    Semua <span class="badge bg-secondary ms-1">{{ $asesmens->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual"
                    type="button">
                    Mandiri <span
                        class="badge bg-primary ms-1">{{ $asesmens->where('is_collective', false)->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="collective-tab" data-bs-toggle="tab" data-bs-target="#collective"
                    type="button">
                    Kolektif <span
                        class="badge bg-info ms-1">{{ $asesmens->where('is_collective', true)->count() }}</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="filterTabsContent">
            <!-- All -->
            <div class="tab-pane fade show active" id="all" role="tabpanel">
                @include('tuk.verifications.table', ['data' => $asesmens])
            </div>

            <!-- Individual -->
            <div class="tab-pane fade" id="individual" role="tabpanel">
                @include('tuk.verifications.table', ['data' => $asesmens->where('is_collective', false)])
            </div>

            <!-- Collective -->
            <div class="tab-pane fade" id="collective" role="tabpanel">
                @include('tuk.verifications.table', ['data' => $asesmens->where('is_collective', true)])
            </div>
        </div>

        <!-- Batch Verification Section for Collective -->
        @php
        $collectiveBatches = $asesmens->where('is_collective', true)
        ->whereNotNull('collective_batch_id')
        ->groupBy('collective_batch_id');
        @endphp

        @if($collectiveBatches->count() > 0)
        <hr>
        <h6><i class="bi bi-layers"></i> Verifikasi Batch Kolektif</h6>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Anda dapat memverifikasi seluruh batch sekaligus setelah memeriksa semua data peserta.
        </div>

        <div class="accordion" id="batchAccordion">
            @foreach($collectiveBatches as $batchId => $batchAsesmens)
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#batch-{{ md5($batchId) }}">
                        <strong>{{ $batchId }}</strong>
                        <span class="badge bg-primary ms-2">{{ $batchAsesmens->count() }} peserta</span>
                        <span class="badge bg-info ms-2">{{ $batchAsesmens->first()->skema->name ?? '-' }}</span>
                        <span
                            class="badge bg-{{ $batchAsesmens->first()->collective_payment_timing === 'before' ? 'warning' : 'success' }} ms-2">
                            Bayar:
                            {{ $batchAsesmens->first()->collective_payment_timing === 'before' ? 'Sebelum' : 'Setelah' }}
                            Asesmen
                        </span>
                    </button>
                </h2>
                <div id="batch-{{ md5($batchId) }}" class="accordion-collapse collapse"
                    data-bs-parent="#batchAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>NIK</th>
                                        <th>Status Dokumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($batchAsesmens as $asesmen)
                                    <tr>
                                        <td>{{ $asesmen->full_name ?? $asesmen->user->name }}</td>
                                        <td>{{ $asesmen->email }}</td>
                                        <td>{{ $asesmen->nik ?? '-' }}</td>
                                        <td>
                                            @if($asesmen->photo_path && $asesmen->ktp_path && $asesmen->document_path)
                                            <span class="badge bg-success">Lengkap</span>
                                            @else
                                            <span class="badge bg-warning">Belum Lengkap</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <form action="{{ route('tuk.verifications.batch') }}" method="POST" class="row g-3">
                            @csrf
                            <input type="hidden" name="batch_id" value="{{ $batchId }}">

                            <div class="col-12">
                                <label class="form-label">Catatan Verifikasi (Opsional)</label>
                                <textarea class="form-control" name="notes" rows="3"
                                    placeholder="Catatan untuk batch ini (misal: Semua dokumen lengkap dan sesuai)"></textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="confirm-batch-{{ md5($batchId) }}" required>
                                    <label class="form-check-label" for="confirm-batch-{{ md5($batchId) }}">
                                        Saya telah memeriksa semua data dan dokumen {{ $batchAsesmens->count() }}
                                        peserta dalam batch ini
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-all"></i> Verifikasi Batch ({{ $batchAsesmens->count() }}
                                    peserta)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Make sure the table exists before trying to initialize DataTables
    if ($('#verification-table').length) {
        $('#verification-table').DataTable({
            // Optional: Add language for Indonesian
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            // Optional: Define column targets if needed
            "columnDefs": [{
                    "targets": 0,
                    "orderable": false
                }, // Disable sorting on the 'No' column
                {
                    "targets": 8,
                    "orderable": false
                } // Disable sorting on the 'Aksi' column
            ],
            // Optional: Set default page length
            "pageLength": 25
        });
    }
});
</script>
@endpush