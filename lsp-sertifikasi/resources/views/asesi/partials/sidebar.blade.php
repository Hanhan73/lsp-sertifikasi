{{-- Sidebar untuk Asesi --}}

@php
    $currentRoute = Route::currentRouteName();
    $asesmen = auth()->user()->asesmen;
@endphp

{{-- Dashboard --}}
<a href="{{ route('asesi.dashboard') }}" 
   class="nav-link {{ $currentRoute == 'asesi.dashboard' ? 'active' : '' }}">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

{{-- Jika belum ada asesmen, hanya tampilkan menu pendaftaran --}}
@if(!$asesmen)
    <a href="{{ route('asesi.complete-data') }}" 
       class="nav-link {{ $currentRoute == 'asesi.complete-data' ? 'active' : '' }}">
        <i class="bi bi-person-plus"></i> Daftar Asesi
    </a>
@else
    {{-- Menu berdasarkan status asesmen --}}
    
    {{-- Data Pribadi - bisa diakses jika status masih registered atau data_completed --}}
    @if(in_array($asesmen->status, ['registered', 'data_completed']))
        <a href="{{ route('asesi.complete-data') }}" 
           class="nav-link {{ $currentRoute == 'asesi.complete-data' ? 'active' : '' }}">
            <i class="bi bi-person-fill"></i> 
            @if($asesmen->status == 'registered')
                Lengkapi Data <span class="badge bg-warning text-dark ms-1">!</span>
            @else
                Data Pribadi
            @endif
        </a>
    @endif

    {{-- Pembayaran - hanya untuk mandiri yang sudah verified --}}
    @if(!$asesmen->is_collective && $asesmen->status === 'verified')
        <a href="{{ route('asesi.payment') }}" 
           class="nav-link {{ $currentRoute == 'asesi.payment' ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Pembayaran
            <span class="badge bg-danger text-white ms-1">!</span>
        </a>
    @endif

    {{-- Status Pembayaran - tampil jika sudah ada payment --}}
    @if($asesmen->payment || ($asesmen->is_collective && in_array($asesmen->status, ['verified', 'paid', 'scheduled', 'pre_assessment_completed', 'assessed', 'certified'])))
        <a href="{{ route('asesi.payment.status') }}" 
           class="nav-link {{ $currentRoute == 'asesi.payment.status' ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Status Pembayaran
            @if($asesmen->payment && $asesmen->payment->status === 'pending')
                <span class="badge bg-warning text-dark ms-1">Pending</span>
            @elseif($asesmen->payment && $asesmen->payment->status === 'verified')
                <span class="badge bg-success ms-1">✓</span>
            @endif
        </a>
    @endif

    {{-- Divider --}}
    @if($asesmen->status !== 'registered')
        <hr class="my-2 mx-3" style="border-color: rgba(255,255,255,0.2);">
    @endif

    {{-- Pra-Asesmen - tampil jika scheduled atau sudah selesai pra-asesmen --}}
    @if(in_array($asesmen->status, ['scheduled', 'pre_assessment_completed']))
        <a href="{{ route('asesi.pre-assessment') }}" 
           class="nav-link {{ $currentRoute == 'asesi.pre-assessment' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Pra-Asesmen
            @if($asesmen->status === 'scheduled')
                <span class="badge bg-warning text-dark ms-1">!</span>
            @elseif($asesmen->status === 'pre_assessment_completed')
                <span class="badge bg-success ms-1">✓</span>
            @endif
        </a>
    @endif

    {{-- Jadwal Asesmen - tampil jika sudah ada schedule --}}
    @if($asesmen->schedule)
        <a href="{{ route('asesi.schedule') }}" 
           class="nav-link {{ $currentRoute == 'asesi.schedule' ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i> Jadwal Asesmen
            @if(in_array($asesmen->status, ['scheduled', 'pre_assessment_completed']))
                <span class="badge bg-info ms-1">
                    {{ $asesmen->schedule->assessment_date->format('d/m') }}
                </span>
            @endif
        </a>
    @endif

    {{-- Hasil Asesmen - tampil jika sudah assessed atau certified --}}
    @if(in_array($asesmen->status, ['assessed', 'certified']))
        <a href="{{ route('asesi.result') }}" 
           class="nav-link {{ $currentRoute == 'asesi.result' ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i> Hasil Asesmen
            @if($asesmen->result === 'kompeten')
                <span class="badge bg-success ms-1">Kompeten</span>
            @elseif($asesmen->result === 'belum_kompeten')
                <span class="badge bg-danger ms-1">BK</span>
            @endif
        </a>
    @endif

    {{-- Sertifikat - tampil jika certified --}}
    @if($asesmen->status === 'certified')
        <a href="{{ route('asesi.certificate') }}" 
           class="nav-link {{ $currentRoute == 'asesi.certificate' ? 'active' : '' }}">
            <i class="bi bi-award"></i> Sertifikat
            <span class="badge bg-success ms-1">✓</span>
        </a>
    @endif

    {{-- Divider sebelum menu informasi --}}
    @if($asesmen->status !== 'registered')
        <hr class="my-2 mx-3" style="border-color: rgba(255,255,255,0.2);">
    @endif

    {{-- Tracking Status - selalu tampil setelah ada asesmen --}}
    <a href="{{ route('asesi.tracking') }}" 
       class="nav-link {{ $currentRoute == 'asesi.tracking' ? 'active' : '' }}">
        <i class="bi bi-list-check"></i> Timeline Proses
    </a>

    {{-- Dokumen - tampil jika sudah upload dokumen --}}
    @if($asesmen->status !== 'registered' && ($asesmen->photo || $asesmen->ktp || $asesmen->document))
        <a href="{{ route('asesi.documents') }}" 
           class="nav-link {{ $currentRoute == 'asesi.documents' ? 'active' : '' }}">
            <i class="bi bi-folder2-open"></i> Dokumen Saya
        </a>
    @endif

    {{-- Batch Info - hanya untuk kolektif --}}
    @if($asesmen->is_collective && $asesmen->collective_batch_id)
        <a href="{{ route('asesi.batch-info') }}" 
           class="nav-link {{ $currentRoute == 'asesi.batch-info' ? 'active' : '' }}">
            <i class="bi bi-people"></i> Info Batch Kolektif
        </a>
    @endif
@endif


{{-- Status Progress Card (Optional - di bagian bawah sidebar) --}}
@if($asesmen && $asesmen->status !== 'certified')
<div class="px-3 mt-4">
    <div class="card bg-dark bg-opacity-25 border-0 text-white">
        <div class="card-body py-2 px-3">
            <small class="text-white-50 d-block mb-1">Progress:</small>
            <div class="progress" style="height: 8px;">
                @php
                    $progress = 0;
                    switch($asesmen->status) {
                        case 'registered': $progress = 10; break;
                        case 'data_completed': $progress = 20; break;
                        case 'verified': $progress = 40; break;
                        case 'paid': $progress = 60; break;
                        case 'scheduled': $progress = 70; break;
                        case 'pre_assessment_completed': $progress = 80; break;
                        case 'assessed': $progress = 90; break;
                        case 'certified': $progress = 100; break;
                    }
                @endphp
                <div class="progress-bar bg-warning" role="progressbar" 
                     style="width: {{ $progress }}%" 
                     aria-valuenow="{{ $progress }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
            <small class="text-white-50 d-block mt-1">{{ $progress }}% Selesai</small>
        </div>
    </div>
</div>
@endif