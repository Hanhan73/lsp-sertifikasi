@extends('layouts.app')
@section('title', 'Tambah Surat Masuk')
@section('page-title', 'Tambah Surat Masuk')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-envelope-arrow-down me-2"></i>Tambah Surat Masuk</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.surat.masuk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.surat.masuk._form', ['surat' => null])
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.surat.masuk.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection