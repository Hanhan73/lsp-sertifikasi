@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@section('content')
@php $asesmen = $user->asesmen; @endphp
@include('profile.partials.alerts')

<div class="row g-4">
    <div class="col-lg-4">
        @include('profile.partials.identity-card', [
        'avatarLabel' => strtoupper(substr($asesmen?->full_name ?? $user->name, 0, 1)),
        'name' => $asesmen?->full_name ?? $user->name,
        'email' => $user->email,
        'roleLabel' => 'Asesi',
        'roleBadge' => 'primary',
        'extraRows' => [
        ['label' => 'NIK', 'value' => $asesmen?->nik ?? '-'],
        ['label' => 'Telepon', 'value' => $asesmen?->phone ?? '-'],
        ['label' => 'Skema', 'value' => $asesmen?->skema?->name ?? '-'],
        ['label' => 'Status', 'value' => $asesmen?->status_label ?? '-'],
        ],
        'photoUrl' => $user->photo_path ? asset('storage/' . $user->photo_path) : null, // ← tambah ini
        ])

        @if($asesmen)
        <div class="alert alert-light border mt-3 small">
            <i class="bi bi-info-circle text-primary me-1"></i>
            Untuk mengubah data diri, perbarui di form <strong>APL-01</strong>.
        </div>
        @endif
    </div>

    <div class="col-lg-8">
        {{-- Form ubah email --}}
        @include('profile.partials.form-info', [
            'nameValue'  => $asesmen?->full_name ?? $user->name,
            'emailValue' => $user->email,
        ])

        {{-- Form ganti password --}}
        @include('profile.partials.form-password')
    </div>
</div>
@endsection