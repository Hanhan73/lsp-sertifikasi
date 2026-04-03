@extends('layouts.app')

@section('title', 'Verifikasi Asesi Mandiri')
@section('page-title', 'Verifikasi Asesi Mandiri')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-person-check"></i> Asesi Mandiri Menunggu Verifikasi
        </h5>
        <div>
            <span class="badge bg-warning">{{ $asesmens->count() }} Perlu Verifikasi</span>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Alur Verifikasi Asesi Mandiri:</strong>
            <ol class="mb-0 mt-2">
                <li>Asesi mandiri mengisi data lengkap</li>
                <li><strong>Admin LSP verifikasi data dan tetapkan biaya</strong></li>
                <li>Admin assign asesi ke TUK yang sesuai</li>
                <li>Asesi melakukan pembayaran</li>
                <li>TUK melakukan penjadwalan</li>
            </ol>
        </div>

        @if($asesmens->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
            <h4 class="mt-3">Semua Asesi Mandiri Sudah Diverifikasi</h4>
            <p class="text-muted">Tidak ada data asesi mandiri yang menunggu verifikasi saat ini.</p>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover data-table">
                <thead>
                    <tr>
                        <th>No Reg</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Skema</th>
                        <th>Pelatihan</th>
                        <th>Biaya Estimasi</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesmens as $asesmen)
                    <tr>
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>{{ $asesmen->full_name }}</td>
                        <td>{{ $asesmen->user->email }}</td>
                        <td>
                            <span class="badge bg-primary">{{ $asesmen->skema->name }}</span>
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
                            @php
                            $estimatedFee = $asesmen->skema->fee + ($asesmen->training_flag ? 1500000 : 0);
                            @endphp
                            <strong>Rp {{ number_format($estimatedFee, 0, ',', '.') }}</strong>
                            @if($asesmen->training_flag)
                            <br><small class="text-muted">+ Pelatihan</small>
                            @endif
                        </td>
                        <td>{{ $asesmen->registration_date->translatedFormat('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('admin.mandiri.verify', $asesmen) }}" class="btn btn-sm btn-success"
                                data-bs-toggle="tooltip" title="Verifikasi & Tetapkan Biaya">
                                <i class="bi bi-check-circle"></i> Verifikasi
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

@push('scripts')
<script>
$(document).ready(function() {
    $('.data-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
        },
        order: [
            [6, 'asc']
        ], // Sort by registration date
        pageLength: 25
    });

    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush