@extends('layouts.app')

@section('title', 'Soal Observasi')
@section('breadcrumb', 'Bank Soal › Soal Observasi')

@section('sidebar')
@include('manajer-sertifikasi.partials.sidebar')
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Soal Observasi</h4>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Upload dan kelola lembar observasi (PDF) per skema sertifikasi.
        </p>
    </div>
    <a href="{{ route('manajer-sertifikasi.soal-observasi.create') }}" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i> Upload Soal Observasi
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-eye text-primary me-2"></i>Daftar Soal Observasi
            <span class="badge bg-secondary ms-1">{{ $soalObservasi->total() }}</span>
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
        @if($soalObservasi->isEmpty())
        <div class="empty-state">
            <i class="bi bi-file-earmark-pdf"></i>
            <p class="fw-semibold">Belum ada soal observasi</p>
            <a href="{{ route('manajer-sertifikasi.soal-observasi.create') }}" class="btn btn-sm btn-primary mt-2">
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
                    @foreach($soalObservasi as $i => $s)
                    <tr>
                        <td class="ps-4 text-muted">{{ $soalObservasi->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:.875rem">{{ $s->judul }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary rounded-pill badge-pill">
                                {{ $s->skema->name }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                <span style="font-size:.8rem;color:#6b7280">{{ $s->file_name }}</span>
                            </div>
                        </td>
                        <td style="font-size:.875rem">{{ $s->dibuatOleh->name ?? '-' }}</td>
                        <td style="font-size:.8rem;color:#6b7280">{{ $s->created_at->format('d M Y') }}</td>
                        <td>
                            @if($s->distribusi->count() > 0)
                            <span class="badge bg-success-subtle text-success rounded-pill">
                                {{ $s->distribusi->count() }} jadwal
                            </span>
                            @else
                            <span class="text-muted" style="font-size:.8rem">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('manajer-sertifikasi.soal-observasi.download', $s) }}"
                                class="btn btn-sm btn-outline-primary me-1" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            <form method="POST" action="{{ route('manajer-sertifikasi.soal-observasi.destroy', $s) }}"
                                class="d-inline" onsubmit="return confirm('Hapus soal observasi ini?')">
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
            {{ $soalObservasi->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

@endsection