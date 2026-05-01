@extends('layouts.app')
@section('title', 'Tambah Surat Keluar')
@section('page-title', 'Tambah Surat Keluar')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-envelope-arrow-up me-2"></i>Tambah Surat Keluar</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.surat.keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.surat.keluar._form', ['surat' => null])
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.surat.keluar.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection