@extends('layouts.app')

@section('title', 'Kelola Paket — ' . $soalObservasi->judul)
@section('breadcrumb', 'Soal Observasi › ' . $soalObservasi->judul)

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <a href="{{ route('manajer-sertifikasi.soal-observasi.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">{{ $soalObservasi->judul }}</h4>
            <div style="font-size:.875rem;color:#6b7280">
                <span class="badge bg-primary-subtle text-primary rounded-pill me-2">
                    {{ $soalObservasi->skema->name }}
                </span>
                <span>{{ $soalObservasi->paket->count() }} paket tersimpan</span>
                <span class="mx-2">·</span>
                <span>Dibuat oleh {{ $soalObservasi->dibuatOleh->name ?? '-' }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('manajer-sertifikasi.soal-observasi.destroy', $soalObservasi) }}"
            onsubmit="return confirm('Hapus soal observasi beserta semua paket?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash3 me-1"></i> Hapus Soal Observasi
            </button>
        </form>
    </div>
</div>

<div class="row g-4">
    {{-- Kiri: daftar paket yang sudah ada --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-collection text-primary me-2"></i>
                    Paket dalam Soal Ini
                    <span class="badge bg-secondary ms-1">{{ $soalObservasi->paket->count() }}</span>
                </h6>
            </div>
            <div class="card-body p-0">
                @if($soalObservasi->paket->isEmpty())
                <div class="empty-state">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <p class="fw-semibold">Belum ada paket</p>
                    <p class="text-muted mb-0" style="font-size:.875rem">
                        Tambahkan paket A, B, C, D di sebelah kanan
                    </p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width:80px">Kode</th>
                                <th>Judul</th>
                                <th>File</th>
                                <th>Diupload</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($soalObservasi->paket as $p)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge rounded-pill fw-bold"
                                        style="background:#2563eb;color:white;font-size:.85rem;width:30px;height:30px;line-height:22px;text-align:center">
                                        {{ $p->kode_paket }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold" style="font-size:.875rem">{{ $p->judul }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                        <span style="font-size:.78rem;color:#6b7280">{{ $p->file_name }}</span>
                                    </div>
                                </td>
                                <td style="font-size:.78rem;color:#6b7280">
                                    {{ $p->created_at->format('d M Y') }}<br>
                                    {{ $p->dibuatOleh->name ?? '-' }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('manajer-sertifikasi.soal-observasi.paket.download', $p) }}"
                                        class="btn btn-sm btn-outline-primary me-1" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form method="POST"
                                        action="{{ route('manajer-sertifikasi.soal-observasi.paket.destroy', $p) }}"
                                        class="d-inline" onsubmit="return confirm('Hapus paket ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash3"></i>
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
    </div>

    {{-- Kanan: form tambah paket baru --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-upload text-warning me-2"></i>Upload Paket Baru
                </h6>
            </div>
            <div class="card-body">
                <form method="POST"
                    action="{{ route('manajer-sertifikasi.soal-observasi.paket.store', $soalObservasi) }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">
                            Kode Paket <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            @foreach(['A','B','C','D','E','F'] as $kode)
                            @php $sudahAda = $soalObservasi->paket->contains('kode_paket', $kode); @endphp
                            <button type="button"
                                class="btn btn-sm {{ $sudahAda ? 'btn-success disabled' : 'btn-outline-primary' }} kode-btn"
                                onclick="pilihKode('{{ $kode }}')" {{ $sudahAda ? 'disabled' : '' }}>
                                Paket {{ $kode }}
                                @if($sudahAda) <i class="bi bi-check-lg ms-1"></i> @endif
                            </button>
                            @endforeach
                        </div>
                        <input type="text" name="kode_paket" id="inputKodePaket"
                            class="form-control form-control-sm @error('kode_paket') is-invalid @enderror"
                            placeholder="Atau ketik manual, cth: G" value="{{ old('kode_paket') }}" required>
                        @error('kode_paket') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Kode paket harus unik dalam observasi ini.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">
                            Judul Paket <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="judul" value="{{ old('judul') }}"
                            class="form-control form-control-sm @error('judul') is-invalid @enderror"
                            placeholder="cth: Lembar Observasi Paket A" required>
                        @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.875rem">
                            File PDF <span class="text-danger">*</span>
                        </label>
                        <div class="border rounded-3 p-3 text-center bg-light"
                            onclick="document.getElementById('filePaket').click()"
                            style="cursor:pointer;border-style:dashed!important">
                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size:1.6rem"></i>
                            <div class="mt-1 fw-semibold" style="font-size:.82rem" id="labelFilePaket">
                                Klik untuk pilih PDF
                            </div>
                            <small class="text-muted">Maks. 10 MB</small>
                            <input type="file" name="file" id="filePaket" accept=".pdf" class="d-none"
                                onchange="previewPaket(this)" required>
                        </div>
                        @error('file') <div class="text-danger mt-1" style="font-size:.875rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-upload me-1"></i> Upload Paket
                    </button>
                </form>
            </div>
        </div>

        {{-- Digunakan di berapa jadwal --}}
        @if($soalObservasi->distribusi->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="fw-bold mb-0 text-muted" style="font-size:.875rem">
                    <i class="bi bi-send-check me-2"></i>Digunakan di Jadwal
                </h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($soalObservasi->distribusi->take(5) as $dist)
                    <li class="list-group-item px-3 py-2" style="font-size:.82rem">
                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                        {{ $dist->schedule->skema->name ?? '-' }} —
                        {{ $dist->schedule->assessment_date?->format('d M Y') ?? '-' }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
function pilihKode(kode) {
    document.getElementById('inputKodePaket').value = kode;
    document.querySelectorAll('.kode-btn:not(:disabled)').forEach(b => b.classList.remove('btn-primary'));
    event.target.classList.add('btn-primary');
    event.target.classList.remove('btn-outline-primary');
}

function previewPaket(input) {
    const label = document.getElementById('labelFilePaket');
    if (input.files[0]) {
        const f = input.files[0];
        label.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i>${f.name}`;
    }
}
</script>
@endpush