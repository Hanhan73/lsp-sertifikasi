@extends('layouts.app')
@section('title', 'Chart of Account')
@section('page-title', 'Chart of Account (CoA)')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-x-circle"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filter + Tambah --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <label class="fw-semibold mb-0"><i class="bi bi-funnel"></i> Tipe:</label>
            <form method="GET" class="mb-0">
                <select name="tipe" class="form-select form-select-sm" style="width:160px"
                        onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    @foreach($tipeList as $key => $label)
                    <option value="{{ $key }}" {{ request('tipe') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </form>
            <span class="text-muted small">{{ $akuns->count() }} akun</span>
            <a href="{{ route('bendahara.coa.create') }}" class="btn btn-success btn-sm ms-auto">
                <i class="bi bi-plus-circle"></i> Tambah Akun
            </a>
        </div>
    </div>
</div>

{{-- Tabel per grup tipe --}}
@foreach($tipeList as $tipeKey => $tipeLabel)
@php $group = $grouped->get($tipeKey, collect()); @endphp
@if($group->count() && (!request('tipe') || request('tipe') === $tipeKey))
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
        <span class="badge bg-{{ match($tipeKey) {
            'aset'       => 'success',
            'kewajiban'  => 'danger',
            'ekuitas'    => 'warning',
            'pendapatan' => 'info',
            'beban'      => 'secondary',
            default      => 'light'
        } }} fs-6">{{ $tipeLabel }}</span>
        <span class="text-muted small">{{ $group->count() }} akun</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-sm mb-0 align-middle" style="font-size:.875rem;">
            <thead class="table-light">
                <tr>
                    <th style="width:100px">Kode</th>
                    <th>Nama Akun</th>
                    <th>Sub Tipe</th>
                    <th>Keterangan</th>
                    <th class="text-center" style="width:80px">Status</th>
                    <th class="text-center" style="width:80px">Sistem</th>
                    <th class="text-center" style="width:100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($group->sortBy('urutan') as $akun)
            <tr class="{{ !$akun->is_active ? 'text-muted' : '' }}">
                <td>
                    <code class="text-primary fw-semibold">{{ $akun->kode }}</code>
                </td>
                <td class="fw-semibold">{{ $akun->nama }}</td>
                <td>
                    @if($akun->sub_tipe)
                    <span class="badge bg-light text-dark border" style="font-size:.75rem;">
                        {{ $akun->sub_tipe_label }}
                    </span>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-muted small">{{ $akun->keterangan ?? '—' }}</td>
                <td class="text-center">
                    @if($akun->is_active)
                    <span class="badge bg-success">Aktif</span>
                    @else
                    <span class="badge bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($akun->is_system)
                    <i class="bi bi-shield-lock-fill text-warning" title="Akun sistem"></i>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-center">
                    <a href="{{ route('bendahara.coa.edit', $akun) }}"
                       class="btn btn-xs btn-outline-primary btn-sm py-0 px-2">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @if(!$akun->is_system)
                    <form action="{{ route('bendahara.coa.destroy', $akun) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus akun {{ $akun->kode }} - {{ $akun->nama }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-2">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                    @endif
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endforeach

@endsection