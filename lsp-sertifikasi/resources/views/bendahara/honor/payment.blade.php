@extends('layouts.app')

@section('title', 'Detail Kwitansi Honor')
@section('page-title', 'Detail Kwitansi Honor')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
        class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- Kiri: Detail kwitansi --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                <span class="fw-semibold">{{ $honor->nomor_kwitansi }}</span>
                <span class="badge bg-{{ $honor->status_badge }} ms-auto">{{ $honor->status_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Asesor</div>
                        <div class="fw-semibold">{{ $honor->asesor->nama }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Tanggal Kwitansi</div>
                        <div>{{ optional($honor->tanggal_kwitansi)->translatedFormat('d F Y') }}</div>
                    </div>
                </div>

                {{-- Detail per jadwal --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Skema</th>
                                <th>TUK</th>
                                <th>Tanggal</th>
                                <th class="text-center">Asesi</th>
                                <th class="text-end">@Rp</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($honor->details as $i => $detail)
                            <tr>
                                <td class="text-muted small">{{ $i+1 }}</td>
                                <td class="small fw-semibold">{{ $detail->schedule->skema->name }}</td>
                                <td class="small">{{ $detail->schedule->tuk->name ?? '-' }}</td>
                                <td class="small">
                                    {{ optional($detail->schedule->assessment_date)->translatedFormat('d M Y') }}</td>
                                <td class="text-center small">{{ $detail->jumlah_asesi }}</td>
                                <td class="text-end small">{{ number_format($detail->honor_per_asesi, 0, ',', '.') }}
                                </td>
                                <td class="text-end small fw-semibold">
                                    {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="6" class="fw-bold text-end">Total Honor</td>
                                <td class="text-end fw-bold">Rp {{ number_format($honor->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Aksi: Preview/Download kwitansi --}}
                <div class="d-flex gap-2 mt-2">
                    @if($honor->isDikonfirmasi())
                    <a href="{{ route('bendahara.honor.payment.kwitansi', ['honor' => $honor, 'preview' => 1]) }}"
                        target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i>Preview Kwitansi
                    </a>
                    <a href="{{ route('bendahara.honor.payment.kwitansi', $honor) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-download me-1"></i>Download Kwitansi PDF
                    </a>
                    @else
                    <span class="text-muted small fst-italic">
                        <i class="bi bi-info-circle me-1"></i>
                        Kwitansi tersedia setelah asesor konfirmasi penerimaan.
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Kanan: Upload bukti transfer --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-upload me-1 text-success"></i>Bukti Transfer
            </div>
            <div class="card-body">

                @if($honor->isMenunggu())
                <form action="{{ route('bendahara.honor.payment.bukti', $honor) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @error('bukti_transfer')
                    <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Upload Bukti Transfer <span
                                class="text-danger">*</span></label>
                        <input type="file" name="bukti_transfer" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                            required>
                        <div class="form-text">Format: JPG, PNG, atau PDF. Maks 5MB.</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-upload me-1"></i>Upload & Tandai Sudah Dibayar
                    </button>
                </form>

                @elseif($honor->isSudahDibayar())
                <div class="alert alert-info">
                    <i class="bi bi-clock me-1"></i>
                    Bukti transfer sudah diupload. Menunggu konfirmasi dari asesor.
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Dibayar pada</div>
                    <div>{{ optional($honor->dibayar_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>
                <a href="{{ route('bendahara.honor.payment.bukti.download', $honor) }}"
                    class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-download me-1"></i>Download Bukti Transfer
                </a>

                @elseif($honor->isDikonfirmasi())
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Asesor sudah konfirmasi penerimaan honor.
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Dikonfirmasi pada</div>
                    <div>{{ optional($honor->dikonfirmasi_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>
                <a href="{{ route('bendahara.honor.payment.bukti.download', $honor) }}"
                    class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-download me-1"></i>Download Bukti Transfer
                </a>
                @endif

            </div>
        </div>

        {{-- Info status --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-arrow-right-circle me-1"></i>Status Alur
            </div>
            <div class="card-body py-2">
                @php
                $steps = [
                ['label' => 'Kwitansi Dibuat', 'done' => true],
                ['label' => 'Bukti Transfer Upload','done' => in_array($honor->status,
                ['sudah_dibayar','dikonfirmasi'])],
                ['label' => 'Konfirmasi Asesor', 'done' => $honor->isDikonfirmasi()],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                    <i
                        class="bi {{ $step['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                    <span class="small {{ $step['done'] ? 'fw-semibold' : 'text-muted' }}">{{ $step['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection