{{-- resources/views/admin/skemas/partials/unit-card.blade.php --}}
<div class="card unit-card mb-3" id="unit-{{ $unit->id }}">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2" style="cursor:pointer"
        data-bs-toggle="collapse" data-bs-target="#unit-body-{{ $unit->id }}">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-chevron-down text-muted" style="font-size:.8rem; transition:.2s"></i>
            <div>
                <strong>Unit {{ $unit->urutan }}</strong>
                @if($unit->kode_unit)
                <code class="ms-2" style="font-size:.78rem;">{{ $unit->kode_unit }}</code>
                @endif
                <span class="ms-2">{{ $unit->judul_unit }}</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3" onclick="event.stopPropagation()">
            <small class="text-muted">
                {{ $unit->elemens->count() }} elemen &bull;
                {{ $unit->elemens->sum(fn($e) => $e->kuks->count()) }} KUK
            </small>
            <button class="btn btn-sm btn-outline-success btn-icon" title="Tambah Elemen"
                onclick="showAddElemen({{ $unit->id }})">
                <i class="bi bi-plus"></i> Elemen
            </button>
            <button class="btn btn-sm btn-outline-warning btn-icon" title="Edit Unit"
                onclick="editUnit({{ $unit->id }}, '{{ addslashes($unit->kode_unit) }}', '{{ addslashes($unit->judul_unit) }}', '{{ addslashes($unit->standar_kompetensi) }}')">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger btn-icon" title="Hapus Unit"
                onclick="deleteUnit({{ $unit->id }}, '{{ addslashes($unit->judul_unit) }}')">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    <div class="collapse show" id="unit-body-{{ $unit->id }}">
        <div class="card-body py-2">
            @forelse($unit->elemens as $elemen)
            <div class="elemen-item">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold text-dark" style="font-size:.875rem;">
                        <span class="text-primary me-1">{{ $elemen->urutan }}.</span>
                        {{ $elemen->judul }}
                    </span>
                    <div class="d-flex gap-1" style="flex-shrink:0">
                        <button class="btn btn-xs btn-outline-info btn-icon" title="Tambah KUK"
                            onclick="showAddKuk({{ $elemen->id }})" style="font-size:.75rem; padding:.15rem .4rem;">
                            <i class="bi bi-plus"></i> KUK
                        </button>
                        <button class="btn btn-xs btn-outline-warning btn-icon" title="Edit Elemen"
                            onclick="editElemen({{ $elemen->id }}, '{{ addslashes($elemen->judul) }}', `{{ addslashes($elemen->hint_bukti ?? '') }}`)"
                            style="font-size:.75rem; padding:.15rem .4rem;">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-outline-danger btn-icon" title="Hapus Elemen"
                            onclick="deleteElemen({{ $elemen->id }}, '{{ addslashes($elemen->judul) }}')"
                            style="font-size:.75rem; padding:.15rem .4rem;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                {{-- KUK list --}}
                @forelse($elemen->kuks as $kuk)
                <div class="kuk-item">
                    <span class="kuk-kode">{{ $kuk->kode }}.</span>
                    <span class="kuk-text text-muted">{{ $kuk->deskripsi }}</span>
                    <div class="d-flex gap-1 ms-auto" style="flex-shrink:0">
                        <button class="btn btn-xs btn-outline-warning btn-icon" title="Edit KUK"
                            onclick="editKuk({{ $kuk->id }}, `{{ addslashes($kuk->deskripsi) }}`)"
                            style="font-size:.7rem; padding:.1rem .3rem;">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-outline-danger btn-icon" title="Hapus KUK"
                            onclick="deleteKuk({{ $kuk->id }})" style="font-size:.7rem; padding:.1rem .3rem;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                @empty
                <div class="text-muted" style="font-size:.8rem; padding:.3rem .5rem;">
                    <i class="bi bi-dash"></i> Belum ada KUK
                </div>
                @endforelse
            </div>
            @empty
            <div class="text-center py-3 text-muted">
                <i class="bi bi-inbox"></i> Belum ada elemen — klik "+ Elemen" untuk menambah
            </div>
            @endforelse
        </div>
    </div>
</div>