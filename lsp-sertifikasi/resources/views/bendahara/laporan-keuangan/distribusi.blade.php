@extends('layouts.app')
@section('title', 'Distribusi ke Yayasan')
@section('page-title', 'Distribusi ke Yayasan')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@include('bendahara.laporan-keuangan._filter', ['route' => 'bendahara.laporan-keuangan.distribusi'])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-x-circle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-3">

    {{-- Form distribusi --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-send-arrow-down text-danger me-2"></i>
                Pencatatan Distribusi Tahun {{ $tahun }}
            </div>
            <div class="card-body">

                {{-- Surplus dari jurnal (bukan dari $balance accessor) --}}
                <div class="alert alert-info small">
                    Surplus tahun {{ $tahun }} (dari jurnal):
                    <strong class="{{ $summary['surplus'] >= 0 ? 'text-success' : 'text-danger' }}">
                        Rp {{ number_format($summary['surplus'],0,',','.') }}
                    </strong>
                </div>

                <form action="{{ route('bendahara.laporan-keuangan.distribusi.update') }}?tahun={{ $tahun }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Jumlah Distribusi ke Yayasan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="distribusi_display"
                                   value="{{ number_format($balance->distribusi_yayasan,0,',','.') }}"
                                   placeholder="0" inputmode="numeric">
                            <input type="hidden" name="distribusi_yayasan" id="distribusi_yayasan"
                                   value="{{ $balance->distribusi_yayasan }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hutang Distribusi (belum dibayar)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="hutang_display"
                                   value="{{ number_format($balance->hutang_distribusi,0,',','.') }}"
                                   placeholder="0" inputmode="numeric">
                            <input type="hidden" name="hutang_distribusi" id="hutang_distribusi"
                                   value="{{ $balance->hutang_distribusi }}">
                        </div>
                        <div class="form-text">Bagian distribusi yang belum ditransfer ke yayasan.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Distribusi</label>
                        <input type="date" name="tanggal_distribusi" class="form-control"
                               value="{{ $balance->tanggal_distribusi?->format('Y-m-d') }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan_distribusi" class="form-control" rows="2"
                                  placeholder="Keterangan distribusi...">{{ $balance->catatan_distribusi }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Info jurnal balik --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-arrow-counterclockwise text-warning me-2"></i>
                Jurnal Balik Akhir Tahun
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Jurnal balik dilakukan di awal tahun berikutnya untuk memindahkan saldo distribusi
                    ke akun <strong>Hutang Distribusi Yayasan</strong>, sehingga surplus tahun berjalan
                    kembali ke nol dan dicatat sebagai kewajiban yang harus dilunasi.
                </p>

                <div class="card bg-light border-0 mb-3 p-3" style="font-size:.85rem;">
                    <div><strong>Jurnal Distribusi:</strong></div>
                    <div class="ms-3 text-success">
                        Dr. Surplus Tahun Berjalan &nbsp;&nbsp;
                        Rp {{ number_format($balance->distribusi_yayasan,0,',','.') }}
                    </div>
                    <div class="ms-5 text-danger">
                        Cr. Hutang Distribusi Yayasan &nbsp;&nbsp;
                        Rp {{ number_format($balance->distribusi_yayasan,0,',','.') }}
                    </div>
                </div>

                @if($balance->jurnal_balik_done)
                <div class="alert alert-success small mb-3">
                    <i class="bi bi-check-circle me-1"></i>
                    Jurnal balik tahun {{ $tahun }} sudah dilakukan.
                </div>
                @else
                <div class="alert alert-warning small mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Jurnal balik tahun {{ $tahun }} belum dilakukan.
                    Lakukan di awal tahun {{ $tahun + 1 }}.
                </div>
                @endif

                <form action="{{ route('bendahara.laporan-keuangan.jurnal-balik') }}?tahun={{ $tahun }}"
                      method="POST"
                      onsubmit="return confirm('Yakin lakukan jurnal balik? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm"
                            {{ $balance->jurnal_balik_done ? 'disabled' : '' }}>
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Lakukan Jurnal Balik Tahun {{ $tahun }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Hutang distribusi --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold border-bottom">
                <i class="bi bi-exclamation-circle text-danger me-2"></i>
                Hutang Distribusi ke Yayasan
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total terdistribusi tahun {{ $tahun }}</span>
                    <strong>Rp {{ number_format($balance->distribusi_yayasan,0,',','.') }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-muted">Hutang distribusi (belum dibayar)</span>
                    <strong class="text-danger">Rp {{ number_format($balance->hutang_distribusi,0,',','.') }}</strong>
                </div>
                @if($balance->tanggal_distribusi)
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-muted">Tanggal distribusi</span>
                    <strong>{{ $balance->tanggal_distribusi->translatedFormat('d F Y') }}</strong>
                </div>
                @endif
                @if($balance->catatan_distribusi)
                <div class="mt-2 text-muted small">{{ $balance->catatan_distribusi }}</div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
function setupRupiahInput(displayId, hiddenId) {
    const display = document.getElementById(displayId);
    const hidden  = document.getElementById(hiddenId);
    display.addEventListener('input', function () {
        const raw = parseInt(this.value.replace(/\./g, '') || '0', 10);
        hidden.value = raw;
        this.value = raw ? raw.toLocaleString('id-ID') : '';
    });
}
setupRupiahInput('distribusi_display', 'distribusi_yayasan');
setupRupiahInput('hutang_display', 'hutang_distribusi');
</script>
@endpush