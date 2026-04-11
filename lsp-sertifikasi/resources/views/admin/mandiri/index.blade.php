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
            <i class="bi bi-person-check"></i> Asesi Mandiri Menunggu Verifikasi
        </h5>
        <div>
            <span class="badge bg-warning">{{ $asesmens->count() }} Perlu Verifikasi</span>
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
            <h4 class="mt-3">Semua Asesi Mandiri Sudah Diverifikasi</h4>
            <p class="text-muted">Tidak ada data asesi mandiri yang menunggu verifikasi saat ini.</p>
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
                    Nama mereka ditemukan di pendaftaran batch kolektif. Kemungkinan mereka mendaftar ulang secara
                    mandiri karena tidak tahu sudah didaftarkan oleh TUK. Baris yang bermasalah ditandai
                    <span class="badge bg-danger">Duplikat Kolektif</span> — periksa dahulu sebelum memverifikasi.
                    Pertimbangkan untuk menghapus akun mandiri tersebut.
                </p>
            </div>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover data-table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No Reg</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Skema</th>
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
                        $isDuplikat    = !empty($kolektifBatch);
                        $estimatedFee  = $asesmen->skema->fee + ($asesmen->training_flag ? 1500000 : 0);
                    @endphp
                    <tr class="{{ $isDuplikat ? 'table-danger' : '' }}" data-asesmen-id="{{ $asesmen->id }}">
                        <td><strong>#{{ $asesmen->id }}</strong></td>
                        <td>
                            <div class="fw-semibold">{{ $asesmen->full_name }}</div>
                            @if($isDuplikat)
                                <span class="badge bg-danger mt-1">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Duplikat Kolektif
                                </span>
                                <div class="small text-muted mt-1">
                                    Batch: <code>{{ $kolektifBatch }}</code>
                                    @if($kolektifTuk)
                                        — {{ $kolektifTuk }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <small>{{ $asesmen->user->email ?? '-' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $asesmen->skema->name }}</span>
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
                            <strong>Rp {{ number_format($estimatedFee, 0, ',', '.') }}</strong>
                            @if($asesmen->training_flag)
                            <br><small class="text-muted">+ Pelatihan</small>
                            @endif
                        </td>
                        <td>
                            <small>{{ $asesmen->registration_date->translatedFormat('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1" style="min-width: 110px;">
                                <a href="{{ route('admin.mandiri.verify', $asesmen) }}"
                                    class="btn btn-sm btn-success"
                                    data-bs-toggle="tooltip" title="Verifikasi & Tetapkan Biaya">
                                    <i class="bi bi-check-circle"></i> Verifikasi
                                </a>
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
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('.data-table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        order: [[6, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: 7 }],
        // Duplikat naik ke atas — sort merah dulu
        rowCallback: function (row, data) {
            if ($(row).hasClass('table-danger')) {
                $(row).prependTo($(row).closest('tbody'));
            }
        }
    });

    $('[data-bs-toggle="tooltip"]').tooltip();
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