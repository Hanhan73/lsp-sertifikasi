@extends('layouts.app')
@section('title', 'Review SK Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom › Review')

@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <a href="{{ route('direktur.sk-ujikom.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Review SK Hasil Ujikom</h4>
            <p class="text-muted mb-0" style="font-size:.875rem;">
                Batch: <code>{{ $skUjikom->collective_batch_id }}</code> &nbsp;·&nbsp;
                {{ $first?->skema?->name ?? '-' }}
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Preview selalu tersedia --}}
            <a href="{{ route('direktur.sk-ujikom.preview', $skUjikom) }}" target="_blank"
                class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i>Preview SK (Draft)
            </a>
            @if($skUjikom->isApproved() && $skUjikom->hasSk())
            <form action="{{ route('direktur.sk-ujikom.regenerate', $skUjikom) }}" method="POST"
                onsubmit="return confirm('Re-generate ulang PDF SK? File lama akan ditimpa.')">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-1"></i>Re-generate
                </button>
            </form>
            <a href="{{ route('direktur.sk-ujikom.download', $skUjikom) }}" class="btn btn-success">
                <i class="bi bi-download me-2"></i>Unduh SK PDF
            </a>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
</div>
@endif

{{-- Status Banner --}}
@if($skUjikom->isApproved())
<div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div>
        <div class="fw-semibold">Sudah Disetujui</div>
        <div class="small">{{ $skUjikom->approved_at?->translatedFormat('d F Y H:i') }} &nbsp;·&nbsp; Dokumen SK telah
            digenerate</div>
    </div>
</div>
@elseif($skUjikom->isRejected())
<div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
    <i class="bi bi-x-circle-fill fs-4"></i>
    <div>
        <div class="fw-semibold">Ditolak</div>
        <div class="small">{{ $skUjikom->catatan_direktur }}</div>
    </div>
</div>
@endif

<div class="row g-4">

    {{-- ── Kiri: Detail SK & Aksi ── --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Info SK
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0" style="font-size:.875rem;">
                    <tr>
                        <td class="text-muted" style="width:130px;">Nomor SK</td>
                        <td class="fw-semibold font-monospace" style="font-size:.8rem;">{{ $skUjikom->nomor_sk }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Pleno</td>
                        <td>{{ $skUjikom->tanggal_pleno?->translatedFormat('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dikeluarkan di</td>
                        <td>{{ $skUjikom->tempat_dikeluarkan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Diajukan oleh</td>
                        <td>{{ $skUjikom->createdBy?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Ajuan</td>
                        <td class="small">{{ $skUjikom->submitted_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Jadwal --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calendar3 me-2 text-secondary"></i>Jadwal Terkait
            </div>
            <div class="card-body p-0">
                @foreach($schedules as $s)
                @php $ba = $s->beritaAcara; @endphp
                <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between"
                    style="font-size:.83rem;">
                    <div>
                        <div class="fw-semibold">{{ $s->assessment_date->translatedFormat('d M Y') }}</div>
                        <div class="text-muted">{{ $s->tuk?->name ?? '-' }} &nbsp;·&nbsp; {{ $s->asesor?->nama ?? '-' }}
                        </div>
                    </div>
                    @if($ba)
                    <div class="d-flex gap-1">
                        <a href="{{ route('direktur.jadwal.berita-acara.pdf', $s) }}?preview=1" target="_blank"
                            class="btn btn-outline-danger btn-sm py-0 px-2" title="Lihat PDF BA">
                            <i class="bi bi-file-pdf" style="font-size:.8rem;"></i>
                        </a>
                        @if($ba->file_path)
                        <a href="{{ route('direktur.jadwal.berita-acara.download-file', $s) }}"
                            class="btn btn-outline-secondary btn-sm py-0 px-2" title="Download Excel BA">
                            <i class="bi bi-file-earmark-spreadsheet" style="font-size:.8rem;"></i>
                        </a>
                        @endif
                    </div>
                    @else
                    <span class="badge bg-secondary" style="font-size:.7rem;">BA -</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tombol Aksi --}}
        @if($skUjikom->isSubmitted())
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-shield-check me-2 text-success"></i>Keputusan
            </div>
            <div class="card-body d-grid gap-2">
                {{-- Approve --}}
                <button class="btn btn-success" onclick="confirmApprove()">
                    <i class="bi bi-check-lg me-2"></i>Setujui & Generate SK
                </button>

                {{-- Reject --}}
                <button class="btn btn-outline-danger" onclick="showRejectForm()">
                    <i class="bi bi-x-lg me-2"></i>Tolak
                </button>

                {{-- Form Approve (hidden) --}}
                <form id="formApprove" action="{{ route('direktur.sk-ujikom.approve', $skUjikom) }}" method="POST"
                    class="d-none">
                    @csrf
                    <input type="hidden" name="catatan" id="catatanApprove">
                </form>

                {{-- Form Reject (hidden, shown via JS) --}}
                <div id="rejectFormWrap" class="d-none mt-2">
                    <form action="{{ route('direktur.sk-ujikom.reject', $skUjikom) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Alasan Penolakan <span
                                    class="text-danger">*</span></label>
                            <textarea name="catatan_direktur"
                                class="form-control form-control-sm @error('catatan_direktur') is-invalid @enderror"
                                rows="3"
                                placeholder="Jelaskan alasan penolakan...">{{ old('catatan_direktur') }}</textarea>
                            @error('catatan_direktur')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i class="bi bi-x-circle me-1"></i>Kirim Penolakan
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Kanan: Daftar Peserta Kompeten ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <span class="fw-semibold">
                    <i class="bi bi-people me-2 text-success"></i>Peserta yang akan Mendapat SK
                </span>
                <span class="badge bg-success px-3">{{ $pesertaKompeten->count() }} Kompeten</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 align-middle" style="font-size:.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px;">No</th>
                            <th>Nama Lengkap</th>
                            <th>Instansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pesertaKompeten as $i => $asesi)
                        <tr>
                            <td class="ps-3 text-muted">{{ $i + 1 }}.</td>
                            <td class="fw-semibold">{{ $asesi->full_name }}</td>
                            <td class="text-muted">{{ $asesi->institution ?? $first?->tuk?->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function confirmApprove() {
    Swal.fire({
        title: 'Setujui Pengajuan SK?',
        html: `Dokumen SK PDF akan otomatis digenerate setelah Anda menyetujui.<br>
               <strong>{{ $pesertaKompeten->count() }} peserta kompeten</strong> akan tercantum dalam SK.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Ya, Setujui',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#198754',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('formApprove').submit();
        }
    });
}

function showRejectForm() {
    const wrap = document.getElementById('rejectFormWrap');
    wrap.classList.toggle('d-none');
}
</script>
@endpush

@endsection