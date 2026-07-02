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
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button"
        class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- ── Kiri: Detail kwitansi ──────────────────────────────────────── --}}
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
                <div id="formEditNomor" class="d-none d-flex align-items-center gap-1 ms-1">
                    <input type="text" id="inputNomorKwitansi"
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

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Skema</th>
                                <th>TUK</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Batch</th>
                                <th class="text-center">Asesi</th>
                                <th class="text-end">@Rp</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        @foreach($honor->details as $i => $detail)
                        @php
                            $sch        = $detail->schedule;
                            $asesmens   = $sch->asesmens ?? collect();
                            $batches    = $asesmens->where('is_collective', true)
                                            ->pluck('collective_batch_id')->filter()->unique()->values();
                            $adaMandiri = $asesmens->where('is_collective', false)->isNotEmpty();
                        @endphp
                        <tr>
                            <td class="text-muted small">{{ $i+1 }}</td>
                            <td class="small fw-semibold">{{ $sch->skema->name }}</td>
                            <td class="small">{{ $sch->tuk->name ?? '-' }}</td>
                            <td class="small">
                                @if($sch->location_type === 'online')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Online</span>
                                    @if($sch->meeting_link)
                                    <br><a href="{{ $sch->meeting_link }}" target="_blank" class="small">Link Meeting</a>
                                    @endif
                                @else
                                    {{ $sch->location ?? '-' }}
                                @endif
                            </td>
                            <td class="small">{{ optional($sch->assessment_date)->translatedFormat('d M Y') }}</td>
                            <td class="small">
                                @forelse($batches as $batch)
                                    <span class="badge bg-primary bg-opacity-75 font-monospace" style="font-size:.65rem;">
                                        <i class="bi bi-people-fill me-1"></i>{{ $batch }}
                                    </span>
                                    @if(!$loop->last)<br>@endif
                                @empty
                                    @if($adaMandiri)
                                    <span class="badge bg-secondary" style="font-size:.65rem;">Mandiri</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                @endforelse
                            </td>
                            <td class="text-center small">{{ $detail->jumlah_asesi }}</td>
                            <td class="text-end small">Rp {{ number_format($detail->honor_per_asesi, 0, ',', '.') }}</td>
                            <td class="text-end small fw-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="8" class="fw-bold text-end">Total Honor</td>
                                <td class="text-end fw-bold">Rp {{ number_format($honor->total, 0, ',', '.') }}</td>
                            </tr>
                            @if($honor->has_deduction)
                            <tr class="table-warning">
                                <td colspan="8" class="text-end" style="color:#dc3545;">
                                    <i class="bi bi-dash-circle me-1"></i>Cicilan Hutang
                                    @if($honor->deductionReceivable)
                                    <small class="ms-1">({{ $honor->deductionReceivable->uraian ?? $honor->deductionReceivable->jenis_label }})</small>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold" style="color:#dc3545;">
                                    - Rp {{ number_format($honor->deduction_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="8" class="fw-bold text-end">Transfer Bersih ke Asesor</td>
                                <td class="text-end fw-bold text-success">Rp {{ number_format($honor->jumlah_transfer, 0, ',', '.') }}</td>
                            </tr>
                            @if($honor->deduction_note)
                            <tr>
                                <td colspan="9" class="text-muted small fst-italic py-1">
                                    <i class="bi bi-chat-left-text me-1"></i>{{ $honor->deduction_note }}
                                </td>
                            </tr>
                            @endif
                            @endif
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex gap-2 mt-3 flex-wrap">
                    <a href="{{ route('bendahara.honor.payment.kwitansi', ['honor' => $honor, 'preview' => 1]) }}"
                    target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye me-1"></i>Preview Draft
                    </a>
                    @if($honor->isDikonfirmasi())
                    <a href="{{ route('bendahara.honor.payment.kwitansi', $honor) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-download me-1"></i>Download Kwitansi Final
                    </a>
                    @endif

                    <div class="ms-auto d-flex gap-2">
                        @if($honor->can_reset)
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="confirmReset({{ $honor->id }})">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset & Edit Honor
                        </button>
                        @endif
                        @if(!$honor->isDikonfirmasi())
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="confirmDeleteHonor({{ $honor->id }})">
                            <i class="bi bi-trash me-1"></i>Hapus Kwitansi
                        </button>
                        @endif
                    </div>
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

    {{-- ── Kanan: Bukti Transfer ───────────────────────────────────────── --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-upload me-1 text-success"></i>Bukti Transfer
            </div>
            <div class="card-body">

                @if($honor->isMenunggu())
                {{-- Upload pertama --}}
                <form action="{{ route('bendahara.honor.payment.bukti', $honor) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @include('bendahara.honor._form-bukti', [
                        'honor'       => $honor,
                        'hutangAsesor'=> $hutangAsesor,
                        'isReplace'   => false,
                    ])
                </form>

                {{-- Shortcut tambah hutang --}}
                <div class="mt-3 pt-2 border-top">
                    @if($hutangAsesor->isEmpty())
                    <div class="text-center">
                        <p class="text-muted small mb-2">Belum ada hutang tercatat untuk asesor ini.</p>
                        <button type="button" class="btn btn-sm btn-outline-warning"
                                data-bs-toggle="modal" data-bs-target="#modalTambahHutang">
                            <i class="bi bi-plus-circle me-1"></i>Catat Hutang Asesor
                        </button>
                    </div>
                    @else
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-link text-muted p-0"
                                data-bs-toggle="modal" data-bs-target="#modalTambahHutang"
                                style="font-size:.8rem;">
                            <i class="bi bi-plus-circle me-1"></i>Tambah hutang baru
                        </button>
                    </div>
                    @endif
                </div>

                @elseif($honor->isSudahDibayar())
                {{-- Sudah upload, menunggu konfirmasi --}}
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-clock me-1"></i>
                    Bukti transfer sudah diupload. Menunggu konfirmasi dari asesor.
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Dibayar pada</div>
                    <div>{{ optional($honor->dibayar_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>

                @include('bendahara.honor._bukti-preview', ['honor' => $honor])

                {{-- Ganti bukti --}}
                <hr>
                <p class="small fw-semibold mb-2"><i class="bi bi-arrow-repeat me-1"></i>Ganti bukti transfer:</p>
                <form action="{{ route('bendahara.honor.payment.bukti', $honor) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @include('bendahara.honor._form-bukti', [
                        'honor'       => $honor,
                        'hutangAsesor'=> $hutangAsesor,
                        'isReplace'   => true,
                    ])
                </form>

                <div class="mt-2 text-end">
                    <button type="button" class="btn btn-sm btn-link text-muted p-0"
                            data-bs-toggle="modal" data-bs-target="#modalTambahHutang"
                            style="font-size:.8rem;">
                        <i class="bi bi-plus-circle me-1"></i>Tambah hutang baru
                    </button>
                </div>

                @elseif($honor->isDikonfirmasi())
                {{-- Sudah dikonfirmasi --}}
                <div class="alert alert-success py-2 small mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Asesor sudah konfirmasi penerimaan honor.
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Dikonfirmasi pada</div>
                    <div>{{ optional($honor->dikonfirmasi_at)->translatedFormat('d F Y, H:i') }}</div>
                </div>

                @include('bendahara.honor._bukti-preview', ['honor' => $honor])

                {{-- Bendahara tetap bisa ganti bukti meski sudah dikonfirmasi --}}
                <hr>
                <p class="small fw-semibold mb-1">
                    <i class="bi bi-arrow-repeat me-1"></i>Ganti bukti transfer:
                </p>
                <form action="{{ route('bendahara.honor.payment.bukti', $honor) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @include('bendahara.honor._form-bukti', [
                        'honor'        => $honor,
                        'hutangAsesor' => $hutangAsesor,
                        'isReplace'    => true,
                    ])
                </form>
                <div class="mt-2 text-end">
                    <button type="button" class="btn btn-sm btn-link text-muted p-0"
                            data-bs-toggle="modal" data-bs-target="#modalTambahHutang"
                            style="font-size:.8rem;">
                        <i class="bi bi-plus-circle me-1"></i>Tambah hutang baru
                    </button>
                </div>
                @endif

            </div>
        </div>

        {{-- Status alur --}}
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-arrow-right-circle me-1"></i>Status Alur
            </div>
            <div class="card-body py-2">
                @php
                $steps = [
                    ['label' => 'Kwitansi Dibuat',      'done' => true],
                    ['label' => 'Bukti Transfer Upload', 'done' => in_array($honor->status, ['sudah_dibayar','dikonfirmasi'])],
                    ['label' => 'Konfirmasi Asesor',     'done' => $honor->isDikonfirmasi()],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                    <i class="bi {{ $step['done'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-muted' }}"></i>
                    <span class="small {{ $step['done'] ? 'fw-semibold' : 'text-muted' }}">{{ $step['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ── Modal Tambah Hutang Asesor ──────────────────────────────────────── --}}
{{-- Modal tambah hutang tersedia di semua status --}}
@if(true)
<div class="modal fade" id="modalTambahHutang" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('bendahara.other-receivables.store') }}" method="POST"
              enctype="multipart/form-data">
            @csrf
            {{-- Context sudah diketahui dari kwitansi ini --}}
            <input type="hidden" name="asesor_id"      value="{{ $honor->asesor_id }}">
            <input type="hidden" name="nama_pihak"     value="{{ $honor->asesor->nama }}">
            <input type="hidden" name="jenis"          value="pinjaman">
            <input type="hidden" name="_redirect_back" value="{{ route('bendahara.honor.payment.show', $honor) }}">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-cash-coin text-warning me-2"></i>
                        Catat Hutang — {{ $honor->asesor->nama }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Jenis: <strong>Pinjaman/Kasbon</strong> &nbsp;·&nbsp;
                        Jurnal otomatis: Dr. Piutang / Cr. Kas-Bank.
                        Setelah disimpan, hutang ini bisa dipilih sebagai cicilan saat upload bukti transfer.
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Uraian <span class="text-danger">*</span></label>
                            <input type="text" name="uraian" class="form-control"
                                   placeholder="cth: Kasbon operasional, Pinjaman pribadi..." required>
                        </div>
<div class="col-md-4">
    <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
    <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="text" id="inputJumlahPiutang"
               class="form-control text-end font-monospace"
               placeholder="1.000.000"
               autocomplete="off">
        <input type="hidden" name="jumlah" id="hiddenJumlahPiutang">
    </div>
</div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                   value="{{ today()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jatuh Tempo</label>
                            <input type="date" name="jatuh_tempo" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Akun Piutang <span class="text-danger">*</span></label>
                            <select name="coa_id" class="form-select" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach($coaOptions as $coa)
                                <option value="{{ $coa->id }}">{{ $coa->kode }} — {{ $coa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="catatan" class="form-control" placeholder="Opsional">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bukti Dokumen</label>
                            <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">jpg, png, pdf — maks 5MB (opsional)</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save me-1"></i>Simpan & Buat Jurnal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// ── Edit nomor kwitansi ────────────────────────────────────────────────────
function toggleEditNomor() {
    const display = document.getElementById('nomorKwitansiDisplay');
    const btnEdit = document.getElementById('btnEditNomor');
    const form    = document.getElementById('formEditNomor');
    const input   = document.getElementById('inputNomorKwitansi');
    const editing = !form.classList.contains('d-none');
    form.classList.toggle('d-none', editing);
    display.classList.toggle('d-none', !editing);
    btnEdit.classList.toggle('d-none', !editing);
    if (!editing) { input.value = display.textContent.trim(); input.focus(); input.select(); }
}

async function simpanNomor() {
    const nomor = document.getElementById('inputNomorKwitansi').value.trim();
    if (!nomor) return;
    try {
        const res  = await fetch('{{ route("bendahara.honor.payment.nomor.update", $honor) }}', {
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
            Swal.fire({ icon: 'success', title: 'Tersimpan', text: data.message, timer: 1800, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.errors?.nomor_kwitansi?.[0] ?? data.message ?? 'Terjadi kesalahan.' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
    }
}

document.getElementById('inputNomorKwitansi')?.addEventListener('keydown', e => {
    if (e.key === 'Enter')  simpanNomor();
    if (e.key === 'Escape') toggleEditNomor();
});

// ── Reset kwitansi ─────────────────────────────────────────────────────────
function confirmReset(id) {
    Swal.fire({
        title: 'Reset Kwitansi?',
        html: 'Kwitansi ini akan dihapus dan Anda bisa membuat ulang dengan tarif yang benar.<br><small class="text-warning">Hanya bisa dilakukan sebelum bukti transfer diupload.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(`/bendahara/honor/payment/${id}/reset`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => window.location.href = data.redirect);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', text: 'Terjadi kesalahan.' }));
    });
}

function confirmDeleteHonor(id) {
    Swal.fire({
        title: 'Hapus Kwitansi Ini?',
        html: 'Kwitansi, bukti transfer, dan jurnal terkait akan dihapus permanen.<br>' +
              '<small class="text-danger">Gunakan hanya untuk kasus salah bayar. Tindakan tidak dapat dibatalkan.</small>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(`/bendahara/honor/payment/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Terhapus', text: data.message, timer: 2000, showConfirmButton: false })
                    .then(() => window.location.href = data.redirect);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', text: 'Terjadi kesalahan.' }));
    });
}

// ── COA baru toggle (modal tambah hutang) ──────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const selectCoa = document.querySelector('#modalTambahHutang select[name="coa_id"]');
    const coaBaru   = document.getElementById('coaBaruHutang');

    if (selectCoa && coaBaru) {
        selectCoa.addEventListener('change', function () {
            const isBaru = this.value === '__baru__';
            coaBaru.style.display = isBaru ? 'block' : 'none';
            // Biarkan value '__baru__' tetap — controller handle via coa_baru_kode/nama
        });
    }

    // ── Toggle deduction panel ─────────────────────────────────────────────
    document.querySelectorAll('[id^="deduction_receivable_id"]').forEach(function (select) {
        const panelId = select.id === 'deduction_receivable_id' ? 'deduction-panel' : 'deduction-panel-replace';
        const panel   = document.getElementById(panelId);
        if (!panel) return;

        select.addEventListener('change', function () {
            panel.style.display = this.value ? 'block' : 'none';
            const opt        = this.options[this.selectedIndex];
            const maxEl       = panel.querySelector('.deduction-max-label');
            const amtEl       = panel.querySelector('input[name="deduction_amount"]');
            const sisa        = parseFloat(opt?.dataset?.sisa) || 0;
            const totalHonor  = {{ $honor->total }};
            const maxAllowed  = Math.min(sisa, totalHonor);
            if (maxEl && opt?.dataset?.sisa) {
                maxEl.textContent = 'Sisa hutang: Rp ' + new Intl.NumberFormat('id-ID').format(sisa);
            }
            if (amtEl) amtEl.max = maxAllowed;
        });
        const amtEl = panel?.querySelector('input[name="deduction_amount"]');
        if (amtEl) {
            const total = {{ $honor->total }};
            amtEl.addEventListener('input', function () {
                const v          = parseFloat(this.value) || 0;
                const trf        = Math.max(0, total - v);
                const previewDed = panel.querySelector('.preview-deduction');
                const previewTrf = panel.querySelector('.preview-transfer');
                if (previewDed) previewDed.textContent = new Intl.NumberFormat('id-ID').format(v);
                if (previewTrf) previewTrf.textContent = new Intl.NumberFormat('id-ID').format(trf);
            });
        }
    });
});

(function () {
    const display = document.getElementById('inputJumlahPiutang');  // sesuai HTML
    const hidden  = document.getElementById('hiddenJumlahPiutang'); // sesuai HTML
    if (!display || !hidden) return;

    function formatRupiah(val) {
        const angka = val.replace(/\D/g, '');
        return angka.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    display.addEventListener('input', function () {
        const raw    = this.value.replace(/\D/g, '');
        this.value   = raw ? formatRupiah(raw) : '';
        hidden.value = raw;
    });

    display.addEventListener('blur', function () {
        if (!hidden.value) this.value = '';
    });

    hidden.required = true;
})();
</script>
@endpush