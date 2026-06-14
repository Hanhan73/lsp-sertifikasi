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
    <div class="col-lg-5">

        {{-- Info Asesi --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-badge me-2 text-primary"></i>Data Asesi
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-3" width="130">No. Reg</td>
                        <td><strong>#{{ $payment->asesmen->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Nama</td>
                        <td><strong>{{ $payment->asesmen->full_name ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Email</td>
                        <td>{{ $payment->asesmen->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">No. HP</td>
                        <td>{{ $payment->asesmen->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">TUK</td>
                        <td>{{ $payment->asesmen->tuk->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Skema</td>
                        <td>{{ $payment->asesmen->skema->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Jenis</td>
                        <td>
                            @if($payment->asesmen->is_collective)
                            <span class="badge bg-primary"><i class="bi bi-people me-1"></i>Kolektif</span>
                            @else
                            <span class="badge bg-success"><i class="bi bi-person me-1"></i>Mandiri</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Status</td>
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
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt me-2 text-primary"></i>Info Pembayaran
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted ps-3" width="130">Jumlah</td>
                        <td><strong class="text-success fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Metode</td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ strtoupper($payment->payment_type ?? $payment->method ?? '-') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Status</td>
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
                    @if($payment->order_id)
                    <tr>
                        <td class="text-muted ps-3">Order ID</td>
                        <td><code class="small">{{ $payment->order_id }}</code></td>
                    </tr>
                    @endif
                    @if($payment->transaction_id)
                    <tr>
                        <td class="text-muted ps-3">Transaction ID</td>
                        <td><code class="small">{{ $payment->transaction_id }}</code></td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted ps-3">Diupload</td>
                        <td>{{ $payment->created_at->translatedFormat('d F Y, H:i') }}</td>
                    </tr>
                    @if($payment->status === 'verified')
                    <tr>
                        <td class="text-muted ps-3">Diverifikasi</td>
                        <td>
                            {{ $payment->verified_at?->translatedFormat('d F Y, H:i') }}<br>
                            <small class="text-muted">
                                oleh
                                @if($payment->is_auto_verified)
                                <span class="badge bg-success"><i class="bi bi-robot me-1"></i>Auto (Midtrans)</span>
                                @else
                                {{ $payment->verifier->name ?? 'Admin' }}
                                @endif
                            </small>
                        </td>
                    </tr>
                    @endif
                    @if($payment->notes)
                    <tr>
                        <td class="text-muted ps-3">Catatan</td>
                        <td><small class="text-muted">{{ $payment->notes }}</small></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

    </div>

    {{-- Kolom kanan: Bukti Transfer + Aksi --}}
    <div class="col-lg-7">

        {{-- Bukti Transfer --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark-image me-2 text-primary"></i>Bukti Pembayaran
            </div>
            <div class="card-body text-center">
                @if($payment->proof_path)
                    @php $ext = strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION)); @endphp
                    @if(in_array($ext, ['jpg','jpeg','png','webp']))
                    <img src="{{ route('admin.payments.bukti', $payment) }}"
                         alt="Bukti Pembayaran"
                         class="img-fluid rounded border mb-3"
                         style="max-height: 400px; object-fit: contain;">
                    @else
                    <div class="py-4">
                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 4rem;"></i>
                        <p class="mt-2 text-muted">File PDF — klik Download untuk melihat</p>
                    </div>
                    @endif
                    <a href="{{ route('admin.payments.bukti', $payment) }}"
                       class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-download me-1"></i>Download Bukti
                    </a>
                @else
                <div class="py-5 text-muted">
                    <i class="bi bi-image fs-1 d-block mb-2 opacity-25"></i>
                    @if($payment->transaction_id)
                    Pembayaran diproses via Midtrans.<br>
                    <code class="small">{{ $payment->transaction_id }}</code>
                    @else
                    Asesi belum mengupload bukti pembayaran.
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Verifikasi Manual --}}
        @if($payment->status === 'pending')
        <div class="card border-0 shadow-sm mb-4" style="border-top: 3px solid #ffc107 !important;">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-shield-check me-2 text-primary"></i>Tindakan
            </div>
            <div class="card-body">
                <div class="alert alert-warning py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Gunakan hanya jika auto-verification Midtrans gagal atau untuk transfer manual.
                </div>
                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                    @csrf
                    <input type="hidden" name="status" id="verify-status" value="">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Catatan <span class="text-danger">*</span>
                        </label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"
                            placeholder="Misal: Transfer tanggal 14 Juni 2026, nominal sesuai…"
                            required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="submit" class="btn btn-success w-100"
                                onclick="document.getElementById('verify-status').value='verified';
                                         return confirm('Verifikasi pembayaran ini?')">
                                <i class="bi bi-check-circle me-1"></i>Verifikasi
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-danger w-100"
                                onclick="document.getElementById('verify-status').value='rejected';
                                         return confirm('Tolak pembayaran ini?')">
                                <i class="bi bi-x-circle me-1"></i>Tolak
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @elseif($payment->status === 'verified')
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            Pembayaran sudah terverifikasi pada
            {{ $payment->verified_at?->translatedFormat('d F Y, H:i') }}
            oleh <strong>
                @if($payment->is_auto_verified) Sistem (Midtrans)
                @else {{ $payment->verifier->name ?? 'Admin' }}
                @endif
            </strong>.
        </div>

        @else
        <div class="alert alert-danger">
            <i class="bi bi-x-circle-fill me-2"></i>
            Pembayaran ini sudah ditolak.
        </div>
        @endif

        {{-- Link ke detail asesi --}}
        <a href="{{ route('admin.asesi.show', $payment->asesmen->id) }}"
           class="btn btn-outline-primary btn-sm w-100">
            <i class="bi bi-person me-1"></i>Lihat Detail Asesi
        </a>

    </div>
</div>

@endsection