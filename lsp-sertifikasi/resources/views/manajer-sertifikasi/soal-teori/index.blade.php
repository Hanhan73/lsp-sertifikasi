@extends('layouts.app')

@section('title', 'Bank Soal Teori')
@section('breadcrumb', 'Bank Soal › Soal Teori (PG)')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Bank Soal Teori</h4>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Kelola soal pilihan ganda. Default pool: 90 soal per skema, distribusi ke asesi: 30 soal.
        </p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
        <i class="bi bi-plus-lg me-1"></i> Tambah Soal
    </button>
</div>

{{-- Filter skema --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold mb-1" style="font-size:.8rem">Filter Skema</label>
                <select name="skema_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— Semua Skema —</option>
                    @foreach($skemas as $sk)
                    <option value="{{ $sk->id }}" {{ request('skema_id') == $sk->id ? 'selected' : '' }}>
                        {{ $sk->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold mb-1" style="font-size:.8rem">Cari Pertanyaan</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                    placeholder="Ketik untuk mencari...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
            </div>
            @if(request('skema_id') || request('q'))
            <div class="col-md-2">
                <a href="{{ route('manajer-sertifikasi.soal-teori.index') }}"
                    class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-x me-1"></i> Reset
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Ringkasan per skema --}}
@if($ringkasanSkema->isNotEmpty())
<div class="d-flex gap-2 flex-wrap mb-3">
    @foreach($ringkasanSkema as $r)
    <a href="{{ route('manajer-sertifikasi.soal-teori.index') }}?skema_id={{ $r->skema_id }}" class="badge rounded-pill text-decoration-none
              {{ request('skema_id') == $r->skema_id ? 'bg-primary' : 'bg-light text-dark border' }}"
        style="font-size:.8rem;padding:.4em .8em">
        {{ $r->skema_name }} ({{ $r->total }})
        @if($r->total < 30) <i class="bi bi-exclamation-circle text-warning ms-1"></i>
            @endif
    </a>
    @endforeach
</div>
@endif

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-journal-text text-primary me-2"></i>Daftar Soal
            <span class="badge bg-secondary ms-1">{{ $soalTeori->total() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        @if($soalTeori->isEmpty())
        <div class="empty-state">
            <i class="bi bi-journal-x"></i>
            <p class="fw-semibold">Belum ada soal teori</p>
            <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalTambahSoal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Soal Pertama
            </button>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:50px">#</th>
                        <th>Pertanyaan</th>
                        <th>Skema</th>
                        <th>Pilihan Jawaban</th>
                        <th class="text-center">Jawaban</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($soalTeori as $i => $s)
                    <tr>
                        <td class="ps-4 text-muted">{{ $soalTeori->firstItem() + $i }}</td>
                        <td style="max-width:300px">
                            <div style="font-size:.875rem;font-weight:500">
                                {{ Str::limit($s->pertanyaan, 100) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary rounded-pill badge-pill">
                                {{ $s->skema->name }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size:.78rem;color:#6b7280;line-height:1.6">
                                <span class="me-1">A.</span>{{ Str::limit($s->pilihan_a, 40) }}<br>
                                <span class="me-1">B.</span>{{ Str::limit($s->pilihan_b, 40) }}<br>
                                <span class="me-1">C.</span>{{ Str::limit($s->pilihan_c, 40) }}<br>
                                <span class="me-1">D.</span>{{ Str::limit($s->pilihan_d, 40) }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-circle fw-bold"
                                style="background:#16a34a;color:white;width:28px;height:28px;line-height:20px;font-size:.85rem">
                                {{ strtoupper($s->jawaban_benar) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-1" title="Edit"
                                onclick="editSoal({{ $s->toJson() }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('manajer-sertifikasi.soal-teori.destroy', $s) }}"
                                class="d-inline" onsubmit="return confirm('Hapus soal ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $soalTeori->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ===== MODAL TAMBAH / EDIT SOAL ===== --}}
<div class="modal fade" id="modalTambahSoal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formSoalTeori" action="{{ route('manajer-sertifikasi.soal-teori.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="soal_id" id="soalId">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalSoalTitle">
                        <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Soal Teori
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">Skema</label>
                        <select name="skema_id" class="form-select" required id="selectSkema">
                            <option value="">— Pilih Skema —</option>
                            @foreach($skemas as $sk)
                            <option value="{{ $sk->id }}" {{ request('skema_id') == $sk->id ? 'selected' : '' }}>
                                {{ $sk->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">Pertanyaan</label>
                        <textarea name="pertanyaan" class="form-control" rows="3" placeholder="Tuliskan pertanyaan..."
                            required id="inputPertanyaan"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size:.875rem">Pilihan {{ $label }}</label>
                            <input type="text" name="pilihan_{{ $key }}" class="form-control"
                                placeholder="Jawaban {{ $label }}" required id="inputPilihan{{ strtoupper($key) }}">
                        </div>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.875rem">Jawaban Benar</label>
                        <select name="jawaban_benar" class="form-select" required id="selectJawaban">
                            <option value="">— Pilih Jawaban Benar —</option>
                            <option value="a">A</option>
                            <option value="b">B</option>
                            <option value="c">C</option>
                            <option value="d">D</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitSoal">
                        <i class="bi bi-save me-1"></i> Simpan Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editSoal(soal) {
    document.getElementById('modalSoalTitle').innerHTML =
        '<i class="bi bi-pencil-square text-warning me-2"></i>Edit Soal Teori';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('soalId').value = soal.id;
    document.getElementById('formSoalTeori').action =
        '{{ url("manajer-sertifikasi/soal-teori") }}/' + soal.id;

    document.getElementById('selectSkema').value = soal.skema_id;
    document.getElementById('inputPertanyaan').value = soal.pertanyaan;
    document.getElementById('inputPilihanA').value = soal.pilihan_a;
    document.getElementById('inputPilihanB').value = soal.pilihan_b;
    document.getElementById('inputPilihanC').value = soal.pilihan_c;
    document.getElementById('inputPilihanD').value = soal.pilihan_d;
    document.getElementById('selectJawaban').value = soal.jawaban_benar;

    new bootstrap.Modal(document.getElementById('modalTambahSoal')).show();
}

// Reset modal saat ditutup
document.getElementById('modalTambahSoal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formSoalTeori').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('soalId').value = '';
    document.getElementById('formSoalTeori').action = '{{ route("manajer-sertifikasi.soal-teori.store") }}';
    document.getElementById('modalSoalTitle').innerHTML =
        '<i class="bi bi-plus-circle text-primary me-2"></i>Tambah Soal Teori';
});
</script>
@endpush