{{-- resources/views/profile/partials/rekening-asesor.blade.php --}}
{{-- Di-include di profile.asesor, harus ada $asesor dan $user --}}

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom d-flex align-items-center">
        <i class="bi bi-credit-card me-2 text-success"></i>
        <span class="fw-semibold">Rekening Bank</span>
        <button type="button" class="btn btn-sm btn-outline-success ms-auto"
            data-bs-toggle="modal" data-bs-target="#modalTambahRekening">
            <i class="bi bi-plus-lg me-1"></i>Tambah Rekening
        </button>
    </div>
    <div class="card-body p-0">
        @if($asesor->rekenings->isEmpty())
            <div class="text-center text-muted py-4 small">
                <i class="bi bi-credit-card fs-4 d-block mb-1"></i>
                Belum ada rekening bank tersimpan.
            </div>
        @else
            <div class="list-group list-group-flush">
                @foreach($asesor->rekenings as $rek)
                <div class="list-group-item px-3 py-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="fw-semibold">
                                {{ $rek->nama_bank }}
                                @if($rek->is_utama)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size:.7rem;">Utama</span>
                                @endif
                            </div>
                            <div class="font-monospace small mt-1">{{ $rek->nomor_rekening }}</div>
                            <div class="text-muted small">a.n. {{ $rek->nama_pemilik }}</div>
                            @if($rek->cabang)
                                <div class="text-muted" style="font-size:.78rem;">Cabang: {{ $rek->cabang }}</div>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditRekening{{ $rek->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('asesor.rekening.destroy', $rek) }}" method="POST"
                                onsubmit="return confirm('Hapus rekening ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Modal Edit --}}
                <div class="modal fade" id="modalEditRekening{{ $rek->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('asesor.rekening.update', $rek) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="modal-header">
                                    <h6 class="modal-title fw-semibold">Edit Rekening</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    @include('profile.partials._form-rekening', ['data' => $rek])
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambahRekening" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('asesor.rekening.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-semibold">Tambah Rekening Bank</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('profile.partials._form-rekening', ['data' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>