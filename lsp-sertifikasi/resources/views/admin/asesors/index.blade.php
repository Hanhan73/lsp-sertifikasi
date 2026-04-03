@extends('layouts.app')

@section('title', 'Kelola Asesor')
@section('page-title', 'Kelola Asesor')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')

{{-- Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #3b82f6; --bg-color-end: #1d4ed8;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:0.8rem;">TOTAL ASESOR</p>
                    <h3>{{ $stats['total'] }}</h3>
                </div>
                <i class="bi bi-person-badge" style="font-size:2.5rem; opacity:0.4;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #10b981; --bg-color-end: #047857;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:0.8rem;">AKTIF</p>
                    <h3>{{ $stats['aktif'] }}</h3>
                </div>
                <i class="bi bi-check-circle" style="font-size:2.5rem; opacity:0.4;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #f59e0b; --bg-color-end: #b45309;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:0.8rem;">EXPIRE</p>
                    <h3>{{ $stats['expire'] }}</h3>
                </div>
                <i class="bi bi-clock-history" style="font-size:2.5rem; opacity:0.4;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="--bg-color: #ef4444; --bg-color-end: #b91c1c;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75" style="font-size:0.8rem;">NONAKTIF</p>
                    <h3>{{ $stats['nonaktif'] }}</h3>
                </div>
                <i class="bi bi-x-circle" style="font-size:2.5rem; opacity:0.4;"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0"><i class="bi bi-person-badge"></i> Daftar Asesor</h5>
        <div class="d-flex gap-2 flex-wrap">
            {{-- Import --}}
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-excel"></i> Import Excel
            </button>
            {{-- Tambah --}}
            <a href="{{ route('admin.asesors.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Asesor
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card-body border-bottom py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-1">Cari</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Nama, email, NIK, No. Reg..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Status Registrasi</label>
                <select name="status_reg" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status_reg') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="expire" {{ request('status_reg') === 'expire' ? 'selected' : '' }}>Expire</option>
                    <option value="nonaktif" {{ request('status_reg') === 'nonaktif' ? 'selected' : '' }}>Nonaktif
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="L" {{ request('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ request('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('admin.asesors.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        @if($asesors->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-person-badge" style="font-size: 4rem; color: #ccc;"></i>
            <h4 class="mt-3 text-muted">Belum Ada Data Asesor</h4>
            <p class="text-muted">Tambahkan asesor pertama atau import dari Excel</p>
            <a href="{{ route('admin.asesors.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle"></i> Tambah Asesor
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 data-table-asesor">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Asesor</th>
                        <th>NIK</th>
                        <th>Kontak</th>
                        <th>No. Reg. Met</th>
                        <th>No. Blanko</th>
                        <th>SIAPKerja</th>
                        <th>Status</th>
                        <th width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($asesors as $i => $asesor)
                    <tr>
                        <td class="text-center text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $asesor->foto_url }}" alt="Foto"
                                    style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #e2e8f0;">
                                <div>
                                    <strong>{{ $asesor->nama }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $asesor->jenis_kelamin_label }} &bull;
                                        {{ $asesor->tempat_lahir }}, {{ $asesor->tanggal_lahir->translatedFormat('d/m/Y') }}
                                        ({{ $asesor->umur }} th)
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code style="font-size:0.8rem;">{{ $asesor->nik }}</code>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-envelope text-muted" style="font-size:0.75rem;"></i>
                                <small>{{ $asesor->email }}</small>
                            </div>
                            @if($asesor->telepon)
                            <div>
                                <i class="bi bi-telephone text-muted" style="font-size:0.75rem;"></i>
                                <small>{{ $asesor->telepon }}</small>
                            </div>
                            @endif
                            @if($asesor->kota)
                            <div>
                                <i class="bi bi-geo-alt text-muted" style="font-size:0.75rem;"></i>
                                <small class="text-muted">{{ $asesor->kota }}</small>
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($asesor->no_reg_met)
                            <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                                {{ $asesor->no_reg_met }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($asesor->no_blanko)
                            <small>{{ $asesor->no_blanko }}</small>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($asesor->siap_kerja === 'Memiliki')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle"></i> Memiliki
                            </span>
                            @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                <i class="bi bi-x-circle"></i> Tidak
                            </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $asesor->status_badge }}">
                                {{ $asesor->status_label }}
                            </span>
                            @if($asesor->status_reg === 'expire' && $asesor->expire_date)
                            <br>
                            <small class="text-muted" style="font-size:0.7rem;">
                                s/d {{ $asesor->expire_date->translatedFormat('d/m/Y') }}
                            </small>
                            @endif
                            @if($asesor->user_id)
                            <br>
                            <small class="text-primary" style="font-size:0.7rem;">
                                <i class="bi bi-person-circle"></i> Punya Akun
                            </small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                {{-- Detail --}}
                                <button class="btn btn-sm btn-outline-info" title="Lihat Detail"
                                    onclick="viewDetail({{ $asesor->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                {{-- Edit --}}
                                <a href="{{ route('admin.asesors.edit', $asesor) }}"
                                    class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                {{-- Hapus --}}
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                    onclick="confirmDelete({{ $asesor->id }}, '{{ addslashes($asesor->nama) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Hidden delete form --}}
                                <form id="delete-form-{{ $asesor->id }}"
                                    action="{{ route('admin.asesors.destroy', $asesor) }}" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
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

{{-- Modal Detail Asesor --}}
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-badge"></i> Detail Asesor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detail-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('import_success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill"></i> {!! session('import_success') !!}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('import_errors'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <strong><i class="bi bi-exclamation-triangle-fill"></i> Beberapa baris dilewati:</strong>
    <ul class="mb-0 mt-2">
        @foreach(session('import_errors') as $err)
        <li><small>{{ $err }}</small></li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif


{{-- ── Modal Import ── --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-excel"></i> Import Asesor dari Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('admin.asesors.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Info format --}}
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Format Kolom Excel yang Diterima:</strong><br>
                        <small>
                            <code>Nama</code> &bull; <code>NIK</code> &bull; <code>Tempat Lahir</code> &bull;
                            <code>Tanggal Lahir</code> &bull; <code>P/L</code> &bull; <code>Alamat</code> &bull;
                            <code>Kota</code> &bull; <code>Provinsi</code> &bull; <code>Telepon</code> &bull;
                            <code>E-mail</code> &bull; <code>No. Reg. Met</code> &bull; <code>No. Blanko</code> &bull;
                            <code>SIAPKerja</code> &bull; <code>Keterangan</code>
                        </small>
                    </div>

                    {{-- Pilih file --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            File Excel <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file" id="import-file" class="form-control" accept=".xlsx,.xls"
                            required>
                        <small class="text-muted">Format: .xlsx / .xls &bull; Maks. 5 MB</small>
                    </div>

                    {{-- ✅ Opsi buat akun --}}
                    <div class="card border-primary mb-3">
                        <div class="card-body py-3">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" name="buat_akun" id="buat_akun_import"
                                    value="1" role="switch">
                                <label class="form-check-label fw-semibold" for="buat_akun_import">
                                    <i class="bi bi-person-lock"></i>
                                    Buatkan akun login untuk semua asesor yang diimport
                                </label>
                            </div>
                            <div id="akun-info" class="mt-2" style="display:none;">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-key"></i>
                                    Password default yang akan diset: <code>asesor123</code><br>
                                    <small class="text-muted">
                                        Akun hanya dibuat jika email belum terdaftar di sistem.
                                        Asesor tetap dapat login meskipun email duplikat di tabel users dilewati.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preview nama file --}}
                    <div id="file-preview" class="d-none">
                        <div class="alert alert-success py-2 mb-0">
                            <i class="bi bi-file-earmark-excel-fill text-success"></i>
                            File dipilih: <strong id="file-name"></strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="import-submit-btn">
                        <i class="bi bi-upload"></i> Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
// Toggle info akun
document.getElementById('buat_akun_import').addEventListener('change', function() {
    document.getElementById('akun-info').style.display = this.checked ? 'block' : 'none';
});

// Preview nama file
document.getElementById('import-file').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        document.getElementById('file-name').textContent = file.name;
        document.getElementById('file-preview').classList.remove('d-none');
    }
});

// Loading state saat submit
document.querySelector('#importModal form').addEventListener('submit', function() {
    const btn = document.getElementById('import-submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengimport...';
});

$(document).ready(function() {
    // DataTable
    if ($('.data-table-asesor').length && !$.fn.DataTable.isDataTable('.data-table-asesor')) {
        $('.data-table-asesor').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            order: [
                [1, 'asc']
            ],
            pageLength: 25,
            columnDefs: [{
                orderable: false,
                targets: [8]
            }]
        });
    }
});

function viewDetail(id) {
    $('#detailModal').modal('show');
    $('#detail-content').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Memuat data...</p>
        </div>
    `);

    $.ajax({
        url: '/admin/asesors/' + id,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#detail-content').html(response.html);
            } else {
                $('#detail-content').html('<div class="alert alert-danger">Gagal memuat data.</div>');
            }
        },
        error: function() {
            $('#detail-content').html('<div class="alert alert-danger">Terjadi kesalahan server.</div>');
        }
    });
}

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Asesor?',
        html: `Apakah Anda yakin ingin menghapus asesor <strong>${nama}</strong>?<br><small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

function buatAkunAsesor(asesorId, nama) {
    Swal.fire({
        title: 'Buatkan Akun Login?',
        html: `Akun login akan dibuat untuk asesor <strong>${nama}</strong>.<br>
               <small class="text-muted">Password default: <code>asesor123</code></small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-person-plus"></i> Ya, Buatkan!',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (!result.isConfirmed) return;
 
        const btn = document.getElementById('btn-buat-akun-' + asesorId);
        const resultDiv = document.getElementById('buat-akun-result-' + asesorId);
 
        // Loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
 
        $.ajax({
            url: '/admin/asesors/' + asesorId + '/buat-akun',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Tampilkan sukses di dalam modal
                    resultDiv.innerHTML = `
                        <div class="alert alert-success py-2 text-start mt-1">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <strong>Berhasil!</strong><br>
                            <small>${response.message}</small>
                        </div>`;
 
                    // Sembunyikan tombol buat akun
                    btn.style.display = 'none';
 
                    // Toast notifikasi
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: response.info === 'linked'
                            ? 'Akun berhasil dihubungkan!'
                            : 'Akun berhasil dibuat!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                    });
 
                    // Refresh badge di tabel (update kolom "Punya Akun")
                    // Reload halaman setelah 2 detik agar tabel terupdate
                    setTimeout(() => {
                        location.reload();
                    }, 2500);
 
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger py-2 text-start mt-1">
                            <i class="bi bi-x-circle-fill text-danger"></i>
                            <small>${response.message}</small>
                        </div>`;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-person-plus"></i> Buatkan Akun Login';
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan server.';
                resultDiv.innerHTML = `
                    <div class="alert alert-danger py-2 text-start mt-1">
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        <small>${msg}</small>
                    </div>`;
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-person-plus"></i> Buatkan Akun Login';
            }
        });
    });
}
</script>
@endpush