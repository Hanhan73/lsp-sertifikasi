@extends('layouts.app')
@section('title', 'Piutang Lainnya')
@section('page-title', 'Piutang Lainnya')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Outstanding</div>
                <div class="fw-bold fs-5 text-danger">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Sudah Lunas</div>
                <div class="fw-bold fs-5 text-success">Rp {{ number_format($totalLunas, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 d-flex align-items-center justify-content-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg"></i> Tambah Piutang
        </button>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari nama / uraian..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="outstanding" @selected(request('status')==='outstanding')>Outstanding</option>
                    <option value="lunas" @selected(request('status')==='lunas')>Lunas</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="jenis" class="form-select form-select-sm">
                    <option value="">Semua Jenis</option>
                    <option value="pinjaman" @selected(request('jenis')==='pinjaman')>Pinjaman</option>
                    <option value="tagihan" @selected(request('jenis')==='tagihan')>Tagihan</option>
                </select>
            </div>
            {{-- Filter Asesor --}}
            <div class="col-md-2">
                <select name="asesor_id" class="form-select form-select-sm">
                    <option value="">Semua Pihak</option>
                    @foreach($asesors as $a)
                    <option value="{{ $a->id }}" @selected(request('asesor_id') == $a->id)>{{ $a->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select name="tahun" class="form-select form-select-sm">
                    <option value="">Tahun</option>
                    @foreach($tahunList as $t)
                    <option value="{{ $t }}" @selected(request('tahun')==$t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Pihak</th>
                        <th>Uraian</th>
                        <th>Jenis</th>
                        <th>Akun Piutang</th>
                        <th>Akun Lawan</th>
                        <th class="text-end">Jumlah</th>
                        <th class="text-end">Sisa</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receivables as $r)
                    <tr>
                        <td class="text-nowrap">{{ $r->tanggal->format('d/m/Y') }}</td>
                        <td>
                            {{ $r->nama_pihak }}
                            @if($r->asesor)
                            <div class="text-muted" style="font-size:.72rem;">
                                <i class="bi bi-person-badge me-1"></i>Asesor
                            </div>
                            @endif
                        </td>
                        <td>
                            {{ $r->uraian }}
                            @if($r->catatan)
                            <div class="text-muted" style="font-size:.75rem">{{ $r->catatan }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $r->jenis === 'pinjaman' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                {{ $r->jenis_label }}
                            </span>
                        </td>
                        <td><small class="text-muted">{{ $r->coa->kode }} {{ $r->coa->nama }}</small></td>
                        <td>
                            <small class="text-muted">
                                @if($r->jenis === 'pinjaman')
                                1-002 Kas/Bank
                                @else
                                {{ $r->coaLawan?->kode }} {{ $r->coaLawan?->nama }}
                                @endif
                            </small>
                        </td>
                        <td class="text-end fw-semibold">Rp {{ number_format($r->jumlah, 0, ',', '.') }}</td>
                        <td class="text-end">
                            @if($r->sisa > 0 && $r->status !== 'lunas')
                            <span class="text-danger fw-semibold">Rp {{ number_format($r->sisa, 0, ',', '.') }}</span>
                            @elseif($r->status === 'lunas')
                            <span class="text-success">Lunas</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($r->jatuh_tempo)
                            <span class="{{ $r->status === 'outstanding' && $r->jatuh_tempo->isPast() ? 'text-danger fw-semibold' : '' }}">
                                {{ $r->jatuh_tempo->format('d/m/Y') }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($r->status === 'outstanding')
                            <span class="badge bg-danger">Outstanding</span>
                            @elseif($r->status === 'cicilan')
                            <span class="badge bg-warning text-dark">Cicilan</span>
                            <div class="text-muted" style="font-size:.72rem">
                                Terbayar Rp {{ number_format($r->jumlah_lunas, 0, ',', '.') }}
                            </div>
                            @else
                            <span class="badge bg-success">Lunas</span>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $r->tanggal_lunas->format('d/m/Y') }} •
                                Rp {{ number_format($r->jumlah_lunas, 0, ',', '.') }}
                            </div>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($r->bukti_path)
                            <a href="{{ route('bendahara.other-receivables.bukti', $r) }}" target="_blank"
                                class="btn btn-sm btn-outline-secondary" title="Lihat Bukti">
                                <i class="bi bi-paperclip"></i>
                            </a>
                            @endif
                            @if($r->status !== 'lunas')
                            <button class="btn btn-sm btn-success btn-lunas"
                                data-id="{{ $r->id }}"
                                data-nama="{{ $r->nama_pihak }}"
                                data-jumlah="{{ $r->jumlah }}"
                                data-sisa="{{ $r->sisa }}"
                                data-bs-toggle="modal"
                                data-bs-target="#modalLunas">
                                <i class="bi bi-check-lg"></i> Lunas
                            </button>
                            <form action="{{ route('bendahara.other-receivables.destroy', $r) }}" method="POST"
                                class="d-inline form-hapus">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-trigger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Tidak ada data piutang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $receivables->links() }}</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('bendahara.other-receivables.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Piutang Lainnya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Jenis --}}
                        <div class="col-md-6">
                            <label class="form-label">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" class="form-select" id="selectJenis" required>
                                <option value="pinjaman">Pinjaman / Kasbon</option>
                                <option value="tagihan">Tagihan (belum terima uang)</option>
                            </select>
                            <div class="form-text text-info" id="jenisHint">
                                Jurnal: Dr. Piutang / Cr. Kas-Bank
                            </div>
                        </div>

                        {{-- Nama Pihak + Asesor --}}
                        <div class="col-md-6">
                            <label class="form-label">Nama Pihak <span class="text-danger">*</span></label>

                            {{-- Toggle: Asesor atau Pihak Lain --}}
                            <div class="d-flex gap-2 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="_pihak_tipe"
                                           id="pihakAsesor" value="asesor" checked>
                                    <label class="form-check-label small" for="pihakAsesor">Asesor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="_pihak_tipe"
                                           id="pihakLainnya" value="lainnya">
                                    <label class="form-check-label small" for="pihakLainnya">Pihak Lain</label>
                                </div>
                            </div>

                            {{-- Dropdown asesor --}}
                            <div id="wrapAsesor">
                                <select name="asesor_id" id="selectAsesor" class="form-select">
                                    <option value="">-- Pilih Asesor --</option>
                                    @foreach($asesors as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="nama_pihak" id="hiddenNamaPihak">
                            </div>

                            {{-- Input manual --}}
                            <div id="wrapNamaPihak" style="display:none">
                                <input type="text" name="nama_pihak" id="inputNamaPihak"
                                       class="form-control" placeholder="Nama orang / instansi">
                            </div>
                        </div>

                        {{-- Uraian --}}
                        <div class="col-12">
                            <label class="form-label">Uraian <span class="text-danger">*</span></label>
                            <input type="text" name="uraian" class="form-control"
                                   placeholder="Keterangan piutang" required>
                        </div>

                        {{-- Jumlah, Tanggal, Jatuh Tempo --}}
                        <div class="col-md-4">
                            <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                   value="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jatuh Tempo</label>
                            <input type="date" name="jatuh_tempo" class="form-control">
                        </div>

                        {{-- Akun Piutang (Dr) --}}
                        <div class="col-md-6">
                            <label class="form-label">Akun Piutang / Debit <span class="text-danger">*</span></label>
                            <select name="coa_id" class="form-select" id="selectCoa" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach($coaOptions as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->kode }} — {{ $coa->nama }}</option>
                                @endforeach
                                <option value="__baru__">+ Buat akun baru...</option>
                            </select>
                        </div>

                        {{-- Akun Lawan (Cr) — hanya muncul kalau tagihan --}}
                        <div class="col-md-6" id="wrapCoaLawan" style="display:none">
                            <label class="form-label">Akun Lawan / Kredit <span class="text-danger">*</span></label>
                            <select name="coa_lawan_id" class="form-select" id="selectCoaLawan">
                                <option value="">-- Pilih Akun --</option>
                                @foreach($coaLawanOptions as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->kode }} — {{ $coa->nama }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Akun yang dikreditkan (misal: Pendapatan Lainnya, Titipan, dll)</div>
                        </div>

                        {{-- Form COA baru --}}
                        <div id="coaBaru" class="col-12" style="display:none">
                            <div class="card bg-light border p-3">
                                <div class="small fw-semibold mb-2 text-secondary">Buat Akun Baru (tipe: Aset)</div>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label form-label-sm">Kode Akun</label>
                                        <input type="text" name="coa_baru_kode" class="form-control form-control-sm"
                                               placeholder="1-105">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label form-label-sm">Nama Akun</label>
                                        <input type="text" name="coa_baru_nama" class="form-control form-control-sm"
                                               placeholder="Piutang Pinjaman Internal">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bukti & Catatan --}}
                        <div class="col-md-6">
                            <label class="form-label">Bukti Dokumen</label>
                            <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">jpg, png, pdf — maks 5MB</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="catatan" class="form-control" placeholder="Opsional">
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan & Buat Jurnal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Lunas --}}
<div class="modal fade" id="modalLunas" tabindex="-1">
    <div class="modal-dialog">
        <form id="formLunas" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tandai Lunas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle"></i>
                        Jurnal otomatis: <strong>Dr. Kas-Bank / Cr. Piutang</strong>
                    </div>
                    <p class="text-muted mb-3">Piutang: <strong id="lunasNama"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lunas <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lunas" class="form-control"
                               value="{{ today()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Diterima (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="jumlah_lunas" id="inputJumlahLunas" class="form-control" min="1" required>
                        <div class="form-text">Sisa hutang: <span id="lunasMaksimal"></span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="catatan" class="form-control" placeholder="Opsional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Tandai Lunas
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Pihak: asesor vs lainnya ───────────────────────────────────────────────
const radios       = document.querySelectorAll('input[name="_pihak_tipe"]');
const wrapAsesor   = document.getElementById('wrapAsesor');
const wrapNama     = document.getElementById('wrapNamaPihak');
const selectAsesor = document.getElementById('selectAsesor');
const hiddenNama   = document.getElementById('hiddenNamaPihak');
const inputNama    = document.getElementById('inputNamaPihak');

