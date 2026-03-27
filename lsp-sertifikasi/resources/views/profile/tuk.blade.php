@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('sidebar')
    @include('tuk.partials.sidebar')
@endsection

@section('content')
@include('profile.partials.alerts')

<div class="row g-4">
    <div class="col-lg-4">
        @include('profile.partials.identity-card', [
            'avatarLabel' => strtoupper(substr($user->name, 0, 1)),
            'name'        => $user->name,
            'email'       => $user->email,
            'roleLabel'   => 'TUK',
            'roleBadge'   => 'info',
            'extraRows'   => [
                ['label' => 'Kode TUK',  'value' => $user->tuk?->kode  ?? '-'],
                ['label' => 'Kota',      'value' => $user->tuk?->kota   ?? '-'],
                ['label' => 'Provinsi',  'value' => $user->tuk?->provinsi ?? '-'],
            ],
        ])
    </div>

    <div class="col-lg-8">
        @include('profile.partials.form-info', [
            'nameValue'  => $user->name,
            'emailValue' => $user->email,
        ])

        @include('profile.partials.form-password')
    </div>
</div>
@endsection