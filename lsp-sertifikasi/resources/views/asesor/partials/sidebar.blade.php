@php
$asesor = auth()->user()->asesor ?? null;
$currentRoute = request()->route()->getName();
@endphp

<div class="sidebar-header px-3 py-3 border-bottom">
    <div class="d-flex align-items-center gap-2">
        @if($asesor?->foto_path)
        <img src="{{ asset('storage/' . $asesor->foto_path) }}" class="rounded-circle border"
            style="width:38px;height:38px;object-fit:cover;" alt="foto">
        @else
        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
            style="width:38px;height:38px;font-size:.9rem;">
            {{ strtoupper(substr($asesor?->nama ?? 'A', 0, 1)) }}
        </div>
        @endif
        <div style="line-height:1.2;">
            <div class="fw-semibold small">{{ $asesor?->nama ?? auth()->user()->name }}</div>
            <div style="font-size:.7rem;" class="text-muted">Asesor</div>
        </div>
    </div>
</div>

<nav class="sidebar-nav px-2 py-2">
    <ul class="nav flex-column gap-1">

        <li class="nav-item">
            <a href="{{ route('asesor.dashboard') }}"
                class="nav-link rounded {{ str_starts_with($currentRoute, 'asesor.dashboard') ? 'active bg-primary text-white' : 'text-dark' }}">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('asesor.schedule') }}"
                class="nav-link rounded {{ str_starts_with($currentRoute, 'asesor.schedule') ? 'active bg-primary text-white' : 'text-dark' }}">
                <i class="bi bi-calendar3 me-2"></i>Jadwal Asesmen
                @php
                $todayCount = 0;
                if ($asesor) {
                $todayCount = \App\Models\Schedule::where('asesor_id', $asesor->id)
                ->whereDate('assessment_date', today())->count();
                }
                @endphp
                @if($todayCount > 0)
                <span class="badge bg-warning text-dark ms-auto" style="font-size:.65rem;">{{ $todayCount }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('asesor.dokumen.sk') }}"
                class="nav-link rounded {{ str_starts_with($currentRoute, 'asesor.dokumen') ? 'active bg-primary text-white' : 'text-dark' }}">
                <i class="bi bi-file-earmark-text me-2"></i>Dokumen SK
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('asesor.honor.index') }}"
                class="nav-link rounded {{ str_starts_with($currentRoute, 'asesor.honor') ? 'active bg-primary text-white' : 'text-dark' }}">
                <i class="bi bi-currency-dollar me-2"></i>Honor Asesor
                @php
                $honorBaru = $asesor
                ? \App\Models\HonorPayment::where('asesor_id', $asesor->id)
                ->where('status', 'sudah_dibayar')->count()
                : 0;
                @endphp
                @if($honorBaru > 0)
                <span class="badge bg-warning text-dark ms-auto" style="font-size:.65rem;">{{ $honorBaru }}</span>
                @endif
            </a>
        </li>

        <li class="nav-item mt-3">
            <hr class="my-1">
        </li>

        <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link rounded text-dark w-100 text-start border-0 bg-transparent">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </form>
        </li>
    </ul>
</nav>