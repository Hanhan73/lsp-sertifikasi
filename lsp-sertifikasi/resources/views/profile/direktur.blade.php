@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('sidebar')
{{-- sidebar admin sudah ada --}}
@include('direktur.partials.sidebar')
@endsection

@section('content')
@include('profile.partials.alerts')

<div class="row g-4">
    {{-- Kartu Identitas --}}
    <div class="col-lg-4">
        @include('profile.partials.identity-card', [
        'avatarLabel' => strtoupper(substr($user->name, 0, 1)),
        'name' => $user->name,
        'email' => $user->email,
        'roleLabel' => 'Direktur',
        'roleBadge' => 'success',
        'extraRows' => [],
        'photoUrl' => $user->photo_path ? asset('storage/' . $user->photo_path) : null, // ← tambah ini
        ])
    </div>

    {{-- Kolom kanan --}}
    <div class="col-lg-8">
        {{-- Form Info --}}
        @include('profile.partials.form-info', [
        'nameValue' => $user->name,
        'emailValue' => $user->email,
        ])

        {{-- Form Password --}}
        @include('profile.partials.form-password')
    </div>
</div>
@endsection