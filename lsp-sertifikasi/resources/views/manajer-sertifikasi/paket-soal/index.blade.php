{{-- resources/views/manajer-sertifikasi/paket-soal/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Paket Soal')
@section('breadcrumb', 'Bank Soal › Paket Soal')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Paket Soal</h4>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Upload dan kelola paket soal (PDF) per skema sertifikasi.
        </p>
    </div>
    <a href="{{ route('manajer-sertifikasi.paket-soal.create') }}" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i> Upload Paket Soal
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-folder2-open text-warning me-2"></i>Daftar Paket Soal
            <span class="badge bg-secondary ms-1">{{ $paketSoal->total() }}</span>
        </h6>
        <form method="GET" class="d-flex gap-2">
            <select name="skema_id" class="form-select form-select-sm" style="width:200px"
                onchange="this.form.submit()">
                <option value="">— Semua Skema —</option>
                @foreach($skemas as $sk)
                <option value="{{ $sk->id }}" {{ request('skema_id') == $sk->id ? 'selected' : '' }}>
                    {{ $sk->name }}
                </option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0">
        @if($paketSoal->isEmpty())
        <div class="empty-state">
            <i class="bi bi-folder2"></i>
            <p class="fw-semibold">Belum ada paket soal</p>
            <a href="{{ route('manajer-sertifikasi.paket-soal.create') }}" class="btn btn-sm btn-primary mt-2">
                <i class="bi bi-upload me-1"></i> Upload Sekarang
            </a>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Judul</th>
                        <th>Skema</th>
                        <th>File</th>
                        <th>Diupload Oleh</th>
                        <th>Tanggal</th>
                        <th>Dipakai di Jadwal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paketSoal as $i => $p)
                    <tr>
                        <td class="ps-4 text-muted">{{ $paketSoal->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.875rem">{{ $p->judul }}</div>
                        </td>
                        <td>
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill badge-pill">
                                {{ $p->skema->name }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                <span style="font-size:.8rem;color:#6b7280">{{ $p->file_name }}</span>
                            </div>
                        </td>
                        <td style="font-size:.875rem">{{ $p->dibuatOleh->name ?? '-' }}</td>
                        <td style="font-size:.8rem;color:#6b7280">{{ $p->created_at->format('d M Y') }}</td>
                        <td>
                            @if($p->distribusi->count() > 0)
                            <span class="badge bg-success-subtle text-success rounded-pill">
                                {{ $p->distribusi->count() }} jadwal
                            </span>
                            @else
                            <span class="text-muted" style="font-size:.8rem">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('manajer-sertifikasi.paket-soal.download', $p) }}"
                                class="btn btn-sm btn-outline-primary me-1" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            <form method="POST" action="{{ route('manajer-sertifikasi.paket-soal.destroy', $p) }}"
                                class="d-inline" onsubmit="return confirm('Hapus paket soal ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $paketSoal->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

@endsection