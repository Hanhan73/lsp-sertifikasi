@extends('layouts.app')

@section('title', 'Pembayaran Kolektif')
@section('page-title', 'Pembayaran Kolektif')

@section('sidebar')
@include('bendahara.partials.sidebar')
@endsection

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Daftar Batch Kolektif</h5>
        <span class="badge bg-primary">{{ $batches->count() }} Batch</span>
    </div>
    <div class="card-body">

        @if($batches->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size:3.5rem;color:#ccc;"></i>
            <h5 class="mt-3 text-muted">Belum ada batch kolektif terdaftar</h5>
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable">
                <thead class="table-light">
                    <tr>
                        <th>Batch ID</th>
                        <th>TUK</th>
                        <th>Skema</th>
                        <th class="text-center">Asesi</th>
                        <th>No. Invoice</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status Invoice</th>
                        <th class="text-center">Angsuran</th>
                        <th>Tgl Daftar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $b)
                    @php
                        $inv = $b->invoice;
                        $lunas = $inv ? $inv->collectivePayments->where('status','verified')->sum('amount') : 0;
                        $sisa  = $inv ? ($inv->total_amount - $lunas) : 0;
                    @endphp
                    <tr>
                        <td>
                            <code class="small text-primary">{{ Str::limit($b->collective_batch_id, 18) }}</code>
                        </td>
                        <td>{{ $b->tuk->name ?? '-' }}</td>
                        <td>
                            @php
                                $skemaNames = \App\Models\Asesmen::where('collective_batch_id', $b->collective_batch_id)
                                    ->with('skema')->get()->pluck('skema.name')->unique()->filter();
                            @endphp
                            @foreach($skemaNames as $sn)
                                <span class="badge bg-light text-dark border small">{{ Str::limit($sn, 28) }}</span><br>
                            @endforeach
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $b->jumlah_asesi }}</span>
                        </td>
                        <td>
                            @if($inv)
                                <small class="fw-semibold">{{ $inv->invoice_number }}</small>
                            @else
                                <span class="text-muted small fst-italic">Belum dibuat</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($inv)
                                <strong>Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</strong>
                                @if($sisa > 0 && $sisa < $inv->total_amount)
                                <br><small class="text-danger">Sisa: Rp {{ number_format($sisa, 0, ',', '.') }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($inv)
                                <span class="badge bg-{{ $inv->status_badge }}">{{ $inv->status_label }}</span>
                            @else
                                <span class="badge bg-secondary">Belum Ada</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($inv)
                                @php
                                    $totalAng  = $inv->collectivePayments->count();
                                    $verAng    = $inv->collectivePayments->where('status','verified')->count();
                                    $pendAng   = $inv->collectivePayments->where('status','pending')->whereNotNull('proof_path')->count();
                                @endphp
                                <small>
                                    {{ $verAng }}/{{ $totalAng }} lunas
                                    @if($pendAng > 0)
                                    <br><span class="badge bg-warning text-dark">{{ $pendAng }} menunggu</span>
                                    @endif
                                </small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ \Carbon\Carbon::parse($b->tanggal_daftar)->translatedFormat('d M Y') }}</small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('bendahara.payments.kolektif.detail', $b->collective_batch_id) }}"
                               class="btn btn-sm {{ $inv ? 'btn-outline-primary' : 'btn-primary' }}">
                                <i class="bi bi-{{ $inv ? 'eye' : 'plus-circle' }}"></i>
                                {{ $inv ? 'Kelola' : 'Buat Invoice' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

@endsection