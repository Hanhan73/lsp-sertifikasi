@extends('layouts.app')

@section('title', 'Detail Honor — ' . $asesor->nama)
@section('page-title', 'Honor Asesor: ' . $asesor->nama)

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- Kiri: Info Asesor + Rekening + Pilih Jadwal --}}
    <div class="col-lg-7">

        {{-- Info Asesor --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-circle me-1 text-primary"></i>Informasi Asesor
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
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

                {{-- Rekening Bank --}}
                <div class="border-top pt-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="small fw-semibold text-muted">
                            <i class="bi bi-credit-card me-1"></i>Rekening Bank
                        </div>
                        <a href="{{ route('bendahara.rekening.show', $asesor) }}"
                            class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;">
                            <i class="bi bi-pencil me-1"></i>Kelola
                        </a>
                    </div>
                    @if($asesor->rekenings->isEmpty())
                        <div class="alert alert-warning py-2 mb-0 small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Asesor belum memiliki rekening bank.
                            <a href="{{ route('bendahara.rekening.show', $asesor) }}" class="alert-link">Tambah sekarang</a>.
                        </div>
                    @else
                        <div class="row g-2">
                            @foreach($asesor->rekenings as $rek)
                            <div class="col-12">
                                <div class="border rounded px-3 py-2 {{ $rek->is_utama ? 'border-success bg-success-subtle' : 'bg-light' }}">
                                    <div class="fw-semibold small">
                                        {{ $rek->nama_bank }}
                                        @if($rek->is_utama)<span class="badge bg-success ms-1" style="font-size:.65rem;">Utama</span>@endif
                                    </div>
                                    <div class="font-monospace" style="font-size:.85rem;">{{ $rek->nomor_rekening }}</div>
                                    <div class="text-muted small">a.n. {{ $rek->nama_pemilik }}</div>
                                    @if($rek->cabang)<div class="text-muted" style="font-size:.75rem;">{{ $rek->cabang }}</div>@endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
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
                            $jumlah  = $jadwal->asesmens->count();
                            $tiers   = $jadwal->skema->honorTiers;
                            $default = $tiers->firstWhere('is_default', true) ?? $tiers->first();
                            $defaultAmount = $default->amount ?? ($jadwal->skema->honor_per_asesi ?? 0);
                            $defaultTierId = $default->id ?? null;
                        @endphp
                        <div class="border rounded p-3 mb-2 jadwal-item"
                             data-schedule="{{ $jadwal->id }}"
                             data-jumlah="{{ $jumlah }}"
                             data-default-amount="{{ $defaultAmount }}">

                            <div class="d-flex align-items-start gap-2">
                                <input class="form-check-input jadwal-check mt-1 flex-shrink-0"
                                       type="checkbox" name="schedule_ids[]"
                                       value="{{ $jadwal->id }}"
                                       id="jadwal_{{ $jadwal->id }}">

                                <label class="form-check-label w-100" for="jadwal_{{ $jadwal->id }}">
                                    <div class="fw-semibold">{{ $jadwal->skema->name }}</div>
                                    <div class="text-muted small">
                                        TUK: {{ $jadwal->tuk->name ?? '-' }} &bull;
                                        {{ optional($jadwal->assessment_date)->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="small mt-1 text-muted">{{ $jumlah }} asesi</div>
                                </label>
                            </div>

                            {{-- Pilih tarif --}}
                            <div class="mt-2 ms-4">
                                @if($tiers->isEmpty())
                                    {{-- Fallback: input manual --}}
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="small text-muted flex-shrink-0">Honor/asesi:</label>
                                        <div class="input-group input-group-sm" style="max-width:160px;">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text"
                                                   name="honor_amounts[{{ $jadwal->id }}]"
                                                   class="form-control honor-manual font-monospace"
                                                   data-schedule="{{ $jadwal->id }}"
                                                   value="{{ number_format($defaultAmount, 0, ',', '.') }}"
                                                   inputmode="numeric">
                                        </div>
                                        <span class="badge bg-warning text-dark small">Tarif belum diset</span>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <label class="small text-muted flex-shrink-0">Pilih tarif:</label>
                                        <select name="tier_ids[{{ $jadwal->id }}]"
                                                class="form-select form-select-sm tier-select"
                                                data-schedule="{{ $jadwal->id }}"
                                                style="max-width:220px;">
                                            @foreach($tiers as $tier)
                                            <option value="{{ $tier->id }}"
                                                    data-amount="{{ $tier->amount }}"
                                                    {{ $tier->id === optional($default)->id ? 'selected' : '' }}>
                                                {{ $tier->label }} — Rp {{ number_format($tier->amount, 0, ',', '.') }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <span class="small text-muted">
                                            &times; {{ $jumlah }} =
                                            <strong class="text-dark subtotal-label" data-schedule="{{ $jadwal->id }}">
                                                Rp {{ number_format($defaultAmount * $jumlah, 0, ',', '.') }}
                                            </strong>
                                        </span>
                                    </div>
                                @endif
                            </div>

                        </div>
                        @endforeach
                    </div>

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
                            <span class="badge bg-{{ $honor->status_badge }} ms-2">{{ $honor->status_label }}</span>
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
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Format input manual rupiah
document.querySelectorAll('.honor-manual').forEach(input => {
    input.addEventListener('input', () => {
        const raw = input.value.replace(/\D/g, '');
        input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
        updateTotal();
    });
});

// Update subtotal label saat ganti tier
document.querySelectorAll('.tier-select').forEach(sel => {
    sel.addEventListener('change', () => {
        const scheduleId = sel.dataset.schedule;
        const amount     = parseInt(sel.options[sel.selectedIndex].dataset.amount) || 0;
        const item       = document.querySelector(`.jadwal-item[data-schedule="${scheduleId}"]`);
        const jumlah     = parseInt(item.dataset.jumlah) || 0;
        const label      = document.querySelector(`.subtotal-label[data-schedule="${scheduleId}"]`);
        if (label) label.textContent = 'Rp ' + (amount * jumlah).toLocaleString('id-ID');
        updateTotal();
    });
});

// Checkbox toggle
document.querySelectorAll('.jadwal-check').forEach(cb => {
    cb.addEventListener('change', updateTotal);
});

function getItemAmount(item) {
    const scheduleId = item.dataset.schedule;
    const jumlah     = parseInt(item.dataset.jumlah) || 0;

    // Cek apakah pakai tier select atau manual
    const sel = item.querySelector('.tier-select');
    if (sel) {
        const amount = parseInt(sel.options[sel.selectedIndex].dataset.amount) || 0;
        return amount * jumlah;
    }

    const manual = item.querySelector('.honor-manual');
    if (manual) {
        const amount = parseInt(manual.value.replace(/\D/g, '')) || 0;
        return amount * jumlah;
    }

    return parseInt(item.dataset.defaultAmount || 0) * jumlah;
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.jadwal-check').forEach(cb => {
        if (!cb.checked) return;
        const item = cb.closest('.jadwal-item');
        total += getItemAmount(item);
    });
    document.getElementById('totalHonor').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('btnBuat').disabled = total === 0;
}
</script>
@endpush