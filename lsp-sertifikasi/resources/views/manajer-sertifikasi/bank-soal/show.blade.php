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
        {{-- Mini stats --}}
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
                {{-- Kiri: daftar soal observasi --}}
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
                            {{-- Header --}}
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
                                            data-bs-target="#collapseObs{{ $obs->id }}"
                                            title="Upload paket">
                                        <i class="bi bi-plus-lg me-1"></i> Paket
                                    </button>
                                    <form method="POST"
                                          action="{{ route('manajer-sertifikasi.bank-soal.observasi.destroy', [$skema, $obs]) }}"
                                          onsubmit="return confirm('Hapus soal observasi beserta semua paket?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Daftar paket --}}
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
                                           class="btn btn-link btn-sm p-0 ms-1 text-primary" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
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

                            {{-- Form upload paket (collapse) --}}
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
                                                        @if($ada) <i class="bi bi-check-lg ms-1"></i> @endif
                                                    </button>
                                                    @endforeach
                                                </div>
                                                <input type="text" name="kode_paket" id="kodeInput{{ $obs->id }}"
                                                       class="form-control form-control-sm mt-1"
                                                       placeholder="atau ketik: G, H, ..."
                                                       value="" required style="max-width:120px">
                                            </div>
                                            <div class="col">
                                                <label class="form-label fw-semibold mb-1" style="font-size:.8rem">File PDF <span class="text-danger">*</span></label>
                                                <input type="file" name="file" class="form-control form-control-sm"
                                                       accept=".pdf" required>
                                                <div class="form-text">PDF · Maks. 10 MB</div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-upload me-1"></i> Upload
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

                {{-- Kanan: buat observasi baru --}}
                <div class="col-md-5">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Soal Observasi
                    </h6>
                    <div class="border rounded-3 p-3 bg-light">
                        <p class="text-muted mb-3" style="font-size:.8rem">
                            Setiap soal observasi bisa punya beberapa paket (A, B, C, D, dst).
                        </p>
                        <form method="POST"
                              action="{{ route('manajer-sertifikasi.bank-soal.observasi.store', $skema) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    Nama / Judul Observasi <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="judul" class="form-control form-control-sm"
                                       placeholder="cth: Lembar Observasi Unit 1"
                                       value="{{ old('judul') }}" required>
                                <div class="form-text">Misal: "Observasi Teknis Pemasangan Listrik"</div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-plus-lg me-1"></i> Buat Soal Observasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================
             TAB 2: SOAL TEORI PG
        ================================================================ --}}
        <div class="tab-pane fade {{ request('tab') === 'teori' ? 'show active' : '' }} p-4"
             id="pane-teori">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="fw-bold mb-0">Bank Soal Teori</h6>
                    <small class="text-muted">Pool: {{ $jumlahTeori }} soal · distribusi ke asesi: ±30 soal acak</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('manajer-sertifikasi.bank-soal.teori.template', $skema) }}"
                    class="btn btn-outline-success btn-sm">
                        <i class="bi bi-file-earmark-excel me-1"></i> Download Template
                    </a>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalImportSoal">
                        <i class="bi bi-upload me-1"></i> Import Excel
                    </button>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Soal
                    </button>
                </div>
            </div>

            @if($jumlahTeori < 30)
            <div class="alert alert-warning d-flex gap-2 py-2 px-3 mb-3" style="font-size:.8rem">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                Bank soal baru punya <strong>{{ $jumlahTeori }} soal</strong>. Disarankan minimal 30 sebelum didistribusikan.
            </div>
            @endif

            @if($soalTeori->isEmpty())
            <div class="text-center py-5 border rounded-3 text-muted">
                <i class="bi bi-journal-x" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                <p class="fw-semibold mb-2">Belum ada soal teori</p>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Soal Pertama
                </button>
            </div>
            @else
            <div class="d-flex flex-column gap-2">
                @foreach($soalTeori as $i => $s)
                @php $idx = $soalTeori->firstItem() + $i; @endphp
                <div class="border rounded-3 overflow-hidden soal-card">
                    <div class="d-flex align-items-start gap-3 px-3 py-3 bg-white soal-header"
                        onclick="toggleSoal({{ $s->id }})" style="cursor:pointer;">
                        <div class="fw-bold text-muted flex-shrink-0"
                            style="min-width:28px;font-size:.85rem;padding-top:2px;">
                            {{ $idx }}.
                        </div>
                        <div class="flex-grow-1" style="min-width:0;overflow:visible;">
                            <div style="font-size:.875rem;font-weight:500;line-height:1.5;white-space:normal;word-break:break-word;overflow-wrap:anywhere;">
                                {{ $s->pertanyaan }}
                            </div>
                            <div class="soal-preview-pills d-flex flex-wrap gap-1 mt-2" id="preview-{{ $s->id }}">
                                @foreach(['a','b','c','d','e'] as $opt)
                                @if($s->{'pilihan_' . $opt})
                                <span class="badge rounded-pill px-2 py-1
                                    {{ $s->jawaban_benar === $opt ? 'bg-success text-white' : 'bg-light text-dark border' }}"
                                    style="font-size:.72rem;">
                                    {{ strtoupper($opt) }}. {{ Str::limit($s->{'pilihan_' . $opt}, 30) }}
                                    @if($s->jawaban_benar === $opt)<i class="bi bi-check-lg ms-1"></i>@endif
                                </span>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="flex-shrink-0 d-flex align-items-center gap-2">
                            <span class="badge rounded-circle fw-bold"
                                style="background:#16a34a;color:white;width:28px;height:28px;line-height:20px;font-size:.85rem;text-align:center;">
                                {{ strtoupper($s->jawaban_benar) }}
                            </span>
                            <i class="bi bi-chevron-down text-muted soal-chevron" id="chevron-{{ $s->id }}"
                            style="transition:transform .2s;"></i>
                        </div>
                    </div>

                    <div class="soal-detail border-top bg-light px-3 py-3" id="detail-{{ $s->id }}"
                        style="display:none;">
                        <div class="row g-2 mb-3">
                            @foreach(['a','b','c','d','e'] as $opt)
                            @php $val = $s->{'pilihan_' . $opt}; @endphp
                            @if($val)
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-2 p-2 rounded-2
                                    {{ $s->jawaban_benar === $opt ? 'bg-success bg-opacity-10 border border-success' : 'bg-white border' }}">
                                    <span class="badge flex-shrink-0 mt-1
                                        {{ $s->jawaban_benar === $opt ? 'bg-success' : 'bg-secondary' }}"
                                        style="min-width:22px;font-size:.78rem;">
                                        {{ strtoupper($opt) }}
                                    </span>
                                    <span style="font-size:.85rem;">{{ $val }}</span>
                                    @if($s->jawaban_benar === $opt)
                                    <i class="bi bi-check-circle-fill text-success ms-auto flex-shrink-0 mt-1"></i>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="btn btn-sm btn-outline-primary"
                                    onclick="event.stopPropagation(); editSoal({{ $s->toJson() }})">
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                            <form method="POST"
                                action="{{ route('manajer-sertifikasi.bank-soal.teori.destroy', [$skema, $s]) }}"
                                class="d-inline"
                                onsubmit="event.stopPropagation(); return confirm('Hapus soal ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash3 me-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="px-3 py-3">
                {{ $soalTeori->appends(array_merge(request()->query(), ['tab' => 'teori']))->fragment('pane-teori')->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>

        {{-- ================================================================
             TAB 3: PORTOFOLIO
             Di sini hanya mendaftarkan form penilaian (judul + file).
             File yang diupload di sini akan didistribusikan ke jadwal dari
             halaman Distribusi — tidak perlu upload ulang.
        ================================================================ --}}
        <div class="tab-pane fade {{ request('tab') === 'portofolio' ? 'show active' : '' }} p-4"
             id="pane-portofolio">

            <div class="row g-4">
                {{-- Kiri: daftar form portofolio --}}
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
                                      onsubmit="return confirm('Hapus form portofolio ini? Distribusi yang sudah ada juga akan terpengaruh.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Kanan: tambah form portofolio baru --}}
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
                                          placeholder="Keterangan singkat tentang form ini...">{{ old('deskripsi') }}</textarea>
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
                                         id="labelPorto{{ $skema->id }}">
                                        Klik untuk pilih file
                                    </div>
                                    <small class="text-muted">Excel / Word / PDF · Maks. 20 MB</small>
                                    <input type="file" name="file" id="filePorto{{ $skema->id }}" class="d-none"
                                           accept=".xlsx,.xls,.xlsm,.pdf,.doc,.docx"
                                           onchange="previewPorto('{{ $skema->id }}', this)">
                                </div>
                                @error('file')<div class="text-danger mt-1" style="font-size:.8rem">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-save me-1"></i> Simpan Form Portofolio
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /tab-content --}}
</div>

{{-- ===== MODAL TAMBAH / EDIT SOAL TEORI ===== --}}
<div class="modal fade" id="modalTambahSoal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formSoalTeori"
                  action="{{ route('manajer-sertifikasi.bank-soal.teori.store', $skema) }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalSoalTitle">
                        <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Soal Teori
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea name="pertanyaan" class="form-control" rows="3"
                                  placeholder="Tuliskan pertanyaan..." required id="inputPertanyaan"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $key => $label)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem">
                                Pilihan {{ $label }}
                                @if($key === 'e')<span class="text-muted fw-normal">(opsional)</span>@else<span class="text-danger">*</span>@endif
                            </label>
                            <input type="text" name="pilihan_{{ $key }}"
                                   class="form-control form-control-sm"
                                   placeholder="Jawaban {{ $label }}"
                                   {{ $key !== 'e' ? 'required' : '' }}
                                   id="inputPilihan{{ strtoupper($key) }}">
                        </div>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">Jawaban Benar <span class="text-danger">*</span></label>
                        <select name="jawaban_benar" class="form-select" required id="selectJawaban">
                            <option value="">— Pilih Jawaban Benar —</option>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                            <option value="e">E</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Simpan Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Modal Import Soal Teori --}}
<div class="modal fade" id="modalImportSoal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import Soal Teori dari Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('manajer-sertifikasi.bank-soal.teori.import', $skema) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Alert error import --}}
                    @if(session('import_errors') && count(session('import_errors')))
                    <div class="alert alert-warning alert-dismissible fade show">
                        <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Beberapa baris dilewati:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach(session('import_errors') as $err)
                                <li><small>{{ $err }}</small></li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.85rem">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Format kolom:</strong>
                        No · Pertanyaan · Pilihan A · Pilihan B · Pilihan C · Pilihan D · Pilihan E (opsional) · Jawaban Benar (a/b/c/d/e)
                    </div>

                    <div class="card mb-3">
                        <div class="card-body text-center py-3">
                            <p class="text-muted small mb-2">Belum punya template? Download dulu:</p>
                            <a href="{{ route('manajer-sertifikasi.bank-soal.teori.template', $skema) }}"
                               class="btn btn-outline-success btn-sm">
                                <i class="bi bi-file-earmark-excel me-1"></i>Download Template Excel
                            </a>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <div class="form-text">Format: .xlsx / .xls · Maks. 10 MB</div>
                    </div>

                    <div class="alert alert-warning py-2 px-3" style="font-size:.8rem">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Soal yang diimport akan <strong>ditambahkan</strong> ke bank soal yang sudah ada (tidak mengganti).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-upload me-1"></i>Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pilihKode(obsId, kode) {
    document.getElementById('kodeInput' + obsId).value = kode;
    document.querySelectorAll('.kode-btn-' + obsId + ':not(:disabled)').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    event.target.classList.remove('btn-outline-primary');
    event.target.classList.add('btn-primary');
}

function previewPorto(skemaId, input) {
    const label = document.getElementById('labelPorto' + skemaId);
    if (input.files && input.files[0]) {
        const f = input.files[0];
        label.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i>${f.name}`;
    }
}

function editSoal(soal) {
    document.getElementById('modalSoalTitle').innerHTML =
        '<i class="bi bi-pencil-square text-warning me-2"></i>Edit Soal Teori';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('formSoalTeori').action =
        '{{ url("manajer-sertifikasi/bank-soal/' . $skema->id . '/teori") }}/' + soal.id;

    document.getElementById('inputPertanyaan').value = soal.pertanyaan;
    document.getElementById('inputPilihanA').value = soal.pilihan_a;
    document.getElementById('inputPilihanB').value = soal.pilihan_b;
    document.getElementById('inputPilihanC').value = soal.pilihan_c;
    document.getElementById('inputPilihanD').value = soal.pilihan_d;
    document.getElementById('inputPilihanE').value = soal.pilihan_e ?? '';
    document.getElementById('selectJawaban').value = soal.jawaban_benar;

    new bootstrap.Modal(document.getElementById('modalTambahSoal')).show();
}

document.getElementById('modalTambahSoal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('formSoalTeori').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('formSoalTeori').action =
        '{{ route("manajer-sertifikasi.bank-soal.teori.store", $skema) }}';
    document.getElementById('modalSoalTitle').innerHTML =
        '<i class="bi bi-plus-circle text-primary me-2"></i>Tambah Soal Teori';
});

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam  = urlParams.get('tab');
    const hash = window.location.hash;
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
            const target = e.target.getAttribute('data-bs-target');
            history.replaceState(null, null, target);
        });
    });
});

function toggleSoal(id) {
    const detail  = document.getElementById('detail-'  + id);
    const chevron = document.getElementById('chevron-' + id);
    const preview = document.getElementById('preview-' + id);
    const open    = detail.style.display !== 'none';

    detail.style.display  = open ? 'none' : 'block';
    preview.style.display = open ? 'flex' : 'none';
    if (chevron) chevron.style.transform = open ? '' : 'rotate(180deg)';
}
</script>
@endpush