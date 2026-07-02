@extends('layouts.app')

@section('title', 'Honor Saya')
@section('page-title', 'Honor Asesor')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
        class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Warning kalau ada yang perlu dikonfirmasi --}}
@php $adaMenungguKonfirmasi = $honors->where('status', 'sudah_dibayar')->isNotEmpty(); @endphp
@if($adaMenungguKonfirmasi)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
    <div>
        <strong>Ada honor yang menunggu konfirmasi Anda.</strong>
        Silakan buka detail dan konfirmasi penerimaan honor.
    </div>
</div>
@endif

@if($hutangAktif->isNotEmpty())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-wallet2 me-1 text-warning"></i>Ringkasan Hutang Anda
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-4">
                <div class="text-muted small">Total Hutang</div>
                <div class="fw-semibold">Rp {{ number_format($totalHutangAwal, 0, ',', '.') }}</div>
            </div>
            <div class="col-4">
                <div class="text-muted small">Sudah Dicicil</div>
                <div class="fw-semibold text-success">Rp {{ number_format($totalSudahDicicil, 0, ',', '.') }}</div>
            </div>
            <div class="col-4">
                <div class="text-muted small">Sisa Hutang</div>
                <div class="fw-bold text-danger fs-6">Rp {{ number_format($totalSisaHutang, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Uraian</th>
                        <th>Tanggal</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Sudah Dicicil</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hutangAktif as $h)
                    <tr>
                        <td class="small">{{ $h->uraian ?? $h->jenis_label }}</td>
                        <td class="small">{{ optional($h->tanggal)->translatedFormat('d M Y') }}</td>
                        <td class="text-end small">Rp {{ number_format($h->jumlah, 0, ',', '.') }}</td>
                        <td class="text-end small text-success">Rp {{ number_format($h->jumlah_lunas ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end small fw-semibold text-danger">Rp {{ number_format($h->sisa, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $h->status === 'cicilan' ? 'warning text-dark' : 'secondary' }}" style="font-size:.65rem;">
                                {{ $h->status === 'cicilan' ? 'Sedang Dicicil' : 'Belum Dicicil' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-cash-coin me-1 text-success"></i>Riwayat Honor
    </div>
    <div class="card-body p-0">
        @if($honors->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Belum ada honor yang dibuat.
        </div>
        @else
        <div class="list-group list-group-flush">
            @foreach($honors as $honor)
            <div class="list-group-item px-4 py-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold font-monospace small">{{ $honor->nomor_kwitansi }}</div>
                        <div class="text-muted small">
                            {{ $honor->details->count() }} jadwal
                            @if($honor->asesor_can_view)
                            &bull; Total Honor: <strong>Rp {{ number_format($honor->total, 0, ',', '.') }}</strong>
                            @if($honor->has_deduction)
                            &bull; Transfer Bersih:
                            <strong class="text-success">Rp {{ number_format($honor->jumlah_transfer, 0, ',', '.') }}</strong>
                            <span class="text-danger">(- Rp {{ number_format($honor->deduction_amount, 0, ',', '.') }} cicilan)</span>
                            @endif
                            @else
                            &bull; <em class="text-muted">Nominal akan tampil setelah pembayaran diproses</em>
                            @endif
                        </div>
                        <div class="text-muted small">
                            {{ optional($honor->tanggal_kwitansi)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $honor->status_badge }}">{{ $honor->status_label }}</span>
                        @if($honor->isSudahDibayar())
                        <div class="text-danger small mt-1 fw-semibold">
                            <i class="bi bi-exclamation-circle me-1"></i>Perlu konfirmasi
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if($honor->asesor_can_view)
                <div class="d-flex gap-2 mt-2">
                    <a href="{{ route('asesor.honor.show', $honor) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>
                    @if($honor->isSudahDibayar())
                    <form action="{{ route('asesor.honor.konfirmasi', $honor) }}" method="POST"
                          onsubmit="return confirm('Konfirmasi bahwa Anda sudah menerima honor ini?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="bi bi-check-circle me-1"></i>Konfirmasi
                        </button>
                    </form>
                    @endif
                    @if($honor->isDikonfirmasi())
                    <a href="{{ route('asesor.honor.kwitansi', $honor) }}"
                       class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download me-1"></i>Kwitansi
                    </a>
                    @endif
                </div>
                @else
                <div class="mt-2">
                    <span class="badge bg-light text-muted border" style="font-size:.75rem;">
                        <i class="bi bi-lock me-1"></i>Menunggu proses pembayaran dari LSP
                    </span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection