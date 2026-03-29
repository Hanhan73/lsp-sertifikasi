{{-- direktur/schedules/_card.blade.php --}}
@php
    $statusClass = match($schedule->approval_status) {
        'approved' => 'approved',
        'rejected' => 'rejected',
        default    => '',
    };
@endphp

<div class="sched-card {{ $statusClass }}">
    <div class="sched-card-header">
        {{-- Date chip --}}
        <div class="date-chip">
            <div class="day">{{ $schedule->assessment_date->format('d') }}</div>
            <div class="month">{{ $schedule->assessment_date->format('M Y') }}</div>
        </div>

        {{-- Info --}}
        <div class="flex-grow-1">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div>
                    <div class="fw-semibold">{{ $schedule->skema?->name ?? '-' }}</div>
                    <div class="text-muted small">
                        <i class="bi bi-building me-1"></i>{{ $schedule->tuk?->name ?? '-' }}
                        &nbsp;&bull;&nbsp;
                        <i class="bi bi-geo-alt me-1"></i>{{ $schedule->location }}
                        &nbsp;&bull;&nbsp;
                        <i class="bi bi-clock me-1"></i>{{ $schedule->start_time }} – {{ $schedule->end_time }}
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-people me-1"></i>{{ $schedule->asesmens->count() }} peserta
                        @if($schedule->asesor)
                        &nbsp;&bull;&nbsp;
                        <i class="bi bi-person-badge me-1"></i>{{ $schedule->asesor->nama }}
                        @else
                        &nbsp;&bull;&nbsp;
                        <span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Asesor belum ditugaskan</span>
                        @endif
                    </div>
                    <div class="text-muted" style="font-size:.72rem;margin-top:4px;">
                        Dibuat oleh {{ $schedule->creator?->name ?? '-' }} pada {{ $schedule->created_at->format('d M Y H:i') }}
                    </div>
                </div>

                <div class="d-flex flex-column align-items-end gap-2">
                    <span class="badge bg-{{ $schedule->approval_status_badge }}">
                        {{ $schedule->approval_status_label }}
                    </span>

                    @if($schedule->isApproved() && $schedule->sk_number)
                    <span class="badge bg-light text-dark border" style="font-size:.65rem;font-family:monospace;">
                        {{ $schedule->sk_number }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Rejection note --}}
    @if($schedule->isRejected() && $schedule->approval_notes)
    <div class="rejection-note">
        <i class="bi bi-x-circle-fill me-1"></i>
        <strong>Alasan Penolakan:</strong> {{ $schedule->approval_notes }}
    </div>
    @endif

    {{-- Approval note --}}
    @if($schedule->isApproved() && $schedule->approval_notes)
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:8px 14px;font-size:.82rem;color:#166534;margin:10px 16px 0;">
        <i class="bi bi-check-circle-fill me-1"></i>{{ $schedule->approval_notes }}
    </div>
    @endif

    {{-- Catatan/notes --}}
    @if($schedule->notes)
    <div style="padding:8px 16px;font-size:.82rem;color:#64748b;">
        <i class="bi bi-sticky me-1"></i>{{ $schedule->notes }}
    </div>
    @endif

    {{-- Actions --}}
    <div style="padding:10px 16px;display:flex;gap:8px;align-items:center;border-top:1px solid #f1f5f9;margin-top:8px;">
        <a href="{{ route('direktur.schedules.show', $schedule) }}"
           class="btn btn-sm btn-outline-primary">
            <i class="bi bi-eye me-1"></i>Lihat Detail
        </a>

        @if($showActions && $schedule->isPendingApproval())
        <button class="btn btn-sm btn-success"
                onclick="approveSchedule({{ $schedule->id }}, {{ $schedule->asesmens->count() }}, '{{ addslashes($schedule->skema?->name ?? '') }}')">
            <i class="bi bi-check-lg me-1"></i>Setujui
        </button>
        <button class="btn btn-sm btn-danger"
                onclick="rejectSchedule({{ $schedule->id }})">
            <i class="bi bi-x-lg me-1"></i>Tolak
        </button>
        @endif

        @if($schedule->isApproved() && $schedule->hasSk())
        <a href="{{ route('direktur.schedules.sk.download', $schedule) }}"
           class="btn btn-sm btn-outline-secondary ms-auto">
            <i class="bi bi-download me-1"></i>Unduh SK
        </a>
        @endif
    </div>
</div>

@once
@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

async function approveSchedule(id, count, skema) {
    const result = await Swal.fire({
        title: 'Setujui Jadwal?',
        html: `<div class="text-start small">
            <div class="p-3 rounded bg-light mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Skema</span><strong>${skema}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Peserta</span><strong>${count} orang</strong>
                </div>
            </div>
            <div class="mb-2 fw-semibold">Catatan (opsional):</div>
            <textarea id="approval-notes" class="form-control form-control-sm" rows="2"
                      placeholder="Catatan untuk admin (opsional)..."></textarea>
            <div class="alert alert-info py-2 mt-3 mb-0 small">
                <i class="bi bi-info-circle me-1"></i>
                SK Asesmen akan otomatis di-generate dan status ${count} asesi berubah ke <strong>Terjadwal</strong>.
            </div>
        </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i>Ya, Setujui',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#16a34a',
        reverseButtons: true,
        didOpen: () => {
            document.getElementById('approval-notes').addEventListener('click', e => e.stopPropagation());
        },
        preConfirm: () => document.getElementById('approval-notes')?.value ?? '',
    });

    if (!result.isConfirmed) return;

    try {
        const res  = await fetch(`/direktur/schedules/${id}/approve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ notes: result.value }),
        });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Disetujui!', text: data.message, timer: 2500, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}

async function rejectSchedule(id) {
    const result = await Swal.fire({
        title: 'Tolak Jadwal?',
        html: `<div class="text-start">
            <p class="text-muted small mb-2">Admin akan diberitahu untuk melakukan perbaikan.</p>
            <label class="form-label small fw-semibold">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea id="rejection-notes" class="form-control" rows="3"
                      placeholder="Contoh: Terlalu banyak peserta untuk 1 asesor, atau asesor bentrok jadwal di TUK lain..."></textarea>
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-x-lg me-1"></i>Ya, Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        reverseButtons: true,
        preConfirm: () => {
            const val = document.getElementById('rejection-notes')?.value?.trim() ?? '';
            if (val.length < 10) {
                Swal.showValidationMessage('Alasan penolakan minimal 10 karakter.');
                return false;
            }
            return val;
        },
    });

    if (!result.isConfirmed) return;

    try {
        const res  = await fetch(`/direktur/schedules/${id}/reject`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' },
            body: JSON.stringify({ rejection_notes: result.value }),
        });
        const data = await res.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Ditolak', text: data.message, timer: 2000, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch {
        Swal.fire('Error', 'Terjadi kesalahan.', 'error');
    }
}
</script>
@endpush
@endonce