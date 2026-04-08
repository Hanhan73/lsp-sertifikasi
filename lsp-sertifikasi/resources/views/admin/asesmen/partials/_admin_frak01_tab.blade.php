@php $ak01 = $asesmen->frak01; @endphp
 
@if(!$ak01)
<div class="text-center py-5 text-muted">
    <i class="bi bi-hourglass-split fs-1 opacity-25"></i>
    <p class="mt-2">FR.AK.01 belum dibuat oleh asesi.</p>
</div>
 
@else
 
{{-- Alert jika returned --}}
@if($ak01->status === 'returned' && $ak01->rejection_notes)
<div class="alert alert-danger border-0 shadow-sm d-flex gap-3 align-items-start mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0 mt-1"></i>
    <div>
        <div class="fw-semibold mb-1">Dikembalikan ke Asesi</div>
        <div class="small">{{ $ak01->rejection_notes }}</div>
        <div class="text-muted small mt-1">
            {{ $ak01->returned_at?->translatedFormat('d M Y H:i') }}
        </div>
    </div>
</div>
@endif
 
{{-- Status bar --}}
<div class="d-flex align-items-center gap-3 mb-4 p-3 rounded
    {{ $ak01->status === 'verified' ? 'status-bar-verified' : ($ak01->status === 'submitted' ? 'status-bar-submitted' : 'status-bar-default') }}
    flex-wrap">
    <span class="badge bg-{{ $ak01->status_badge }} fs-6">{{ $ak01->status_label }}</span>
    @if($ak01->submitted_at)
    <span class="small text-muted">Submit: {{ $ak01->submitted_at->translatedFormat('d M Y H:i') }}</span>
    @endif
 
    <div class="ms-auto d-flex gap-2 flex-wrap">
        @if(in_array($ak01->status, ['verified','approved']))
        <a href="{{ route('admin.frak01.pdf', [$ak01, 'preview' => 1]) }}" target="_blank"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-pdf me-1"></i>Preview PDF
        </a>
        <a href="{{ route('admin.frak01.pdf', $ak01) }}" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i>Download PDF
        </a>
        @endif
    </div>
</div>
 
<div class="row g-4">
    <div class="col-md-6">
        <div class="section-heading">Info Dokumen</div>
        <div class="info-row"><span class="info-label">Skema</span><span class="info-value">{{ $ak01->skema_judul }}</span></div>
        <div class="info-row"><span class="info-label">TUK</span><span class="info-value">{{ $ak01->tuk_nama }}</span></div>
        <div class="info-row"><span class="info-label">Asesor</span><span class="info-value">{{ $ak01->nama_asesor }}</span></div>
        <div class="info-row"><span class="info-label">Hari / Tanggal</span><span class="info-value">{{ $ak01->hari_tanggal }}</span></div>
        <div class="info-row"><span class="info-label">Waktu</span><span class="info-value">{{ $ak01->waktu_asesmen ?? '-' }}</span></div>
 
        <div class="section-heading mt-4">Bukti yang Dikumpulkan</div>
        @foreach([
            'bukti_verifikasi_portofolio'     => 'Verifikasi Portofolio',
            'bukti_hasil_review_produk'        => 'Review Produk',
            'bukti_observasi_langsung'         => 'Observasi Langsung',
            'bukti_pertanyaan_lisan'           => 'Pertanyaan Lisan',
            'bukti_pertanyaan_tertulis'        => 'Pertanyaan Tertulis',
            'bukti_pertanyaan_wawancara'       => 'Wawancara',
            'bukti_hasil_kegiatan_terstruktur' => 'Kegiatan Terstruktur',
        ] as $field => $lbl)
        <div class="d-flex align-items-center gap-2 py-1 border-bottom">
            <i class="bi bi-{{ $ak01->$field ? 'check-circle-fill text-success' : 'circle text-muted' }}"></i>
            <span class="small {{ $ak01->$field ? '' : 'text-muted' }}">{{ $lbl }}</span>
        </div>
        @endforeach
        @if($ak01->bukti_lainnya)
        <div class="d-flex align-items-center gap-2 py-1">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span class="small">Lainnya: {{ $ak01->bukti_lainnya_keterangan }}</span>
        </div>
        @endif
    </div>
 
    <div class="col-md-6">
        @if($ak01->ttd_asesi)
        <div class="section-heading">TTD Asesi</div>
        <div class="d-flex align-items-center gap-3 mb-4">
            <img src="{{ $ak01->ttd_asesi_image }}" class="ttd-thumb">
            <div class="small text-muted">
                <div class="fw-semibold text-dark">{{ $ak01->nama_ttd_asesi }}</div>
                {{ $ak01->tanggal_ttd_asesi?->translatedFormat('d M Y') }}
            </div>
        </div>
        @else
        <div class="text-muted small mb-4">
            <i class="bi bi-circle text-muted me-1"></i>
            @if($ak01->status === 'returned')
                TTD direset — menunggu asesi tanda tangan ulang.
            @else
                Belum ada tanda tangan asesi.
            @endif
        </div>
        @endif
 
        @if($ak01->ttd_asesor)
        <div class="section-heading">TTD Asesor</div>
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $ak01->ttd_asesor_image }}" class="ttd-thumb">
            <div class="small text-muted">
                <div class="fw-semibold text-dark">{{ $ak01->nama_ttd_asesor }}</div>
                {{ $ak01->tanggal_ttd_asesor?->translatedFormat('d M Y') }}
            </div>
        </div>
        @else
        <div class="text-center py-4 text-muted">
            <i class="bi bi-lock fs-2 opacity-25"></i>
            <p class="small mt-1">Menunggu tanda tangan asesor.</p>
        </div>
        @endif
    </div>
</div>
 
@endif

 
@push('scripts')
<script>
</script>
@endpush