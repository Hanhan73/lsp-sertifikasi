@php
    $jenisList   = \App\Models\AsesorDocument::JENIS_LABELS;
    $docsByJenis = $asesor->documents->keyBy('jenis_dokumen');
    $isAdmin     = ($context ?? 'asesor') === 'admin';
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-folder2-open text-primary"></i> Dokumen Pendukung Asesor
        <span class="badge bg-secondary ms-auto">{{ $docsByJenis->count() }}/{{ count($jenisList) }}</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
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
                <tr>
                    <td class="ps-3">
                        <div class="fw-semibold small">{{ $label }}</div>
                        @if($doc)
                        <div class="text-muted" style="font-size:.72rem;">
                            {{ $doc->file_name }} &bull; {{ $doc->file_size_human }}
                        </div>
                        @endif
                    </td>
                    <td>
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
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @if($doc)
                                <a href="{{ $isAdmin
                                        ? route('admin.asesors.documents.download', [$asesor, $doc])
                                        : route('asesor.documents.download', $doc) }}"
                                   class="btn btn-sm btn-outline-success" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                <form action="{{ $isAdmin
                                        ? route('admin.asesors.documents.destroy', [$asesor, $doc])
                                        : route('asesor.documents.destroy', $doc) }}"
                                      method="POST" onsubmit="return confirm('Hapus {{ $label }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    title="{{ $doc ? 'Ganti File' : 'Upload' }}"
                                    onclick="document.getElementById('doc-input-{{ $jenis }}').click()">
                                <i class="bi bi-upload"></i>
                            </button>
                            <form id="doc-form-{{ $jenis }}" class="d-none"
                                  action="{{ $isAdmin
                                        ? route('admin.asesors.documents.store', $asesor)
                                        : route('asesor.documents.store') }}"
                                  method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="jenis_dokumen" value="{{ $jenis }}">
                                <input type="file" name="file" id="doc-input-{{ $jenis }}" accept=".pdf"
                                       onchange="document.getElementById('doc-form-{{ $jenis }}').submit()">
                            </form>
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