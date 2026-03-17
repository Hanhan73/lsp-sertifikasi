@extends('layouts.app')

@section('title', 'Penetapan Biaya Kolektif')
@section('page-title', 'Penetapan Biaya Sertifikasi Kolektif')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-layers"></i> Batch Kolektif Menunggu Penetapan Biaya
        </h5>
        <div>
            <span class="badge bg-warning">{{ $batches->count() }} Batch</span>
            <a href="{{ route('admin.mandiri.verifications') }}" class="btn btn-sm btn-primary ms-2">
                <i class="bi bi-person"></i> Verifikasi Mandiri
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($batches->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
            <h4 class="mt-3">Semua Batch Sudah Ditetapkan Biaya</h4>
            <p class="text-muted">Tidak ada batch kolektif yang menunggu penetapan biaya saat ini.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Batch ID</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th>Total Peserta</th>
                        <th>Dengan Pelatihan</th>
                        <th>Metode Bayar</th>
                        <th>Verifikasi TUK</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batchId => $batchAsesmens)
                    @php
                    $firstBatch = $batchAsesmens->first();
                    $trainingCount = $batchAsesmens->where('training_flag', true)->count();
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $batchId }}</strong>
                        </td>
                        <td>{{ $firstBatch->tuk->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $firstBatch->skema->name ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ $batchAsesmens->count() }}</strong> orang
                        </td>
                        <td>
                            @if($trainingCount > 0)
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-mortarboard-fill"></i> {{ $trainingCount }} orang
                            </span>
                            @else
                            <span class="badge bg-secondary">Tidak ada</span>
                            @endif
                        </td>
                        <td>
                            @if($firstBatch->payment_phases === 'single')
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
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i>
                                {{ $firstBatch->tuk_verified_at->format('d/m/Y') }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.verifications.batch.show', $batchId) }}"
                                class="btn btn-sm btn-warning">
                                <i class="bi bi-cash-coin"></i> Tetapkan Biaya
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection