@extends('layouts.app')
@section('title', 'Kelola Skema')
@section('page-title', 'Kelola Skema')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Daftar Skema Sertifikasi</h5>
        <a href="{{ route('admin.skemas.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Skema
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Skema</th>
                        <th>Jenis</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Asesi</th>
                        <th class="text-end">Biaya</th>
                        <th class="text-center">Status</th>
                        <th width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($skemas as $skema)
                    <tr>
                        <td><code>{{ $skema->code }}</code></td>
                        <td>
                            <strong>{{ $skema->name }}</strong>
                            @if($skema->nomor_skema)
                            <br><small class="text-muted">{{ $skema->nomor_skema }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $skema->jenis_badge }}">
                                {{ $skema->jenis_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            {{ $skema->unitKompetensis->count() ?? 0 }}
                        </td>
                        <td class="text-center">{{ $skema->asesmens_count }}</td>
                        <td class="text-end">Rp {{ number_format($skema->fee, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($skema->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                {{-- Detail & Unit Kompetensi --}}
                                <a href="{{ route('admin.skemas.show', $skema) }}"
                                    class="btn btn-sm btn-outline-primary" title="Detail & Unit Kompetensi">
                                    <i class="bi bi-eye"></i>
                                </a>
                                {{-- Edit --}}
                                <a href="{{ route('admin.skemas.edit', $skema) }}"
                                    class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection