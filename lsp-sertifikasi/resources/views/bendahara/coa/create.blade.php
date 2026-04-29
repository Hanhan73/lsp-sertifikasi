{{-- resources/views/bendahara/coa/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Tambah Akun CoA')
@section('page-title', 'Tambah Akun CoA')
@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between">
        <span><i class="bi bi-plus-circle text-success me-2"></i>Tambah Akun Baru</span>
        <a href="{{ route('bendahara.coa.index') }}" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('bendahara.coa.store') }}" method="POST">
            @csrf
            @include('bendahara.coa._form')
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                <a href="{{ route('bendahara.coa.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
// Auto-generate kode saat tipe berubah
document.getElementById('tipe').addEventListener('change', function () {
    const prefix = { aset:'1', kewajiban:'2', ekuitas:'3', pendapatan:'4', beban:'5' };
    const p = prefix[this.value] || '9';
    // Hanya update jika kode masih kosong atau baru diisi otomatis
    const kodeInput = document.getElementById('kode');
    if (!kodeInput.value || kodeInput.dataset.auto === 'true') {
        kodeInput.value = p + '-';
        kodeInput.dataset.auto = 'true';
    }
});
document.getElementById('kode').addEventListener('input', function () {
    this.dataset.auto = 'false';
});
</script>
@endpush