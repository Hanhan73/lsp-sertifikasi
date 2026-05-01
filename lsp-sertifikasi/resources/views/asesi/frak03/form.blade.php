{{-- resources/views/asesi/frak03/form.blade.php --}}
@extends('layouts.asesi')

@section('title', 'FR.AK.03 - Umpan Balik Asesmen')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            {{-- Header --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <h5 class="fw-bold mb-1">FR.AK.03</h5>
                    <h6 class="text-muted">UMPAN BALIK DAN CATATAN ASESMEN</h6>
                </div>
            </div>

            {{-- Info Header --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted fw-semibold" style="width:140px">Skema Sertifikasi</td>
                                    <td>: {{ $asesmen->skema->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Nomor Skema</td>
                                    <td>: {{ $asesmen->skema->nomor_skema ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">TUK</td>
                                    <td>: {{ $asesmen->schedule->tuk->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted fw-semibold" style="width:140px">Nama Asesor</td>
                                    <td>: {{ $asesmen->schedule->asesor->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Nama Asesi</td>
                                    <td>: {{ $asesmen->full_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-semibold">Tanggal Asesmen</td>
                                    <td>: {{ $asesmen->schedule->assessment_date?->translatedFormat('d F Y') ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($frAk03 && $frAk03->isSubmitted())
                {{-- Sudah submit — tampilkan hasil --}}
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <div>
                        Umpan balik sudah dikirim pada
                        <strong>{{ $frAk03->submitted_at->translatedFormat('d F Y, H:i') }}</strong>.
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-semibold">Jawaban Anda</div>
                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px" class="text-center">No</th>
                                    <th>Komponen</th>
                                    <th style="width:60px" class="text-center">Ya</th>
                                    <th style="width:60px" class="text-center">Tidak</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $pertanyaan = \App\Http\Controllers\ManajerSertifikasi\FrAk03ManajerController::pertanyaanList();
                                @endphp
                                @foreach($pertanyaan as $i => $p)
                                    @php $item = $frAk03->getJawabanItem($i); @endphp
                                    <tr>
                                        <td class="text-center align-middle">{{ $i + 1 }}</td>
                                        <td class="align-middle small">{{ $p }}</td>
                                        <td class="text-center align-middle">
                                            @if(($item['jawaban'] ?? '') === 'ya')
                                                <i class="bi bi-check-lg text-success fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if(($item['jawaban'] ?? '') === 'tidak')
                                                <i class="bi bi-check-lg text-danger fs-5"></i>
                                            @endif
                                        </td>
                                        <td class="align-middle small text-muted">{{ $item['catatan'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($frAk03->catatan_lain)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light fw-semibold">Catatan/Komentar Lainnya</div>
                        <div class="card-body">
                            <p class="mb-0">{{ $frAk03->catatan_lain }}</p>
                        </div>
                    </div>
                @endif

            @else
                {{-- Belum submit — tampilkan form --}}
                <form action="{{ route('asesi.frak03.submit') }}" method="POST" id="formFrAk03">
                    @csrf

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <span class="fw-semibold">Umpan balik dari Asesi</span>
                            <span class="text-muted small ms-1">(diisi setelah pengambilan keputusan)</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40px" class="text-center">No</th>
                                        <th>Komponen</th>
                                        <th style="width:70px" class="text-center">Ya</th>
                                        <th style="width:70px" class="text-center">Tidak</th>
                                        <th style="width:200px">Catatan/Komentar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $pertanyaan = \App\Http\Controllers\ManajerSertifikasi\FrAk03ManajerController::pertanyaanList();
                                    @endphp
                                    @foreach($pertanyaan as $i => $p)
                                        @php
                                            $old = old("jawaban.$i");
                                            $savedJawaban = $frAk03?->getJawabanItem($i)['jawaban'] ?? null;
                                            $savedCatatan = $frAk03?->getJawabanItem($i)['catatan'] ?? null;
                                        @endphp
                                        <tr>
                                            <td class="text-center align-middle">{{ $i + 1 }}</td>
                                            <td class="align-middle small">{{ $p }}</td>
                                            <td class="text-center align-middle">
                                                <input
                                                    type="radio"
                                                    name="jawaban[{{ $i }}][jawaban]"
                                                    value="ya"
                                                    class="form-check-input jawaban-radio"
                                                    {{ ($old['jawaban'] ?? $savedJawaban) === 'ya' ? 'checked' : '' }}
                                                    required
                                                >
                                            </td>
                                            <td class="text-center align-middle">
                                                <input
                                                    type="radio"
                                                    name="jawaban[{{ $i }}][jawaban]"
                                                    value="tidak"
                                                    class="form-check-input jawaban-radio"
                                                    {{ ($old['jawaban'] ?? $savedJawaban) === 'tidak' ? 'checked' : '' }}
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="jawaban[{{ $i }}][catatan]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Opsional"
                                                    value="{{ old("jawaban.$i.catatan", $savedCatatan) }}"
                                                    maxlength="500"
                                                >
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Catatan lain --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light fw-semibold">Catatan/Komentar Lainnya <span class="text-muted fw-normal small">(apabila ada)</span></div>
                        <div class="card-body">
                            <textarea
                                name="catatan_lain"
                                class="form-control"
                                rows="4"
                                maxlength="1000"
                                placeholder="Tuliskan catatan atau komentar tambahan Anda di sini..."
                            >{{ old('catatan_lain', $frAk03?->catatan_lain) }}</textarea>
                        </div>
                    </div>

                    {{-- Tombol submit --}}
                    <div class="d-flex justify-content-end mb-4">
                        <button type="button" class="btn btn-primary px-4" id="btnSubmit">
                            <i class="bi bi-send me-1"></i> Kirim Umpan Balik
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form   = document.getElementById('formFrAk03');
    const btnSubmit = document.getElementById('btnSubmit');
    if (!form || !btnSubmit) return;

    btnSubmit.addEventListener('click', function () {
        // Validasi semua 10 pertanyaan sudah dijawab
        const total = 10;
        let unanswered = 0;

        for (let i = 0; i < total; i++) {
            const radios = form.querySelectorAll(`input[name="jawaban[${i}][jawaban]"]`);
            const answered = Array.from(radios).some(r => r.checked);
            if (!answered) unanswered++;
        }

        if (unanswered > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Lengkap',
                text: `Masih ada ${unanswered} pertanyaan yang belum dijawab. Semua pertanyaan wajib diisi.`,
                confirmButtonColor: '#0d6efd',
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Kirim Umpan Balik?',
            text: 'Setelah dikirim, jawaban tidak dapat diubah lagi.',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0d6efd',
        }).then(result => {
            if (result.isConfirmed) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection