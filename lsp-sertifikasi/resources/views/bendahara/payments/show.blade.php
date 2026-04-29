@extends('layouts.app')
@section('title', 'Verifikasi Pembayaran')
@section('page-title', 'Verifikasi Pembayaran')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')
<div class="row g-4">

    {{-- Kiri: Info Asesi + Pembayaran --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-badge me-2 text-primary"></i>Data Asesi
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted ps-3" width="130">Nama</td><td class="fw-semibold">{{ $payment->asesmen->full_name }}</td></tr>
                    <tr><td class="text-muted ps-3">NIK</td><td>{{ $payment->asesmen->nik }}</td></tr>
                    <tr><td class="text-muted ps-3">No. Reg</td><td>#{{ $payment->asesmen->id }}</td></tr>
                    <tr><td class="text-muted ps-3">Skema</td><td>{{ $payment->asesmen->skema->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted ps-3">TUK</td><td>{{ $payment->asesmen->tuk->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted ps-3">Telepon</td><td>{{ $payment->asesmen->phone ?? '-' }}</td></tr>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt me-2 text-primary"></i>Info Pembayaran
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted ps-3" width="130">Jumlah</td>
                        <td class="fw-bold text-success fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td></tr>
                    <tr><td class="text-muted ps-3">Metode</td><td><span class="badge bg-secondary">{{ strtoupper($payment->method) }}</span></td></tr>
                    <tr><td class="text-muted ps-3">Status</td>
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
                    <tr><td class="text-muted ps-3">Diupload</td><td>{{ $payment->created_at->translatedFormat('d F Y, H:i') }}</td></tr>
                    @if($payment->notes)
                    <tr><td class="text-muted ps-3">Catatan</td><td class="small">{{ $payment->notes }}</td></tr>
                    @endif
                    @if($payment->status === 'verified')
                    <tr><td class="text-muted ps-3">Diverifikasi</td>
                        <td>{{ $payment->verified_at?->translatedFormat('d F Y, H:i') }}<br>
                        <small class="text-muted">oleh {{ $payment->verifier->name ?? 'Sistem' }}</small></td></tr>
                    @elseif($payment->status === 'rejected')
                    <tr><td class="text-muted ps-3">Ditolak</td>
                        <td>{{ $payment->verified_at?->translatedFormat('d F Y, H:i') }}<br>
                        <div class="small text-danger mt-1 bg-danger bg-opacity-10 rounded p-1">{{ $payment->rejection_notes }}</div></td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Kanan: Bukti + Form Verifikasi --}}
    <div class="col-lg-7">

        {{-- Bukti Pembayaran --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark-image me-2 text-primary"></i>Bukti Pembayaran
            </div>
            <div class="card-body text-center">
                @if($payment->proof_path)
                @php $ext = strtolower(pathinfo($payment->proof_path, PATHINFO_EXTENSION)); @endphp
                @if(in_array($ext, ['jpg','jpeg','png']))
                <img src="{{ route('bendahara.payments.download-bukti', $payment) }}"
                     alt="Bukti Pembayaran" class="img-fluid rounded border mb-3"
                     style="max-height:400px;object-fit:contain;">
                @else
                <div class="py-4">
                    <i class="bi bi-file-earmark-pdf text-danger" style="font-size:4rem;"></i>
                    <p class="mt-2 text-muted">File PDF — klik Download untuk melihat</p>
                </div>
                @endif
                <a href="{{ route('bendahara.payments.download-bukti', $payment) }}"
                   class="btn btn-outline-primary" target="_blank">
                    <i class="bi bi-download me-1"></i>Download Bukti
                </a>
                @else
                <div class="py-5 text-muted">
                    <i class="bi bi-image fs-1 d-block mb-2 opacity-25"></i>
                    Asesi belum mengupload bukti pembayaran.
                </div>
                @endif
            </div>
        </div>

        {{-- Form Aksi --}}
        @if($payment->status === 'pending')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-check2-square me-2 text-primary"></i>Tindakan
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Verifikasi --}}
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('bendahara.payments.verify', $payment) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small">Catatan (opsional)</label>
                                <input type="text" name="notes" class="form-control form-control-sm"
                                    placeholder="Catatan verifikasi…" maxlength="500">
                            </div>
                            <button type="submit" class="btn btn-success w-100"
                                onclick="return confirm('Verifikasi pembayaran ini?')">
                                <i class="bi bi-check-circle me-1"></i>Verifikasi & Setujui
                            </button>
                        </form>
                    </div>

                    {{-- Tolak --}}
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('bendahara.payments.reject', $payment) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small">Alasan Penolakan <span class="text-danger">*</span></label>
                                <textarea name="rejection_notes" class="form-control form-control-sm" rows="2"
                                    placeholder="Bukti tidak jelas / nominal tidak sesuai / bukan rekening LSP…"
                                    required maxlength="500"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100"
                                onclick="return confirm('Tolak pembayaran ini?')">
                                <i class="bi bi-x-circle me-1"></i>Tolak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @elseif($payment->status === 'verified')
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            Pembayaran ini sudah diverifikasi pada {{ $payment->verified_at?->translatedFormat('d F Y, H:i') }}
            oleh <strong>{{ $payment->verifier->name ?? 'Sistem' }}</strong>.
        </div>
    
        {{-- TAMBAHKAN INI: Tombol Invoice & Kwitansi --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-file-earmark-text me-1 text-primary"></i>Dokumen Pembayaran
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('bendahara.payments.invoice', $payment) }}"
                    target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Invoice
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-text me-1"></i>Kwitansi
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item"
                                href="{{ route('bendahara.payments.kwitansi', [$payment, 'versi' => 'kosong']) }}"
                                target="_blank">
                                    <i class="bi bi-file-earmark me-1"></i>Kosong (tanpa TTD)
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                href="{{ route('bendahara.payments.kwitansi', [$payment, 'versi' => 'berisi']) }}"
                                target="_blank">
                                    <i class="bi bi-file-earmark-check me-1"></i>Berisi (TTD + Stempel)
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        @else
        <div class="alert alert-danger">
            <i class="bi bi-x-circle-fill me-2"></i>
            Pembayaran ini sudah ditolak. Asesi akan diminta upload ulang.
        </div>
        @endif

        <div class="mt-3">
            <a href="{{ route('bendahara.payments.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
            </a>
        </div>

    </div>
</div>
@endsection