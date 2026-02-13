@extends('layouts.app')

@section('title', 'Verifikasi Asesi - TUK')
@section('page-title', 'Verifikasi Data - ' . ($asesmen->full_name ?? $asesmen->user->name))

@section('sidebar')
@include('tuk.partials.tuk-sidebar')
@endsection

@section('content')
<div class="row">
    <!-- Left Column - Asesi Data -->
    <div class="col-lg-8">
        <!-- Registration Info -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pendaftaran</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>No. Registrasi</strong></td>
                                <td>: #{{ $asesmen->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar</strong></td>
                                <td>: {{ $asesmen->registration_date->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Type</strong></td>
                                <td>:
                                    @if($asesmen->is_collective)
                                    <span class="badge bg-primary">Kolektif</span>
                                    @else
                                    <span class="badge bg-secondary">Mandiri</span>
                                    @endif
                                </td>
                            </tr>
                            @if($asesmen->is_collective)
                            <tr>
                                <td><strong>Batch ID</strong></td>
                                <td>: {{ $asesmen->collective_batch_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Waktu Bayar</strong></td>
                                <td>:
                                    <span
                                        class="badge bg-{{ $asesmen->collective_payment_timing === 'before' ? 'warning' : 'success' }}">
                                        {{ $asesmen->collective_payment_timing === 'before' ? 'Sebelum Asesmen' : 'Setelah Asesmen' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Didaftarkan Oleh</strong></td>
                                <td>: {{ $asesmen->registrar->name ?? '-' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Skema</strong></td>
                                <td>: {{ $asesmen->skema->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kode Skema</strong></td>
                                <td>: {{ $asesmen->skema->code ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Biaya Skema</strong></td>
                                <td>: Rp {{ number_format($asesmen->skema->fee ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Pilihan</strong></td>
                                <td>: {{ $asesmen->preferred_date ? $asesmen->preferred_date->format('d F Y') : '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Data -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Data Pribadi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Nama Lengkap</strong></td>
                                <td>: {{ $asesmen->full_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>NIK</strong></td>
                                <td>: {{ $asesmen->nik }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tempat Lahir</strong></td>
                                <td>: {{ $asesmen->birth_place }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Lahir</strong></td>
                                <td>: {{ $asesmen->birth_date->format('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin</strong></td>
                                <td>: {{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $asesmen->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon</strong></td>
                                <td>: {{ $asesmen->phone }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Alamat</strong></td>
                                <td>: {{ $asesmen->address }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kode Provinsi</strong></td>
                                <td>: {{ $asesmen->province_code }}</td>
                            </tr>
                            <tr>
                                <td><strong>Kode Kota</strong></td>
                                <td>: {{ $asesmen->city_code }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pendidikan</strong></td>
                                <td>: {{ $asesmen->education }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pekerjaan</strong></td>
                                <td>: {{ $asesmen->occupation }}</td>
                            </tr>
                            <tr>
                                <td><strong>Sumber Anggaran</strong></td>
                                <td>: {{ $asesmen->budget_source }}</td>
                            </tr>
                            <tr>
                                <td><strong>Asal Lembaga</strong></td>
                                <td>: {{ $asesmen->institution }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark"></i> Dokumen</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <h6>Pas Foto</h6>
                        @if($asesmen->photo_path)
                        <img src="{{ asset('storage/' . $asesmen->photo_path) }}" alt="Foto" class="img-thumbnail mb-2"
                            style="max-height: 200px;">
                        <br>
                        <a href="{{ asset('storage/' . $asesmen->photo_path) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Lihat
                        </a>
                        @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Belum upload
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-center">
                        <h6>KTP</h6>
                        @if($asesmen->ktp_path)
                        <iframe src="{{ asset('storage/' . $asesmen->ktp_path) }}"
                            style="width: 100%; height: 200px; border: 1px solid #ddd;" class="mb-2"></iframe>
                        <br>
                        <a href="{{ asset('storage/' . $asesmen->ktp_path) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                        @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Belum upload
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4 text-center">
                        <h6>Ijazah/Transkrip</h6>
                        @if($asesmen->document_path)
                        <iframe src="{{ asset('storage/' . $asesmen->document_path) }}"
                            style="width: 100%; height: 200px; border: 1px solid #ddd;" class="mb-2"></iframe>
                        <br>
                        <a href="{{ asset('storage/' . $asesmen->document_path) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                        @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Belum upload
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Verification Form -->
    <div class="col-lg-4">
        <div class="card" style="top: 20px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle"></i> Verifikasi TUK</h5>
            </div>
            <div class="card-body">
                @if($asesmen->is_collective)
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Pendaftaran Kolektif</strong>
                    <p class="mb-0 mt-2 small">
                        Asesi ini terdaftar melalui TUK secara kolektif.
                        Setelah verifikasi TUK, Admin LSP akan menetapkan biaya.
                    </p>
                </div>
                @endif

                <form action="{{ route('tuk.verifications.process', $asesmen) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Status Dokumen</label>
                        <div class="card">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>Pas Foto</span>
                                    @if($asesmen->photo_path)
                                    <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    @else
                                    <span class="badge bg-danger"><i class="bi bi-x"></i></span>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>KTP</span>
                                    @if($asesmen->ktp_path)
                                    <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    @else
                                    <span class="badge bg-danger"><i class="bi bi-x"></i></span>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Ijazah/Transkrip</span>
                                    @if($asesmen->document_path)
                                    <span class="badge bg-success"><i class="bi bi-check"></i></span>
                                    @else
                                    <span class="badge bg-danger"><i class="bi bi-x"></i></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Verifikasi (Opsional)</label>
                        <textarea class="form-control" name="notes" rows="4"
                            placeholder="Catatan untuk verifikasi ini (misal: Semua dokumen lengkap dan sesuai, KTP jelas terbaca, dll)">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                Saya telah memeriksa semua data dan dokumen dengan teliti dan menyatakan data ini
                                <strong>VALID</strong>
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Verifikasi Data
                        </button>
                        <a href="{{ route('tuk.verifications') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection