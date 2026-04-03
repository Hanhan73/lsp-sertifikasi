@extends('layouts.app')
@section('title', 'Dokumen SK')
@section('page-title', 'Dokumen SK')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

@php
    $skValid      = $asesor->sk_pengangkatan_valid_until;
    $skExpired    = $skValid && $skValid->isPast();
    $skNearExpiry = $skValid && !$skExpired && $skValid->diffInDays(now()) < 90;
    $hasSk        = (bool) $asesor->sk_pengangkatan_path;
@endphp

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ── KIRI: SK Pengangkatan ── --}}
    <div class="col-lg-5">

        {{-- Profil --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body d-flex gap-3 align-items-center">
                <img src="{{ $asesor->foto_url }}" class="rounded-circle border flex-shrink-0"
                     style="width:56px;height:56px;object-fit:cover;" alt="foto">
                <div>
                    <div class="fw-bold">{{ $asesor->nama }}</div>
                    <div class="text-muted small">No. Reg: <code>{{ $asesor->no_reg_met ?? '-' }}</code></div>
                    <span class="badge bg-{{ $asesor->status_badge }} mt-1">{{ $asesor->status_label }}</span>
                </div>
            </div>
        </div>

        {{-- SK Pengangkatan card --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-check text-primary"></i>SK Pengangkatan Asesor
                @if($hasSk)
                    @if($skExpired) <span class="badge bg-danger ms-auto">Expired</span>
                    @elseif($skNearExpiry) <span class="badge bg-warning text-dark ms-auto">Segera Expire</span>
                    @else <span class="badge bg-success ms-auto">Aktif</span>
                    @endif
                @else
                    <span class="badge bg-secondary ms-auto">Belum Ada</span>
                @endif
            </div>
            <div class="card-body">

                @if($hasSk)
                {{-- Alert status --}}
                @if($skExpired)
                <div class="alert alert-danger border-0 py-2 mb-3 small">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    SK kadaluarsa sejak {{ $skValid->translatedFormat('d M Y') }}. Upload SK baru.
                </div>
                @elseif($skNearExpiry)
                <div class="alert alert-warning border-0 py-2 mb-3 small">
                    <i class="bi bi-clock-fill me-1"></i>
                    Akan habis dalam {{ $skValid->diffInDays(now()) }} hari.
                </div>
                @endif

                {{-- Info SK --}}
                <table class="table table-sm table-borderless mb-3">
                    <tr>
                        <td class="text-muted" style="width:130px;">Nomor SK</td>
                        <td class="fw-semibold font-monospace" style="font-size:.85rem;">{{ $asesor->sk_pengangkatan_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal SK</td>
                        <td>{{ $asesor->sk_pengangkatan_date?->translatedFormat('d M Y') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Berlaku Hingga</td>
                        <td class="{{ $skExpired ? 'text-danger fw-bold' : '' }}">
                            {{ $skValid ? $skValid->translatedFormat('d M Y') : 'Tidak ditentukan' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">File</td>
                        <td class="text-muted small">{{ $asesor->sk_pengangkatan_filename }}</td>
                    </tr>
                </table>

                <div class="d-flex gap-2 mb-4">
                    <a href="{{ route('asesor.sk.download') }}" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="bi bi-download me-1"></i>Download SK
                    </a>
                    <form action="{{ route('asesor.sk.delete') }}" method="POST"
                          onsubmit="return confirm('Hapus SK ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus SK">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>

                <hr class="my-3">
                <p class="fw-semibold small text-muted mb-2">Perbarui SK:</p>
                @endif

                {{-- Form upload --}}
                <form action="{{ route('asesor.sk.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nomor SK <span class="text-danger">*</span></label>
                        <input type="text" name="sk_number"
                               class="form-control form-control-sm @error('sk_number') is-invalid @enderror"
                               placeholder="cth: 001/SK/LSP-KAP/2024"
                               value="{{ old('sk_number') }}" required>
                        @error('sk_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Tanggal SK <span class="text-danger">*</span></label>
                        <input type="date" name="sk_date"
                               class="form-control form-control-sm @error('sk_date') is-invalid @enderror"
                               value="{{ old('sk_date') }}" required>
                        @error('sk_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Berlaku Hingga</label>
                        <input type="date" name="sk_valid_until"
                               class="form-control form-control-sm @error('sk_valid_until') is-invalid @enderror"
                               value="{{ old('sk_valid_until') }}">
                        <div class="form-text">Kosongkan jika tidak ada batas waktu</div>
                        @error('sk_valid_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            File SK (PDF) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="sk_file" accept=".pdf"
                               class="form-control form-control-sm @error('sk_file') is-invalid @enderror"
                               {{ $hasSk ? '' : 'required' }}>
                        <div class="form-text">Format PDF, maks. 5 MB</div>
                        @error('sk_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i>{{ $hasSk ? 'Perbarui SK' : 'Upload SK' }}
                    </button>
                </form>

            </div>
        </div>
    </div>

    {{-- ── KANAN: Riwayat SK Penugasan ── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>Riwayat SK Penugasan
                <span class="badge bg-secondary ms-auto">{{ $schedules->count() }} jadwal</span>
            </div>
            @if($schedules->isEmpty())
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-calendar-x" style="font-size:2.5rem;opacity:.3;"></i>
                <p class="mt-2 mb-0 small">Belum ada riwayat penugasan asesmen.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Skema / No. SK</th>
                            <th>TUK</th>
                            <th class="text-center">Peserta</th>
                            <th class="text-center">Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold small">{{ $schedule->assessment_date->translatedFormat('d M Y') }}</div>
                                <div class="text-muted" style="font-size:.73rem;">
                                    {{ $schedule->start_time }} – {{ $schedule->end_time }}
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-truncate" style="max-width:160px;">
                                    {{ $schedule->skema->name ?? '-' }}
                                </div>
                                @if($schedule->sk_number)
                                <div class="text-muted font-monospace" style="font-size:.7rem;">
                                    {{ $schedule->sk_number }}
                                </div>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $schedule->tuk->name ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border" style="font-size:.72rem;">
                                    {{ $schedule->asesmens_count }} asesi
                                </span>
                            </td>
                            <td class="text-center">
                                @if($schedule->hasSk())
                                <a href="{{ route('asesor.schedule.sk.download', $schedule) }}"
                                   class="btn btn-sm btn-outline-success" style="font-size:.72rem;padding:2px 10px;">
                                    <i class="bi bi-download me-1"></i>SK
                                </a>
                                @else
                                <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>

@endsection