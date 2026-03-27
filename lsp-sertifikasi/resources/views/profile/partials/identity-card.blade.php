{{--
    resources/views/profile/partials/identity-card.blade.php
    Props:
        $avatarLabel  — huruf inisial
        $name         — nama lengkap
        $email        — email
        $roleLabel    — label role (string)
        $roleBadge    — warna badge Bootstrap (primary, danger, info, dst)
        $extraRows    — array of ['label' => '...', 'value' => '...']
        $photoUrl     — (opsional) URL foto jika ada
--}}
<div class="card border-0 shadow-sm">
    <div class="card-body text-center p-4">

        {{-- Avatar / Foto --}}
        <div class="mb-3">
            @if(!empty($photoUrl))
            <img src="{{ $photoUrl }}"
                 class="rounded-circle border shadow-sm"
                 style="width:90px;height:90px;object-fit:cover;" alt="Foto Profil">
            @else
            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center
                        text-white fw-bold mx-auto"
                 style="width:90px;height:90px;font-size:2rem;">
                {{ $avatarLabel }}
            </div>
            @endif
        </div>

        <h6 class="fw-bold mb-0">{{ $name }}</h6>
        <div class="text-muted small mb-2">{{ $email }}</div>
        <span class="badge bg-{{ $roleBadge }} mb-3">{{ $roleLabel }}</span>

        {{-- Upload foto (hanya jika ada route photo) --}}
        @if(Route::has('profile.upload-photo'))
        <form action="{{ route('profile.upload-photo') }}" method="POST" enctype="multipart/form-data"
              id="form-foto-user" class="mb-3">
            @csrf
            <input type="file" name="photo" id="foto-user" class="d-none"
                   accept="image/jpeg,image/png,image/jpg"
                   onchange="document.getElementById('form-foto-user').submit()">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                    onclick="document.getElementById('foto-user').click()">
                <i class="bi bi-camera me-1"></i> Ganti Foto
            </button>
        </form>
        @endif

        {{-- Extra rows --}}
        @if(!empty($extraRows))
        <hr>
        <div class="text-start small">
            @foreach($extraRows as $row)
            <div class="d-flex justify-content-between py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                <span class="text-muted">{{ $row['label'] }}</span>
                <span class="fw-semibold">{{ $row['value'] }}</span>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>