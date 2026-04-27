@extends('layouts.app')

@section('title', 'Honor Asesor')
@section('page-title', 'Honor Asesor')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center gap-2">
        <i class="bi bi-person-badge text-primary"></i>
        <span class="fw-semibold">Daftar Asesor — Berita Acara Sudah Diupload</span>
        <span class="badge bg-primary ms-auto">{{ $asesors->count() }} Asesor</span>
    </div>
    <div class="card-body p-0">
        @if($asesors->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada asesor dengan berita acara yang diupload.
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Asesor</th>
                        <th>No. Reg MET</th>
                        <th class="text-center">Jadwal Selesai</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesors as $i => $asesor)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $asesor->nama }}</div>
                            <div class="text-muted small">{{ $asesor->email }}</div>
                        </td>
                        <td class="small">{{ $asesor->no_reg_met ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $asesor->schedules->count() }} Jadwal</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('bendahara.honor.show', $asesor) }}"
                                class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Detail
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