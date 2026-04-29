@extends('layouts.app')

@section('title', 'Edit Biaya Operasional')
@section('page-title', 'Edit Biaya Operasional')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Edit — <span class="text-primary">{{ $biayaOperasional->nomor }}</span>
                </h6>
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

                <form action="{{ route('bendahara.biaya-operasional.update', $biayaOperasional) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    @include('bendahara.biaya-operasional._form', ['item' => $biayaOperasional])

                    {{-- Preview bukti existing --}}
                    <div class="row mt-3 g-3">
                        @if($biayaOperasional->bukti_transaksi)
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Bukti Transaksi Saat Ini</label><br>
                            <a href="{{ $biayaOperasional->bukti_transaksi_url }}" target="_blank">
                                <img src="{{ $biayaOperasional->bukti_transaksi_url }}"
                                     style="max-height:140px;border-radius:6px;border:1px solid #dee2e6;">
                            </a>
                            <div class="form-text">Upload baru untuk mengganti.</div>
                        </div>
                        @endif
                        @if($biayaOperasional->bukti_kegiatan)
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Bukti Kegiatan Saat Ini</label><br>
                            <a href="{{ $biayaOperasional->bukti_kegiatan_url }}" target="_blank">
                                <img src="{{ $biayaOperasional->bukti_kegiatan_url }}"
                                     style="max-height:140px;border-radius:6px;border:1px solid #dee2e6;">
                            </a>
                            <div class="form-text">Upload baru untuk mengganti.</div>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Perbarui
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