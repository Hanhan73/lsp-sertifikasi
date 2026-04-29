@extends('layouts.app')

@section('title', 'Detail Honor')
@section('page-title', 'Detail Honor')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- Kiri: Detail --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                <span class="fw-semibold">{{ $honor->nomor_kwitansi }}</span>
                <span class="badge bg-{{ $honor->status_badge }} ms-auto">{{ $honor->status_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Tanggal Kwitansi</div>
                        <div>{{ optional($honor->tanggal_kwitansi)->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Total Honor</div>
                        <div class="fw-bold fs-6">Rp {{ number_format($honor->total, 0, ',', '.') }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Skema</th>
                                <th>TUK / Tanggal</th>
                                <th class="text-center">Asesi</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($honor->details as $i => $detail)
                            <tr>
                                <td class="text-muted small">{{ $i+1 }}</td>
                                <td class="small fw-semibold">{{ $detail->schedule->skema->name }}</td>
                                <td class="small">
                                    {{ $detail->schedule->tuk->name ?? '-' }}<br>
                                    <span class="text-muted">{{ optional($detail->schedule->assessment_date)->translatedFormat('d M Y') }}</span>
                                </td>
                                <td class="text-center small">{{ $detail->jumlah_asesi }}</td>
                                <td class="text-end small fw-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="4" class="fw-bold text-end">Total</td>
                                <td class="text-end fw-bold">Rp {{ number_format($honor->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Kanan: Aksi --}}
    <div class="col-lg-5">

        {{-- Status card --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-arrow-right-circle me-1"></i>Status Pembayaran
            </div>
            <div class="card-body py-2">
                @php
                    $steps = [
                        ['label' => 'Kwitansi Dibuat',      'done' => true],
                        ['label' => 'Transfer dari LSP KAP', 'done' => in_array($honor->status, ['sudah_dibayar','dikonfirmasi'])],
                        ['label' => 'Konfirmasi Anda',       'done' => $honor->isDikonfirmasi()],
                    ];
                @endphp
                @foreach($steps as $step)
                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                    <i class="bi {{ $step['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                    <span class="small {{ $step['done'] ? 'fw-semibold' : 'text-muted' }}">{{ $step['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Bukti Transfer --}}
        @if($honor->isSudahDibayar() || $honor->isDikonfirmasi())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-receipt me-1 text-info"></i>Bukti Transfer
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="text-muted small">Ditransfer pada</div>
                    <div>{{ optional($honor->dibayar_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>

                @include('bendahara.honor._bukti-preview', ['honor' => $honor])

                <a href="{{ route('asesor.honor.bukti.download', $honor) }}"
                class="btn btn-sm btn-outline-info w-100 mb-3">
                    <i class="bi bi-download me-1"></i>Download Bukti Transfer
                </a>

                @if($honor->isSudahDibayar())
                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Silakan cek bukti transfer di atas, lalu konfirmasi penerimaan honor.
                </div>
                <form action="{{ route('asesor.honor.konfirmasi', $honor) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100"
                        onclick="return confirm('Konfirmasi bahwa Anda sudah menerima honor ini?')">
                        <i class="bi bi-check-circle me-1"></i>Konfirmasi Penerimaan Honor
                    </button>
                </form>
                @endif
            </div>
        </div>

        @endif

        {{-- Download Kwitansi --}}
        @if($honor->isDikonfirmasi())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-file-pdf me-1 text-danger"></i>Kwitansi
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <a href="{{ route('asesor.honor.kwitansi', ['honor' => $honor, 'preview' => 1]) }}"
                       target="_blank" class="btn btn-sm btn-outline-danger flex-fill">
                        <i class="bi bi-eye me-1"></i>Preview
                    </a>
                    <a href="{{ route('asesor.honor.kwitansi', $honor) }}"
                       class="btn btn-sm btn-danger flex-fill">
                        <i class="bi bi-download me-1"></i>Download
                    </a>
                </div>
                <div class="text-muted small mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Kwitansi sudah memuat tanda tangan Anda dari profil.
                </div>
            </div>
        </div>
        @endif

    </div>

</div>
@endsection