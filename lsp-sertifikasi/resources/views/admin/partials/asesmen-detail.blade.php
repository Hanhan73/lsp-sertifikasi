<div class="row">
    <!-- Left Column: Personal Info -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-person-badge"></i> Informasi Pribadi</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>No. Registrasi</strong></td>
                        <td>: #{{ $asesmen->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nama Lengkap</strong></td>
                        <td>: {{ $asesmen->full_name ?? $asesmen->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>: {{ $asesmen->email ?? $asesmen->user->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>No. HP</strong></td>
                        <td>: {{ $asesmen->phone ?? '-' }}</td>
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
                        <td>: {{ $asesmen->birth_date ? $asesmen->birth_date->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jenis Kelamin</strong></td>
                        <td>: {{ $asesmen->gender ? ucfirst($asesmen->gender) : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Alamat</strong></td>
                        <td>: {{ $asesmen->address ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Education & Work -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-book"></i> Pendidikan & Pekerjaan</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Pendidikan</strong></td>
                        <td>: {{ $asesmen->education ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pekerjaan</strong></td>
                        <td>: {{ $asesmen->occupation ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Instansi</strong></td>
                        <td>: {{ $asesmen->institution ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Sumber Biaya</strong></td>
                        <td>: {{ $asesmen->budget_source ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Verification Info -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Informasi Verifikasi</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Verifikasi Admin</strong></td>
                        <td>:
                            @if($asesmen->admin_verified_at)
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Terverifikasi</span>
                            <br><small class="text-muted">
                                {{ \Carbon\Carbon::parse($asesmen->admin_verified_at)->format('d/m/Y H:i') }}
                                @if($asesmen->assessorRegistrar)
                                <br>oleh: {{ $asesmen->assessorRegistrar->name }}
                                @endif
                            </small>
                            @else
                            <span class="badge bg-secondary">Belum Verifikasi</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Verifikasi TUK</strong></td>
                        <td>:
                            @if($asesmen->tuk_verified_at)
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Terverifikasi</span>
                            <br><small class="text-muted">
                                {{ \Carbon\Carbon::parse($asesmen->tuk_verified_at)->format('d/m/Y H:i') }}
                                @if($asesmen->tukVerifier)
                                <br>oleh: {{ $asesmen->tukVerifier->name }}
                                @endif
                            </small>
                            @else
                            <span class="badge bg-secondary">Belum Verifikasi</span>
                            @endif
                        </td>
                    </tr>
                    @if($asesmen->tuk_verification_notes)
                    <tr>
                        <td><strong>Catatan TUK</strong></td>
                        <td>: {{ $asesmen->tuk_verification_notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Assignment Info (untuk Mandiri) --}}
        @if($asesmen->assignedTuk)
        <div class="card mb-3 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-building-check"></i> Assignment TUK</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Assigned ke</strong></td>
                        <td>: {{ $asesmen->assignedTuk->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Assign</strong></td>
                        <td>:
                            {{ $asesmen->assigned_at ? \Carbon\Carbon::parse($asesmen->assigned_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>
                    @if($asesmen->assigner)
                    <tr>
                        <td><strong>Di-assign oleh</strong></td>
                        <td>: {{ $asesmen->assigner->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column: Registration Info -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Informasi Pendaftaran</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>TUK Asal</strong></td>
                        <td>: {{ $asesmen->tuk->name ?? '-' }}</td>
                    </tr>
                    @if($asesmen->assignedTuk && $asesmen->assignedTuk->id !== $asesmen->tuk_id)
                    <tr>
                        <td><strong>TUK Pelaksana</strong></td>
                        <td>: <span class="badge bg-info">{{ $asesmen->assignedTuk->name }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Skema</strong></td>
                        <td>:
                            {{ $asesmen->skema->name ?? '-' }}
                            @if($asesmen->skema)
                            <br><small class="text-muted">Kode: {{ $asesmen->skema->code }}</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Jenis Registrasi</strong></td>
                        <td>:
                            @if($asesmen->is_collective)
                            <span class="badge bg-primary"><i class="bi bi-people"></i> Kolektif</span>
                            <br><small class="text-muted">Batch: {{ $asesmen->collective_batch_id }}</small>
                            @if($asesmen->registrar)
                            <br><small class="text-muted">Didaftarkan oleh: {{ $asesmen->registrar->name }}</small>
                            @endif
                            @else
                            <span class="badge bg-success"><i class="bi bi-person"></i> Mandiri</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Pelatihan</strong></td>
                        <td>:
                            @if($asesmen->training_flag)
                            <span class="badge bg-info">Ya</span>
                            @else
                            <span class="badge bg-secondary">Tidak</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td>: <span class="badge bg-{{ $asesmen->status_badge }}">{{ $asesmen->status_label }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fase Pembayaran</strong></td>
                        <td>:
                            @if($asesmen->payment_phases === 'two_phase')
                            <span class="badge bg-warning">2 Fase</span>
                            @else
                            <span class="badge bg-info">Single</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Biaya Asesmen</strong></td>
                        <td>:
                            @if($asesmen->fee_amount)
                            <strong class="text-success">Rp
                                {{ number_format($asesmen->fee_amount, 0, ',', '.') }}</strong>
                            @if($asesmen->payment_phases === 'two_phase')
                            <br><small class="text-muted">
                                Fase 1: Rp {{ number_format($asesmen->phase_1_amount ?? 0, 0, ',', '.') }}
                                <br>
                                Fase 2: Rp {{ number_format($asesmen->phase_2_amount ?? 0, 0, ',', '.') }}
                            </small>
                            @endif
                            @else
                            <span class="text-muted">Belum ditetapkan</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Daftar</strong></td>
                        <td>: {{ $asesmen->registration_date->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Info -->
        @if($asesmen->payments && $asesmen->payments->count() > 0)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Riwayat Pembayaran</h6>
            </div>
            <div class="card-body">
                @foreach($asesmen->payments as $payment)
                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>
                                @if($payment->payment_phase === 'phase_1')
                                <i class="bi bi-1-circle"></i> Fase 1
                                @elseif($payment->payment_phase === 'phase_2')
                                <i class="bi bi-2-circle"></i> Fase 2
                                @else
                                <i class="bi bi-cash"></i> Pembayaran Penuh
                                @endif
                            </strong>
                        </div>
                        <div>
                            @if($payment->status === 'verified')
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                            @elseif($payment->status === 'pending')
                            <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>
                            @endif
                        </div>
                    </div>
                    <table class="table table-sm table-borderless mt-2">
                        <tr>
                            <td width="150">Jumlah</td>
                            <td>: <strong class="text-success">Rp
                                    {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td>Metode</td>
                            <td>: {{ strtoupper($payment->payment_method ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td>Order ID</td>
                            <td>: <code>{{ $payment->order_id ?? '-' }}</code></td>
                        </tr>
                        @if($payment->verified_at)
                        <tr>
                            <td>Tanggal</td>
                            <td>: {{ $payment->verified_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
                @endforeach
            </div>
        </div>
        @elseif($asesmen->payment)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-credit-card"></i> Informasi Pembayaran</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Status</strong></td>
                        <td>:
                            @if($asesmen->payment->status === 'verified')
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Terverifikasi</span>
                            @elseif($asesmen->payment->status === 'pending')
                            <span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Jumlah</strong></td>
                        <td>: <strong class="text-success">Rp
                                {{ number_format($asesmen->payment->amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Metode</strong></td>
                        <td>: {{ strtoupper($asesmen->payment->payment_method ?? '-') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Order ID</strong></td>
                        <td>: <code>{{ $asesmen->payment->order_id ?? '-' }}</code></td>
                    </tr>
                    @if($asesmen->payment->verified_at)
                    <tr>
                        <td><strong>Tanggal Bayar</strong></td>
                        <td>: {{ $asesmen->payment->verified_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif

        <!-- Schedule Info -->
        @if($asesmen->schedule)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-calendar-event"></i> Jadwal Asesmen</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Tanggal</strong></td>
                        <td>: <strong>{{ $asesmen->schedule->assessment_date->format('d/m/Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Waktu</strong></td>
                        <td>: {{ $asesmen->schedule->start_time }} - {{ $asesmen->schedule->end_time }} WIB</td>
                    </tr>
                    <tr>
                        <td><strong>Lokasi</strong></td>
                        <td>: {{ $asesmen->schedule->location }}</td>
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
        @if($asesmen->result)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clipboard-check"></i> Hasil Asesmen</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>Hasil</strong></td>
                        <td>:
                            @if($asesmen->result === 'kompeten')
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> KOMPETEN</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> BELUM KOMPETEN</span>
                            @endif
                        </td>
                    </tr>
                    @if($asesmen->assessed_at)
                    <tr>
                        <td><strong>Tanggal</strong></td>
                        <td>: {{ \Carbon\Carbon::parse($asesmen->assessed_at)->format('d/m/Y H:i') }}</td>
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

        <!-- Certificate Info -->
        @if($asesmen->certificate)
        <div class="card mb-3 border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-award-fill"></i> Sertifikat</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>No. Sertifikat</strong></td>
                        <td>: <strong>{{ $asesmen->certificate->certificate_number }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Terbit</strong></td>
                        <td>: {{ $asesmen->certificate->issued_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Berlaku Sampai</strong></td>
                        <td>: {{ $asesmen->certificate->valid_until->format('d/m/Y') }}</td>
                    </tr>
                    @if($asesmen->certificate->pdf_path)
                    <tr>
                        <td colspan="2" class="pt-2">
                            <a href="{{ Storage::url($asesmen->certificate->pdf_path) }}"
                                class="btn btn-sm btn-success w-100" target="_blank">
                                <i class="bi bi-download"></i> Download Sertifikat
                            </a>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Timeline / Activity Log -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Aktivitas</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @if($asesmen->certificate)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-award"></i> Sertifikat Diterbitkan</strong>
                            <br><small
                                class="text-muted">{{ $asesmen->certificate->issued_date->format('d/m/Y H:i') }}</small>
                            <br><span class="badge bg-success">No:
                                {{ $asesmen->certificate->certificate_number }}</span>
                        </div>
                    </div>
                    @endif

                    @if($asesmen->assessed_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-clipboard-check"></i> Asesmen Selesai</strong>
                            <br><small
                                class="text-muted">{{ \Carbon\Carbon::parse($asesmen->assessed_at)->format('d/m/Y H:i') }}</small>
                            @if($asesmen->result)
                            <br><span class="badge bg-{{ $asesmen->result === 'kompeten' ? 'success' : 'danger' }}">
                                {{ strtoupper($asesmen->result) }}
                            </span>
                            @endif
                            @if($asesmen->assessor)
                            <br><small class="text-muted">oleh: {{ $asesmen->assessor->name }}</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($asesmen->schedule)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-calendar-event"></i> Jadwal Ditentukan</strong>
                            <br><small
                                class="text-muted">{{ $asesmen->schedule->created_at->format('d/m/Y H:i') }}</small>
                            <br><span
                                class="badge bg-info">{{ $asesmen->schedule->assessment_date->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    @endif

                    @if($asesmen->payments && $asesmen->payments->count() > 0)
                    @foreach($asesmen->payments->sortBy('verified_at') as $payment)
                    @if($payment->verified_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-credit-card"></i> Pembayaran
                                @if($payment->payment_phase === 'phase_1')
                                Fase 1
                                @elseif($payment->payment_phase === 'phase_2')
                                Fase 2
                                @else
                                Penuh
                                @endif
                            </strong>
                            <br><small class="text-muted">{{ $payment->verified_at->format('d/m/Y H:i') }}</small>
                            <br><span class="badge bg-success">Rp
                                {{ number_format($payment->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @elseif($asesmen->payment && $asesmen->payment->verified_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-credit-card"></i> Pembayaran Terverifikasi</strong>
                            <br><small
                                class="text-muted">{{ $asesmen->payment->verified_at->format('d/m/Y H:i') }}</small>
                            <br><span class="badge bg-success">Rp
                                {{ number_format($asesmen->payment->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endif

                    @if($asesmen->assigned_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-building-check"></i> Di-assign ke TUK</strong>
                            <br><small
                                class="text-muted">{{ \Carbon\Carbon::parse($asesmen->assigned_at)->format('d/m/Y H:i') }}</small>
                            @if($asesmen->assignedTuk)
                            <br><span class="badge bg-info">{{ $asesmen->assignedTuk->name }}</span>
                            @endif
                            @if($asesmen->assigner)
                            <br><small class="text-muted">oleh: {{ $asesmen->assigner->name }}</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($asesmen->tuk_verified_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-building-check"></i> Diverifikasi TUK</strong>
                            <br><small
                                class="text-muted">{{ \Carbon\Carbon::parse($asesmen->tuk_verified_at)->format('d/m/Y H:i') }}</small>
                            @if($asesmen->tukVerifier)
                            <br><span class="badge bg-primary">{{ $asesmen->tukVerifier->name }}</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($asesmen->admin_verified_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-shield-check"></i> Diverifikasi Admin</strong>
                            <br><small
                                class="text-muted">{{ \Carbon\Carbon::parse($asesmen->admin_verified_at)->format('d/m/Y H:i') }}</small>
                            @if($asesmen->assessorRegistrar)
                            <br><span class="badge bg-warning">{{ $asesmen->assessorRegistrar->name }}</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="timeline-item">
                        <div class="timeline-marker bg-secondary"></div>
                        <div class="timeline-content">
                            <strong><i class="bi bi-person-plus"></i> Pendaftaran</strong>
                            <br><small class="text-muted">{{ $asesmen->registration_date->format('d/m/Y H:i') }}</small>
                            @if($asesmen->is_collective)
                            <br><span class="badge bg-primary">Kolektif - {{ $asesmen->collective_batch_id }}</span>
                            @if($asesmen->registrar)
                            <br><small class="text-muted">oleh: {{ $asesmen->registrar->name }}</small>
                            @endif
                            @else
                            <br><span class="badge bg-success">Mandiri</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    padding-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 9px;
    top: 20px;
    height: calc(100% - 10px);
    width: 2px;
    background: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 5px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    padding: 5px 0;
}

.timeline-content strong {
    color: #495057;
    font-size: 14px;
}

.timeline-content small {
    font-size: 12px;
}

.timeline-content .badge {
    margin-top: 5px;
}
</style>