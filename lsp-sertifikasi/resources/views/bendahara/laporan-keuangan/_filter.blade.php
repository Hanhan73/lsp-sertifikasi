{{-- resources/views/bendahara/laporan-keuangan/_filter.blade.php --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <label class="fw-semibold mb-0"><i class="bi bi-calendar3"></i> Tahun:</label>
            <form method="GET" class="mb-0">
                <select name="tahun" class="form-select form-select-sm" style="width:120px" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('bendahara.laporan-keuangan.index', ['tahun' => $tahun]) }}"
               class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>