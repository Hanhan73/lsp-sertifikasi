@extends('layouts.app')
@section('title', 'Detail Pembayaran #' . $payment->id)
@section('page-title', 'Detail Pembayaran')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

<div class="mb-3">
    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('warning'))
<div class="alert alert-warning alert-dismissible">
    {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- Kolom kiri: Info Asesi + Info Pembayaran --}}
    <div class="col-md-7">

        {{-- Info Asesi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-1"></i>Informasi Asesi</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="160">No. Registrasi</td>
                        <td><strong>#{{ $payment->asesmen->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama</td>
                        <td><strong>{{ $payment->asesmen->full_name ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $payment->asesmen->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. HP</td>
                        <td>{{ $payment->asesmen->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">TUK</td>
                        <td>{{ $payment->asesmen->tuk->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Skema</td>
                        <td>{{ $payment->asesmen->skema->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis</td>
                        <td>
                            @if($payment->asesmen->is_collective)
                            <span class="badge bg-primary"><i class="bi bi-people me-1"></i>Kolektif</span>
                            @else
                            <span class="badge bg-success"><i class="bi bi-person me-1"></i>Mandiri</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Asesi</td>
                        <td>
                            <span class="badge bg-{{ $payment->asesmen->status_badge }}">
                                {{ $payment->asesmen->status_label }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Info Pembayaran --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-credit-card me-1"></i>Informasi Pembayaran</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="160">Payment ID</td>
                        <td>#{{ $payment->id }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Order ID</td>
                        <td><code>{{ $payment->order_id ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Transaction ID</td>
                        <td>
                            @if($payment->transaction_id)
                            <code>{{ $payment->transaction_id }}</code>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Metode</td>
                        <td>
                            @if($payment->payment_type)
                            <span class="badge bg-secondary">{{ strtoupper($payment->payment_type) }}</span>
                            @elseif($payment->method)
                            <span class="badge bg-secondary">{{ strtoupper($payment->method) }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jumlah</td>
                        <td><strong class="text-success fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            @if($payment->status === 'pending')
                            <span class="badge bg-warning text-dark">Menunggu</span>
                            @elseif($payment->status === 'verified')
                            <span class="badge bg-success">Terverifikasi</span>
                            @else
                            <span class="badge bg-danger">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tgl Pembayaran</td>
                        <td>{{ $payment->created_at->translatedFormat('d M Y H:i') }}</td>
                    </tr>
                    @if($payment->verified_at)
                    <tr>
                        <td class="text-muted">Tgl Verifikasi</td>
                        <td>{{ $payment->verified_at->translatedFormat('d M Y H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Verifikasi Oleh</td>
                        <td>
                            @if($payment->is_auto_verified)
                            <span class="badge bg-success"><i class="bi bi-robot me-1"></i>Auto (Midtrans)</span>
                            @elseif($payment->verifier)
                            <span class="badge bg-info"><i class="bi bi-person-check me-1"></i>{{ $payment->verifier->name }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @if($payment->notes)
                    <tr>
                        <td class="text-muted">Catatan</td>
                        <td><small class="text-muted">{{ $payment->notes }}</small></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

    </div>

    {{-- Kolom kanan: Bukti Transfer + Aksi --}}
    <div class="col-md-5">

        {{-- Bukti Transfer --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-1"></i>Bukti Transfer</h6>
            </div>
            <div class="card-body text-center">
                @if($payment->proof_path)
                    @php
                        $ext = strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION));
                        $buktiUrl = route('admin.payments.bukti', $payment);
                    @endphp

                    @if(in_array($ext, ['jpg','jpeg','png','webp']))
                    <img src="{{ $buktiUrl }}"
                         class="img-fluid rounded mb-3" style="max-height: 400px;"
                         alt="Bukti Transfer"
                         onerror="this.style.display='none'; document.getElementById('bukti-fallback-{{ $payment->id }}').style.display='block'">
                    <div id="bukti-fallback-{{ $payment->id }}" style="display:none;" class="py-3">
                        <i class="bi bi-image text-muted fs-1 d-block mb-2"></i>
                        <small class="text-muted">Gambar tidak dapat ditampilkan</small>
                    </div>
                    @elseif($ext === 'pdf')
                    <div class="py-3">
                        <i class="bi bi-file-earmark-pdf fs-1 text-danger d-block mb-2"></i>
                        <small class="text-muted">File PDF tersedia</small>
                    </div>
                    @else
                    <div class="py-3">
                        <i class="bi bi-file-earmark fs-1 text-secondary d-block mb-2"></i>
                        <small class="text-muted">File tersedia</small>
                    </div>
                    @endif

                    <a href="{{ $buktiUrl }}" target="_blank"
                       class="btn btn-sm btn-outline-primary mt-1">
                        <i class="bi bi-arrow-up-right-square me-1"></i>Buka / Download Bukti
                    </a>

                @else
                <div class="py-4 text-muted">
                    <i class="bi bi-image fs-2 d-block mb-2 opacity-50"></i>
                    @if($payment->transaction_id)
                    <small>Pembayaran via Midtrans<br>
                        <code class="small">{{ $payment->transaction_id }}</code>
                    </small>
                    @else
                    <small>Belum ada bukti transfer diunggah</small>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Link ke detail asesi --}}
        <div class="mt-3">
            <a href="{{ route('admin.asesi.show', $payment->asesmen->id) }}"
               class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-person me-1"></i>Lihat Detail Asesi
            </a>
        </div>

    </div>
</div>

@endsection