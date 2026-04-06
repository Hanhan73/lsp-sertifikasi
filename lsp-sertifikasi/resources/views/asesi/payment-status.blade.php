@extends('layouts.app')
@section('title', 'Status Pembayaran')
@section('page-title', 'Status Pembayaran')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">

            @if(!$payment)
            {{-- Belum upload --}}
            <i class="bi bi-credit-card" style="font-size:5rem;color:#cbd5e1;"></i>
            <h4 class="mt-4">Belum Ada Pembayaran</h4>
            <p class="text-muted">Anda belum mengupload bukti pembayaran.</p>
            @if(in_array($asesmen->status, ['data_completed','payment_pending']))
            <a href="{{ route('asesi.payment') }}" class="btn btn-primary mt-2">
                <i class="bi bi-upload me-1"></i>Upload Bukti Pembayaran
            </a>
            @endif

            @elseif($payment->status === 'pending')
            {{-- Menunggu verifikasi --}}
            <i class="bi bi-hourglass-split text-warning" style="font-size:5rem;"></i>
            <h4 class="mt-4 text-warning">Menunggu Verifikasi</h4>
            <p class="text-muted">Bukti pembayaran Anda sudah diterima dan sedang diperiksa oleh bendahara LSP.</p>
            <div class="alert alert-light border mt-3 text-start">
                <div class="row">
                    <div class="col-6"><small class="text-muted">Jumlah</small><br><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></div>
                    <div class="col-6"><small class="text-muted">Metode</small><br><strong>{{ strtoupper($payment->method) }}</strong></div>
                    <div class="col-12 mt-2"><small class="text-muted">Diupload</small><br>{{ $payment->created_at->translatedFormat('d F Y, H:i') }}</div>
                    @if($payment->notes)
                    <div class="col-12 mt-2"><small class="text-muted">Catatan</small><br>{{ $payment->notes }}</div>
                    @endif
                </div>
            </div>
            <a href="{{ route('payment.download-bukti', $payment) }}" class="btn btn-outline-secondary mt-2">
                <i class="bi bi-file-earmark me-1"></i>Lihat Bukti yang Diupload
            </a>

            @elseif($payment->status === 'verified')
            {{-- Terverifikasi --}}
            <i class="bi bi-check-circle-fill text-success" style="font-size:5rem;"></i>
            <h4 class="mt-4 text-success">Pembayaran Terverifikasi!</h4>
            <p class="text-muted">Pembayaran Anda telah diverifikasi oleh bendahara LSP. Proses sertifikasi akan segera dimulai.</p>
            <div class="alert alert-success mt-3 text-start">
                <div class="row">
                    <div class="col-6"><small>Jumlah</small><br><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></div>
                    <div class="col-6"><small>Metode</small><br><strong>{{ strtoupper($payment->method) }}</strong></div>
                    <div class="col-12 mt-2"><small>Diverifikasi</small><br>{{ $payment->verified_at?->translatedFormat('d F Y, H:i') }}</div>
                </div>
            </div>
            <a href="{{ route('asesi.dashboard') }}" class="btn btn-success mt-3">
                <i class="bi bi-house me-1"></i>Kembali ke Dashboard
            </a>

            @elseif($payment->status === 'rejected')
            {{-- Ditolak --}}
            <i class="bi bi-x-circle-fill text-danger" style="font-size:5rem;"></i>
            <h4 class="mt-4 text-danger">Bukti Pembayaran Ditolak</h4>
            <div class="alert alert-danger mt-3 text-start">
                <strong><i class="bi bi-chat-left-text me-1"></i>Alasan Penolakan:</strong>
                <p class="mb-0 mt-1">{{ $payment->rejection_notes }}</p>
            </div>
            <p class="text-muted">Silakan perbaiki dan upload ulang bukti pembayaran yang valid.</p>
            <a href="{{ route('asesi.payment') }}" class="btn btn-danger mt-2">
                <i class="bi bi-arrow-clockwise me-1"></i>Upload Ulang
            </a>
            @endif

        </div>
    </div>

</div>
</div>
@endsection