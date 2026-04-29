@extends('layouts.app')
@section('title', 'Input Saldo Manual')
@section('page-title', 'Input Saldo Manual ' . $tahun)
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

<div class="row justify-content-center">
<div class="col-lg-7">

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3">
            <label class="fw-semibold mb-0"><i class="bi bi-calendar3"></i> Tahun:</label>
            <form method="GET" class="mb-0">
                <select name="tahun" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold border-bottom">
        <i class="bi bi-pencil-square text-primary me-2"></i>
        Input Saldo Manual — Tahun {{ $tahun }}
    </div>
    <div class="card-body">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="alert alert-info small mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Isian di bawah adalah data yang <strong>tidak bisa dihitung otomatis</strong> dari sistem —
            seperti kas fisik, saldo bank, aset perlengkapan, dan kewajiban di luar sistem.
            Data lain seperti piutang asesi, utang honor, dan surplus dihitung otomatis.
        </div>

        <form action="{{ route('bendahara.laporan-keuangan.update-saldo') }}" method="POST">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">

            <h6 class="fw-bold text-success mb-3"><i class="bi bi-box-arrow-in-down-right me-1"></i> ASET</h6>

            <div class="mb-3">
                <label class="form-label">Kas (Tunai)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control" id="kas_display"
                           value="{{ number_format($balance->kas, 0, ',', '.') }}"
                           placeholder="0" inputmode="numeric">
                    <input type="hidden" name="kas" id="kas" value="{{ $balance->kas }}">
                </div>
                <div class="form-text">Uang tunai yang dipegang bendahara secara fisik.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Bank (Saldo Rekening)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control" id="bank_display"
                           value="{{ number_format($balance->bank, 0, ',', '.') }}"
                           placeholder="0" inputmode="numeric">
                    <input type="hidden" name="bank" id="bank" value="{{ $balance->bank }}">
                </div>
                <div class="form-text">Saldo rekening bank LSP.</div>
            </div>

            <div class="mb-4">
                <label class="form-label">Perlengkapan (Aset Tetap)</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control" id="perlengkapan_display"
                           value="{{ number_format($balance->perlengkapan, 0, ',', '.') }}"
                           placeholder="0" inputmode="numeric">
                    <input type="hidden" name="perlengkapan" id="perlengkapan" value="{{ $balance->perlengkapan }}">
                </div>
                <div class="form-text">Nilai peralatan dan perlengkapan kantor LSP.</div>
            </div>

            <h6 class="fw-bold text-danger mb-3"><i class="bi bi-box-arrow-up-right me-1"></i> KEWAJIBAN</h6>

            <div class="mb-4">
                <label class="form-label">Utang Operasional</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control" id="utang_operasional_display"
                           value="{{ number_format($balance->utang_operasional, 0, ',', '.') }}"
                           placeholder="0" inputmode="numeric">
                    <input type="hidden" name="utang_operasional" id="utang_operasional" value="{{ $balance->utang_operasional }}">
                </div>
                <div class="form-text">Kewajiban operasional yang belum tercatat di sistem.</div>
            </div>

            <h6 class="fw-bold text-warning mb-3"><i class="bi bi-wallet2 me-1"></i> EKUITAS</h6>

            <div class="mb-4">
                <label class="form-label">Saldo Dana Awal</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control" id="saldo_dana_display"
                           value="{{ number_format($balance->saldo_dana, 0, ',', '.') }}"
                           placeholder="0" inputmode="numeric">
                    <input type="hidden" name="saldo_dana" id="saldo_dana" value="{{ $balance->saldo_dana }}">
                </div>
                <div class="form-text">Akumulasi saldo dana LSP dari tahun-tahun sebelumnya.</div>
            </div>

            {{-- Auto-computed (readonly info) --}}
            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3 text-muted"><i class="bi bi-cpu me-1"></i> Dihitung Otomatis Sistem</h6>
                    <div class="row g-2" style="font-size:.875rem;">
                        <div class="col-6">
                            <span class="text-muted">Piutang Asesi:</span>
                            <strong class="float-end text-primary">Rp {{ number_format($balance->piutang_asesi,0,',','.') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Utang Honor Asesor:</span>
                            <strong class="float-end text-danger">Rp {{ number_format($balance->utang_honor,0,',','.') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Pendapatan:</span>
                            <strong class="float-end text-success">Rp {{ number_format($balance->pendapatan,0,',','.') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Beban Honor:</span>
                            <strong class="float-end text-danger">Rp {{ number_format($balance->beban_honor,0,',','.') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Beban Operasional:</span>
                            <strong class="float-end text-danger">Rp {{ number_format($balance->beban_operasional,0,',','.') }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Surplus/Defisit:</span>
                            <strong class="float-end {{ $balance->surplus >= 0 ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($balance->surplus,0,',','.') }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Simpan
                </button>
                <a href="{{ route('bendahara.laporan-keuangan.index', ['tahun' => $tahun]) }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

</div>
</div>

@endsection

@push('scripts')
<script>
// Format rupiah input
function setupRupiahInput(displayId, hiddenId) {
    const display = document.getElementById(displayId);
    const hidden  = document.getElementById(hiddenId);
    display.addEventListener('input', function () {
        const raw = parseInt(this.value.replace(/\./g, '') || '0', 10);
        hidden.value = raw;
        this.value = raw ? raw.toLocaleString('id-ID') : '';
    });
}
setupRupiahInput('kas_display', 'kas');
setupRupiahInput('bank_display', 'bank');
setupRupiahInput('perlengkapan_display', 'perlengkapan');
setupRupiahInput('utang_operasional_display', 'utang_operasional');
setupRupiahInput('saldo_dana_display', 'saldo_dana');
</script>
@endpush