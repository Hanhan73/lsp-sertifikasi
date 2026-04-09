@extends('layouts.app')
@section('title', 'SK Hasil Ujikom')
@section('breadcrumb', 'SK Hasil Ujikom')

@section('sidebar')
@include('direktur.partials.sidebar')
@endsection

@section('content')

<div class="mb-4">
    <h4 class="fw-bold mb-1">Persetujuan SK Hasil Ujikom</h4>
    <p class="text-muted mb-0" style="font-size:.875rem;">
        Tinjau dan setujui pengajuan Surat Keputusan hasil ujian kompetensi dari Manajer Sertifikasi.
    </p>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
</div>
@endif

{{-- Tab --}}
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'pending' ? 'active fw-semibold' : '' }}"
           href="?tab=pending">
            Menunggu Persetujuan
            @if($pending->count() > 0)
            <span class="badge bg-warning text-dark ms-1">{{ $pending->count() }}</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'history' ? 'active fw-semibold' : '' }}"
           href="?tab=history">
            Riwayat
        </a>
    </li>
</ul>

@if($tab === 'pending')
    @if($pending->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-check2-all" style="font-size:2.5rem;opacity:.3;"></i>
            <p class="mt-3 mb-0">Tidak ada pengajuan SK yang menunggu persetujuan.</p>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach($pending as $item)
        @include('direktur.sk-ujikom._card', ['item' => $item])
        @endforeach
    </div>
    @endif

@else
    @if($history->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-clock-history" style="font-size:2.5rem;opacity:.3;"></i>
            <p class="mt-3 mb-0">Belum ada riwayat persetujuan SK.</p>
        </div>
    </div>
    @else
    <div class="row g-3">
        @foreach($history as $item)
        @include('direktur.sk-ujikom._card', ['item' => $item])
        @endforeach
    </div>
    @endif
@endif

@endsection