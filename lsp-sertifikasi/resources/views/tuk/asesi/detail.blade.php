@extends('layouts.app')

@section('title', 'Detail Asesi')
@section('page-title', 'Detail Asesi - ' . ($asesmen->full_name ?? $asesmen->user->name))

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
                                <td><strong>Jenis Pendaftaran</strong></td>
                                <td>:
                                    @if($asesmen->is_collective)
                                    <span class="badge bg-primary">Kolektif</span>
                                    @else
                                    <span class="badge bg-success">Mandiri</span>
                                    @endif
                                </td>
                            </tr>
                            @if($asesmen->is_collective)
                            <tr>
                                <td><strong>Batch ID</strong></td>
                                <td>: {{ $asesmen->collective_batch_id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Metode Bayar</strong></td>
                                <td>:
                                    @if($asesmen->payment_phases === 'single')
                                    <span class="badge bg-success">1 Fase</span>
                                    @else
                                    <span class="badge bg-primary">2 Fase</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Skema</strong></td>
                                <td>: {{ $asesmen->skema->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Pilihan</strong></td>
                                <td>: {{ $asesmen->preferred_date ? $asesmen->preferred_date->format('d F Y') : '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Status</strong></td>
                                <td>:
                                    <span class="badge bg-{{ $asesmen->status_badge }}">
                                        {{ $asesmen->status_label }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Pelatihan</strong></td>
                                <td>:
                                    @if($asesmen->training_flag)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-mortarboard-fill"></i> Ya
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                            </tr>
                            @if($asesmen->fee_amount)
                            <tr>
                                <td><strong>Biaya</strong></td>
                                <td>: Rp {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($asesmen->is_collective && $asesmen->payment_phases === 'two_phase')
                            <tr>
                                <td><strong>Fase 1</strong></td>
                                <td>: Rp {{ number_format($asesmen->phase_1_amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Fase 2</strong></td>
                                <td>: Rp {{ number_format($asesmen->phase_2_amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endif
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
                                <td>: {{ $asesmen->nik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tempat Lahir</strong></td>
                                <td>: {{ $asesmen->birth_place ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Lahir</strong></td>
                                <td>: {{ $asesmen->birth_date ? $asesmen->birth_date->format('d F Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin</strong></td>
                                <td>: {{ $asesmen->gender === 'L' ? 'Laki-laki' : ($asesmen->gender === 'P' ?
                                    'Perempuan' : '-') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>: {{ $asesmen->email ?? $asesmen->user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon</strong></td>
                                <td>: {{ $asesmen->phone ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="150"><strong>Alamat</strong></td>
                                <td>: {{ $asesmen->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pendidikan</strong></td>
                                <td>: {{ $asesmen->education ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Pekerjaan</strong></td>
                                <td>: {{ $asesmen->occupation ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Sumber Anggaran</strong></td>
                                <td>: {{ $asesmen->budget_source ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Asal Lembaga</strong></td>
                                <td>: {{ $asesmen->institution ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents -->
        @if($asesmen->photo_path || $asesmen->ktp_path || $asesmen->document_path)
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
        @endif

        <!-- Payment Info -->
        @if($asesmen->payment || $asesmen->payments->count() > 0)
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Informasi Pembayaran</h5>
            </div>
            <div class="card-body">
                @if($asesmen->is_collective && $asesmen->payment_phases === 'two_phase')
                <div class="row">
                    <div class="col-md-6">
                        <h6>Fase 1 (50%)</h6>
                        @php
                        $phase1 = $asesmen->payments()->where('payment_phase', 'phase_1')->first();
                        @endphp
                        @if($phase1)
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="120">Jumlah:</td>
                                <td>Rp {{ number_format($phase1->amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td>
                                    <span class="badge bg-{{ $phase1->status_badge }}">
                                        {{ $phase1->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @if($phase1->verified_at)
                            <tr>
                                <td>Tanggal:</td>
                                <td>{{ $phase1->verified_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                        @else
                        <p class="text-muted">Belum ada pembayaran</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>Fase 2 (50%)</h6>
                        @php
                        $phase2 = $asesmen->payments()->where('payment_phase', 'phase_2')->first();
                        @endphp
                        @if($phase2)
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="120">Jumlah:</td>
                                <td>Rp {{ number_format($phase2->amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td>
                                    <span class="badge bg-{{ $phase2->status_badge }}">
                                        {{ $phase2->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @if($phase2->verified_at)
                            <tr>
                                <td>Tanggal:</td>
                                <td>{{ $phase2->verified_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endif
                        </table>
                        @else
                        <p class="text-muted">Belum ada pembayaran</p>
                        @endif
                    </div>
                </div>
                @else
                <table class="table table-borderless table-sm">
                    <tr>
                        <td width="150"><strong>Jumlah</strong></td>
                        <td>: Rp {{ number_format($asesmen->payment->amount ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Metode</strong></td>
                        <td>: {{ ucfirst($asesmen->payment->method ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>:
                            <span class="badge bg-{{ $asesmen->payment->status_badge ?? 'secondary' }}">
                                {{ $asesmen->payment->status_label ?? 'Belum Bayar' }}
                            </span>
                        </td>
                    </tr>
                    @if($asesmen->payment && $asesmen->payment->verified_at)
                    <tr>
                        <td><strong>Tanggal Bayar</strong></td>
                        <td>: {{ $asesmen->payment->verified_at->format('d F Y H:i') }}</td>
                    </tr>
                    @endif
                    @if($asesmen->payment && $asesmen->payment->transaction_id)
                    <tr>
                        <td><strong>Transaction ID</strong></td>
                        <td>: {{ $asesmen->payment->transaction_id }}</td>
                    </tr>
                    @endif
                </table>
                @endif
            </div>
        </div>
        @endif

        <!-- Schedule Info -->
        @if($asesmen->schedule)
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-calendar"></i> Jadwal Asesmen</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td width="150"><strong>Tanggal</strong></td>
                        <td>: {{ $asesmen->schedule->assessment_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Waktu</strong></td>
                        <td>: {{ $asesmen->schedule->start_time }} - {{ $asesmen->schedule->end_time }}</td>
                    </tr>
                    <tr>
                        <td><strong>Lokasi</strong></td>
                        <td>: {{ $asesmen->schedule->location ?? '-' }}</td>
                    </tr>
                    @if($asesmen->schedule->notes)
                    <tr>
                        <td><strong>Catatan</strong></td>
                        <td>: {{ $asesmen->schedule->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif

        <!-- Assessment Result -->
        @if($asesmen->status === 'assessed' || $asesmen->status === 'certified')
        <div class="card mb-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> Hasil Asesmen</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td width="150"><strong>Hasil</strong></td>
                        <td>:
                            <span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                                {{ strtoupper($asesmen->result) }}
                            </span>
                        </td>
                    </tr>
                    @if($asesmen->assessed_at)
                    <tr>
                        <td><strong>Tanggal</strong></td>
                        <td>: {{ $asesmen->assessed_at->format('d F Y') }}</td>
                    </tr>
                    @endif
                    @if($asesmen->assessor)
                    <tr>
                        <td><strong>Asesor</strong></td>
                        <td>: {{ $asesmen->assessor->name }}</td>
                    </tr>
                    @endif
                    @if($asesmen->result_notes)
                    <tr>
                        <td><strong>Catatan</strong></td>
                        <td>: {{ $asesmen->result_notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column - Actions & Status -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Actions</h5>
            </div>
            <div class="card-body">
                @if($asesmen->status === 'data_completed' && !$asesmen->tuk_verified_at)
                <a href="{{ route('tuk.verifications.process', $asesmen) }}" class="btn btn-success w-100 mb-2">
                    <i class="bi bi-check-circle"></i> Verifikasi Data
                </a>
                @endif

                @if($asesmen->status === 'paid' && !$asesmen->schedule)
                <a href="{{ route('tuk.schedules.create', ['asesmen_id' => $asesmen->id]) }}"
                    class="btn btn-warning w-100 mb-2">
                    <i class="bi bi-calendar-plus"></i> Buat Jadwal
                </a>
                @endif

                @if($asesmen->is_collective && $asesmen->collective_batch_id)
                <a href="{{ route('tuk.collective.payment', $asesmen->collective_batch_id) }}"
                    class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-cash-coin"></i> Lihat Pembayaran Batch
                </a>
                @endif

                <a href="{{ route('tuk.asesi') }}" class="btn btn-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <!-- TUK Verification Status -->
        @if($asesmen->tuk_verified_at)
        <div class="card mt-3">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-shield-check"></i> Verifikasi TUK</h6>
            </div>
            <div class="card-body">
                <p class="mb-1">
                    <strong>Diverifikasi oleh:</strong><br>
                    {{ $asesmen->tukVerifier->name ?? '-' }}
                </p>
                <p class="mb-1">
                    <strong>Tanggal:</strong><br>
                    {{ $asesmen->tuk_verified_at->format('d F Y H:i') }}
                </p>
                @if($asesmen->tuk_verification_notes)
                <p class="mb-0">
                    <strong>Catatan:</strong><br>
                    {{ $asesmen->tuk_verification_notes }}
                </p>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection