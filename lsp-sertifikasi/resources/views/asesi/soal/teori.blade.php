{{-- resources/views/asesi/soal/teori.blade.php --}}
@extends('layouts.ujian')

@section('title', 'Ujian Soal Teori')
@section('ujian-label', 'Soal Teori — ' . ($asesmen->skema->name ?? ''))
@section('ujian-asesi-name', $asesmen->full_name)

@section('ujian-content')

{{-- Nav panel kiri --}}
<div id="nav-panel">
    <div class="nav-title">Navigasi Soal</div>
    <div class="nav-grid" id="soalNav">
        @foreach($soalAsesi as $i => $soalRow)
        <button type="button"
                class="nav-bubble {{ $soalRow->jawaban ? 'answered' : '' }} {{ $i === 0 ? 'current' : '' }}"
                id="navBtn{{ $i }}"
                onclick="jumpToSoal({{ $i }})">
            {{ $i + 1 }}
        </button>
        @endforeach
    </div>

    <div class="nav-legend">
        <div class="leg-item">
            <div class="leg-dot" style="background:#dcfce7;border:2px solid #86efac"></div>
            Sudah dijawab
        </div>
        <div class="leg-item">
            <div class="leg-dot" style="background:white;border:2px solid #e2e8f0"></div>
            Belum dijawab
        </div>
        <div class="leg-item">
            <div class="leg-dot" style="background:#1e3a5f;border:2px solid #1e3a5f"></div>
            Sedang dibuka
        </div>
    </div>

    {{-- Summary --}}
    <div class="mt-4 p-3 rounded-3" style="background:#f8fafc;font-size:.78rem">
        <div class="d-flex justify-content-between mb-1">
            <span class="text-muted">Terjawab</span>
            <span class="fw-bold text-success" id="summaryAnswered">{{ $soalAsesi->whereNotNull('jawaban')->count() }}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span class="text-muted">Belum</span>
            <span class="fw-bold text-warning" id="summaryUnanswered">
                {{ $soalAsesi->count() - $soalAsesi->whereNotNull('jawaban')->count() }}
            </span>
        </div>
    </div>

    {{-- Submit button --}}
    <button type="button" class="btn w-100 mt-3 fw-bold text-white"
            style="background:#1e3a5f;border:none;border-radius:10px;font-size:.85rem;padding:10px"
            onclick="submitUjian()">
        <i class="bi bi-send me-1"></i> Submit Ujian
    </button>
</div>

{{-- Area soal --}}
<div id="soal-area">
    @foreach($soalAsesi as $i => $soalRow)
    @php $s = $soalRow->soalTeori; @endphp
    <div class="soal-card {{ $i > 0 ? 'd-none' : '' }}"
         id="soalCard{{ $i }}"
         data-index="{{ $i }}"
         data-soal-id="{{ $soalRow->id }}">

        {{-- Nomor --}}
        <div class="soal-number">
            <div class="num-badge">{{ $i + 1 }}</div>
            <div class="num-label">Soal {{ $i + 1 }} dari {{ $soalAsesi->count() }}</div>
        </div>

        {{-- Pertanyaan --}}
        <p class="soal-text">{{ $s->pertanyaan }}</p>

        {{-- Pilihan --}}
        <div class="pilihan-wrapper">
            @foreach(['a','b','c','d','e'] as $opt)
            @if($s->{"pilihan_{$opt}"})
            <div class="pilihan-item {{ $soalRow->jawaban === $opt ? 'selected' : '' }}"
                 data-opt="{{ $opt }}"
                 onclick="pilihJawaban({{ $i }}, {{ $soalRow->id }}, '{{ $opt }}', this)">
                <div class="opt-circle">{{ strtoupper($opt) }}</div>
                <div class="opt-text">{{ $s->{"pilihan_{$opt}"} }}</div>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Nav bar bawah --}}
        <div class="soal-nav-bar">
            @if($i > 0)
            <button type="button" class="btn btn-outline-secondary btn-sm"
                    onclick="jumpToSoal({{ $i - 1 }})">
                <i class="bi bi-arrow-left me-1"></i> Sebelumnya
            </button>
            @else
            <div></div>
            @endif

            @if($i < $soalAsesi->count() - 1)
            <button type="button" class="btn btn-primary btn-sm"
                    onclick="jumpToSoal({{ $i + 1 }})">
                Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
            </button>
            @else
            <button type="button" class="btn btn-success btn-sm fw-bold"
                    onclick="submitUjian()">
                <i class="bi bi-check-circle me-1"></i> Selesai & Submit
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- JS vars --}}
<script>
const DURASI_DETIK   = {{ $durasi * 60 }};
const STARTED_AT = @json($startedAt ? (is_string($startedAt) ? $startedAt : $startedAt->toISOString()) : null);const SAVE_URL       = "{{ route('asesi.soal.teori.save') }}";
const SUBMIT_URL     = "{{ route('asesi.soal.teori.submit') }}";
const REDIRECT_AFTER = "{{ route('asesi.schedule') }}";
const CSRF           = "{{ csrf_token() }}";

let currentIndex = 0;
let totalSoal    = {{ $soalAsesi->count() }};
let answered     = {{ $soalAsesi->whereNotNull('jawaban')->count() }};
</script>
@endsection

@push('scripts')
<script>
// ─── Navigasi soal ────────────────────────────────────────────────────────────
function jumpToSoal(idx) {
    // Sembunyikan semua card
    document.querySelectorAll('.soal-card').forEach(c => c.classList.add('d-none'));
    document.getElementById('soalCard' + idx).classList.remove('d-none');

    // Update nav bubble
    document.querySelectorAll('.nav-bubble').forEach((btn, i) => {
        btn.classList.remove('current');
        if (i === idx) btn.classList.add('current');
    });

    // Scroll soal ke atas
    document.getElementById('soal-area').scrollTo({ top: 0, behavior: 'smooth' });

    currentIndex = idx;
}

// ─── Pilih jawaban ────────────────────────────────────────────────────────────
async function pilihJawaban(index, soalId, opt, el) {
    const card = document.getElementById('soalCard' + index);

    // Reset semua pilihan di card ini
    card.querySelectorAll('.pilihan-item').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');

    // Saving indicator
    const ind = document.getElementById('saving-indicator');
    ind.style.display = 'flex';

    try {
        const res = await fetch(SAVE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ soal_id: soalId, jawaban: opt }),
        });
        const data = await res.json();
        if (data.success) {
            answered = data.answered;
            updateProgress(data.answered, data.total);

            // Nav bubble → hijau
            const navBtn = document.getElementById('navBtn' + index);
            navBtn.classList.add('answered');
        }
    } catch(e) {
        console.error('Save error', e);
    } finally {
        setTimeout(() => { ind.style.display = 'none'; }, 800);
    }
}

// ─── Update progress ──────────────────────────────────────────────────────────
function updateProgress(ans, total) {
    const pct = total > 0 ? Math.round(ans / total * 100) : 0;

    // Topbar
    document.getElementById('topbarProgressFill').style.width  = pct + '%';
    document.getElementById('topbarProgressText').textContent  = ans + ' / ' + total;

    // Panel kiri
    document.getElementById('summaryAnswered').textContent   = ans;
    document.getElementById('summaryUnanswered').textContent = total - ans;
}

// ─── Timer ────────────────────────────────────────────────────────────────────
function startTimer() {
    const startedAt = STARTED_AT ? new Date(STARTED_AT) : new Date();

    const interval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startedAt.getTime()) / 1000);
        const sisa    = Math.max(0, DURASI_DETIK - elapsed);

        const h = String(Math.floor(sisa / 3600)).padStart(2, '0');
        const m = String(Math.floor((sisa % 3600) / 60)).padStart(2, '0');
        const s = String(sisa % 60).padStart(2, '0');

        document.getElementById('timerDisplay').textContent = `${h}:${m}:${s}`;

        // Merah + pulse jika < 5 menit
        if (sisa < 300) {
            document.getElementById('timer-box').classList.add('danger');
        }

        if (sisa <= 0) {
            clearInterval(interval);
            submitUjian(true);
        }
    }, 1000);
}

// ─── Submit ───────────────────────────────────────────────────────────────────
async function submitUjian(forced = false) {
    if (!forced) {
        const unanswered = totalSoal - answered;
        const result = await Swal.fire({
            title: 'Submit Ujian?',
            html: unanswered > 0
                ? `<span class="text-warning fw-bold">${unanswered} soal</span> belum dijawab.<br>Jawaban yang belum diisi dihitung kosong.`
                : `Semua ${totalSoal} soal sudah dijawab.<br>Yakin ingin mengakhiri ujian?`,
            icon: unanswered > 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-send me-1"></i> Ya, Submit',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#1e3a5f',
            reverseButtons: true,
        });
        if (!result.isConfirmed) return;
    }

    // Disable semua interaksi
    document.querySelectorAll('.pilihan-item, .nav-bubble, button').forEach(el => {
        el.style.pointerEvents = 'none';
        el.style.opacity = '.5';
    });

    try {
        const res = await fetch(SUBMIT_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({}),
        });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                title: '✅ Ujian Disubmit!',
                html: `<strong>${data.answered}</strong> dari <strong>${data.total}</strong> soal berhasil dijawab.<br><small class="text-muted">Hasil akan diumumkan oleh Asesor.</small>`,
                icon: 'success',
                confirmButtonText: 'Lihat Ringkasan',
                confirmButtonColor: '#1e3a5f',
                allowOutsideClick: false,
            });
            window.location.href = REDIRECT_AFTER;
        } else {
            Swal.fire('Gagal', data.message ?? 'Terjadi kesalahan.', 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Koneksi bermasalah. Coba lagi.', 'error');
    }
}

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    startTimer();
    updateProgress(answered, totalSoal);

    // Soal pertama aktif
    document.getElementById('navBtn0')?.classList.add('current');

    // Warning tab visibility (jangan kabur dari halaman ujian)
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            // Opsional: bisa log ke server kalau mau
            console.warn('Tab tidak aktif!');
        }
    });
});
</script>
@endpush