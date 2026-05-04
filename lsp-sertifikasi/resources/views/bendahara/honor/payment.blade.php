@extends('layouts.app')

@section('title', 'Detail Kwitansi Honor')
@section('page-title', 'Detail Kwitansi Honor')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button"
        class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- Kiri: Detail kwitansi --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                    <span class="fw-semibold font-monospace" id="nomorKwitansiDisplay">{{ $honor->nomor_kwitansi }}</span>
                    <button class="btn btn-outline-secondary ms-2"
                            style="font-size:.7rem;padding:2px 6px;"
                            onclick="toggleEditNomor()"
                            id="btnEditNomor"
                            title="Edit nomor kwitansi">
                        <i class="bi bi-pencil"></i>
                    </button>

                    {{-- Form inline edit --}}
                    <div id="formEditNomor" class="d-none d-flex align-items-center gap-1 ms-1">
                        <input type="text"
                            id="inputNomorKwitansi"
                            class="form-control form-control-sm font-monospace"
                            style="max-width:240px;font-size:.85rem;"
                            value="{{ $honor->nomor_kwitansi }}"
                            placeholder="001/LSP-KAP/KEU.KK/IV/2026">
                        <button class="btn btn-sm btn-success" onclick="simpanNomor()" title="Simpan">
                            <i class="bi bi-check-lg"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="toggleEditNomor()" title="Batal">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>                
                    <span class="badge bg-{{ $honor->status_badge }} ms-auto">{{ $honor->status_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <div class="text-muted small">Asesor</div>
                        <div class="fw-semibold">{{ $honor->asesor->nama }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Tanggal Kwitansi</div>
                        <div>{{ optional($honor->tanggal_kwitansi)->translatedFormat('d F Y') }}</div>
                    </div>
                </div>

                {{-- Detail per jadwal --}}
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Skema</th>
                                <th>TUK</th>
                                <th>Tanggal</th>
                                <th>Batch</th>
                                <th class="text-center">Asesi</th>
                                <th class="text-end">@Rp</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($honor->details as $i => $detail)
                            @php
                                $asesmens   = $detail->schedule->asesmens ?? collect();
                                $batches    = $asesmens->where('is_collective', true)
                                                    ->pluck('collective_batch_id')
                                                    ->filter()->unique()->values();
                                $adaMandiri = $asesmens->where('is_collective', false)->isNotEmpty();
                            @endphp
                            <tr>
                                <td class="text-muted small">{{ $i+1 }}</td>
                                <td class="small fw-semibold">{{ $detail->schedule->skema->name }}</td>
                                <td class="small">{{ $detail->schedule->tuk->name ?? '-' }}</td>
                                <td class="small">
                                    {{ optional($detail->schedule->assessment_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="small">
                                    @forelse($batches as $batch)
                                        @php
                                            $institutions = $asesmens
                                                ->where('is_collective', true)
                                                ->where('collective_batch_id', $batch)
                                                ->pluck('institution')
                                                ->filter()->unique()->values();
                                            $batchCount = $asesmens
                                                ->where('is_collective', true)
                                                ->where('collective_batch_id', $batch)
                                                ->count();
                                        @endphp
                                        <div class="mb-1">
                                            <span class="badge bg-primary bg-opacity-75 font-monospace"
                                                style="font-size:.62rem;letter-spacing:.3px;">
                                                <i class="bi bi-people-fill me-1"></i>{{ $batch }}
                                            </span>
                                            <span class="text-muted" style="font-size:.72rem;">
                                                {{ $batchCount }} asesi
                                            </span>
                                            @if($institutions->isNotEmpty())
                                            <div style="font-size:.7rem;color:#555;margin-left:2px;margin-top:1px;">
                                                <i class="bi bi-building me-1" style="font-size:.65rem;"></i>{{ $institutions->implode(', ') }}
                                            </div>
                                            @endif
                                        </div>
                                        @if(!$loop->last)<hr class="my-1">@endif
                                    @empty
                                    @endforelse
                                    @if($adaMandiri)
                                        @php
                                            $mandiriCount = $asesmens->where('is_collective', false)->count();
                                            $mandiriInstitutions = $asesmens
                                                ->where('is_collective', false)
                                                ->pluck('institution')
                                                ->filter()->unique()->values();
                                        @endphp
                                        <div>
                                            <span class="badge bg-success bg-opacity-75" style="font-size:.62rem;">
                                                <i class="bi bi-person me-1"></i>Mandiri · {{ $mandiriCount }} asesi
                                            </span>
                                            @if($mandiriInstitutions->isNotEmpty())
                                            <div style="font-size:.7rem;color:#555;margin-left:2px;margin-top:1px;">
                                                <i class="bi bi-building me-1" style="font-size:.65rem;"></i>{{ $mandiriInstitutions->implode(', ') }}
                                            </div>
                                            @endif
                                        </div>
                                    @endif
                                    @if($batches->isEmpty() && !$adaMandiri)
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center small">{{ $detail->jumlah_asesi }}</td>
                                <td class="text-end small">{{ number_format($detail->honor_per_asesi, 0, ',', '.') }}</td>
                                <td class="text-end small fw-semibold">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="7" class="fw-bold text-end">Total Honor</td>
                                <td class="text-end fw-bold">Rp {{ number_format($honor->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Aksi: Preview/Download kwitansi --}}
                <div class="d-flex gap-2 mt-2 flex-wrap">
                    @if($honor->isDikonfirmasi())
                        {{-- Final: kwitansi dengan TTD --}}
                        <a href="{{ route('bendahara.honor.payment.kwitansi', ['honor' => $honor, 'preview' => 1]) }}"
                            target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>Preview Kwitansi
                        </a>
                        <a href="{{ route('bendahara.honor.payment.kwitansi', $honor) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-download me-1"></i>Download Kwitansi PDF
                        </a>
                    @else
                        {{-- Draft: kwitansi tanpa TTD, ada watermark DRAFT --}}
                        <a href="{{ route('bendahara.honor.payment.kwitansi', ['honor' => $honor, 'preview' => 1]) }}"
                            target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Preview Draft Kwitansi
                        </a>
                        <a href="{{ route('bendahara.honor.payment.kwitansi', $honor) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i>Download Draft
                        </a>
                        <span class="text-muted small fst-italic align-self-center">
                            <i class="bi bi-info-circle me-1"></i>
                            Kwitansi final tersedia setelah asesor konfirmasi.
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Rekening Bank Asesor --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-credit-card me-1 text-success"></i>
                <span class="fw-semibold">Rekening Bank Asesor</span>
                <a href="{{ route('bendahara.rekening.show', $honor->asesor) }}"
                    class="btn ms-auto" style="font-size:.75rem;padding:2px 8px;border:1px solid #ccc;">
                    <i class="bi bi-pencil me-1"></i>Kelola
                </a>
            </div>
            <div class="card-body">
                @php $rekenings = $honor->asesor->rekenings; @endphp
                @if($rekenings->isEmpty())
                    <div class="alert alert-warning py-2 mb-0 small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Asesor belum memiliki rekening bank tersimpan.
                        <a href="{{ route('bendahara.rekening.show', $honor->asesor) }}" class="alert-link">Tambah sekarang</a>.
                    </div>
                @else
                    <div class="row g-2">
                        @foreach($rekenings as $rek)
                        <div class="col-sm-6">
                            <div class="border rounded px-3 py-2 h-100 {{ $rek->is_utama ? 'border-success bg-success-subtle' : 'bg-light' }}">
                                <div class="fw-semibold small">
                                    {{ $rek->nama_bank }}
                                    @if($rek->is_utama)
                                        <span class="badge bg-success ms-1" style="font-size:.65rem;">Utama</span>
                                    @endif
                                </div>
                                <div class="font-monospace" style="font-size:.85rem;">{{ $rek->nomor_rekening }}</div>
                                <div class="text-muted small">a.n. {{ $rek->nama_pemilik }}</div>
                                @if($rek->cabang)
                                    <div class="text-muted" style="font-size:.75rem;">{{ $rek->cabang }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kanan: Upload bukti transfer --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-upload me-1 text-success"></i>Bukti Transfer
            </div>
            <div class="card-body">

                @if($honor->isMenunggu())
                {{-- Form upload --}}
                <form action="{{ route('bendahara.honor.payment.bukti', $honor) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @error('bukti_transfer')
                    <div class="alert alert-danger py-2 small">{{ $message }}</div>
                    @enderror
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Upload Bukti Transfer <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="bukti_transfer" class="form-control" accept=".jpg,.jpeg,.png,.pdf"
                            required>
                        <div class="form-text">Format: JPG, PNG, atau PDF. Maks 5MB.</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-upload me-1"></i>Upload & Tandai Sudah Dibayar
                    </button>
                </form>

                @elseif($honor->isSudahDibayar())
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-clock me-1"></i>
                    Bukti transfer sudah diupload. Menunggu konfirmasi dari asesor.
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Dibayar pada</div>
                    <div>{{ optional($honor->dibayar_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>

                {{-- Preview bukti --}}
                @include('bendahara.honor._bukti-preview', ['honor' => $honor])

                @elseif($honor->isDikonfirmasi())
                <div class="alert alert-success py-2 small mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Asesor sudah konfirmasi penerimaan honor.
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Dikonfirmasi pada</div>
                    <div>{{ optional($honor->dikonfirmasi_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>

                {{-- Preview bukti --}}
                @include('bendahara.honor._bukti-preview', ['honor' => $honor])

                @endif

            </div>
        </div>

        {{-- Info status --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-arrow-right-circle me-1"></i>Status Alur
            </div>
            <div class="card-body py-2">
                @php
                $steps = [
                ['label' => 'Kwitansi Dibuat', 'done' => true],
                ['label' => 'Bukti Transfer Upload', 'done' => in_array($honor->status,
                ['sudah_dibayar','dikonfirmasi'])],
                ['label' => 'Konfirmasi Asesor', 'done' => $honor->isDikonfirmasi()],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                    <i
                        class="bi {{ $step['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                    <span class="small {{ $step['done'] ? 'fw-semibold' : 'text-muted' }}">{{ $step['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
    

<script>
function toggleEditNomor() {
    const display = document.getElementById('nomorKwitansiDisplay');
    const btnEdit = document.getElementById('btnEditNomor');
    const form    = document.getElementById('formEditNomor');
    const input   = document.getElementById('inputNomorKwitansi');

    const editing = !form.classList.contains('d-none');
    form.classList.toggle('d-none', editing);
    display.classList.toggle('d-none', !editing);
    btnEdit.classList.toggle('d-none', !editing);

    if (!editing) {
        input.value = display.textContent.trim();
        input.focus();
        input.select();
    }
}

async function simpanNomor() {
    const nomor = document.getElementById('inputNomorKwitansi').value.trim();
    if (!nomor) return;

    try {
        const res = await fetch('{{ route("bendahara.honor.payment.nomor.update", $honor) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ nomor_kwitansi: nomor }),
        });

        const data = await res.json();

        if (res.ok && data.success) {
            document.getElementById('nomorKwitansiDisplay').textContent = data.nomor;
            toggleEditNomor();
            Swal.fire({
                icon: 'success', title: 'Tersimpan',
                text: data.message, timer: 1800, showConfirmButton: false,
            });
        } else {
            const errMsg = data.errors?.nomor_kwitansi?.[0]
                        ?? data.message
                        ?? 'Terjadi kesalahan.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: errMsg });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
    }
}

// Simpan dengan Enter, batal dengan Escape
document.getElementById('inputNomorKwitansi')?.addEventListener('keydown', e => {
    if (e.key === 'Enter')  simpanNomor();
    if (e.key === 'Escape') toggleEditNomor();
});
</script>
@endpush