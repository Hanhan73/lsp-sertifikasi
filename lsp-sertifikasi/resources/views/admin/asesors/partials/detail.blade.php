<div class="row">
    {{-- Kolom Kiri: Foto + Identitas Utama --}}
    <div class="col-md-4 text-center border-end">
        <img src="{{ $asesor->foto_url }}" alt="Foto {{ $asesor->nama }}"
            style="width:150px; height:160px; object-fit:cover; border-radius:10px; border:3px solid #e2e8f0;"
            class="mb-3">

        <h5 class="fw-bold mb-1">{{ $asesor->nama }}</h5>
        <p class="text-muted mb-2">{{ $asesor->jenis_kelamin_label }} &bull; {{ $asesor->umur }} tahun</p>

        <span class="badge bg-{{ $asesor->status_badge }} fs-6 mb-2">
            {{ $asesor->status_label }}
        </span>

        @if($asesor->status_reg === 'expire' && $asesor->expire_date)
        <br>
        <small class="text-muted">Expire: {{ $asesor->expire_date->format('d M Y') }}</small>
        @endif

        <hr>

        {{-- Reg. Metodologi --}}
        <div class="text-start">
            <p class="mb-1 small text-muted">No. Reg. Metodologi:</p>
            <p class="fw-bold">{{ $asesor->no_reg_met ?? '-' }}</p>

            <p class="mb-1 small text-muted">No. Blanko:</p>
            <p class="fw-bold">{{ $asesor->no_blanko ?? '-' }}</p>

            <p class="mb-1 small text-muted">SIAPKerja:</p>
            @if($asesor->siap_kerja === 'Memiliki')
            <span class="badge bg-success">✅ Memiliki</span>
            @else
            <span class="badge bg-danger">❌ Tidak</span>
            @endif
        </div>

        <hr>
        {{-- Akun --}}
        @if($asesor->user)
        <div class="alert alert-success py-2 text-start mb-0">
            <i class="bi bi-person-circle"></i>
            <strong>Punya Akun Login</strong><br>
            <small>{{ $asesor->user->email }}</small>
        </div>
        @else
        <div class="alert alert-secondary py-2 text-start mb-0">
            <i class="bi bi-person-x"></i> Belum ada akun login
        </div>
        @endif
    </div>

    {{-- Kolom Kanan: Detail Data --}}
    <div class="col-md-8">
        {{-- IDENTITAS --}}
        <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">
            <i class="bi bi-person-vcard"></i> Data Identitas
        </h6>
        <table class="table table-sm table-borderless mb-4">
            <tr>
                <td width="150" class="text-muted">NIK</td>
                <td><code>{{ $asesor->nik }}</code></td>
            </tr>
            <tr>
                <td class="text-muted">Tempat, Tgl Lahir</td>
                <td>{{ $asesor->tempat_lahir }}, {{ $asesor->tanggal_lahir->format('d F Y') }}</td>
            </tr>
            <tr>
                <td class="text-muted">Jenis Kelamin</td>
                <td>{{ $asesor->jenis_kelamin_label }}</td>
            </tr>
            <tr>
                <td class="text-muted">Alamat</td>
                <td>{{ $asesor->alamat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-muted">Kota / Provinsi</td>
                <td>
                    {{ $asesor->kota ?? '-' }}
                    @if($asesor->provinsi) / {{ $asesor->provinsi }} @endif
                </td>
            </tr>
        </table>

        {{-- KONTAK --}}
        <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">
            <i class="bi bi-envelope"></i> Kontak
        </h6>
        <table class="table table-sm table-borderless mb-4">
            <tr>
                <td width="150" class="text-muted">Email</td>
                <td>
                    <a href="mailto:{{ $asesor->email }}">{{ $asesor->email }}</a>
                </td>
            </tr>
            <tr>
                <td class="text-muted">Telepon</td>
                <td>
                    @if($asesor->telepon)
                    <a href="tel:{{ $asesor->telepon }}">{{ $asesor->telepon }}</a>
                    @else
                    <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        </table>

        {{-- KETERANGAN --}}
        @if($asesor->keterangan)
        <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">
            <i class="bi bi-chat-left-text"></i> Keterangan
        </h6>
        <p class="text-muted">{{ $asesor->keterangan }}</p>
        @endif

        {{-- SISTEM --}}
        <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">
            <i class="bi bi-info-circle"></i> Informasi Sistem
        </h6>
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td width="150" class="text-muted">ID Asesor</td>
                <td>#{{ $asesor->id }}</td>
            </tr>
            <tr>
                <td class="text-muted">Dibuat</td>
                <td>{{ $asesor->created_at->format('d F Y H:i') }}</td>
            </tr>
            <tr>
                <td class="text-muted">Diupdate</td>
                <td>{{ $asesor->updated_at->diffForHumans() }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- Footer actions --}}
<div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
    <a href="{{ route('admin.asesors.edit', $asesor) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
        Tutup
    </button>
</div>