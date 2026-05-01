{{-- resources/views/manajer-sertifikasi/frak03/index.blade.php --}}
@extends('layouts.manajer-sertifikasi')

@section('title', 'FR.AK.03 - Monitoring Umpan Balik')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0">FR.AK.03 — Umpan Balik Asesmen</h5>
            <p class="text-muted small mb-0">Monitoring status pengisian umpan balik per jadwal</p>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Jadwal</th>
                            <th>Skema</th>
                            <th>TUK</th>
                            <th>Asesor</th>
                            <th class="text-center">Total Asesi</th>
                            <th class="text-center">Sudah Isi</th>
                            <th class="text-center">Progress</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td class="align-middle">
                                    <span class="fw-semibold">{{ $schedule->assessment_date?->translatedFormat('d M Y') ?? '-' }}</span>
                                    <br><small class="text-muted">{{ $schedule->start_time }} - {{ $schedule->end_time }}</small>
                                </td>
                                <td class="align-middle small">{{ $schedule->skema->name ?? '-' }}</td>
                                <td class="align-middle small">{{ $schedule->tuk->name ?? '-' }}</td>
                                <td class="align-middle small">{{ $schedule->asesor->nama ?? '-' }}</td>
                                <td class="text-center align-middle">{{ $schedule->frak03_total }}</td>
                                <td class="text-center align-middle">
                                    <span class="badge {{ $schedule->frak03_submitted === $schedule->frak03_total && $schedule->frak03_total > 0 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $schedule->frak03_submitted }}
                                    </span>
                                </td>
                                <td class="align-middle" style="min-width:120px">
                                    <div class="progress" style="height:8px">
                                        <div
                                            class="progress-bar {{ $schedule->frak03_pct === 100 ? 'bg-success' : 'bg-primary' }}"
                                            style="width: {{ $schedule->frak03_pct }}%"
                                        ></div>
                                    </div>
                                    <small class="text-muted">{{ $schedule->frak03_pct }}%</small>
                                </td>
                                <td class="text-center align-middle">
                                    <a href="{{ route('manajer-sertifikasi.frak03.detail', $schedule) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada jadwal asesmen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($schedules->hasPages())
            <div class="card-footer">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>

</div>
@endsection