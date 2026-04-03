@extends('layouts.app')

@section('title', 'Verifikasi Asesi Mandiri')
@section('page-title', 'Verifikasi Asesi Mandiri - ' . $asesmen->full_name)

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="row">
    <!-- Left Column - Asesi Data -->
    <div class="col-lg-8">
        <!-- Personal Data -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
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
                                <td><strong>Tempat, Tgl Lahir</strong></td>
                                <td>: {{ $asesmen->birth_place }}, {{ $asesmen->birth_date->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin</strong></td>
                                <td>: {{ $asesmen->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $asesmen->user->email }}</td>
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

        <!-- Certification Data -->
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-award"></i> Data Sertifikasi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="180"><strong>Skema Sertifikasi</strong></td>
                                <td>: {{ $asesmen->skema->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Biaya Skema</strong></td>
                                <td>: Rp {{ number_format($asesmen->skema->fee, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Dipilih</strong></td>
                                <td>: {{ $asesmen->preferred_date->translatedFormat('d F Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Pelatihan</strong></td>
                                <td>:
                                    @if($asesmen->training_flag)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-mortarboard-fill"></i> Ya
                                    </span>
                                    <br><small class="text-success">+ Rp 1.500.000</small>
                                    @else
                                    <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Estimasi Total</strong></td>
                                <td>:
                                    @php
                                    $estimatedFee = $asesmen->skema->fee + ($asesmen->training_flag ? 1500000 : 0);
                                    @endphp
                                    <span class="badge bg-success" style="font-size: 1.1rem;">
                                        Rp {{ number_format($estimatedFee, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar</strong></td>
                                <td>: {{ $asesmen->registration_date->translatedFormat('d F Y') }}</td>
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
                            <i class="bi bi-eye"></i> Lihat
                        </a>
                        @else
                        <p class="text-muted">-</p>
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
                        <p class="text-muted">-</p>
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
                        <p class="text-muted">-</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Verification Form -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle"></i> Verifikasi & Penetapan Biaya</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Asesi Mandiri</strong>
                    <p class="mb-0 mt-2 small">
                        Verifikasi data dan tetapkan biaya sertifikasi. Setelah diverifikasi, asesi perlu di-assign ke
                        TUK yang sesuai.
                    </p>
                </div>

                <form action="{{ route('admin.mandiri.verify.process', $asesmen) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Biaya Skema (Referensi)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control"
                                value="{{ number_format($asesmen->skema->fee, 0, ',', '.') }}" readonly>
                        </div>
                    </div>

                    @if($asesmen->training_flag)
                    <div class="mb-3">
                        <label class="form-label">Biaya Pelatihan</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" value="{{ number_format(1500000, 0, ',', '.') }}"
                                readonly>
                        </div>
                        <small class="text-success">
                            <i class="bi bi-mortarboard-fill"></i> Peserta pilih ikut pelatihan
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estimasi Total</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control"
                                value="{{ number_format($asesmen->skema->fee + 1500000, 0, ',', '.') }}" readonly>
                        </div>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Biaya yang Ditetapkan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control rupiah @error('fee_amount') is-invalid @enderror"
                                name="fee_amount" id="fee_amount" required min="0"
                                value="{{ old('fee_amount', $asesmen->skema->fee + ($asesmen->training_flag ? 1500000 : 0)) }}">
                        </div>
                        <small class="text-muted">Anda dapat menyesuaikan biaya sesuai kebijakan LSP</small>
                        @error('fee_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Verifikasi (Opsional)</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Catatan untuk verifikasi ini">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                Saya telah memeriksa data dan menetapkan biaya dengan tepat
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Verifikasi & Tetapkan Biaya
                        </button>
                        <a href="{{ route('admin.mandiri.verifications') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection