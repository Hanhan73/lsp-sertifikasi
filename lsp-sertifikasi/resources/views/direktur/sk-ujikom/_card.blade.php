{{-- direktur/sk-ujikom/_card.blade.php --}}
@php
    $sk    = $item['sk'];
    $tuk   = $item['tuk'];
    $skema = $item['skema'];
@endphp

<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center g-3">

                <div class="col-lg-5">
                    <div class="fw-semibold mb-1">{{ $skema?->name ?? '-' }}</div>
                    <div class="text-muted small mb-1">
                        <i class="bi bi-building me-1"></i>{{ $tuk?->name ?? '-' }}
                        &nbsp;·&nbsp;
                        <code style="font-size:.75rem;">{{ $sk->collective_batch_id }}</code>
                    </div>
                    <div class="small">
                        <span class="text-success fw-semibold">
                            <i class="bi bi-check-circle me-1"></i>{{ $item['total_k'] }} Peserta Kompeten
                        </span>
                        &nbsp;·&nbsp;
                        <span class="text-muted">
                            Diajukan {{ $sk->submitted_at?->translatedFormat('d M Y') ?? '-' }}
                        </span>
                    </div>
                    @if($sk->nomor_sk)
                    <div class="small text-muted mt-1 font-monospace">{{ $sk->nomor_sk }}</div>
                    @endif
                </div>

                <div class="col-lg-3">
                    <span class="badge bg-{{ $sk->status_badge }} px-3 py-2">{{ $sk->status_label }}</span>
                    @if($sk->isRejected())
                    <div class="small text-danger mt-1">{{ Str::limit($sk->catatan_direktur, 60) }}</div>
                    @endif
                    @if($sk->isApproved())
                    <div class="small text-muted mt-1">
                        {{ $sk->approved_at?->translatedFormat('d M Y') }}
                    </div>
                    @endif
                </div>

                <div class="col-lg-4 d-flex justify-content-end gap-2 flex-wrap">
                    <a href="{{ route('direktur.sk-ujikom.show', $sk) }}"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>Review
                    </a>
                    @if($sk->isApproved() && $sk->hasSk())
                    <a href="{{ route('direktur.sk-ujikom.download', $sk) }}"
                       class="btn btn-success btn-sm">
                        <i class="bi bi-download me-1"></i>Unduh SK
                    </a>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>