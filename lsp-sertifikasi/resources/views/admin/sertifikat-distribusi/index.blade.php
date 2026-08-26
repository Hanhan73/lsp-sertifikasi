@extends('layouts.app')
@section('title', 'Distribusi Sertifikat')
@section('page-title', 'Distribusi Sertifikat')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm"><i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}</div>
@endif

{{-- ── BATCH KOLEKTIF ── --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-people-fill me-2 text-primary"></i>Batch Kolektif
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch ID</th><th>TUK</th><th>Skema</th><th class="text-center">Peserta</th>
                    <th class="text-center">Berita Acara</th><th class="text-center">Status</th><th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $b)
                <tr>
                    <td><code>{{ $b->batch_id }}</code></td>
                    <td>{{ $b->tuk->name ?? '-' }}</td>
                    <td class="small">{{ $b->skema->name ?? '-' }}</td>
                    <td class="text-center">{{ $b->total }}</td>
                    <td class="text-center">
                        @if($b->ada_ba)
                        <i class="bi bi-check-circle-fill text-success" title="Ada Berita Acara"></i>
                        @else
                        <i class="bi bi-x-circle-fill text-danger" title="Belum ada Berita Acara"></i>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($b->sudah_distribusi)
                        <span class="badge bg-success">Sudah Didistribusikan</span>
                        @if($b->sudah_upload)
                        <span class="badge bg-primary">Semua Sudah Upload</span>
                        @endif
                        @elseif($b->siap_distribusi)
                        <span class="badge bg-warning text-dark">Siap Distribusi</span>
                        @else
                        <span class="badge bg-secondary">Belum di-SK</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!$b->sudah_distribusi)
                        <form action="{{ route('admin.sertifikat-distribusi.batch', $b->batch_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning"
                                {{ (!$b->siap_distribusi || !$b->ada_ba) ? 'disabled' : '' }}
                                onclick="return confirm('Tandai batch {{ $b->batch_id }} sudah didistribusikan secara fisik?')">
                                <i class="bi bi-truck me-1"></i>Tandai Terdistribusi
                            </button>
                        </form>
                        @if(!$b->ada_ba)
                        <div class="small text-muted mt-1">Berita Acara belum ada</div>
                        @elseif(!$b->siap_distribusi)
                        <div class="small text-muted mt-1">Belum semua di-SK</div>
                        @endif
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada batch yang sudah diases.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── MANDIRI ── --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-person-fill me-2 text-success"></i>Asesi Mandiri
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama</th><th>Skema</th><th class="text-center">Berita Acara</th>
                    <th class="text-center">Status</th><th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mandiri as $m)
                <tr>
                    <td>{{ $m->full_name }}</td>
                    <td class="small">{{ $m->skema->name ?? '-' }}</td>
                    <td class="text-center">
                        @if($m->schedule?->beritaAcara)
                        <i class="bi bi-check-circle-fill text-success"></i>
                        @else
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $m->status_badge }}">{{ $m->status_label }}</span>
                        @if($m->status === 'certificate_distributed' && $m->hasUploadedPhysicalCertificate())
                        <span class="badge bg-primary">Sudah Upload</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($m->status === 'certified')
                        <form action="{{ route('admin.sertifikat-distribusi.individual', $m) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning"
                                {{ !$m->schedule?->beritaAcara ? 'disabled' : '' }}
                                onclick="return confirm('Tandai {{ $m->full_name }} sudah didistribusikan secara fisik?')">
                                <i class="bi bi-truck me-1"></i>Tandai Terdistribusi
                            </button>
                        </form>
                        @if(!$m->schedule?->beritaAcara)
                        <div class="small text-muted mt-1">Berita Acara belum ada</div>
                        @endif
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada asesi mandiri yang sudah diases.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection