@extends('layouts.app')

@section('title', 'Verifikasi Asesi Mandiri')
@section('page-title', 'Verifikasi Asesi Mandiri')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-person-check"></i> Asesi Mandiri
        </h5>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary fs-6">{{ $asesmens->count() }} Total</span>
            <span class="badge bg-warning fs-6">{{ $asesmens->where('status', 'data_completed')->count() }} Perlu Verifikasi</span>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>Alur Verifikasi Asesi Mandiri:</strong>
            <ol class="mb-0 mt-2">
                <li>Asesi mandiri mengisi data lengkap</li>
                <li><strong>Admin LSP verifikasi data dan tetapkan biaya</strong></li>
                <li>Admin assign asesi ke TUK yang sesuai</li>
                <li>Asesi melakukan pembayaran</li>
                <li>TUK melakukan penjadwalan</li>
            </ol>
        </div>

        @if($asesmens->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-check-circle" style="font-size: 4rem; color: #28a745;"></i>
            <h4 class="mt-3">Belum Ada Asesi Mandiri</h4>
            <p class="text-muted">Tidak ada data asesi mandiri saat ini.</p>
        </div>
        @else

        @php
            $dupCount = $asesmens->filter(fn($a) => !empty($a->_kolektif_batch))->count();
        @endphp

        @if($dupCount > 0)
        <div class="alert alert-warning d-flex gap-3 align-items-start mb-4">
            <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <strong>{{ $dupCount }} asesi diduga sudah terdaftar secara kolektif!</strong>
                <p class="mb-0 small mt-1">
                    Nama mereka ditemukan di pendaftaran batch kolektif. Kemungkinan mendaftar ulang secara
                    mandiri karena tidak tahu sudah didaftarkan TUK. Klik <strong>"Bandingkan"</strong> untuk melihat
                    detail perbandingan sebelum memutuskan untuk menghapus.
                </p>
            </div>
        </div>
        @endif
        @php $requestHapusCount = $asesmens->filter(fn($a) => $a->delete_requested)->count(); @endphp
        @if($requestHapusCount > 0)
        <span class="badge bg-danger fs-6">{{ $requestHapusCount }} Request Hapus</span>
        @endif

        <div class="table-responsive">
            <table class="table table-hover data-table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No Reg</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Skema</th>
                        <th>Status</th>
                        <th>Pelatihan</th>
                        <th>Biaya Estimasi</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesmens as $asesmen)
                    @php
                        $kolektifBatch = $asesmen->_kolektif_batch ?? null;
                        $kolektifTuk   = $asesmen->_kolektif_tuk   ?? null;
                        $kolektifData  = $asesmen->_kolektif_data   ?? null;
                        $isDuplikat    = !empty($kolektifBatch);
                        $estimatedFee  = $asesmen->skema ? $asesmen->skema->fee + ($asesmen->training_flag ? 1500000 : 0) : 0;

                        $mandiriJson  = $isDuplikat ? json_encode([
                            'id'        => $asesmen->id,
                            'nama'      => $asesmen->full_name,
                            'email'     => $asesmen->user->email ?? '-',
                            'skema'     => $asesmen->skema->name ?? '-',
                            'status'    => $asesmen->status_label,
                            'tgl_daftar'=> $asesmen->registration_date?->format('d/m/Y') ?? '-',
                            'phone'     => $asesmen->phone ?? '-',
                            'institusi' => $asesmen->institution ?? '-',
                        ]) : '{}';

                        $kolektifJson = $isDuplikat ? json_encode([
                            'batch_id'  => $kolektifData?->collective_batch_id ?? '-',
                            'tuk'       => $kolektifTuk ?? '-',
                            'skema'     => $kolektifData?->skema?->name ?? '-',
                            'status'    => $kolektifData?->status_label ?? '-',
                            'tgl_daftar'=> $kolektifData?->registration_date?->format('d/m/Y') ?? '-',
                        ]) : '{}';
                    @endphp
                    <tr class="{{ $isDuplikat ? 'table-danger' : '' }}"
                        data-asesmen-id="{{ $asesmen->id }}"
                        data-mandiri="{{ $isDuplikat ? htmlspecialchars($mandiriJson) : '' }}"
                        data-kolektif="{{ $isDuplikat ? htmlspecialchars($kolektifJson) : '' }}">

                        <td><strong>#{{ $asesmen->id }}</strong></td>

                        <td>
                            <div class="fw-semibold">{{ $asesmen->full_name }}</div>
                            @if($asesmen->delete_requested)
                            <span class="badge bg-danger mt-1">
                                <i class="bi bi-flag me-1"></i>Request Hapus dari TUK
                            </span>
                            <div class="small text-muted">{{ $asesmen->delete_request_reason }}</div>
                            @endif
                            @if($isDuplikat)
                                <span class="badge bg-danger mt-1">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Duplikat Kolektif
                                </span>
                                <div class="small text-muted mt-1">
                                    Batch: <code>{{ $kolektifBatch }}</code>
                                    @if($kolektifTuk) — {{ $kolektifTuk }} @endif
                                </div>
                            @endif
                        </td>

                        <td><small>{{ $asesmen->user->email ?? '-' }}</small></td>

                        <td>
                            <span class="badge bg-primary">{{ $asesmen->skema->name ?? '-' }}</span>
                        </td>

                        <td>
                            <span class="badge bg-{{ $asesmen->status_badge }}">{{ $asesmen->status_label }}</span>
                        </td>

                        <td>
                            @if($asesmen->training_flag)
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-mortarboard-fill"></i> Ya
                            </span>
                            @else
                            <span class="badge bg-secondary">Tidak</span>
                            @endif
                        </td>

                        <td>
                            @if($asesmen->skema)
                                <strong>Rp {{ number_format($estimatedFee, 0, ',', '.') }}</strong>
                                @if($asesmen->training_flag)
                                <br><small class="text-muted">+ Pelatihan</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>
                            <small>{{ $asesmen->registration_date?->translatedFormat('d/m/Y') ?? '-' }}</small>
                        </td>

                        <td>
                            <div class="d-flex flex-column gap-1" style="min-width: 120px;">
                                @if($asesmen->status === 'data_completed')
                                <a href="{{ route('admin.mandiri.verify', $asesmen) }}"
                                    class="btn btn-sm btn-success"
                                    data-bs-toggle="tooltip" title="Verifikasi & Tetapkan Biaya">
                                    <i class="bi bi-check-circle"></i> Verifikasi
                                </a>
                                @endif

                                @if($isDuplikat)
                                <button class="btn btn-sm btn-warning"
                                    onclick="lihatDuplikat({{ $asesmen->id }})"
                                    data-bs-toggle="tooltip" title="Bandingkan dengan data kolektif">
                                    <i class="bi bi-arrow-left-right"></i> Bandingkan
                                </button>
                                @endif

                                <button class="btn btn-sm btn-danger"
                                    onclick="hapusMandiri({{ $asesmen->id }}, '{{ addslashes($asesmen->full_name) }}')"
                                    data-bs-toggle="tooltip" title="Hapus akun mandiri ini">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Modal Bandingkan Duplikat --}}
<div class="modal fade" id="modalDuplikat" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-left-right me-2"></i>Perbandingan Data Duplikat
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Asesi ini kemungkinan sudah terdaftar via kolektif oleh TUK. Bandingkan datanya, lalu hapus pendaftaran mandirinya jika memang duplikat.
                </div>
                <div class="row g-3">
                    {{-- Mandiri --}}
                    <div class="col-md-6">
                        <div class="card border-danger h-100">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-person me-1"></i> Pendaftaran <strong>Mandiri</strong></span>
                                <span class="badge bg-white text-danger">Kandidat hapus</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr><th class="ps-3 text-muted" style="width:35%">No Reg</th><td id="m-id">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Nama</th><td id="m-nama">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Email</th><td id="m-email">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Skema</th><td id="m-skema">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Status</th><td id="m-status">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Tgl Daftar</th><td id="m-tgl">-</td></tr>
                                        <tr><th class="ps-3 text-muted">No. HP</th><td id="m-phone">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Institusi</th><td id="m-institusi">-</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    {{-- Kolektif --}}
                    <div class="col-md-6">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-people me-1"></i> Pendaftaran <strong>Kolektif</strong></span>
                                <span class="badge bg-white text-success">Sudah terdaftar via TUK</span>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <tbody>
                                        <tr><th class="ps-3 text-muted" style="width:35%">Batch ID</th><td id="k-batch">-</td></tr>
                                        <tr><th class="ps-3 text-muted">TUK</th><td id="k-tuk">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Skema</th><td id="k-skema">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Status</th><td id="k-status">-</td></tr>
                                        <tr><th class="ps-3 text-muted">Tgl Daftar</th><td id="k-tgl">-</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Tutup
                </button>
                <button type="button" class="btn btn-danger" id="btn-hapus-dari-modal">
                    <i class="bi bi-trash me-1"></i> Hapus Pendaftaran Mandiri Ini
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentAsesmenId = null;
let currentNama      = null;

$(document).ready(function () {
    $('.data-table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        order: [[7, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 8 }],
    });

    $('[data-bs-toggle="tooltip"]').tooltip();
});

function lihatDuplikat(asesmenId) {
    const row      = document.querySelector(`tr[data-asesmen-id="${asesmenId}"]`);
    const mandiri  = JSON.parse(row.dataset.mandiri);
    const kolektif = JSON.parse(row.dataset.kolektif);

    currentAsesmenId = asesmenId;
    currentNama      = mandiri.nama;

    document.getElementById('m-id').textContent        = '#' + mandiri.id;
    document.getElementById('m-nama').textContent      = mandiri.nama;
    document.getElementById('m-email').textContent     = mandiri.email;
    document.getElementById('m-skema').textContent     = mandiri.skema;
    document.getElementById('m-status').textContent    = mandiri.status;
    document.getElementById('m-tgl').textContent       = mandiri.tgl_daftar;
    document.getElementById('m-phone').textContent     = mandiri.phone;
    document.getElementById('m-institusi').textContent = mandiri.institusi;

    document.getElementById('k-batch').textContent  = kolektif.batch_id;
    document.getElementById('k-tuk').textContent    = kolektif.tuk;
    document.getElementById('k-skema').textContent  = kolektif.skema;
    document.getElementById('k-status').textContent = kolektif.status;
    document.getElementById('k-tgl').textContent    = kolektif.tgl_daftar;

    new bootstrap.Modal(document.getElementById('modalDuplikat')).show();
}

document.getElementById('btn-hapus-dari-modal').addEventListener('click', function () {
    bootstrap.Modal.getInstance(document.getElementById('modalDuplikat')).hide();
    setTimeout(() => hapusMandiri(currentAsesmenId, currentNama), 300);
});

async function hapusMandiri(asesmenId, nama) {
    const result = await Swal.fire({
        title: 'Hapus Akun Mandiri?',
        html: `Akun <strong>${nama}</strong> akan dihapus permanen beserta seluruh datanya.<br>
               <span class="text-danger small fw-semibold">Tindakan ini tidak bisa dibatalkan!</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Ya, Hapus Permanen',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/asesi/${asesmenId}/hapus-mandiri`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Dihapus!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
            });

            const row = document.querySelector(`tr[data-asesmen-id="${asesmenId}"]`);
            if (row) {
                $('.data-table').DataTable().row(row).remove().draw();
            } else {
                location.reload();
            }
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
    }
}
</script>
@endpush