// Map asesor id → nama untuk hidden field
const asesorMap = {
    @foreach($asesors as $a)
    {{ $a->id }}: "{{ addslashes($a->nama) }}",
    @endforeach
};

radios.forEach(radio => {
    radio.addEventListener('change', function () {
        const isAsesor = this.value === 'asesor';
        wrapAsesor.style.display = isAsesor ? 'block' : 'none';
        wrapNama.style.display   = isAsesor ? 'none'  : 'block';
        selectAsesor.required    = isAsesor;
        inputNama.required       = !isAsesor;
        // Ganti name attribute agar tidak double submit
        if (isAsesor) {
            hiddenNama.name  = 'nama_pihak';
            inputNama.name   = '_nama_pihak_unused';
        } else {
            hiddenNama.name  = '_asesor_nama_unused';
            inputNama.name   = 'nama_pihak';
            selectAsesor.value = '';
        }
    });
});

// Sync hidden nama saat asesor dipilih
selectAsesor.addEventListener('change', function () {
    hiddenNama.value = asesorMap[this.value] ?? '';
});

// Init: asesor mode aktif default
hiddenNama.name  = 'nama_pihak';
inputNama.name   = '_nama_pihak_unused';
selectAsesor.required = true;

// ── Jenis change ───────────────────────────────────────────────────────────
const selectJenis   = document.getElementById('selectJenis');
const wrapCoaLawan  = document.getElementById('wrapCoaLawan');
const selectCoaLawan = document.getElementById('selectCoaLawan');
const jenisHint     = document.getElementById('jenisHint');

selectJenis.addEventListener('change', function () {
    const isPinjaman = this.value === 'pinjaman';
    jenisHint.textContent = isPinjaman
        ? 'Jurnal: Dr. Piutang / Cr. Kas-Bank (kas sudah keluar)'
        : 'Jurnal: Dr. Piutang / Cr. Akun Lawan pilihan';
    wrapCoaLawan.style.display = isPinjaman ? 'none' : 'block';
    selectCoaLawan.required    = !isPinjaman;
    if (isPinjaman) selectCoaLawan.value = '';
});

// ── COA baru toggle ────────────────────────────────────────────────────────
const selectCoa = document.getElementById('selectCoa');
const coaBaru   = document.getElementById('coaBaru');
selectCoa.addEventListener('change', function () {
    const isBaru = this.value === '__baru__';
    coaBaru.style.display = isBaru ? 'block' : 'none';
    if (isBaru) this.value = '';
});

// ── Isi modal lunas ────────────────────────────────────────────────────────
document.querySelectorAll('.btn-lunas').forEach(btn => {
    btn.addEventListener('click', function () {
        const sisa = this.dataset.sisa;
        document.getElementById('lunasNama').textContent        = this.dataset.nama;
        document.getElementById('inputJumlahLunas').value       = sisa;
        document.getElementById('inputJumlahLunas').max         = sisa;
        document.getElementById('lunasMaksimal').textContent    =
            'Rp ' + parseInt(sisa).toLocaleString('id-ID');
        document.getElementById('formLunas').action =
            `/bendahara/piutang-lainnya/${this.dataset.id}/lunas`;
    });
});

// ── Konfirmasi hapus ───────────────────────────────────────────────────────
document.querySelectorAll('.btn-hapus-trigger').forEach(btn => {
    btn.addEventListener('click', function () {
        const form = this.closest('form');
        Swal.fire({
            title: 'Hapus piutang ini?',
            text: 'Data yang sudah masuk jurnal tidak bisa dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc3545',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush