@extends('layouts.app')

@section('title', 'Kelola Soal — ' . $schedule->skema->name)
@section('breadcrumb', 'Distribusi › ' . $schedule->skema->name)

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

{{-- ===== HEADER ===== --}}
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <a href="{{ route('manajer-sertifikasi.distribusi') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Distribusi
        </a>
        <h4 class="fw-bold mb-1">{{ $schedule->skema->name }}</h4>
        <div class="d-flex gap-3 flex-wrap" style="font-size:.875rem;color:#6b7280">
            <span><i class="bi bi-calendar3 me-1"></i>{{ $schedule->assessment_date->translatedFormat('d F Y') }}</span>
            <span><i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}</span>
            <span><i class="bi bi-building me-1"></i>{{ $schedule->tuk->name ?? '-' }}</span>
            <span><i class="bi bi-people me-1"></i>{{ $schedule->asesmens->count() }} asesi</span>
            @if($schedule->asesor)
                <span><i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor->user->name ?? '-' }}</span>
            @endif
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap align-items-center">
        @if($schedule->asesor?->user?->signature)
        <a href="{{ route('manajer-sertifikasi.jadwal.daftar-hadir', $schedule) }}"
           target="_blank" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-pdf me-1"></i>Daftar Hadir
        </a>
        @else
        <button class="btn btn-sm btn-outline-secondary disabled" title="Asesor belum menandatangani">
            <i class="bi bi-file-pdf me-1"></i>Daftar Hadir
        </button>
        @endif

        <div class="d-flex gap-2 flex-wrap">
            @php
                $countObservasi  = $distribusiObservasiIds->count();
                $countPortofolio = $distribusiPortofolioIds->count();
                $countTeori      = $distribusiTeori?->jumlah_soal ?? 0;
                $durasiTeori     = $distribusiTeori?->durasi_menit ?? 30;
            @endphp
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:#f0fdf4;border:1px solid #bbf7d0;min-width:90px">
                <div style="font-size:1.3rem;font-weight:800;color:#16a34a">{{ $countObservasi }}</div>
                <div style="font-size:.68rem;color:#6b7280;font-weight:600">Observasi</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:{{ $distribusiTeori ? '#eff6ff' : '#fffbeb' }};border:1px solid {{ $distribusiTeori ? '#bfdbfe' : '#fde68a' }};min-width:90px">
                <div style="font-size:1.3rem;font-weight:800;color:{{ $distribusiTeori ? '#2563eb' : '#d97706' }}">
                    {{ $countTeori }}
                </div>
                <div style="font-size:.68rem;color:#6b7280;font-weight:600">
                    Soal Teori
                    @if($distribusiTeori)
                    <br><span style="color:#2563eb">{{ $durasiTeori }} mnt</span>
                    @endif
                </div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:#fdf4ff;border:1px solid #e9d5ff;min-width:90px">
                <div style="font-size:1.3rem;font-weight:800;color:#7c3aed">{{ $countPortofolio }}</div>
                <div style="font-size:.68rem;color:#6b7280;font-weight:600">Portofolio</div>
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

{{-- ===== CARD 4 TAB ===== --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white pb-0">
        <ul class="nav nav-tabs" id="soalTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-observasi">
                    <i class="bi bi-eye me-1"></i> Soal Observasi
                    @if($countObservasi)
                        <span class="badge bg-success ms-1" style="font-size:.6rem">{{ $countObservasi }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-teori">
                    <i class="bi bi-journal-text me-1"></i> Soal Teori (PG)
                    @if(!$distribusiTeori)
                        <i class="bi bi-exclamation-circle text-warning ms-1"></i>
                    @else
                        <span class="badge bg-primary ms-1" style="font-size:.6rem">{{ $countTeori }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-portofolio">
                    <i class="bi bi-briefcase me-1"></i> Portofolio
                    @if($countPortofolio)
                        <span class="badge bg-success ms-1" style="font-size:.6rem">{{ $countPortofolio }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-ujikom">
                    <i class="bi bi-google me-1"></i> Dok. Ujikom
                    @php $ujikomCount = $schedule->asesmens->filter(fn($a) => $a->apldua?->gdrive_ujikom)->count(); @endphp
                    @if($ujikomCount)
                    <span class="badge bg-success ms-1" style="font-size:.6rem">{{ $ujikomCount }}</span>
                    @endif
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">

        {{-- ================================================================
             TAB 1: SOAL OBSERVASI
        ================================================================ --}}
        <div class="tab-pane fade show active p-4" id="pane-observasi">
            <div class="row g-4">
                <div class="col-md-7">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-database text-muted me-2"></i>
                        Soal Observasi Tersedia
                        <span class="badge bg-secondary ms-1">{{ $soalObservasiTersedia->count() }}</span>
                    </h6>

                    @if($soalObservasiTersedia->isEmpty())
                        <div class="text-center py-4 border rounded-3 text-muted">
                            <i class="bi bi-file-earmark-pdf" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                            <p class="fw-semibold mb-0">Belum ada soal observasi untuk skema ini</p>
                            <small>Tambahkan soal observasi di menu Bank Soal</small>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($soalObservasiTersedia as $obs)
                            @php
                                $sudah      = $distribusiObservasiIds->contains($obs->id);
                                $distRecord = $sudah
                                    ? $schedule->distribusiSoalObservasi->firstWhere('soal_observasi_id', $obs->id)
                                    : null;
                                $paketAktif = $distRecord?->paketSoalObservasi;
                                $hasForm    = $distRecord && $distRecord->form_penilaian_path !== null;
                            @endphp
                            <div class="border rounded-3 overflow-hidden {{ $sudah ? 'border-success' : '' }}">
                                <div class="d-flex align-items-center justify-content-between px-3 py-2
                                            {{ $sudah ? 'bg-success-subtle' : 'bg-light' }}">
                                    <div>
                                        <div class="fw-semibold" style="font-size:.875rem">{{ $obs->judul }}</div>
                                        <small class="text-muted">
                                            {{ $obs->paket->count() }} paket tersedia
                                            @if($obs->paket->isNotEmpty())
                                                ({{ $obs->paket->pluck('kode_paket')->join(', ') }})
                                            @endif
                                        </small>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center flex-shrink-0">
                                        @if($sudah)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-lg me-1"></i>Paket {{ $paketAktif?->kode_paket ?? '?' }} Aktif
                                            </span>
                                            <button class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#gantiPaket{{ $obs->id }}">
                                                <i class="bi bi-arrow-repeat me-1"></i>Ganti
                                            </button>
                                            <form method="POST" action="{{ route('manajer-sertifikasi.soal-observasi.distribusi.hapus') }}">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                                <input type="hidden" name="soal_observasi_id" value="{{ $obs->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Hapus distribusi ini?')">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        @else
                                            @if($obs->paket->isEmpty())
                                                <span class="btn btn-sm btn-outline-secondary disabled">
                                                    <i class="bi bi-exclamation-circle me-1"></i>Belum ada paket
                                                </span>
                                            @else
                                                <button class="btn btn-sm btn-primary"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#gantiPaket{{ $obs->id }}">
                                                    <i class="bi bi-send me-1"></i>Distribusikan
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                @if($obs->paket->isNotEmpty())
                                <div class="collapse" id="gantiPaket{{ $obs->id }}">
                                    <div class="px-3 py-3 border-top bg-white">
                                        <p class="fw-semibold small mb-2">
                                            <i class="bi bi-collection text-primary me-1"></i>Pilih 1 paket:
                                        </p>
                                        <form method="POST" action="{{ route('manajer-sertifikasi.soal-observasi.distribusi') }}">
                                            @csrf
                                            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                            <input type="hidden" name="soal_observasi_id" value="{{ $obs->id }}">
                                            <div class="d-flex gap-2 flex-wrap mb-3">
                                                @foreach($obs->paket as $p)
                                                <label style="cursor:pointer;">
                                                    <input type="radio" name="paket_soal_observasi_id"
                                                           value="{{ $p->id }}" class="d-none paket-radio"
                                                           {{ $paketAktif?->id === $p->id ? 'checked' : '' }}>
                                                    <div class="border rounded-3 px-3 py-2 d-flex align-items-center gap-2 paket-card
                                                                {{ $paketAktif?->id === $p->id ? 'border-primary bg-primary bg-opacity-10' : 'bg-light' }}"
                                                         onclick="pilihanPaket(this, '{{ $p->id }}')"
                                                         style="font-size:.82rem;transition:all .15s;">
                                                        <span class="badge rounded-circle fw-bold"
                                                              style="width:26px;height:26px;line-height:18px;text-align:center;background:#2563eb;color:white;font-size:.82rem;">
                                                            {{ $p->kode_paket }}
                                                        </span>
                                                        <div>
                                                            <div class="fw-semibold">Paket {{ $p->kode_paket }}</div>
                                                            <div class="text-muted" style="font-size:.72rem;">
                                                                <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i>{{ $p->file_name }}
                                                            </div>
                                                        </div>
                                                        @if($paketAktif?->id === $p->id)
                                                        <i class="bi bi-check-circle-fill text-primary ms-2"></i>
                                                        @endif
                                                    </div>
                                                </label>
                                                @endforeach
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-send me-1"></i>{{ $sudah ? 'Ganti Paket' : 'Distribusikan' }}
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#gantiPaket{{ $obs->id }}">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                @endif

                                @if($sudah && $paketAktif)
                                <div class="px-3 py-2 border-top bg-white d-flex align-items-center gap-2">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                    <div class="flex-grow-1 small">
                                        <span class="fw-semibold">Paket {{ $paketAktif->kode_paket }}</span>
                                        <span class="text-muted ms-2">{{ $paketAktif->file_name }}</span>
                                    </div>
                                    <a href="{{ route('manajer-sertifikasi.soal-observasi.paket.download', $paketAktif) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                                @endif

                                @if($sudah)
                                <div class="px-3 py-2 border-top {{ $hasForm ? 'bg-primary-subtle' : 'bg-white' }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">
                                                <i class="bi bi-clipboard2-check me-1 {{ $hasForm ? 'text-primary' : 'text-muted' }}"></i>
                                                Form Penilaian
                                                @if(!$hasForm)
                                                <span class="badge bg-warning ms-1" style="font-size:.65rem;">Belum diupload</span>
                                                @endif
                                            </div>
                                            @if($hasForm)
                                            <div class="text-muted" style="font-size:.75rem;">
                                                <i class="bi bi-file-earmark-excel me-1 text-success"></i>{{ $distRecord->form_penilaian_name }}
                                            </div>
                                            @else
                                            <div class="text-muted" style="font-size:.75rem;">Template Excel penilaian untuk asesor</div>
                                            @endif
                                        </div>
                                        @if($hasForm)
                                        <a href="{{ route('manajer-sertifikasi.jadwal.observasi.form-penilaian.download', [$schedule, $obs]) }}"
                                           class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                        <form method="POST"
                                              action="{{ route('manajer-sertifikasi.jadwal.observasi.form-penilaian.hapus', [$schedule, $obs]) }}"
                                              onsubmit="return confirm('Hapus form penilaian ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                        @endif
                                        <button class="btn btn-sm {{ $hasForm ? 'btn-outline-secondary' : 'btn-outline-primary' }}"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#uploadFormPenilaian{{ $obs->id }}">
                                            <i class="bi bi-upload me-1"></i>{{ $hasForm ? 'Ganti' : 'Upload' }}
                                        </button>
                                    </div>
                                    <div class="collapse mt-2" id="uploadFormPenilaian{{ $obs->id }}">
                                        <form method="POST"
                                              action="{{ route('manajer-sertifikasi.jadwal.observasi.form-penilaian.upload', [$schedule, $obs]) }}"
                                              enctype="multipart/form-data">
                                            @csrf
                                            <div class="d-flex gap-2">
                                                <input type="file" name="file" class="form-control form-control-sm"
                                                       accept=".xlsx,.xlsm,.xls" required>
                                                <button type="submit" class="btn btn-primary btn-sm flex-shrink-0">
                                                    <i class="bi bi-upload"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Excel (.xlsx / .xlsm / .xls) · Maks. 20 MB</div>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-md-5">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-plus-circle text-primary me-2"></i>Buat Soal Observasi Baru
                    </h6>
                    <div class="border rounded-3 p-3 bg-light">
                        <form method="POST" action="{{ route('manajer-sertifikasi.soal-observasi.store') }}">
                            @csrf
                            <input type="hidden" name="skema_id" value="{{ $schedule->skema_id }}">
                            <input type="hidden" name="redirect_back" value="{{ url()->current() }}">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">Nama Soal Observasi</label>
                                <input type="text" name="judul" class="form-control form-control-sm"
                                       placeholder="cth: Observasi Kompetensi Teknis" required>
                                <div class="form-text">Setelah dibuat, tambahkan paket A, B, C, dst.</div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-plus-lg me-1"></i>Buat & Tambah Paket
                            </button>
                        </form>
                    </div>
                    <div class="mt-3 p-3 border rounded-3 bg-light">
                        <p class="fw-semibold mb-2" style="font-size:.875rem">
                            <i class="bi bi-info-circle text-primary me-1"></i>Cara kerja:
                        </p>
                        <ol class="ps-3 mb-0" style="font-size:.8rem;color:#6b7280;line-height:1.8">
                            <li>Buat soal observasi (judul/kelompok)</li>
                            <li>Tambahkan paket A, B, C, D</li>
                            <li>Distribusikan ke jadwal ini</li>
                            <li>Upload form penilaian (.xlsx) untuk asesor</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================
             TAB 2: SOAL TEORI PG — dengan pilih paket
        ================================================================ --}}
        <div class="tab-pane fade p-4" id="pane-teori">
            <div class="row g-4">
                <div class="col-md-5">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-gear text-muted me-2"></i>Konfigurasi Distribusi Soal Teori
                    </h6>

                    {{-- Status distribusi aktif --}}
                    @if($distribusiTeori)
                    <div class="alert alert-success d-flex gap-2 py-2 px-3 mb-3" style="font-size:.875rem">
                        <i class="bi bi-check-circle-fill flex-shrink-0 mt-1"></i>
                        <div>
                            @if($distribusiTeori->paketSoalTeori)
                            <strong>Paket {{ $distribusiTeori->paketSoalTeori->kode_paket }}</strong>
                            ({{ $distribusiTeori->paketSoalTeori->tahun }})
                            @if($distribusiTeori->paketSoalTeori->nama_paket)
                                — {{ $distribusiTeori->paketSoalTeori->nama_paket }}
                            @endif
                            <br>
                            @endif
                            <strong>{{ $distribusiTeori->jumlah_soal }} soal</strong> per asesi
                            · <strong>{{ $distribusiTeori->durasi_menit ?? 30 }} menit</strong><br>
                            <small class="text-muted">
                                {{ $distribusiTeori->didistribusikanOleh->name ?? '-' }}
                                · {{ $distribusiTeori->updated_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning d-flex gap-2 py-2 px-3 mb-3" style="font-size:.875rem">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                        Soal teori belum didistribusikan.
                    </div>
                    @endif

                    {{-- Form distribusi --}}
                    <div class="border rounded-3 p-3 bg-light">
                        <form method="POST" action="{{ route('manajer-sertifikasi.soal-teori.distribusi') }}"
                              id="formDistribusiTeori">
                            @csrf
                            <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

                            {{-- Pilih Paket --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    Paket Soal <span class="text-danger">*</span>
                                </label>
                                @if($paketSoalTeori->isEmpty())
                                <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:.8rem">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Belum ada paket soal teori untuk skema ini.
                                    <a href="{{ route('manajer-sertifikasi.bank-soal.show', $schedule->skema) }}#pane-teori"
                                       class="alert-link">Buat paket di Bank Soal</a>
                                </div>
                                @else
                                <select name="paket_soal_teori_id" class="form-select" required
                                        onchange="updateJumlahMax(this)">
                                    <option value="">— Pilih Paket —</option>
                                    @foreach($paketSoalTeori as $p)
                                    <option value="{{ $p->id }}"
                                            data-jumlah="{{ $p->soal_teori_count }}"
                                            {{ $distribusiTeori?->paket_soal_teori_id === $p->id ? 'selected' : '' }}>
                                        Paket {{ $p->kode_paket }} ({{ $p->tahun }})
                                        @if($p->nama_paket) — {{ $p->nama_paket }}@endif
                                        · {{ $p->soal_teori_count }} soal
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text" id="infoPaketDipilih">
                                    Pilih paket untuk melihat jumlah soal tersedia.
                                </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    Jumlah Soal per Asesi <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="jumlah_soal" id="inputJumlahSoal"
                                       class="form-control"
                                       value="{{ $distribusiTeori?->jumlah_soal ?? 30 }}"
                                       min="1" max="{{ $jumlahBankSoalTeori }}" required>
                                <div class="form-text">Diacak dari paket yang dipilih per asesi.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:.875rem">
                                    Durasi Pengerjaan
                                </label>
                                <div class="input-group">
                                    <input type="number" name="durasi_menit" class="form-control"
                                           value="{{ $distribusiTeori?->durasi_menit ?? 30 }}"
                                           min="1" max="300" required>
                                    <span class="input-group-text">menit</span>
                                </div>
                                <div class="form-text">Maks. 300 menit (5 jam).</div>
                            </div>

                            @if($distribusiTeori)
                            <div class="alert alert-warning py-2 px-3 mb-3" style="font-size:.8rem">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Akan <strong>mengacak ulang</strong> soal untuk {{ $schedule->asesmens->count() }} asesi.
                            </div>
                            @endif

                            <button type="button" class="btn btn-primary w-100"
                                    onclick="konfirmasiTeori()"
                                    {{ $paketSoalTeori->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-shuffle me-1"></i>
                                {{ $distribusiTeori ? 'Perbarui Distribusi' : 'Distribusikan Soal Teori' }}
                            </button>
                        </form>
                    </div>

                    {{-- Shortcut ke bank soal --}}
                    <div class="mt-3 p-2 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                        <small class="text-muted"><i class="bi bi-collection text-primary me-1"></i>Kelola paket soal</small>
                        <a href="{{ route('manajer-sertifikasi.bank-soal.show', $schedule->skema) }}#pane-teori"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Bank Soal
                        </a>
                    </div>
                </div>

                <div class="col-md-7">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-people text-muted me-2"></i>
                        Daftar Asesi ({{ $schedule->asesmens->count() }} orang)
                    </h6>

                    {{-- Info paket per paket tersedia --}}
                    @if($paketSoalTeori->isNotEmpty())
                    <div class="mb-3 d-flex flex-wrap gap-2">
                        @foreach($paketSoalTeori as $p)
                        <div class="border rounded-3 px-3 py-2 d-flex align-items-center gap-2
                                    {{ $distribusiTeori?->paket_soal_teori_id === $p->id ? 'border-primary bg-primary bg-opacity-10' : 'bg-light' }}"
                             style="font-size:.82rem">
                            <span class="badge {{ $distribusiTeori?->paket_soal_teori_id === $p->id ? 'bg-primary' : 'bg-secondary' }}">
                                Paket {{ $p->kode_paket }}
                            </span>
                            <span class="text-muted">{{ $p->tahun }}</span>
                            <span class="fw-semibold">{{ $p->soal_teori_count }} soal</span>
                            @if($distribusiTeori?->paket_soal_teori_id === $p->id)
                            <span class="badge bg-success ms-1"><i class="bi bi-check-lg me-1"></i>Aktif</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($schedule->asesmens->isEmpty())
                        <div class="text-center py-4 border rounded-3 text-muted">
                            <i class="bi bi-person-x" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                            <p class="mb-0">Belum ada asesi terdaftar</p>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th class="text-center">Soal Teori</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedule->asesmens as $i => $asesmen)
                                @php
                                    $punya = $distribusiTeori
                                        ? $distribusiTeori->soalAsesi()->where('asesmen_id', $asesmen->id)->exists()
                                        : false;
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:.875rem">{{ $asesmen->full_name }}</div>
                                        <small class="text-muted">{{ $asesmen->user->email ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $asesmen->status_badge }}">
                                            {{ $asesmen->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($punya)
                                            <span class="badge bg-success"><i class="bi bi-check-lg"></i> Sudah</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================================================================
             TAB 3: PORTOFOLIO
        ================================================================ --}}
        <div class="tab-pane fade p-4" id="pane-portofolio">
            <div class="row g-4">
                <div class="col-md-8">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-database text-muted me-2"></i>
                        Form Penilaian Portofolio Tersedia
                        <span class="badge bg-secondary ms-1">{{ $portofolioTersedia->count() }}</span>
                    </h6>

                    @if($portofolioTersedia->isEmpty())
                        <div class="text-center py-5 border rounded-3 text-muted">
                            <i class="bi bi-briefcase" style="font-size:2rem;opacity:.3;display:block;margin-bottom:.5rem"></i>
                            <p class="fw-semibold mb-1">Belum ada form penilaian portofolio untuk skema ini</p>
                            <small>Upload form penilaian portofolio terlebih dahulu di menu
                                <a href="{{ route('manajer-sertifikasi.bank-soal.show', $schedule->skema) }}#pane-portofolio">
                                    Bank Soal
                                </a>
                            </small>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($portofolioTersedia as $porto)
                            @php
                                $sudah       = $distribusiPortofolioIds->contains($porto->id);
                                $distRecord  = $schedule->distribusiPortofolio->firstWhere('portofolio_id', $porto->id);
                                $hasKisiKisi = $distRecord && $distRecord->kisi_kisi_path
                                              && Storage::disk('private')->exists($distRecord->kisi_kisi_path);
                                $hasForm     = $distRecord && $distRecord->form_penilaian_path
                                              && Storage::disk('private')->exists($distRecord->form_penilaian_path);
                            @endphp
                            <div class="border rounded-3 overflow-hidden {{ $sudah ? 'border-success' : '' }}">
                                <div class="d-flex align-items-center justify-content-between px-3 py-3
                                            {{ $sudah ? 'bg-success-subtle' : 'bg-light' }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:42px;height:42px;background:{{ $sudah ? '#dcfce7' : '#f5f3ff' }}">
                                            <i class="bi bi-{{ $porto->hasFile() ? 'file-earmark-excel-fill' : 'file-earmark-text' }}"
                                               style="font-size:1.3rem;color:{{ $sudah ? '#16a34a' : '#7c3aed' }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:.875rem">{{ $porto->judul }}</div>
                                            @if($porto->hasFile())
                                                <small class="text-muted"><i class="bi bi-paperclip me-1"></i>{{ $porto->file_name }}</small>
                                            @else
                                                <small class="text-muted fst-italic">Tidak ada file lampiran</small>
                                            @endif
                                            @if($porto->deskripsi)
                                                <br><small class="text-muted">{{ Str::limit($porto->deskripsi, 60) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center flex-shrink-0">
                                        @if($sudah)
                                            <span class="badge bg-success px-3 py-2">
                                                <i class="bi bi-check-lg me-1"></i>Terdistribusi
                                            </span>
                                            <form method="POST"
                                                  action="{{ route('manajer-sertifikasi.portofolio.distribusi.hapus') }}"
                                                  onsubmit="return confirm('Hapus distribusi ini?')">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                                <input type="hidden" name="portofolio_id" value="{{ $porto->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('manajer-sertifikasi.portofolio.distribusi') }}">
                                                @csrf
                                                <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                                                <input type="hidden" name="portofolio_id" value="{{ $porto->id }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-send me-1"></i>Distribusikan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                @if($sudah)
                                {{-- Kisi-kisi --}}
                                <div class="border-top px-3 py-2 {{ $hasKisiKisi ? 'bg-warning-subtle' : 'bg-white' }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">
                                                <i class="bi bi-list-check me-1 {{ $hasKisiKisi ? 'text-warning' : 'text-muted' }}"></i>
                                                Kisi-Kisi / Soal
                                                @if(!$hasKisiKisi)
                                                <span class="badge bg-warning ms-1" style="font-size:.65rem;">Belum diupload</span>
                                                @endif
                                            </div>
                                            @if($hasKisiKisi)
                                            <div class="text-muted" style="font-size:.75rem;">
                                                <i class="bi bi-paperclip me-1"></i>{{ $distRecord->kisi_kisi_name }}
                                            </div>
                                            @else
                                            <div class="text-muted" style="font-size:.75rem;">File kisi-kisi/soal untuk asesor</div>
                                            @endif
                                        </div>
                                        @if($hasKisiKisi)
                                        <a href="{{ route('manajer-sertifikasi.jadwal.portofolio.kisi-kisi.download', [$schedule, $porto]) }}"
                                           class="btn btn-sm btn-outline-warning"><i class="bi bi-download"></i></a>
                                        <form method="POST"
                                              action="{{ route('manajer-sertifikasi.jadwal.portofolio.kisi-kisi.hapus', [$schedule, $porto]) }}"
                                              onsubmit="return confirm('Hapus kisi-kisi ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                        @endif
                                        <button class="btn btn-sm {{ $hasKisiKisi ? 'btn-outline-secondary' : 'btn-outline-warning' }}"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#uploadKisiKisi{{ $porto->id }}">
                                            <i class="bi bi-upload me-1"></i>{{ $hasKisiKisi ? 'Ganti' : 'Upload' }}
                                        </button>
                                    </div>
                                    <div class="collapse mt-2" id="uploadKisiKisi{{ $porto->id }}">
                                        <form method="POST"
                                              action="{{ route('manajer-sertifikasi.jadwal.portofolio.kisi-kisi.upload', [$schedule, $porto]) }}"
                                              enctype="multipart/form-data">
                                            @csrf
                                            <div class="d-flex gap-2">
                                                <input type="file" name="file" class="form-control form-control-sm"
                                                       accept=".xlsx,.xlsm,.xls,.pdf,.doc,.docx" required>
                                                <button type="submit" class="btn btn-warning btn-sm flex-shrink-0">
                                                    <i class="bi bi-upload"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Excel / PDF / Word · Maks. 20 MB</div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Form Penilaian --}}
                                <div class="border-top px-3 py-2 {{ $hasForm ? 'bg-primary-subtle' : 'bg-white' }}">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small">
                                                <i class="bi bi-clipboard2-check me-1 {{ $hasForm ? 'text-primary' : 'text-muted' }}"></i>
                                                Form Penilaian
                                                @if(!$hasForm)
                                                <span class="badge bg-warning ms-1" style="font-size:.65rem;">Belum diupload</span>
                                                @endif
                                            </div>
                                            @if($hasForm)
                                            <div class="text-muted" style="font-size:.75rem;">
                                                <i class="bi bi-file-earmark-excel me-1 text-success"></i>{{ $distRecord->form_penilaian_name }}
                                            </div>
                                            @else
                                            <div class="text-muted" style="font-size:.75rem;">Template Excel penilaian untuk asesor</div>
                                            @endif
                                        </div>
                                        @if($hasForm)
                                        <a href="{{ route('manajer-sertifikasi.jadwal.portofolio.form-penilaian.download', [$schedule, $porto]) }}"
                                           class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                        <form method="POST"
                                              action="{{ route('manajer-sertifikasi.jadwal.portofolio.form-penilaian.hapus', [$schedule, $porto]) }}"
                                              onsubmit="return confirm('Hapus form penilaian ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                                        </form>
                                        @endif
                                        <button class="btn btn-sm {{ $hasForm ? 'btn-outline-secondary' : 'btn-outline-primary' }}"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#uploadFormPenilaian{{ $porto->id }}">
                                            <i class="bi bi-upload me-1"></i>{{ $hasForm ? 'Ganti' : 'Upload' }}
                                        </button>
                                    </div>
                                    <div class="collapse mt-2" id="uploadFormPenilaian{{ $porto->id }}">
                                        <form method="POST"
                                              action="{{ route('manajer-sertifikasi.jadwal.portofolio.form-penilaian.upload', [$schedule, $porto]) }}"
                                              enctype="multipart/form-data">
                                            @csrf
                                            <div class="d-flex gap-2">
                                                <input type="file" name="file" class="form-control form-control-sm"
                                                       accept=".xlsx,.xlsm,.xls" required>
                                                <button type="submit" class="btn btn-primary btn-sm flex-shrink-0">
                                                    <i class="bi bi-upload"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Excel (.xlsx / .xlsm / .xls) · Maks. 20 MB</div>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="border rounded-3 p-3 bg-light">
                        <h6 class="fw-semibold mb-2">
                            <i class="bi bi-info-circle text-primary me-1"></i>Cara kerja Portofolio
                        </h6>
                        <ol class="ps-3 mb-3" style="font-size:.8rem;color:#6b7280;line-height:1.9">
                            <li>Upload form penilaian di <strong>Bank Soal</strong></li>
                            <li>Kembali ke halaman ini</li>
                            <li>Klik <strong>Distribusikan</strong></li>
                            <li>Asesor dapat mengunduh form</li>
                        </ol>
                        <a href="{{ route('manajer-sertifikasi.bank-soal.show', $schedule->skema) }}#pane-portofolio"
                           class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-collection me-1"></i>Kelola Bank Soal Portofolio
                        </a>
                    </div>
                    @if($countPortofolio > 0)
                    <div class="mt-3 border rounded-3 p-3" style="background:#f0fdf4;border-color:#bbf7d0!important">
                        <div class="fw-semibold small text-success mb-1">
                            <i class="bi bi-check-circle-fill me-1"></i>{{ $countPortofolio }} form terdistribusi
                        </div>
                        <div class="text-muted" style="font-size:.78rem;">
                            Asesor dapat mengunduh form melalui dashboard mereka.
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================================================================
             TAB 4: DOKUMEN UJIKOM
        ================================================================ --}}
        <div class="tab-pane fade p-4" id="pane-ujikom">
            <h6 class="fw-bold mb-1">Dokumen Hasil Ujikom / Portofolio Peserta</h6>
            <p class="text-muted small mb-4">
                Link Google Drive yang dilampirkan peserta sebagai bukti hasil ujian kompetensi.
            </p>

            @if($schedule->asesmens->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                <p class="fw-semibold mb-0">Belum ada peserta</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light" style="font-size:.78rem;">
                        <tr>
                            <th class="ps-3" width="40">#</th>
                            <th>Nama Peserta</th>
                            <th>Status</th>
                            <th class="text-center">Dok. Ujikom</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule->asesmens as $i => $asesi)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                            <td>
                                <div class="fw-semibold" style="font-size:.875rem">{{ $asesi->full_name }}</div>
                                <small class="text-muted">{{ $asesi->user->email ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $asesi->status_badge }}">{{ $asesi->status_label }}</span>
                            </td>
                            <td class="text-center">
                                @if($asesi->apldua?->gdrive_ujikom)
                                <a href="{{ $asesi->apldua->gdrive_ujikom }}" target="_blank"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>Buka
                                </a>
                                @else
                                <span class="badge bg-secondary" style="font-size:.7rem;">Belum diisi</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @php $totalUjikom = $schedule->asesmens->filter(fn($a) => $a->apldua?->gdrive_ujikom)->count(); @endphp
            <div class="mt-3 p-3 rounded-3 border {{ $totalUjikom === $schedule->asesmens->count() ? 'bg-success-subtle border-success' : 'bg-warning-subtle border-warning' }}">
                <small class="{{ $totalUjikom === $schedule->asesmens->count() ? 'text-success' : 'text-warning' }} fw-semibold">
                    <i class="bi bi-{{ $totalUjikom === $schedule->asesmens->count() ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-1"></i>
                    {{ $totalUjikom }} dari {{ $schedule->asesmens->count() }} peserta sudah melampirkan dokumen ujikom
                </small>
            </div>
            @endif
        </div>

    </div>{{-- /tab-content --}}
</div>

@endsection

@push('scripts')
<script>
// ── Konfirmasi distribusi teori ──────────────────────────────
function konfirmasiTeori() {
    const paketSelect = document.querySelector('select[name="paket_soal_teori_id"]');
    const paketText   = paketSelect ? paketSelect.options[paketSelect.selectedIndex]?.text : '-';
    const jumlah      = document.querySelector('input[name="jumlah_soal"]').value;
    const durasi      = document.querySelector('input[name="durasi_menit"]').value;
    const asesi       = {{ $schedule->asesmens->count() }};
    const sudah       = {{ $distribusiTeori ? 'true' : 'false' }};

    if (paketSelect && !paketSelect.value) {
        Swal.fire('Pilih paket soal terlebih dahulu.'); return;
    }

    Swal.fire({
        title: sudah ? 'Perbarui Distribusi?' : 'Distribusikan Soal Teori?',
        html: `<b>${paketText}</b><br><b>${jumlah} soal</b> · <b>${durasi} menit</b> · <b>${asesi} asesi</b>`
            + (sudah ? '<br><small class="text-warning">Data lama akan digantikan.</small>' : ''),
        icon: sudah ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: sudah ? 'Ya, Perbarui' : 'Ya, Distribusikan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#2563eb',
    }).then(r => { if (r.isConfirmed) document.getElementById('formDistribusiTeori').submit(); });
}

// ── Update max jumlah soal sesuai paket dipilih ──────────────
function updateJumlahMax(select) {
    const opt    = select.options[select.selectedIndex];
    const jumlah = parseInt(opt.dataset.jumlah || 0);
    const input  = document.getElementById('inputJumlahSoal');
    const info   = document.getElementById('infoPaketDipilih');

    if (jumlah > 0) {
        input.max = jumlah;
        if (parseInt(input.value) > jumlah) input.value = jumlah;
        if (info) info.textContent = `Paket ini memiliki ${jumlah} soal. Maks. ${jumlah} soal per asesi.`;
    } else {
        if (info) info.textContent = 'Pilih paket untuk melihat jumlah soal tersedia.';
    }
}

// ── Restore tab dari URL hash ────────────────────────────────
const hash = window.location.hash;
if (hash) {
    const t = document.querySelector(`[data-bs-target="${hash}"]`);
    if (t) new bootstrap.Tab(t).show();
}
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(t => {
    t.addEventListener('shown.bs.tab', e => {
        history.replaceState(null, null, e.target.getAttribute('data-bs-target'));
    });
});

// ── Pilihan paket observasi ──────────────────────────────────
function pilihanPaket(cardEl, paketId) {
    const form = cardEl.closest('form');
    form.querySelectorAll('.paket-card').forEach(c => {
        c.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        c.classList.add('bg-light');
        const chk = c.querySelector('.bi-check-circle-fill');
        if (chk) chk.remove();
    });
    cardEl.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    cardEl.classList.remove('bg-light');
    form.querySelector(`input[value="${paketId}"]`).checked = true;
}
</script>
@endpush