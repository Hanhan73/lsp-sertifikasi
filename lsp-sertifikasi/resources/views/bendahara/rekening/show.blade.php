{{-- resources/views/bendahara/rekening/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Rekening — ' . $asesor->nama)
@section('page-title', 'Rekening Bank: ' . $asesor->nama)

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

    {{-- Kiri: Info Asesor --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-person-circle me-1 text-primary"></i>Informasi Asesor
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <div class="text-muted small">Nama</div>
                    <div class="fw-semibold">{{ $asesor->nama }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">Email</div>
                    <div>{{ $asesor->email }}</div>
                </div>
                <div class="mb-2">
                    <div class="text-muted small">No. Reg MET</div>
                    <div>{{ $asesor->no_reg_met ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-muted small">Telepon</div>
                    <div>{{ $asesor->telepon ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('bendahara.rekening.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- Kanan: Daftar + Form Tambah Rekening --}}
    <div class="col-lg-8">

        {{-- Daftar rekening --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center">
                <i class="bi bi-credit-card me-1 text-success"></i>
                <span class="fw-semibold">Rekening Tersimpan</span>
                <span class="badge bg-secondary ms-auto">{{ $asesor->rekenings->count() }}</span>
            </div>
            <div class="card-body p-0">
                @if($asesor->rekenings->isEmpty())
                <div class="text-center text-muted py-4 small">
                    <i class="bi bi-credit-card fs-3 d-block mb-1"></i>
                    Belum ada rekening tersimpan.
                </div>
                @else
                <div class="list-group list-group-flush">
                    @foreach($asesor->rekenings as $rek)
                    <div class="list-group-item px-3 py-3">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="flex-grow-1">
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
                            <div class="d-flex gap-1 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEdit{{ $rek->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('bendahara.rekening.destroy', [$asesor, $rek]) }}" method="POST"
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
                    <div class="modal fade" id="modalEdit{{ $rek->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('bendahara.rekening.update', [$asesor, $rek]) }}" method="POST">
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

        {{-- Form tambah rekening baru --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold small">
                <i class="bi bi-plus-circle me-1 text-success"></i>Tambah Rekening Baru
            </div>
            <div class="card-body">
                <form action="{{ route('bendahara.rekening.store', $asesor) }}" method="POST">
                    @csrf
                    @include('profile.partials._form-rekening', ['data' => null])
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Rekening
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection