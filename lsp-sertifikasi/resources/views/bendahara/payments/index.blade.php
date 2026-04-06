@extends('layouts.app')
@section('title', 'Daftar Pembayaran')
@section('page-title', 'Daftar Pembayaran Mandiri')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="search" class="form-control form-control-sm"
                placeholder="Cari nama / NIK…" value="{{ request('search') }}" style="max-width:220px;">
            <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Menunggu</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Cari</button>
            @if(request('search') || request('status'))
            <a href="{{ route('bendahara.payments.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Asesi</th>
                    <th>Skema</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Tgl Upload</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td class="text-muted small">{{ $p->id }}</td>
                    <td>
                        <div class="fw-semibold">{{ $p->asesmen->full_name }}</div>
                        <div class="small text-muted">{{ $p->asesmen->tuk->name ?? '-' }}</div>
                    </td>
                    <td><span class="badge bg-light text-dark border">{{ Str::limit($p->asesmen->skema->name ?? '-', 30) }}</span></td>
                    <td><span class="badge bg-secondary">{{ strtoupper($p->method) }}</span></td>
                    <td class="fw-semibold text-success">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td>
                        @if($p->proof_path)
                        <a href="{{ route('bendahara.payments.download-bukti', $p) }}"
                           class="btn btn-sm btn-outline-secondary" target="_blank">
                            <i class="bi bi-file-earmark-arrow-down"></i>
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($p->status === 'pending')
                        <span class="badge bg-warning text-dark">Menunggu</span>
                        @elseif($p->status === 'verified')
                        <span class="badge bg-success">Terverifikasi</span>
                        @else
                        <span class="badge bg-danger">Ditolak</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $p->created_at->translatedFormat('d M Y') }}</td>
                    <td>
                        <a href="{{ route('bendahara.payments.show', $p) }}" class="btn btn-sm btn-primary">
                            @if($p->status === 'pending' && $p->proof_path)
                            <i class="bi bi-check2-circle me-1"></i>Verifikasi
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