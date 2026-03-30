@extends('layouts.app')
@section('title', 'Portofolio')
@section('page-title', 'Portofolio')
@section('sidebar') @include('manajer-sertifikasi.partials.sidebar') @endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1">Portofolio</h5>
        <p class="text-muted mb-0" style="font-size:.875rem">Kelola portofolio per skema sertifikasi. Format file masih TBD.</p>
    </div>
    <a href="{{ route('manajer-sertifikasi.portofolio.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Portofolio
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <strong><i class="bi bi-briefcase me-2" style="color:#7c3aed"></i>Daftar Portofolio <span class="badge bg-secondary ms-1">{{ $portofolios->total() }}</span></strong>
        <form method="GET">
            <select name="skema_id" class="form-select form-select-sm" style="width:200px" onchange="this.form.submit()">
                <option value="">— Semua Skema —</option>
                @foreach($skemas as $sk)
                    <option value="{{ $sk->id }}" {{ request('skema_id') == $sk->id ? 'selected' : '' }}>{{ $sk->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        @if($portofolios->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-briefcase" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem"></i>
                <p class="fw-semibold mb-2">Belum ada portofolio</p>
                <a href="{{ route('manajer-sertifikasi.portofolio.create') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Sekarang
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Judul</th>
                        <th>Skema</th>
                        <th>Deskripsi</th>
                        <th>File</th>
                        <th>Jadwal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($portofolios as $i => $p)
                    <tr>
                        <td class="ps-4 text-muted">{{ $portofolios->firstItem() + $i }}</td>
                        <td><div class="fw-semibold" style="font-size:.875rem">{{ $p->judul }}</div></td>
                        <td><span class="badge badge-status" style="background:#7c3aed;color:white">{{ $p->skema->name }}</span></td>
                        <td style="font-size:.8rem;color:#6b7280;max-width:200px">{{ Str::limit($p->deskripsi, 60) ?: '—' }}</td>
                        <td>
                            @if($p->hasFile())
                                <span style="font-size:.8rem;color:#6b7280">
                                    <i class="bi bi-paperclip me-1"></i>{{ $p->file_name }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark badge-status">TBD</span>
                            @endif
                        </td>
                        <td>
                            @if($p->distribusi->count() > 0)
                                <span class="badge bg-success badge-status">{{ $p->distribusi->count() }} jadwal</span>
                            @else
                                <span class="text-muted" style="font-size:.8rem">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->hasFile())
                            <a href="{{ route('manajer-sertifikasi.portofolio.download', $p) }}"
                               class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-download"></i></a>
                            @endif
                            <form method="POST" action="{{ route('manajer-sertifikasi.portofolio.destroy', $p) }}"
                                  class="d-inline" onsubmit="return confirm('Hapus portofolio ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $portofolios->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection


{{-- ============================================================
     resources/views/manajer-sertifikasi/portofolio/create.blade.php
============================================================ --}}