@extends('layouts.app')

@section('title', 'Detail Honor — ' . $asesor->nama)
@section('page-title', 'Honor Asesor: ' . $asesor->nama)

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

{{-- Alert --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
        class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button"
        class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- Kiri: Info Asesor + Pilih Jadwal --}}
    <div class="col-lg-7">

        {{-- Info Asesor --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-circle me-1 text-primary"></i>Informasi Asesor
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted small">Nama</div>
                        <div class="fw-semibold">{{ $asesor->nama }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">No. Reg MET</div>
                        <div>{{ $asesor->no_reg_met ?? '-' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Email</div>
                        <div>{{ $asesor->email }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Telepon</div>
                        <div>{{ $asesor->telepon ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pilih Jadwal --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex align-items-center">
                <i class="bi bi-calendar-check me-1 text-success"></i>Jadwal Tersedia untuk Dibayar
                <span class="badge bg-secondary ms-auto">{{ $jadwalTersedia->count() }}</span>
            </div>
            <div class="card-body">
                @if($jadwalTersedia->isEmpty())
                <div class="text-center text-muted py-3">
                    <i class="bi bi-check-circle-fill text-success fs-4 d-block mb-1"></i>
                    Semua jadwal sudah masuk dalam pembayaran.
                </div>
                @else
                <form action="{{ route('bendahara.honor.store', $asesor) }}" method="POST" id="formHonor">
                    @csrf
                    @error('schedule_ids')
                    <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror

                    <div class="mb-3">
                        @foreach($jadwalTersedia as $jadwal)
                        @php
                        $jumlah = $jadwal->asesmens->count();
                        $honor = $jadwal->skema->honor_per_asesi ?? 0;
                        $subtotal = $jumlah * $honor;
                        @endphp
                        <div class="form-check border rounded p-3 mb-2 jadwal-item" data-subtotal="{{ $subtotal }}">
                            <input class="form-check-input jadwal-check" type="checkbox" name="schedule_ids[]"
                                value="{{ $jadwal->id }}" id="jadwal_{{ $jadwal->id }}">
                            <label class="form-check-label w-100" for="jadwal_{{ $jadwal->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold">{{ $jadwal->skema->name }}</div>
                                        <div class="text-muted small">
                                            TUK: {{ $jadwal->tuk->name ?? '-' }} &bull;
                                            {{ optional($jadwal->assessment_date)->translatedFormat('d F Y') }}
                                        </div>
                                        <div class="small mt-1">
                                            {{ $jumlah }} asesi &times;
                                            Rp {{ number_format($honor, 0, ',', '.') }}
                                            = <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                    @if($honor === 0)
                                    <span class="badge bg-warning text-dark ms-2">Tarif belum diset</span>
                                    @endif
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    {{-- Total preview --}}
                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <div>
                            <span class="text-muted small">Total Honor:</span>
                            <span class="fs-5 fw-bold ms-2" id="totalHonor">Rp 0</span>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btnBuat" disabled>
                            <i class="bi bi-file-earmark-text me-1"></i>Buat Kwitansi
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>

    </div>

    {{-- Kanan: Riwayat --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clock-history me-1 text-secondary"></i>Riwayat Honor
            </div>
            <div class="card-body p-0">
                @if($riwayat->isEmpty())
                <div class="text-center text-muted py-4">Belum ada riwayat pembayaran.</div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($riwayat as $honor)
                    <a href="{{ route('bendahara.honor.payment.show', $honor) }}"
                        class="list-group-item list-group-item-action px-3 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small">{{ $honor->nomor_kwitansi }}</div>
                                <div class="text-muted" style="font-size:.78rem;">
                                    {{ $honor->details->count() }} jadwal &bull;
                                    Rp {{ number_format($honor->total, 0, ',', '.') }}
                                </div>
                                <div class="text-muted" style="font-size:.75rem;">
                                    {{ optional($honor->tanggal_kwitansi)->translatedFormat('d M Y') }}
                                </div>
                            </div>
                            <span class="badge bg-{{ $honor->status_badge }} ms-2">
                                {{ $honor->status_label }}
                            </span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const checks = document.querySelectorAll('.jadwal-check');
const totalEl = document.getElementById('totalHonor');
const btnBuat = document.getElementById('btnBuat');

function updateTotal() {
    let total = 0;
    checks.forEach(cb => {
        if (cb.checked) {
            total += parseInt(cb.closest('.jadwal-item').dataset.subtotal || 0);
        }
    });
    totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
    btnBuat.disabled = total === 0;
}

checks.forEach(cb => cb.addEventListener('change', updateTotal));
</script>
@endpush