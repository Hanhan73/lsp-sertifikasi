@extends('layouts.app')
@section('title', 'Dokumen SK Asesor')
@section('page-title', 'Dokumen SK')

@section('sidebar')
@include('asesor.partials.sidebar')
@endsection

@section('content')

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-file-earmark-text me-2"></i>Surat Keputusan (SK) Asesor
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <img src="{{ $asesor->foto_url }}"
                     class="rounded border mb-3"
                     style="width:150px;height:180px;object-fit:cover;" alt="foto asesor">
                <div class="fw-bold">{{ $asesor->nama }}</div>
                <div class="text-muted small">No. Reg: {{ $asesor->no_reg_met }}</div>
                <span class="badge bg-{{ $asesor->status_badge }} mt-1">{{ $asesor->status_label }}</span>
            </div>
            <div class="col-md-8">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Informasi Asesor</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width:35%">Nama Lengkap</td>
                        <td class="fw-semibold">{{ $asesor->nama }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIK</td>
                        <td>{{ $asesor->nik }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tempat, Tgl Lahir</td>
                        <td>{{ $asesor->tempat_lahir }}, {{ $asesor->tanggal_lahir?->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat</td>
                        <td>{{ $asesor->alamat }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Registrasi MET</td>
                        <td><code>{{ $asesor->no_reg_met }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Blanko</td>
                        <td>{{ $asesor->no_blanko ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Registrasi</td>
                        <td>
                            <span class="badge bg-{{ $asesor->status_badge }}">{{ $asesor->status_label }}</span>
                        </td>
                    </tr>
                    @if($asesor->expire_date)
                    <tr>
                        <td class="text-muted">Berlaku Hingga</td>
                        <td class="{{ $asesor->expire_date->isPast() ? 'text-danger fw-bold' : '' }}">
                            {{ $asesor->expire_date->format('d M Y') }}
                            @if($asesor->expire_date->isPast())
                            <span class="badge bg-danger ms-1">Expired</span>
                            @elseif($asesor->expire_date->diffInDays(now()) < 90)
                            <span class="badge bg-warning text-dark ms-1">Segera Expire</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $asesor->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Telepon</td>
                        <td>{{ $asesor->telepon }}</td>
                    </tr>
                </table>

                @if($asesor->keterangan)
                <div class="alert alert-info mt-3 mb-0 small">
                    <i class="bi bi-info-circle me-1"></i>{{ $asesor->keterangan }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection