@extends('layouts.app')
@section('title', 'Monitor Pembayaran')
@section('page-title', 'Monitor & Verifikasi Pembayaran')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-warning">{{ $summary['pending'] }}</div>
            <div class="small text-muted">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-success">{{ $summary['auto_verified'] }}</div>
            <div class="small text-muted">Auto-Verified</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-info">{{ $summary['manual_verified'] }}</div>
            <div class="small text-muted">Manual Verified</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-danger">{{ $summary['rejected'] }}</div>
            <div class="small text-muted">Rejected</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Cari nama / No Reg / NIK…"
                value="{{ request('search') }}" style="max-width: 230px;">

            <select name="status" class="form-select form-select-sm" style="width: auto;"
                onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <select name="verification" class="form-select form-select-sm" style="width: auto;"
                onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                <option value="auto"   {{ request('verification') === 'auto'   ? 'selected' : '' }}>Auto-Verified</option>
                <option value="manual" {{ request('verification') === 'manual' ? 'selected' : '' }}>Manual Verified</option>
            </select>

            <button type="submit" class="btn btn-sm btn-primary">Cari</button>

            @if(request('search') || request('status') || request('verification'))
            <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible mx-3 mt-3 mb-0">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible mx-3 mt-3 mb-0">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Asesi</th>
                    <th>Skema</th>
                    <th>TUK</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Verifikasi</th>
                    <th>Tgl Pembayaran</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td class="text-muted small">#{{ $p->asesmen->id ?? $p->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $p->asesmen->full_name ?? '-' }}</div>
                        <div class="small text-muted">{{ $p->asesmen->email ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            {{ Str::limit($p->asesmen->skema->name ?? '-', 30) }}
                        </span>
                    </td>
                    <td class="small">{{ $p->asesmen->tuk->name ?? '-' }}</td>
                    <td class="fw-semibold text-success">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td>
                        @if($p->status === 'pending')
                        <span class="badge bg-warning text-dark">Menunggu</span>
                        @elseif($p->status === 'verified')
                        <span class="badge bg-success">Terverifikasi</span>
                        @else
                        <span class="badge bg-danger">Ditolak</span>
                        @endif
                    </td>
                    <td>
                        @if($p->status === 'verified')
                            @if($p->is_auto_verified)
                            <span class="badge bg-success">
                                <i class="bi bi-robot"></i> Auto
                            </span>
                            @else
                            <span class="badge bg-info">
                                <i class="bi bi-person-check"></i> Manual
                            </span>
                            @endif
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $p->created_at->translatedFormat('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.payments.show', $p) }}"
                           class="btn btn-sm {{ $p->status === 'pending' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            @if($p->status === 'pending')
                            <i class="bi bi-eye me-1"></i>Periksa
                            @else
                            <i class="bi bi-eye me-1"></i>Detail
                            @endif
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Tidak ada data pembayaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
    <div class="card-footer bg-white">
        {{ $payments->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection