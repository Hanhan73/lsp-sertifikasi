@extends('layouts.app')

@section('title', 'Tambah Biaya Operasional')
@section('page-title', 'Tambah Biaya Operasional')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Form Biaya Operasional</h6>
                <a href="{{ route('bendahara.biaya-operasional.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('bendahara.biaya-operasional.store') }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf

                    @include('bendahara.biaya-operasional._form')

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <a href="{{ route('bendahara.biaya-operasional.index') }}" class="btn btn-secondary">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('bendahara.biaya-operasional._scripts')
@endpush