{{-- resources/views/manajer-sertifikasi/bank-soal/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Bank Soal — ' . $skema->name)
@section('breadcrumb', 'Bank Soal › ' . $skema->name)

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

{{-- Header --}}
<div class="mb-4">
    <a href="{{ route('manajer-sertifikasi.bank-soal.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div>
            <h4 class="fw-bold mb-1">{{ $skema->name }}</h4>
            <div class="d-flex gap-2" style="font-size:.8rem;color:#6b7280">
                <span class="badge rounded-pill" style="background:#eff6ff;color:#2563eb">{{ $skema->code }}</span>
                <span class="badge rounded-pill" style="background:#f5f3ff;color:#7c3aed">{{ $skema->jenis_label }}</span>
            </div>
        </div>
        <div class="ms-auto d-flex gap-2">
            <div class="text-center px-3 py-2 rounded-3" style="background:#f0f9ff;border:1px solid #bae6fd;min-width:70px">
                <div style="font-size:1.1rem;font-weight:800;color:#0284c7">{{ $soalObservasi->count() }}</div>
                <div style="font-size:.62rem;color:#6b7280;font-weight:600">OBSERVASI</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3" style="background:#f0fdf4;border:1px solid #bbf7d0;min-width:70px">
                <div style="font-size:1.1rem;font-weight:800;color:#16a34a">{{ $jumlahTeori }}</div>
                <div style="font-size:.62rem;color:#6b7280;font-weight:600">SOAL TEORI</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3" style="background:#fdf4ff;border:1px solid #e9d5ff;min-width:70px">
                <div style="font-size:1.1rem;font-weight:800;color:#7c3aed">{{ $portofolios->count() }}</div>
                <div style="font-size:.62rem;color:#6b7280;font-weight:600">PORTOFOLIO</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.875rem">
    <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.875rem">
    <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('import_errors') && count(session('import_errors')))
<div class="alert alert-warning alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.875rem">
    <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Beberapa baris dilewati saat import:</strong>
    <ul class="mb-0 mt-1">
        @foreach(session('import_errors') as $err)
            <li><small>{{ $err }}</small></li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- 3 Tab --}}
<div class="card">
    <div class="card-header pb-0">
        <ul class="nav nav-tabs" id="bankSoalTabs">
            <li class="nav-item">
                <button class="nav-link {{ request('tab', 'observasi') === 'observasi' ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#pane-observasi">
                    <i class="bi bi-eye me-1"></i> Soal Observasi
                    @if($soalObservasi->count())
                        <span class="badge bg-primary ms-1" style="font-size:.6rem">{{ $soalObservasi->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ request('tab') === 'teori' ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#pane-teori">
                    <i class="bi bi-journal-text me-1"></i> Soal Teori (PG)
                    @if($jumlahTeori)
                        <span class="badge bg-success ms-1" style="font-size:.6rem">{{ $jumlahTeori }}</span>
                    @else
                        <span class="badge bg-warning ms-1" style="font-size:.6rem">0</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link {{ request('tab') === 'portofolio' ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#pane-portofolio">
                    <i class="bi bi-briefcase me-1"></i> Portofolio
                    @if($portofolios->count())
                        <span class="badge bg-success ms-1" style="font-size:.6rem">{{ $portofolios->count() }}</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">

        {{-- ================================================================
             TAB 1: SOAL OBSERVASI
        ================================================================ --}}
        <div class="tab-pane fade {{ request('tab', 'observasi') === 'observasi' ? 'show active' : '' }} p-4"
             id="pane-observasi">
            <div class="row g-4">
                <div class="col-md-7">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-collection text-muted me-2"></i>
                        Soal Observasi
                        <span class="badge bg-secondary ms-1">{{ $soalObservasi->count() }}</span>
                    </h6>

                    @if($soalObservasi->isEmpty())
                    <div class="text-center py-5 border rounded-3 text-muted">
                        <i class="bi bi-file-earmark-pdf" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                        <p class="fw-semibold mb-1">Belum ada soal observasi</p>
                        <small>Buat soal observasi di kanan, lalu upload paket A, B, C, dst</small>
                    </div>
                    @else
                    <div class="d-flex flex-column gap-3">
                        @foreach($soalObservasi as $obs)
                        <div class="border rounded-3 overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-light">
                                <div>
                                    <div class="fw-semibold" style="font-size:.875rem">{{ $obs->judul }}</div>
                                    <small class="text-muted">
                                        {{ $obs->paket->count() }} paket
                                        @if($obs->paket->isNotEmpty())
                                            ({{ $obs->paket->pluck('kode_paket')->join(', ') }})
                                        @endif
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseObs{{ $obs->id }}">
                                        <i class="bi bi-plus-lg me-1"></i>Paket
                                    </button>
                                    <form method="POST"
                                          action="{{ route('manajer-sertifikasi.bank-soal.observasi.destroy', [$skema, $obs]) }}"
                                          onsubmit="return confirm('Hapus soal observasi beserta semua paket?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </div>

                            @if($obs->paket->isNotEmpty())
                            <div class="px-3 py-2 border-top bg-white">
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($obs->paket as $p)
                                    <div class="d-flex align-items-center gap-1 border rounded px-2 py-1"
                                         style="font-size:.78rem;background:#f8fafc">
                                        <span class="badge rounded-circle fw-bold me-1"
                                              style="background:#2563eb;color:white;width:22px;height:22px;line-height:14px;text-align:center;font-size:.75rem">
                                            {{ $p->kode_paket }}
                                        </span>
                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                        <span class="text-muted">{{ $p->file_name }}</span>
                                        <a href="{{ route('manajer-sertifikasi.bank-soal.paket.download', [$skema, $p]) }}"
                                           class="btn btn-link btn-sm p-0 ms-1 text-primary" title="Download Soal">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        @if($p->lampiran_path)
                                        <a href="{{ route('manajer-sertifikasi.bank-soal.paket.download-lampiran', [$skema, $p]) }}"
                                           class="btn btn-link btn-sm p-0 ms-1 text-secondary" title="Download Lampiran">
                                            <i class="bi bi-file-earmark-word"></i>
                                        </a>
                                        @endif
                                        <form method="POST"
                                              action="{{ route('manajer-sertifikasi.bank-soal.paket.destroy', [$skema, $p]) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus Paket {{ $p->kode_paket }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-link btn-sm p-0 ms-1 text-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="px-3 py-2 border-top">
                                <small class="text-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Belum ada paket — klik "+ Paket" untuk upload
                                </small>
                            </div>
                            @endif

                            <div class="collapse" id="collapseObs{{ $obs->id }}">
                                <div class="px-3 py-3 border-top bg-light">
                                    <form method="POST"
                                          action="{{ route('manajer-sertifikasi.bank-soal.paket.store', [$skema, $obs]) }}"
                                          enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-2 align-items-end">
                                            <div class="col-auto">
                                                <label class="form-label fw-semibold mb-1" style="font-size:.8rem">Kode Paket</label>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    @foreach(['A','B','C','D','E','F'] as $kode)
                                                    @php $ada = $obs->paket->contains('kode_paket', $kode); @endphp
                                                    <button type="button"
                                                            class="btn btn-sm {{ $ada ? 'btn-success disabled' : 'btn-outline-primary' }} kode-btn-{{ $obs->id }}"
                                                            onclick="pilihKode('{{ $obs->id }}','{{ $kode }}')"
                                                            {{ $ada ? 'disabled' : '' }}>
                                                        {{ $kode }}
                                                        @if($ada)<i class="bi bi-check-lg ms-1"></i>@endif
                                                    </button>
                                                    @endforeach
                                                </div>
                                                <input type="text" name="kode_paket" id="kodeInput{{ $obs->id }}"
                                                       class="form-control form-control-sm mt-1"
                                                       placeholder="atau ketik: G, H, ..."
                                                       required style="max-width:120px">
                                            </div>
                                            <div class="col">
                                                <label class="form-label fw-semibold mb-1" style="font-size:.8rem">
                                                    File PDF Soal <span class="text-danger">*</span>
                                                </label>
                                                <input type="file" name="file" class="form-control form-control-sm"
                                                       accept=".pdf" required>
                                                <div class="form-text">PDF · Maks. 10 MB</div>
                                            </div>
                                            <div class="col">
                                                <label class="form-label fw-semibold mb-1" style="font-size:.8rem">
                                                    Lampiran <span class="text-muted fw-normal">(opsional)</span>
                                                </label>
                                                <input type="file" name="lampiran" class="form-control form-control-sm"
                                                       accept=".doc,.docx">
                                                <div class="form-text">DOC/DOCX · Maks. 20 MB</div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-upload me-1"></i>Upload
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="col-md-5">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Soal Observasi
                    </h6>
                    <div class="border rounded-3 p-3 bg-light">
                        <p class="text-muted mb-3" style="font-size:.8rem">
                            Setiap soal observasi bisa punya beberapa paket (A, B, C, D, dst).
                        </p>
                        <form method="POST" action="{{ route('manajer-sertifikasi.bank-soal.observasi.store', $skema) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    Nama / Judul Observasi <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="judul" class="form-control form-control-sm"
                                       placeholder="cth: Lembar Observasi Unit 1"
                                       value="{{ old('judul') }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-plus-lg me-1"></i>Buat Soal Observasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================
             TAB 2: SOAL TEORI
        ================================================================ --}}
        <div class="tab-pane fade {{ request('tab') === 'teori' ? 'show active' : '' }} p-4" id="pane-teori">

            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-journal-text text-muted me-2"></i>
                        Bank Soal Teori
                        <span class="badge bg-secondary ms-1">{{ $jumlahTeori }} soal</span>
                    </h6>
                    <small class="text-muted">Soal dikelompokkan per paket. Saat distribusi ke jadwal, pilih 1 paket.</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('manajer-sertifikasi.bank-soal.teori.template', $skema) }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-download me-1"></i>Template Excel
                    </a>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                            data-bs-target="#formImportTeori">
                        <i class="bi bi-upload me-1"></i>Import Excel
                    </button>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                            data-bs-target="#formBuatPaket">
                        <i class="bi bi-folder-plus me-1"></i>Buat Paket Baru
                    </button>
                </div>
            </div>

            {{-- Form Buat Paket --}}
            <div class="collapse mb-3" id="formBuatPaket">
                <div class="border rounded-3 p-3 bg-light">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-folder-plus text-primary me-1"></i>Buat Paket Soal Teori Baru</h6>
                    <form method="POST" action="{{ route('manajer-sertifikasi.bank-soal.teori.paket.store', $skema) }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold" style="font-size:.8rem">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="kode_paket" class="form-control form-control-sm text-uppercase"
                                       placeholder="A" maxlength="10" required value="{{ old('kode_paket') }}">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold" style="font-size:.8rem">Nama Paket <span class="text-muted fw-normal">(opsional)</span></label>
                                <input type="text" name="nama_paket" class="form-control form-control-sm"
                                       placeholder="cth: Paket Reguler 2025" value="{{ old('nama_paket') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold" style="font-size:.8rem">Tahun <span class="text-danger">*</span></label>
                                <input type="number" name="tahun" class="form-control form-control-sm"
                                       value="{{ old('tahun', date('Y')) }}" min="2020" max="2099" required>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-plus-lg me-1"></i>Buat Paket
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Form Import Excel --}}
            <div class="collapse mb-3" id="formImportTeori">
                <div class="border rounded-3 p-3 bg-light">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-file-earmark-excel text-success me-1"></i>Import Soal dari Excel</h6>
                    <form method="POST"
                          action="{{ route('manajer-sertifikasi.bank-soal.teori.import', $skema) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" style="font-size:.8rem">Masukkan ke Paket</label>
                                <select name="paket_soal_teori_id" class="form-select form-select-sm">
                                    <option value="">— Tanpa Paket (Arsip) —</option>
                                    @foreach($paketSoalTeori as $p)
                                    <option value="{{ $p->id }}">Paket {{ $p->kode_paket }} ({{ $p->tahun }})
                                        @if($p->nama_paket) — {{ $p->nama_paket }}@endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold" style="font-size:.8rem">File Excel (.xlsx) <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="bi bi-upload me-1"></i>Import
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Format: No · Pertanyaan · Pilihan A–E · Jawaban Benar (a/b/c/d/e).
                                Soal yang diimport <strong>ditambahkan</strong>, tidak mengganti yang sudah ada.
                            </small>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Bulk Action Bar --}}
            <div id="bulkActionBar" class="d-none mb-3 p-2 rounded-3 border bg-warning-subtle d-flex align-items-center gap-3 flex-wrap">
                <span class="fw-semibold small"><span id="bulkCount">0</span> soal dipilih</span>
                <div class="d-flex gap-2 ms-auto flex-wrap">
                    <select id="bulkPaketTarget" class="form-select form-select-sm" style="width:auto">
                        <option value="">— Pindah ke Paket —</option>
                        <option value="arsip">Arsip (tanpa paket)</option>
                        @foreach($paketSoalTeori as $p)
                        <option value="{{ $p->id }}">Paket {{ $p->kode_paket }} ({{ $p->tahun }})</option>
                        @endforeach
                    </select>
                    <button onclick="bulkPindahPaket()" class="btn btn-sm btn-warning">
                        <i class="bi bi-arrow-right-circle me-1"></i>Pindah
                    </button>
                    <button onclick="bulkHapus()" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash3 me-1"></i>Hapus Terpilih
                    </button>
                    <button onclick="clearSelection()" class="btn btn-sm btn-outline-secondary">Batal</button>
                </div>
            </div>

            {{-- Daftar Paket + Soal --}}
            @if($paketSoalTeori->isEmpty() && $soalTeoriArsip->isEmpty())
                <div class="text-center py-5 border rounded-3 text-muted">
                    <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                    <p class="fw-semibold mb-1">Belum ada soal teori</p>
                    <small>Buat paket terlebih dahulu, lalu tambahkan soal di dalamnya</small>
                </div>
            @else

                @foreach($paketSoalTeori as $paket)
                @php $soalDalamPaket = $paket->soalTeori()->latest()->get(); @endphp
                <div class="border rounded-3 mb-3 overflow-hidden">
                    {{-- Header paket --}}
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-primary-subtle">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input paket-check"
                                   data-paket="{{ $paket->id }}"
                                   onchange="togglePaketCheck(this)"
                                   title="Pilih semua soal di paket ini">
                            <div>
                                <span class="fw-bold text-primary">Paket {{ $paket->kode_paket }}</span>
                                @if($paket->nama_paket)
                                    <span class="text-muted ms-1">— {{ $paket->nama_paket }}</span>
                                @endif
                                <span class="badge bg-primary ms-2">{{ $paket->tahun }}</span>
                                <span class="badge bg-secondary ms-1">{{ $soalDalamPaket->count() }} soal</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                                    data-bs-target="#formTambahSoal{{ $paket->id }}">
                                <i class="bi bi-plus-lg me-1"></i>Tambah Soal
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                                    data-bs-target="#listSoal{{ $paket->id }}">
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <form method="POST"
                                  action="{{ route('manajer-sertifikasi.bank-soal.teori.paket.destroy', [$skema, $paket]) }}"
                                  onsubmit="return confirm('Hapus Paket {{ $paket->kode_paket }}? Soal di dalamnya akan dipindah ke Arsip.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Hapus paket">
                                    <i class="bi bi-folder-x"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Form tambah soal --}}
                    <div class="collapse" id="formTambahSoal{{ $paket->id }}">
                        <div class="p-3 border-bottom bg-light">
                            <form method="POST" action="{{ route('manajer-sertifikasi.bank-soal.teori.store', $skema) }}">
                                @csrf
                                <input type="hidden" name="paket_soal_teori_id" value="{{ $paket->id }}">
                                <div class="mb-2">
                                    <label class="form-label fw-semibold" style="font-size:.8rem">Pertanyaan <span class="text-danger">*</span></label>
                                    <textarea name="pertanyaan" class="form-control form-control-sm" rows="2" required></textarea>
                                </div>
                                <div class="row g-2 mb-2">
                                    @foreach(['a','b','c','d'] as $pl)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="font-size:.75rem">Pilihan {{ strtoupper($pl) }} <span class="text-danger">*</span></label>
                                        <input type="text" name="pilihan_{{ $pl }}" class="form-control form-control-sm" required>
                                    </div>
                                    @endforeach
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="font-size:.75rem">Pilihan E <span class="text-muted fw-normal">(opsional)</span></label>
                                        <input type="text" name="pilihan_e" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" style="font-size:.75rem">Jawaban Benar <span class="text-danger">*</span></label>
                                        <select name="jawaban_benar" class="form-select form-select-sm" required>
                                            <option value="">— Pilih —</option>
                                            @foreach(['a','b','c','d','e'] as $j)
                                            <option value="{{ $j }}">{{ strtoupper($j) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Simpan ke Paket {{ $paket->kode_paket }}
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- List soal --}}
                    <div class="collapse show" id="listSoal{{ $paket->id }}">
                        @if($soalDalamPaket->isEmpty())
                        <div class="p-3 text-center text-muted" style="font-size:.85rem">
                            <i class="bi bi-inbox me-1"></i>Belum ada soal. Klik "Tambah Soal" untuk mulai.
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:.82rem">
                                <thead class="table-light">
                                    <tr>
                                        <th width="32">
                                            <input type="checkbox" class="form-check-input"
                                                   onchange="toggleAllInPaket(this, {{ $paket->id }})">
                                        </th>
                                        <th width="40">#</th>
                                        <th>Pertanyaan</th>
                                        <th width="70" class="text-center">Jawaban</th>
                                        <th width="90" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($soalDalamPaket as $i => $soal)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input soal-check"
                                                   value="{{ $soal->id }}" data-paket="{{ $paket->id }}"
                                                   onchange="updateBulkBar()">
                                        </td>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::limit($soal->pertanyaan, 80) }}</div>
                                            <div class="text-muted" style="font-size:.72rem">
                                                A: {{ Str::limit($soal->pilihan_a, 25) }} &nbsp;·&nbsp;
                                                B: {{ Str::limit($soal->pilihan_b, 25) }} &nbsp;·&nbsp;
                                                C: {{ Str::limit($soal->pilihan_c, 25) }} &nbsp;·&nbsp;
                                                D: {{ Str::limit($soal->pilihan_d, 25) }}
                                                @if($soal->pilihan_e) &nbsp;·&nbsp; E: {{ Str::limit($soal->pilihan_e, 25) }} @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ strtoupper($soal->jawaban_benar) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary py-0 px-1"
                                                    onclick="editSoal({{ $soal->id }}, {{ json_encode($soal) }})"
                                                    title="Edit">
                                                <i class="bi bi-pencil" style="font-size:.75rem"></i>
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('manajer-sertifikasi.bank-soal.teori.destroy', [$skema, $soal]) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Hapus soal ini?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger py-0 px-1" title="Hapus">
                                                    <i class="bi bi-trash3" style="font-size:.75rem"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Arsip --}}
                @if($soalTeoriArsip->isNotEmpty())
                <div class="border rounded-3 mb-3 overflow-hidden border-secondary">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 bg-secondary-subtle">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" class="form-check-input"
                                   onchange="toggleAllInPaket(this, 'arsip')">
                            <div>
                                <span class="fw-bold text-secondary">
                                    <i class="bi bi-archive me-1"></i>Arsip
                                </span>
                                <span class="badge bg-secondary ms-1">{{ $soalTeoriArsip->count() }} soal</span>
                                <small class="text-muted ms-2">Soal lama tanpa paket — pindahkan ke paket untuk bisa didistribusikan</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse"
                                data-bs-target="#listSoalArsip">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                    <div class="collapse" id="listSoalArsip">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size:.82rem">
                                <thead class="table-light">
                                    <tr>
                                        <th width="32">
                                            <input type="checkbox" class="form-check-input"
                                                   onchange="toggleAllInPaket(this, 'arsip')">
                                        </th>
                                        <th width="40">#</th>
                                        <th>Pertanyaan</th>
                                        <th width="70" class="text-center">Jawaban</th>
                                        <th width="90" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($soalTeoriArsip as $i => $soal)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input soal-check"
                                                   value="{{ $soal->id }}" data-paket="arsip"
                                                   onchange="updateBulkBar()">
                                        </td>
                                        <td class="text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ Str::limit($soal->pertanyaan, 80) }}</div>
                                            <div class="text-muted" style="font-size:.72rem">
                                                A: {{ Str::limit($soal->pilihan_a, 25) }} &nbsp;·&nbsp;
                                                B: {{ Str::limit($soal->pilihan_b, 25) }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ strtoupper($soal->jawaban_benar) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary py-0 px-1"
                                                    onclick="editSoal({{ $soal->id }}, {{ json_encode($soal) }})"
                                                    title="Edit">
                                                <i class="bi bi-pencil" style="font-size:.75rem"></i>
                                            </button>
                                            <form method="POST"
                                                  action="{{ route('manajer-sertifikasi.bank-soal.teori.destroy', [$skema, $soal]) }}"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Hapus soal ini?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger py-0 px-1">
                                                    <i class="bi bi-trash3" style="font-size:.75rem"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

            @endif
        </div>

        {{-- ================================================================
             TAB 3: PORTOFOLIO
        ================================================================ --}}
        <div class="tab-pane fade {{ request('tab') === 'portofolio' ? 'show active' : '' }} p-4"
             id="pane-portofolio">
            <div class="row g-4">
                <div class="col-md-7">
                    <h6 class="fw-bold mb-2">
                        <i class="bi bi-collection text-muted me-2"></i>
                        Daftar Form Penilaian Portofolio
                        <span class="badge bg-secondary ms-1">{{ $portofolios->count() }}</span>
                    </h6>
                    <p class="text-muted mb-3" style="font-size:.8rem">
                        Form ini yang akan didistribusikan ke jadwal asesmen. Asesor akan mengunduhnya untuk menilai asesi.
                    </p>

                    @if($portofolios->isEmpty())
                    <div class="text-center py-5 border rounded-3 text-muted">
                        <i class="bi bi-file-earmark-text" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                        <p class="fw-semibold mb-1">Belum ada form penilaian portofolio</p>
                        <small>Tambahkan form di sebelah kanan</small>
                    </div>
                    @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($portofolios as $porto)
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border bg-white">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:42px;height:42px;background:#f5f3ff">
                                    <i class="bi bi-{{ $porto->hasFile() ? 'file-earmark-excel-fill' : 'file-earmark-text' }}"
                                       style="font-size:1.3rem;color:#7c3aed"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.875rem">{{ $porto->judul }}</div>
                                    @if($porto->hasFile())
                                        <small class="text-muted">
                                            <i class="bi bi-paperclip me-1"></i>{{ $porto->file_name }}
                                            <span class="badge bg-light text-secondary ms-1" style="font-size:.65rem">
                                                {{ strtoupper($porto->tipe_file) }}
                                            </span>
                                        </small>
                                    @else
                                        <small class="text-muted fst-italic">Tidak ada file lampiran</small>
                                    @endif
                                    @if($porto->deskripsi)
                                        <br><small class="text-muted">{{ Str::limit($porto->deskripsi, 60) }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                @if($porto->hasFile())
                                <a href="{{ route('manajer-sertifikasi.bank-soal.portofolio.download', [$skema, $porto]) }}"
                                   class="btn btn-sm btn-outline-primary" title="Download file">
                                    <i class="bi bi-download"></i>
                                </a>
                                @endif
                                <form method="POST"
                                      action="{{ route('manajer-sertifikasi.bank-soal.portofolio.destroy', [$skema, $porto]) }}"
                                      onsubmit="return confirm('Hapus form portofolio ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="col-md-5">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Form Penilaian
                    </h6>
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.8rem">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Daftarkan form penilaian di sini. File opsional — bisa berupa template Excel, Word, atau PDF.
                            Setelah didaftarkan, distribusikan ke jadwal dari menu <strong>Distribusi</strong>.
                        </div>
                        <form method="POST"
                              action="{{ route('manajer-sertifikasi.bank-soal.portofolio.store', $skema) }}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    Judul Form <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="judul" class="form-control form-control-sm"
                                       placeholder="cth: Form Penilaian Portofolio Unit 1"
                                       value="{{ old('judul') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    Deskripsi <span class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <textarea name="deskripsi" class="form-control form-control-sm" rows="2"
                                          placeholder="Keterangan singkat...">{{ old('deskripsi') }}</textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    File Template <span class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <div class="border rounded-3 p-3 text-center bg-white"
                                     onclick="document.getElementById('filePorto{{ $skema->id }}').click()"
                                     style="cursor:pointer;border-style:dashed!important;">
                                    <i class="bi bi-folder2-open text-primary" style="font-size:1.6rem"></i>
                                    <div class="mt-1 fw-semibold" style="font-size:.82rem"
                                         id="labelPorto{{ $skema->id }}">Klik untuk pilih file</div>
                                    <small class="text-muted">Excel / Word / PDF · Maks. 20 MB</small>
                                    <input type="file" name="file" id="filePorto{{ $skema->id }}" class="d-none"
                                           accept=".xlsx,.xls,.xlsm,.pdf,.doc,.docx"
                                           onchange="previewPorto('{{ $skema->id }}', this)">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-save me-1"></i>Simpan Form Portofolio
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>

{{-- ── MODAL EDIT SOAL ── --}}
<div class="modal fade" id="modalEditSoal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Soal Teori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditSoal" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">Paket</label>
                        <select name="paket_soal_teori_id" class="form-select form-select-sm">
                            <option value="">— Tanpa Paket (Arsip) —</option>
                            @foreach($paketSoalTeori as $p)
                            <option value="{{ $p->id }}">
                                Paket {{ $p->kode_paket }} ({{ $p->tahun }})
                                @if($p->nama_paket) — {{ $p->nama_paket }}@endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea name="pertanyaan" id="editPertanyaan" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        @foreach(['a','b','c','d'] as $pl)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem">Pilihan {{ strtoupper($pl) }} <span class="text-danger">*</span></label>
                            <input type="text" name="pilihan_{{ $pl }}" id="editPilihan{{ strtoupper($pl) }}"
                                   class="form-control form-control-sm" required>
                        </div>
                        @endforeach
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem">Pilihan E <span class="text-muted fw-normal">(opsional)</span></label>
                            <input type="text" name="pilihan_e" id="editPilihanE" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem">Jawaban Benar <span class="text-danger">*</span></label>
                            <select name="jawaban_benar" id="editJawaban" class="form-select form-select-sm" required>
                                @foreach(['a','b','c','d','e'] as $j)
                                <option value="{{ $j }}">{{ strtoupper($j) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Observasi paket pilih kode ──────────────────────────────
function pilihKode(obsId, kode) {
    document.getElementById('kodeInput' + obsId).value = kode;
    document.querySelectorAll('.kode-btn-' + obsId + ':not(:disabled)').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    event.target.classList.remove('btn-outline-primary');
    event.target.classList.add('btn-primary');
}

// ── Portofolio file preview ──────────────────────────────────
function previewPorto(skemaId, input) {
    const label = document.getElementById('labelPorto' + skemaId);
    if (input.files && input.files[0]) {
        label.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i>${input.files[0].name}`;
    }
}

// ── Tab restore ──────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam  = urlParams.get('tab');
    const hash      = window.location.hash;
    let targetSelector = null;

    if (tabParam) {
        targetSelector = `[data-bs-target="#pane-${tabParam}"]`;
    } else if (hash) {
        targetSelector = `[data-bs-target="${hash}"]`;
    }

    if (targetSelector) {
        const tabEl = document.querySelector(targetSelector);
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }

    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(t => {
        t.addEventListener('shown.bs.tab', e => {
            history.replaceState(null, null, e.target.getAttribute('data-bs-target'));
        });
    });

    // Jika ada error, arahkan ke tab teori
    @if($errors->any())
    const errTab = document.querySelector('[data-bs-target="#pane-teori"]');
    if (errTab) bootstrap.Tab.getOrCreateInstance(errTab).show();
    @endif
});

// ── Bulk selection ────────────────────────────────────────────
function updateBulkBar() {
    const checked = document.querySelectorAll('.soal-check:checked');
    const bar     = document.getElementById('bulkActionBar');
    document.getElementById('bulkCount').textContent = checked.length;
    bar.classList.toggle('d-none', checked.length === 0);
}

function toggleAllInPaket(masterCheck, paketId) {
    document.querySelectorAll(`.soal-check[data-paket="${paketId}"]`)
        .forEach(cb => cb.checked = masterCheck.checked);
    updateBulkBar();
}

function togglePaketCheck(masterCheck) {
    toggleAllInPaket(masterCheck, masterCheck.dataset.paket);
}

function clearSelection() {
    document.querySelectorAll('.soal-check, .paket-check').forEach(cb => cb.checked = false);
    updateBulkBar();
}

function getSelectedIds() {
    return [...document.querySelectorAll('.soal-check:checked')].map(cb => cb.value);
}

function bulkHapus() {
    const ids = getSelectedIds();
    if (!ids.length) return;
    Swal.fire({
        title: `Hapus ${ids.length} soal?`,
        text: 'Soal yang dihapus tidak bisa dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        confirmButtonColor: '#dc3545',
        cancelButtonText: 'Batal',
    }).then(r => {
        if (!r.isConfirmed) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("manajer-sertifikasi.bank-soal.teori.bulk-delete", $skema) }}';
        form.innerHTML = `<input name="_token" value="{{ csrf_token() }}">`;
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.name = 'ids[]'; inp.value = id;
            form.appendChild(inp);
        });
        document.body.appendChild(form);
        form.submit();
    });
}

function bulkPindahPaket() {
    const ids    = getSelectedIds();
    const target = document.getElementById('bulkPaketTarget').value;
    if (!ids.length) { Swal.fire('Pilih soal terlebih dahulu.'); return; }
    if (!target)     { Swal.fire('Pilih paket tujuan terlebih dahulu.'); return; }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("manajer-sertifikasi.bank-soal.teori.bulk-pindah-paket", $skema) }}';
    form.innerHTML = `<input name="_token" value="{{ csrf_token() }}">
        <input name="paket_soal_teori_id" value="${target === 'arsip' ? '' : target}">`;
    ids.forEach(id => {
        const inp = document.createElement('input');
        inp.name = 'ids[]'; inp.value = id;
        form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
}

// ── Edit soal modal ───────────────────────────────────────────
function editSoal(id, soal) {
    const baseUrl = '{{ url("manajer-sertifikasi/bank-soal/" . $skema->id . "/teori") }}';
    document.getElementById('formEditSoal').action = `${baseUrl}/${id}`;

    document.getElementById('editPertanyaan').value = soal.pertanyaan;
    document.getElementById('editPilihanA').value   = soal.pilihan_a;
    document.getElementById('editPilihanB').value   = soal.pilihan_b;
    document.getElementById('editPilihanC').value   = soal.pilihan_c;
    document.getElementById('editPilihanD').value   = soal.pilihan_d;
    document.getElementById('editPilihanE').value   = soal.pilihan_e ?? '';
    document.getElementById('editJawaban').value    = soal.jawaban_benar;

    const paketSelect = document.querySelector('#modalEditSoal select[name="paket_soal_teori_id"]');
    if (paketSelect) paketSelect.value = soal.paket_soal_teori_id ?? '';

    new bootstrap.Modal(document.getElementById('modalEditSoal')).show();
}
</script>
@endpush