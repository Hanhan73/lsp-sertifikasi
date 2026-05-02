@extends('layouts.app')

@section('title', 'Tarif Honor Asesor')
@section('page-title', 'Tarif Honor Asesor per Asesi')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center gap-2">
        <i class="bi bi-currency-dollar text-success"></i>
        <span class="fw-semibold">Tarif Honor per Skema</span>
        <span class="badge bg-secondary ms-auto">{{ $skemas->count() }} Skema</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="80">Kode</th>
                        <th>Nama Skema</th>
                        <th width="100">Jenis</th>
                        <th>Variasi Tarif</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($skemas as $skema)
                    <tr>
                        <td class="small text-muted align-top pt-3">{{ $skema->code }}</td>
                        <td class="fw-semibold small align-top pt-3">{{ $skema->name }}</td>
                        <td class="align-top pt-3">
                            <span class="badge bg-{{ $skema->jenis_badge }}">{{ $skema->jenis_label }}</span>
                        </td>
                        <td>
                            {{-- Daftar tier --}}
                            <div id="tiers-{{ $skema->id }}" class="d-flex flex-wrap gap-1 mb-2">
                                @forelse($skema->honorTiers as $tier)
                                <span class="badge {{ $tier->is_default ? 'bg-success' : 'bg-secondary' }} d-flex align-items-center gap-1 tier-badge"
                                    data-id="{{ $tier->id }}"
                                    data-skema="{{ $skema->id }}"
                                    data-label="{{ $tier->label }}"
                                    data-amount="{{ $tier->amount }}"
                                    data-default="{{ $tier->is_default ? 1 : 0 }}"
                                    style="font-size:.78rem;cursor:pointer;"
                                    onclick="openEditTier(this)"
                                    title="Klik untuk edit">
                                    {{ $tier->label }}: Rp {{ number_format($tier->amount, 0, ',', '.') }}
                                    @if($tier->is_default)<i class="bi bi-star-fill ms-1" style="font-size:.65rem;"></i>@endif
                                </span>
                                @empty
                                <span class="text-muted small fst-italic" id="empty-{{ $skema->id }}">Belum ada tarif</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-center align-top pt-3">
                            <button type="button" class="btn btn-sm btn-outline-success"
                                onclick="openAddTier({{ $skema->id }}, '{{ addslashes($skema->name) }}')"
                                title="Tambah tarif baru">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Klik badge tarif untuk edit/hapus. Tarif bintang <i class="bi bi-star-fill text-success"></i> = default saat buat kwitansi.
    </div>
</div>

{{-- Modal Tambah Tier --}}
<div class="modal fade" id="modalAddTier" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Tambah Variasi Tarif</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="addTierSkemaName"></p>
                <input type="hidden" id="addTierSkemaId">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Label Tarif <span class="text-danger">*</span></label>
                    <input type="text" id="addTierLabel" class="form-control" placeholder="Cth: Standar, Senior, TUK Swasta">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nominal (Rp) <span class="text-danger">*</span></label>
                    <input type="text" id="addTierAmount" class="form-control font-monospace" inputmode="numeric" placeholder="0">
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="addTierDefault">
                    <label class="form-check-label small" for="addTierDefault">Jadikan tarif default</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveTier()">
                    <i class="bi bi-plus-lg me-1"></i>Tambah
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Tier --}}
<div class="modal fade" id="modalEditTier" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-semibold">Edit Variasi Tarif</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editTierId">
                <input type="hidden" id="editTierSkemaId">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Label Tarif <span class="text-danger">*</span></label>
                    <input type="text" id="editTierLabel" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nominal (Rp) <span class="text-danger">*</span></label>
                    <input type="text" id="editTierAmount" class="form-control font-monospace" inputmode="numeric">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="editTierDefault">
                    <label class="form-check-label small" for="editTierDefault">Jadikan tarif default</label>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="deleteTier()">
                    <i class="bi bi-trash me-1"></i>Hapus Tarif Ini
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="updateTier()">
                    <i class="bi bi-save me-1"></i>Simpan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Format input rupiah
function fmtInput(el) {
    el.addEventListener('input', () => {
        const raw = el.value.replace(/\D/g, '');
        el.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    });
}
fmtInput(document.getElementById('addTierAmount'));
fmtInput(document.getElementById('editTierAmount'));

function getRaw(el) {
    return parseInt(el.value.replace(/\D/g, '')) || 0;
}

// ── Tambah ──────────────────────────────────────────────────────────────────
function openAddTier(skemaId, skemaName) {
    document.getElementById('addTierSkemaId').value   = skemaId;
    document.getElementById('addTierSkemaName').textContent = skemaName;
    document.getElementById('addTierLabel').value     = '';
    document.getElementById('addTierAmount').value    = '';
    document.getElementById('addTierDefault').checked = false;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAddTier')).show();
}

async function saveTier() {
    const skemaId = document.getElementById('addTierSkemaId').value;
    const label   = document.getElementById('addTierLabel').value.trim();
    const amount  = getRaw(document.getElementById('addTierAmount'));

    if (!label || !amount) {
        Swal.fire({ icon: 'warning', text: 'Label dan nominal wajib diisi.', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
        return;
    }

    const res  = await fetch(`/bendahara/tarif-honor/${skemaId}/tiers`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ label, amount, is_default: document.getElementById('addTierDefault').checked ? 1 : 0 }),
    });
    const data = await res.json();

    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalAddTier')).hide();
        appendTierBadge(skemaId, data.tier);
        Swal.fire({ icon: 'success', text: 'Tarif berhasil ditambahkan.', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
    }
}

// ── Edit ─────────────────────────────────────────────────────────────────────
function openEditTier(el) {
    document.getElementById('editTierId').value       = el.dataset.id;
    document.getElementById('editTierSkemaId').value  = el.dataset.skema;
    document.getElementById('editTierLabel').value    = el.dataset.label;
    document.getElementById('editTierAmount').value   = parseInt(el.dataset.amount).toLocaleString('id-ID');
    document.getElementById('editTierDefault').checked = el.dataset.default === '1';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditTier')).show();
}

async function updateTier() {
    const tierId  = document.getElementById('editTierId').value;
    const skemaId = document.getElementById('editTierSkemaId').value;
    const label   = document.getElementById('editTierLabel').value.trim();
    const amount  = getRaw(document.getElementById('editTierAmount'));

    if (!label || !amount) {
        Swal.fire({ icon: 'warning', text: 'Label dan nominal wajib diisi.', toast: true, position: 'top-end', timer: 2500, showConfirmButton: false });
        return;
    }

    const res  = await fetch(`/bendahara/tarif-honor/${skemaId}/tiers/${tierId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ label, amount, is_default: document.getElementById('editTierDefault').checked ? 1 : 0 }),
    });
    const data = await res.json();

    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalEditTier')).hide();
        refreshTierBadge(skemaId, tierId, data.tier);
        Swal.fire({ icon: 'success', text: 'Tarif berhasil diperbarui.', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
    }
}

async function deleteTier() {
    const tierId  = document.getElementById('editTierId').value;
    const skemaId = document.getElementById('editTierSkemaId').value;

    const conf = await Swal.fire({ icon: 'warning', title: 'Hapus tarif ini?', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' });
    if (!conf.isConfirmed) return;

    const res  = await fetch(`/bendahara/tarif-honor/${skemaId}/tiers/${tierId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    const data = await res.json();

    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalEditTier')).hide();
        removeTierBadge(skemaId, tierId);
        Swal.fire({ icon: 'success', text: 'Tarif dihapus.', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
    }
}

// ── DOM helpers ──────────────────────────────────────────────────────────────
function makeBadge(skemaId, tier) {
    const span = document.createElement('span');
    span.className = `badge ${tier.is_default ? 'bg-success' : 'bg-secondary'} d-flex align-items-center gap-1 tier-badge`;
    span.dataset.id      = tier.id;
    span.dataset.skema   = skemaId;
    span.dataset.label   = tier.label;
    span.dataset.amount  = tier.amount;
    span.dataset.default = tier.is_default ? '1' : '0';
    span.style.cssText   = 'font-size:.78rem;cursor:pointer;';
    span.title           = 'Klik untuk edit';
    span.onclick         = () => openEditTier(span);
    span.innerHTML       = `${tier.label}: Rp ${parseInt(tier.amount).toLocaleString('id-ID')}${tier.is_default ? ' <i class="bi bi-star-fill ms-1" style="font-size:.65rem;"></i>' : ''}`;
    return span;
}

function appendTierBadge(skemaId, tier) {
    const container = document.getElementById(`tiers-${skemaId}`);
    // hapus "Belum ada tarif" kalau ada
    const empty = document.getElementById(`empty-${skemaId}`);
    if (empty) empty.remove();
    // kalau is_default, refresh semua badge (warna bisa berubah)
    if (tier.is_default) return reloadTiers(skemaId);
    container.appendChild(makeBadge(skemaId, tier));
}

function refreshTierBadge(skemaId, tierId, tier) {
    if (tier.is_default) return reloadTiers(skemaId);
    const el = document.querySelector(`.tier-badge[data-id="${tierId}"]`);
    if (!el) return;
    const fresh = makeBadge(skemaId, tier);
    el.replaceWith(fresh);
}

function removeTierBadge(skemaId, tierId) {
    const el = document.querySelector(`.tier-badge[data-id="${tierId}"]`);
    if (el) el.remove();
    const container = document.getElementById(`tiers-${skemaId}`);
    if (!container.querySelector('.tier-badge')) {
        container.innerHTML = `<span class="text-muted small fst-italic" id="empty-${skemaId}">Belum ada tarif</span>`;
    }
}

// Reload seluruh badge satu skema (saat ada perubahan is_default)
async function reloadTiers(skemaId) {
    const res  = await fetch(`/bendahara/tarif-honor/${skemaId}/tiers`, { headers: { 'Accept': 'application/json' } });
    const tiers = await res.json();
    const container = document.getElementById(`tiers-${skemaId}`);
    container.innerHTML = '';
    if (!tiers.length) {
        container.innerHTML = `<span class="text-muted small fst-italic" id="empty-${skemaId}">Belum ada tarif</span>`;
        return;
    }
    tiers.forEach(t => container.appendChild(makeBadge(skemaId, t)));
}
</script>
@endpush