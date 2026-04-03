{{-- resources/views/asesi/soal/teori-intro.blade.php --}}
@extends('layouts.app')

@section('title', $sudahSubmit ? 'Ujian Selesai' : 'Persiapan Ujian Soal Teori')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">

        @if($sudahSubmit)
        {{-- ════════════════════════════════════════
             STATE: SUDAH SUBMIT
        ════════════════════════════════════════ --}}

        {{-- Banner selesai --}}
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                 style="width:80px;height:80px;background:linear-gradient(135deg,#16a34a,#22c55e)">
                <i class="bi bi-patch-check-fill text-white" style="font-size:2.2rem"></i>
            </div>
            <h4 class="fw-bold mb-1">Ujian Telah Dikumpulkan</h4>
            <p class="text-muted mb-0" style="font-size:.875rem">{{ $asesmen->skema->name ?? '-' }}</p>
        </div>

        {{-- Stats hasil --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-0">
                <div class="row g-0 text-center">
                    <div class="col-4 py-4 border-end">
                        <div class="fw-bold" style="font-size:1.8rem;color:#16a34a">{{ $jumlahDijawab }}</div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600">DIJAWAB</div>
                    </div>
                    <div class="col-4 py-4 border-end">
                        <div class="fw-bold" style="font-size:1.8rem;color:{{ $jumlahKosong > 0 ? '#f59e0b' : '#16a34a' }}">
                            {{ $jumlahKosong }}
                        </div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600">KOSONG</div>
                    </div>
                    <div class="col-4 py-4">
                        <div class="fw-bold" style="font-size:1.8rem;color:#1e3a5f">{{ $jumlahSoal }}</div>
                        <div class="text-muted" style="font-size:.72rem;font-weight:600">TOTAL</div>
                    </div>
                </div>

                {{-- Progress bar --}}
                @php $pct = $jumlahSoal > 0 ? round($jumlahDijawab / $jumlahSoal * 100) : 0; @endphp
                <div class="px-4 pb-4 pt-2">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;color:#64748b">
                        <span>Kelengkapan jawaban</span>
                        <span class="fw-bold">{{ $pct }}%</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:99px">
                        <div class="progress-bar {{ $pct === 100 ? 'bg-success' : 'bg-warning' }}"
                             style="width:{{ $pct }}%;border-radius:99px"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info waktu --}}
        <div class="card border-0 shadow-sm mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0!important">
            <div class="card-body py-3">
                <div class="row g-0" style="font-size:.82rem">
                    <div class="col-6 d-flex align-items-center gap-2 pe-3">
                        <i class="bi bi-play-circle text-success flex-shrink-0"></i>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;font-weight:600">DIMULAI</div>
                            <div class="fw-semibold">
                                {{ $startedAt ? $startedAt->translatedFormat('H:i, d M Y') : '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6 d-flex align-items-center gap-2 ps-3 border-start">
                        <i class="bi bi-stop-circle text-primary flex-shrink-0"></i>
                        <div>
                            <div class="text-muted" style="font-size:.7rem;font-weight:600">DIKUMPULKAN</div>
                            <div class="fw-semibold">
                                {{ $submittedAt ? $submittedAt->translatedFormat('H:i, d M Y') : '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                @if($startedAt && $submittedAt)
                @php $durasiPengerjaan = $startedAt->diffInMinutes($submittedAt); @endphp
                <hr class="my-2" style="border-color:#bbf7d0">
                <div class="text-center" style="font-size:.8rem;color:#15803d">
                    <i class="bi bi-clock-history me-1"></i>
                    Durasi pengerjaan: <strong>{{ $durasiPengerjaan }} menit</strong>
                    dari alokasi {{ $durasi }} menit
                </div>
                @endif
            </div>
        </div>

        {{-- Distribusi jawaban per opsi --}}
        @if($distribusiJawaban->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0" style="background:#f8fafc">
                <h6 class="fw-bold mb-0" style="font-size:.875rem">
                    <i class="bi bi-bar-chart text-primary me-2"></i>Distribusi Jawaban
                </h6>
            </div>
            <div class="card-body pt-2 pb-3">
                <div class="d-flex gap-3 flex-wrap">
                    @foreach(['a','b','c','d','e'] as $opt)
                    @php $count = $distribusiJawaban->get($opt, 0); @endphp
                    @if($count > 0)
                    <div class="text-center">
                        <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold mb-1"
                             style="width:40px;height:40px;background:#eff6ff;color:#2563eb;font-size:.9rem">
                            {{ strtoupper($opt) }}
                        </div>
                        <div class="fw-bold" style="font-size:.85rem">{{ $count }}</div>
                        <div style="font-size:.68rem;color:#94a3b8">soal</div>
                    </div>
                    @endif
                    @endforeach
                    @if($jumlahKosong > 0)
                    <div class="text-center">
                        <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold mb-1"
                             style="width:40px;height:40px;background:#fff7ed;color:#f59e0b;font-size:.8rem">
                            —
                        </div>
                        <div class="fw-bold" style="font-size:.85rem">{{ $jumlahKosong }}</div>
                        <div style="font-size:.68rem;color:#94a3b8">kosong</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Pesan --}}
        <div class="alert d-flex gap-2 py-3 px-3 mb-0"
             style="background:#f0fdf4;border:1px solid #86efac;font-size:.85rem;border-radius:12px">
            <i class="bi bi-info-circle-fill text-success flex-shrink-0 mt-1"></i>
            <div>
                Jawaban kamu sudah tersimpan dan <strong>tidak dapat diubah</strong>.
                Hasil ujian akan diumumkan oleh Asesor setelah proses penilaian selesai.
            </div>
        </div>

        @else
        {{-- ════════════════════════════════════════
             STATE: BELUM MULAI / SIAP MULAI
        ════════════════════════════════════════ --}}

        {{-- Judul --}}
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                 style="width:72px;height:72px;background:linear-gradient(135deg,#1e3a5f,#2563eb)">
                <i class="bi bi-journal-text text-white" style="font-size:2rem"></i>
            </div>
            <h4 class="fw-bold mb-1">Ujian Soal Teori</h4>
            <p class="text-muted mb-0" style="font-size:.875rem">{{ $asesmen->skema->name ?? '-' }}</p>
        </div>

        {{-- Info ujian --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-0">
                <div class="row g-0 text-center">
                    <div class="col-4 py-4 border-end">
                        <div class="fw-bold" style="font-size:1.6rem;color:#1e3a5f">{{ $jumlahSoal }}</div>
                        <div class="text-muted" style="font-size:.75rem;font-weight:600">SOAL</div>
                    </div>
                    <div class="col-4 py-4 border-end">
                        <div class="fw-bold" style="font-size:1.6rem;color:#1e3a5f">{{ $durasi }}</div>
                        <div class="text-muted" style="font-size:.75rem;font-weight:600">MENIT</div>
                    </div>
                    <div class="col-4 py-4">
                        <div class="fw-bold" style="font-size:1.6rem;color:#1e3a5f">A–E</div>
                        <div class="text-muted" style="font-size:.75rem;font-weight:600">PILIHAN</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Aturan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0" style="background:#f8fafc">
                <h6 class="fw-bold mb-0" style="font-size:.875rem">
                    <i class="bi bi-info-circle text-primary me-2"></i>Petunjuk Pengerjaan
                </h6>
            </div>
            <div class="card-body pt-2">
                <ul class="mb-0 ps-3" style="font-size:.85rem;line-height:2;color:#334155">
                    <li>Ujian terdiri dari <strong>{{ $jumlahSoal }} soal pilihan ganda</strong> (A–E).</li>
                    <li>Waktu pengerjaan <strong>{{ $durasi }} menit</strong> dimulai saat kamu klik "Mulai Ujian".</li>
                    <li>Jawaban tersimpan otomatis setiap kamu memilih pilihan.</li>
                    <li>Kamu bisa <strong>berpindah soal</strong> bebas menggunakan panel navigasi.</li>
                    <li>Ujian otomatis disubmit jika waktu habis.</li>
                    <li>Setelah submit, jawaban <strong>tidak bisa diubah</strong>.</li>
                    <li>Dilarang membuka tab/halaman lain selama ujian berlangsung.</li>
                </ul>
            </div>
        </div>

        {{-- Info peserta --}}
        <div class="card border-0 shadow-sm mb-4" style="background:#f0f9ff;border:1px solid #bae6fd!important">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-person-circle text-primary" style="font-size:2rem;flex-shrink:0"></i>
                    <div style="font-size:.85rem">
                        <div class="fw-bold">{{ $asesmen->full_name }}</div>
                        <div class="text-muted">NIK: {{ $asesmen->nik }}</div>
                        <div class="text-muted">Jadwal: {{ $asesmen->schedule?->assessment_date->translatedFormat('d M Y') ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol mulai --}}
        <form method="POST" action="{{ route('asesi.soal.teori.mulai') }}" id="formMulai">
            @csrf
            <button type="submit"
                    class="btn w-100 py-3 fw-bold text-white"
                    style="font-size:1rem;border-radius:12px;background:linear-gradient(135deg,#1e3a5f,#2563eb);border:none"
                    id="btnMulai">
                <i class="bi bi-play-fill me-2"></i>Mulai Ujian
            </button>
        </form>
        <p class="text-center text-muted mt-2 mb-0" style="font-size:.78rem">
            Timer akan mulai berjalan setelah kamu klik tombol di atas.
        </p>

        @endif

    </div>
</div>

@endsection

@push('scripts')
@if(!$sudahSubmit)
<script>
document.getElementById('formMulai')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;

    const result = await Swal.fire({
        title: 'Siap Mulai Ujian?',
        html: `Timer <strong>{{ $durasi }} menit</strong> akan langsung berjalan.<br>Pastikan koneksi internet stabil.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-play-fill me-1"></i> Ya, Mulai!',
        cancelButtonText: 'Belum',
        confirmButtonColor: '#1e3a5f',
        reverseButtons: true,
    });

    if (result.isConfirmed) {
        document.getElementById('btnMulai').disabled = true;
        document.getElementById('btnMulai').innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Memulai...';
        form.submit();
    }
});
</script>
@endif
@endpush