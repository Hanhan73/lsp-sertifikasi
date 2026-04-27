@extends('layouts.app')

@section('title', 'Tarif Honor Asesor')
@section('page-title', 'Tarif Honor Asesor per Asesi')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

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
                        <th width="220" class="text-center">Honor / Asesi (Rp)</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($skemas as $skema)
                    <tr id="row-{{ $skema->id }}">
                        <td class="small text-muted">{{ $skema->code }}</td>
                        <td class="fw-semibold small">{{ $skema->name }}</td>
                        <td>
                            <span class="badge bg-{{ $skema->jenis_badge }}">{{ $skema->jenis_label }}</span>
                        </td>
                        <td class="text-center">
                            <div class="input-group input-group-sm justify-content-center" style="max-width:200px;margin:0 auto;">
                                <span class="input-group-text">Rp</span>
                                <input type="text"
                                       class="form-control honor-input text-end"
                                       data-id="{{ $skema->id }}"
                                       data-url="{{ route('bendahara.tarif-honor.update', $skema) }}"
                                       data-raw="{{ $skema->honor_per_asesi ?? 0 }}"
                                       value="{{ number_format($skema->honor_per_asesi ?? 0, 0, ',', '.') }}"
                                       inputmode="numeric"
                                       autocomplete="off">
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-sm btn-success save-btn"
                                    data-id="{{ $skema->id }}"
                                    disabled>
                                <i class="bi bi-check-lg"></i> Simpan
                            </button>
                            <span class="saved-indicator text-success small d-none" id="saved-{{ $skema->id }}">
                                <i class="bi bi-check-circle-fill"></i> Tersimpan
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        Ubah nominal lalu klik <strong>Simpan</strong>. Tarif ini dipakai otomatis saat membuat kwitansi honor asesor.
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function formatRupiah(val) {
    const num = parseInt(val.replace(/\D/g, '')) || 0;
    return num.toLocaleString('id-ID');
}

function getRaw(input) {
    return parseInt(input.value.replace(/\D/g, '')) || 0;
}

// Format on load + aktifkan tombol Simpan kalau input berubah
document.querySelectorAll('.honor-input').forEach(input => {
    const id  = input.dataset.id;
    const btn = document.querySelector(`.save-btn[data-id="${id}"]`);

    // Format saat mengetik
    input.addEventListener('input', () => {
        const raw = input.value.replace(/\D/g, '');
        const pos = input.selectionStart;
        input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
        btn.disabled = false;
        document.getElementById(`saved-${id}`).classList.add('d-none');
    });

    // Pilih semua saat fokus
    input.addEventListener('focus', () => input.select());
});

// Simpan via AJAX
document.querySelectorAll('.save-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id    = btn.dataset.id;
        const input = document.querySelector(`.honor-input[data-id="${id}"]`);
        const url   = input.dataset.url;
        const val   = getRaw(input);

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const res  = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ honor_per_asesi: val }),
            });
            const data = await res.json();

            if (data.success) {
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan';
                btn.disabled  = true;
                // Re-format value setelah simpan
                input.value = data.honor_per_asesi
                    ? parseInt(data.honor_per_asesi).toLocaleString('id-ID')
                    : '0';
                const saved = document.getElementById(`saved-${id}`);
                saved.classList.remove('d-none');
                setTimeout(() => saved.classList.add('d-none'), 2500);

                Swal.fire({
                    icon: 'success', title: 'Tersimpan!',
                    text: data.message,
                    toast: true, position: 'top-end',
                    timer: 2000, showConfirmButton: false,
                });
            } else {
                throw new Error(data.message ?? 'Gagal menyimpan.');
            }
        } catch (e) {
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Simpan';
            btn.disabled  = false;
            Swal.fire({ icon: 'error', title: 'Gagal', text: e.message, toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
        }
    });
});
</script>
@endpush