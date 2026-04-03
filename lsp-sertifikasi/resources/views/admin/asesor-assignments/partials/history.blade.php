{{-- resources/views/admin/asesor-assignments/partials/history.blade.php --}}

@if($schedule->assignmentHistories->isEmpty())
<div class="text-center py-4 text-muted">
    <i class="bi bi-inbox" style="font-size:3rem;"></i>
    <p class="mt-2">Belum ada riwayat penugasan</p>
</div>
@else
<div class="timeline">
    @foreach($schedule->assignmentHistories as $history)
    <div class="timeline-item">
        <div class="timeline-badge {{ $history->action_badge }}"></div>
        <div class="card mb-2">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-{{ $history->action_badge }}">{{ $history->action_label }}</span>
                    <small class="text-muted">{{ $history->action_at->diffForHumans() }}</small>
                </div>
                
                @if($history->asesor)
                <div class="d-flex align-items-center gap-2 mb-2">
                    <img src="{{ $history->asesor->foto_url }}" alt="Foto"
                        style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <div>
                        <strong>{{ $history->asesor->nama }}</strong><br>
                        <small class="text-muted">{{ $history->asesor->no_reg_met ?? '-' }}</small>
                    </div>
                </div>
                @endif

                @if($history->notes)
                <div class="alert alert-light py-2 mb-2">
                    <small><i class="bi bi-chat-left-text"></i> {{ $history->notes }}</small>
                </div>
                @endif

                <small class="text-muted">
                    <i class="bi bi-person"></i> oleh {{ $history->assignedBy->name }} • 
                    {{ $history->action_at->translatedFormat('d M Y H:i') }}
                </small>
            </div>
        </div>
    </div>
    @endforeach
</div>

<style>
.timeline { position: relative; padding: 10px 0; }
.timeline-item { position: relative; padding-left: 50px; padding-bottom: 10px; }
.timeline-item::before {
    content: ''; position: absolute; left: 15px; top: 15px; bottom: -10px;
    width: 2px; background: #dee2e6;
}
.timeline-item:last-child::before { display: none; }
.timeline-badge {
    position: absolute; left: 8px; top: 8px;
    width: 16px; height: 16px; border-radius: 50%;
    border: 3px solid white; z-index: 1;
}
.timeline-badge.success { background: #10b981; }
.timeline-badge.warning { background: #f59e0b; }
.timeline-badge.danger  { background: #ef4444; }
</style>
@endif