@extends('layouts.app')

@section('title', 'Pendapatan Luar Asesmen')
@section('page-title', 'Pendapatan Luar Asesmen')

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

    {{-- Kiri: Form Tambah --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top:80px;">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-plus-circle me-1 text-success"></i>Catat Pendapatan Baru
            </div>
            <div class="card-body">
                <form action="{{ route('bendahara.pendapatan-luar.store') }}" method="POST"
                      enctype="multipart/form-data" id="formTambah">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                               value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Uraian <span class="text-danger">*</span></label>
                        <input type="text" name="uraian" class="form-control @error('uraian') is-invalid @enderror"
                               value="{{ old('uraian') }}"
                               placeholder="Contoh: Konsultasi sistem manajemen ISO" required>
                        @error('uraian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="text" name="kategori" class="form-control"
                               value="{{ old('kategori') }}"
                               placeholder="Cth: Konsultasi, Pelatihan, Sewa, Hibah">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Jumlah (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="jumlah" id="inputJumlah"
                                   class="form-control font-monospace @error('jumlah') is-invalid @enderror"
                                   value="{{ old('jumlah') ? number_format(old('jumlah'), 0, ',', '.') : '' }}"
                                   inputmode="numeric" placeholder="0" required>
                            @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Pilih Akun CoA --}}
                    <div class="mb-1">
                        <label class="form-label small fw-semibold">Akun Pendapatan <span class="text-danger">*</span></label>
                        <select name="coa_id" id="selectCoa"
                                class="form-select @error('coa_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach($coaOptions as $coa)
                            <option value="{{ $coa->id }}" {{ old('coa_id') == $coa->id ? 'selected' : '' }}>
                                {{ $coa->kode }} — {{ $coa->nama }}
                            </option>
                            @endforeach
                            <option value="__baru__">+ Buat akun baru...</option>
                        </select>
                        @error('coa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Panel buat akun baru (hidden by default) --}}
                    <div id="panelCoaBaru" class="border rounded p-3 bg-light mb-3 mt-2 d-none">
                        <div class="small fw-semibold mb-2 text-success"><i class="bi bi-plus-circle me-1"></i>Akun Baru</div>
                        <div class="row g-2">
                            <div class="col-5">
                                <input type="text" name="coa_baru_kode"
                                       class="form-control form-control-sm @error('coa_baru_kode') is-invalid @enderror"
                                       placeholder="Kode, cth: 4-005"
                                       value="{{ old('coa_baru_kode') }}">
                                @error('coa_baru_kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-7">
                                <input type="text" name="coa_baru_nama"
                                       class="form-control form-control-sm"
                                       placeholder="Nama akun"
                                       value="{{ old('coa_baru_nama') }}">
                            </div>
                        </div>
                        <div class="form-text">Akun baru akan dibuat otomatis dengan tipe Pendapatan.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bukti Dokumen <span class="text-danger">*</span></label>
                        <input type="file" name="bukti"
                               class="form-control @error('bukti') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.pdf" required>
                        <div class="form-text">JPG, PNG, atau PDF. Maks 5MB.</div>
                        @error('bukti')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea name="catatan" class="form-control" rows="2"
                                  placeholder="Keterangan tambahan...">{{ old('catatan') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-lg me-1"></i>Catat & Buat Jurnal
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Kanan: Daftar --}}
    <div class="col-lg-8">

        {{-- Filter --}}
        <form method="GET" action="{{ route('bendahara.pendapatan-luar.index') }}"
              class="row g-2 mb-3 align-items-end">
            <div class="col-sm-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Cari uraian / kategori..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-sm-3">
                <select name="tahun" class="form-select form-select-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $th)
                    <option value="{{ $th }}" {{ request('tahun') == $th ? 'selected' : '' }}>{{ $th }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <select name="bulan" class="form-select form-select-sm">
                    <option value="">Semua Bulan</option>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">Filter</button>
                <a href="{{ route('bendahara.pendapatan-luar.index') }}" class="btn btn-sm btn-outline-secondary">×</a>
            </div>
        </form>

        {{-- Tabel --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center">
                <span class="fw-semibold small"><i class="bi bi-list-ul me-1"></i>Daftar Pendapatan</span>
                @if($pendapatans->total() > 0)
                <span class="badge bg-secondary ms-auto">{{ $pendapatans->total() }} data</span>
                @endif
            </div>
            <div class="card-body p-0">
                @if($pendapatans->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Belum ada data pendapatan luar asesmen.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Uraian</th>
                                <th>Akun</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-center">Jurnal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendapatans as $item)
                            @php $sudahJurnal = \App\Models\JournalEntry::existsFor(\App\Models\PendapatanLuar::class, $item->id); @endphp
                            <tr>
                                <td class="text-nowrap">{{ $item->tanggal->translatedFormat('d M Y') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->uraian }}</div>
                                    @if($item->kategori)
                                    <span class="badge bg-light text-dark border" style="font-size:.7rem;">{{ $item->kategori }}</span>
                                    @endif
                                    @if($item->catatan)
                                    <div class="text-muted" style="font-size:.75rem;">{{ Str::limit($item->catatan, 60) }}</div>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <span class="font-monospace text-muted">{{ $item->coa->kode }}</span><br>
                                    <span style="font-size:.75rem;">{{ $item->coa->nama }}</span>
                                </td>
                                <td class="text-end text-nowrap fw-semibold">
                                    Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($sudahJurnal)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.7rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Terjurnal
                                    </span>
                                    @else
                                    <span class="badge bg-warning-subtle text-warning border" style="font-size:.7rem;">Belum</span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    {{-- Preview bukti --}}
                                    <a href="{{ route('bendahara.pendapatan-luar.bukti', $item) }}"
                                       target="_blank" class="btn btn-xs btn-outline-secondary"
                                       style="font-size:.75rem;padding:2px 7px;" title="Lihat bukti">
                                        <i class="bi bi-paperclip"></i>
                                    </a>
                                    {{-- Edit --}}
                                    <button type="button" class="btn btn-xs btn-outline-primary"
                                            style="font-size:.75rem;padding:2px 7px;"
                                            onclick="openEdit({{ $item->id }}, {{ $sudahJurnal ? 'true' : 'false' }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    {{-- Hapus --}}
                                    @if(!$sudahJurnal)
                                    <form action="{{ route('bendahara.pendapatan-luar.destroy', $item) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger"
                                                style="font-size:.75rem;padding:2px 7px;" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center bg-light small">
                    <span class="text-muted">
                        Total (halaman ini):
                        <strong>Rp {{ number_format($pendapatans->sum('jumlah'), 0, ',', '.') }}</strong>
                    </span>
                    <div>{{ $pendapatans->links() }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEdit" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title fw-semibold">Edit Pendapatan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="infoJurnal" class="alert alert-info py-2 small d-none">
                        <i class="bi bi-info-circle me-1"></i>
                        Data tanggal, jumlah, dan akun tidak dapat diubah karena sudah masuk jurnal.
                    </div>
                    <div class="mb-3 field-finansial">
                        <label class="form-label small fw-semibold">Tanggal</label>
                        <input type="date" name="tanggal" id="editTanggal" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Uraian <span class="text-danger">*</span></label>
                        <input type="text" name="uraian" id="editUraian" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kategori</label>
                        <input type="text" name="kategori" id="editKategori" class="form-control">
                    </div>
                    <div class="mb-3 field-finansial">
                        <label class="form-label small fw-semibold">Jumlah (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="jumlah" id="editJumlah"
                                   class="form-control font-monospace" inputmode="numeric">
                        </div>
                    </div>
                    <div class="mb-3 field-finansial">
                        <label class="form-label small fw-semibold">Akun Pendapatan</label>
                        <select name="coa_id" id="editCoaId" class="form-select">
                            @foreach($coaOptions as $coa)
                            <option value="{{ $coa->id }}">{{ $coa->kode }} — {{ $coa->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ganti Bukti <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="file" name="bukti" class="form-control"
                               accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Kosongkan jika tidak ingin mengganti bukti.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan</label>
                        <textarea name="catatan" id="editCatatan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Format rupiah ────────────────────────────────────────────────────────────
function fmtRupiah(el) {
    el.addEventListener('input', () => {
        const raw = el.value.replace(/\D/g, '');
        el.value  = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    });
}
fmtRupiah(document.getElementById('inputJumlah'));
fmtRupiah(document.getElementById('editJumlah'));

// Sebelum submit form tambah, konversi jumlah ke integer
document.getElementById('formTambah').addEventListener('submit', function () {
    const el  = document.getElementById('inputJumlah');
    const raw = el.value.replace(/\D/g, '');
    el.value  = raw || '0';
});

// ── Toggle panel CoA baru ────────────────────────────────────────────────────
document.getElementById('selectCoa').addEventListener('change', function () {
    const panel = document.getElementById('panelCoaBaru');
    if (this.value === '__baru__') {
        panel.classList.remove('d-none');
        this.value = ''; // reset agar tidak kirim '__baru__'
    } else {
        panel.classList.add('d-none');
    }
});

// ── Modal Edit ───────────────────────────────────────────────────────────────
// Data diisi via AJAX sederhana — ambil dari data-* di baris tabel
// Tapi kita simpan data di JS object untuk kemudahan
const rowData = @json($rowData);

function openEdit(id, sudahJurnal) {
    const d = rowData[id];
    if (!d) return;

    document.getElementById('formEdit').action = `/bendahara/pendapatan-luar/${id}`;

    document.getElementById('editTanggal').value  = d.tanggal;
    document.getElementById('editUraian').value   = d.uraian;
    document.getElementById('editKategori').value = d.kategori || '';
    document.getElementById('editJumlah').value   = parseInt(d.jumlah).toLocaleString('id-ID');
    document.getElementById('editCoaId').value    = d.coa_id;
    document.getElementById('editCatatan').value  = d.catatan || '';

    // Sembunyikan field finansial kalau sudah jurnal
    document.querySelectorAll('.field-finansial').forEach(el => {
        el.style.display = sudahJurnal ? 'none' : '';
    });
    document.getElementById('infoJurnal').classList.toggle('d-none', !sudahJurnal);

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEdit')).show();
}

// Konversi jumlah sebelum submit edit
document.getElementById('formEdit').addEventListener('submit', function () {
    const el  = document.getElementById('editJumlah');
    const raw = el.value.replace(/\D/g, '');
    el.value  = raw || '0';
});
</script>
@endpush