@php
    $jenisList   = \App\Models\AsesorDocument::JENIS_LABELS;
    $docsByJenis = $asesor->documents->keyBy('jenis_dokumen');
    $isAdmin     = ($context ?? 'asesor') === 'admin';
    $storeUrl    = $isAdmin ? route('admin.asesors.documents.store', $asesor) : route('asesor.documents.store');
@endphp

<div class="card border-0 shadow-sm" id="asesor-documents-card" data-asesor-id="{{ $asesor->id }}" data-store-url="{{ $storeUrl }}">
    <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-folder2-open text-primary"></i> Dokumen Pendukung Asesor
        <span class="badge bg-secondary ms-auto" id="doc-count-badge">{{ $docsByJenis->count() }}/{{ count($jenisList) }}</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0" id="asesor-documents-table">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Jenis Dokumen</th>
                    <th>Status</th>
                    <th class="text-center" style="width:220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jenisList as $jenis => $label)
                @php $doc = $docsByJenis->get($jenis); @endphp
                <tr data-jenis="{{ $jenis }}">
                    <td class="ps-3">
                        <div class="fw-semibold small">{{ $label }}</div>
                        <div class="text-muted doc-filename" style="font-size:.72rem;">
                            @if($doc){{ $doc->file_name }} &bull; {{ $doc->file_size_human }}@endif
                        </div>
                    </td>
                    <td class="doc-status">
                        @if($doc)
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            <i class="bi bi-check-circle"></i> Ada
                        </span>
                        @else
                        <span class="badge bg-secondary-subtle text-secondary border">
                            <i class="bi bi-dash-circle"></i> Belum ada
                        </span>
                        @endif
                    </td>
                    <td class="text-center doc-actions">
                        <div class="d-flex gap-1 justify-content-center align-items-center">
                            <span class="doc-download-wrap">
                                @if($doc)
                                <a href="{{ $isAdmin
                                        ? route('admin.asesors.documents.download', [$asesor, $doc])
                                        : route('asesor.documents.download', $doc) }}"
                                   class="btn btn-sm btn-outline-success" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                @endif
                            </span>
                            <span class="doc-delete-wrap">
                                @if($doc)
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                                        data-delete-url="{{ $isAdmin
                                            ? route('admin.asesors.documents.destroy', [$asesor, $doc])
                                            : route('asesor.documents.destroy', $doc) }}"
                                        title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                                @endif
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-upload-doc"
                                    title="{{ $doc ? 'Ganti File' : 'Upload' }}"
                                    onclick="document.getElementById('doc-input-{{ $jenis }}').click()">
                                <i class="bi bi-upload"></i>
                            </button>
                            <input type="file" class="d-none doc-file-input" id="doc-input-{{ $jenis }}"
                                   data-jenis="{{ $jenis }}" accept=".pdf">
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Semua dokumen bersifat opsional. Format file: PDF, maksimal 5 MB per file.
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            ?? document.querySelector('input[name="_token"]')?.value;
    }

    function toast(icon, title) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon, title, showConfirmButton: false, timer: 3000, timerProgressBar: true });
        }
    }

    function setRowLoading(row, loading) {
        const btn = row.querySelector('.btn-upload-doc');
        if (btn) btn.disabled = loading;
        const delBtn = row.querySelector('.btn-delete-doc');
        if (delBtn) delBtn.disabled = loading;
    }

    function updateBadgeCount(card) {
        const total = card.querySelectorAll('tbody tr').length;
        const filled = card.querySelectorAll('tbody tr .doc-status .bg-success-subtle').length;
        const badge = card.querySelector('#doc-count-badge');
        if (badge) badge.textContent = `${filled}/${total}`;
    }

    function renderFilled(row, doc) {
        row.querySelector('.doc-filename').textContent = `${doc.file_name} • ${doc.file_size_human}`;
        row.querySelector('.doc-status').innerHTML = `
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="bi bi-check-circle"></i> Ada
            </span>`;
        row.querySelector('.doc-download-wrap').innerHTML = `
            <a href="${doc.download_url}" class="btn btn-sm btn-outline-success" title="Download">
                <i class="bi bi-download"></i>
            </a>`;
        row.querySelector('.doc-delete-wrap').innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-doc"
                    data-delete-url="${doc.delete_url}" title="Hapus">
                <i class="bi bi-trash3"></i>
            </button>`;
        const uploadBtn = row.querySelector('.btn-upload-doc');
        if (uploadBtn) uploadBtn.title = 'Ganti File';
    }

    function renderEmpty(row) {
        row.querySelector('.doc-filename').textContent = '';
        row.querySelector('.doc-status').innerHTML = `
            <span class="badge bg-secondary-subtle text-secondary border">
                <i class="bi bi-dash-circle"></i> Belum ada
            </span>`;
        row.querySelector('.doc-download-wrap').innerHTML = '';
        row.querySelector('.doc-delete-wrap').innerHTML = '';
        const uploadBtn = row.querySelector('.btn-upload-doc');
        if (uploadBtn) uploadBtn.title = 'Upload';
    }

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('doc-file-input')) return;

        const input = e.target;
        const file  = input.files[0];
        if (!file) return;

        const card  = input.closest('#asesor-documents-card');
        const row   = input.closest('tr');
        const jenis = input.dataset.jenis;
        const storeUrl = card.dataset.storeUrl || @json($storeUrl);

        const formData = new FormData();
        formData.append('jenis_dokumen', jenis);
        formData.append('file', file);
        formData.append('_token', csrfToken());

        setRowLoading(row, true);

        fetch(storeUrl, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData,
        })
        .then(async (res) => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) {
                const msg = data?.message || (data?.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal mengupload dokumen.');
                throw new Error(msg);
            }
            return data;
        })
        .then((data) => {
            renderFilled(row, data.document);
            updateBadgeCount(card);
            toast('success', data.message || 'Berhasil diupload.');
        })
        .catch((err) => {
            toast('error', err.message || 'Terjadi kesalahan.');
        })
        .finally(() => {
            setRowLoading(row, false);
            input.value = '';
        });
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-doc');
        if (!btn) return;

        e.preventDefault();
        const row  = btn.closest('tr');
        const card = btn.closest('#asesor-documents-card');
        const url  = btn.dataset.deleteUrl;

        const doDelete = () => {
            setRowLoading(row, true);
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            })
            .then(async (res) => {
                const data = await res.json().catch(() => null);
                if (!res.ok || !data || !data.success) throw new Error(data?.message || 'Gagal menghapus dokumen.');
                return data;
            })
            .then((data) => {
                renderEmpty(row);
                updateBadgeCount(card);
                toast('success', data.message || 'Dokumen berhasil dihapus.');
            })
            .catch((err) => {
                toast('error', err.message || 'Terjadi kesalahan.');
            })
            .finally(() => setRowLoading(row, false));
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Hapus dokumen ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
            }).then((result) => { if (result.isConfirmed) doDelete(); });
        } else if (confirm('Hapus dokumen ini?')) {
            doDelete();
        }
    });
})();
</script>
@endpush
@endonce