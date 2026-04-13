@extends('layouts.app')
@section('title', 'Form APL-01')
@section('page-title', 'FR.APL.01 - Permohonan Sertifikasi Kompetensi')

@section('sidebar')
@include('asesi.partials.sidebar')
@endsection

@push('styles')
<style>
.form-section { display: none; }
.form-section.active { display: block; }

.progress-steps { display: flex; justify-content: space-between; margin-bottom: 30px; }
.step { flex: 1; text-align: center; position: relative; }
.step::before { content: ''; position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: #e5e7eb; z-index: 0; }
.step:first-child::before { left: 50%; }
.step:last-child::before { right: 50%; }
.step-circle { width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; color: #6b7280; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin: 0 auto; position: relative; z-index: 1; }
.step.completed .step-circle { background: #10b981; color: white; }
.step.active .step-circle { background: #3b82f6; color: white; }

/* Signature pad */
#sig-wrapper { border: 2px dashed #94a3b8; border-radius: 8px; background: #f0f4f8; width: 100%; overflow: hidden; cursor: crosshair; }
#signature-pad { display: block; width: 100%; height: 220px; cursor: crosshair; touch-action: none; -ms-touch-action: none; -webkit-user-select: none; user-select: none; }

/* Input validation feedback */
.input-hint { font-size: 0.78rem; color: #6b7280; margin-top: 3px; }
.input-hint.error { color: #dc2626; }
.input-hint.success { color: #059669; }
.form-control.is-valid, .form-select.is-valid { border-color: #10b981; }
.form-control.is-invalid, .form-select.is-invalid { border-color: #ef4444; }
.char-counter { font-size: 0.75rem; float: right; color: #6b7280; }
.char-counter.warn { color: #f59e0b; }
.char-counter.error { color: #ef4444; }
</style>
@endpush

@section('content')

{{-- ── Status Banner ── --}}
@if($aplsatu && in_array($aplsatu->status, ['submitted','verified','approved']))
<div class="alert alert-success d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div>
        <strong>APL-01 sudah disubmit</strong> pada {{ $aplsatu->submitted_at?->translatedFormat('d M Y H:i') }}.
        @if($aplsatu->status === 'verified')
            <span class="badge bg-primary ms-2">Sudah Diverifikasi Admin</span>
        @elseif($aplsatu->status === 'submitted')
            <span class="badge bg-warning text-dark ms-2">Menunggu Verifikasi Admin</span>
        @endif
    </div>
    {{-- PDF hanya bisa diunduh jika admin sudah TTD (status verified/approved) --}}
    @if(in_array($aplsatu->status, ['verified','approved']))
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('asesi.apl01.pdf', ['preview'=>1]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye me-1"></i> Preview PDF
        </a>
        <a href="{{ route('asesi.apl01.pdf') }}" class="btn btn-sm btn-success">
            <i class="bi bi-download me-1"></i> Download PDF
        </a>
    </div>
    @else
    <div class="ms-auto">
        <span class="badge bg-secondary fs-6 px-3 py-2">
            <i class="bi bi-lock me-1"></i> PDF tersedia setelah admin memverifikasi
        </span>
    </div>
    @endif
</div>
@endif

{{-- ── Progress Steps ── --}}
@if(!$aplsatu || $aplsatu->status === 'draft' || $aplsatu->status === 'returned')
<div class="progress-steps mb-4">
    @foreach(['Data Pribadi','Data Pekerjaan','Data Sertifikasi','Bukti Dokumen','Review & TTD'] as $i => $label)
    <div class="step {{ $i===0 ? 'active' : '' }}" data-step="{{ $i+1 }}">
        <div class="step-circle">{{ $i+1 }}</div>
        <small class="d-block mt-2">{{ $label }}</small>
    </div>
    @endforeach
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex align-items-center">
        <i class="bi bi-file-earmark-text me-2 fs-5"></i>
        <h5 class="mb-0">FR.APL.01 - PERMOHONAN SERTIFIKASI KOMPETENSI</h5>
    </div>
    <div class="card-body">

        {{-- ── READ-ONLY VIEW (submitted/verified/approved) ── --}}
        @if($aplsatu && in_array($aplsatu->status, ['submitted','verified','approved']))
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">a. Data Pribadi</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-12"><label class="form-label text-muted small">Nama Lengkap</label><div class="fw-semibold">{{ $aplsatu->nama_lengkap }}</div></div>
            <div class="col-md-6"><label class="form-label text-muted small">NIK</label><div class="fw-semibold">{{ $aplsatu->nik }}</div></div>
            <div class="col-md-3"><label class="form-label text-muted small">Tempat Lahir</label><div class="fw-semibold">{{ $aplsatu->tempat_lahir }}</div></div>
            <div class="col-md-3"><label class="form-label text-muted small">Tanggal Lahir</label><div class="fw-semibold">{{ $aplsatu->tanggal_lahir?->translatedFormat('d M Y') }}</div></div>
            <div class="col-md-3"><label class="form-label text-muted small">Jenis Kelamin</label><div class="fw-semibold">{{ $aplsatu->jenis_kelamin }}</div></div>
            <div class="col-md-3"><label class="form-label text-muted small">Kebangsaan</label><div class="fw-semibold">{{ $aplsatu->kebangsaan }}</div></div>
            <div class="col-md-6"><label class="form-label text-muted small">Kualifikasi Pendidikan</label><div class="fw-semibold">{{ $aplsatu->kualifikasi_pendidikan ?? '-' }}</div></div>
            <div class="col-md-9"><label class="form-label text-muted small">Alamat Rumah</label><div class="fw-semibold">{{ $aplsatu->alamat_rumah }}</div></div>
            <div class="col-md-3"><label class="form-label text-muted small">Kode Pos</label><div class="fw-semibold">{{ $aplsatu->kode_pos ?? '-' }}</div></div>
            <div class="col-md-4"><label class="form-label text-muted small">HP</label><div class="fw-semibold">{{ $aplsatu->hp }}</div></div>
            <div class="col-md-4"><label class="form-label text-muted small">Email</label><div class="fw-semibold">{{ $aplsatu->email }}</div></div>
        </div>
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">b. Data Pekerjaan</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label text-muted small">Institusi/Perusahaan</label><div class="fw-semibold">{{ $aplsatu->nama_institusi ?? '-' }}</div></div>
            <div class="col-md-6"><label class="form-label text-muted small">Jabatan</label><div class="fw-semibold">{{ $aplsatu->jabatan ?? '-' }}</div></div>
        </div>
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Data Sertifikasi</h6>
        <table class="table table-bordered mb-4">
            <tr><td width="200"><strong>Skema</strong></td><td>{{ $asesmen->skema->name }}</td></tr>
            <tr><td>Tujuan Asesmen</td><td><strong>{{ $aplsatu->tujuan_asesmen }}</strong>@if($aplsatu->tujuan_asesmen==='Lainnya') : {{ $aplsatu->tujuan_asesmen_lainnya }}@endif</td></tr>
        </table>

        {{-- Bukti read-only: hanya tampilkan link GDrive, tanpa status ceklis --}}
        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Bukti Kelengkapan</h6>
        @php $gdrive = $aplsatu->buktiKelengkapan->whereNotNull('gdrive_file_url')->first()?->gdrive_file_url; @endphp
        @if($gdrive)
        <div class="alert alert-info mb-3">
            <i class="bi bi-google me-2"></i><strong>Link Google Drive:</strong>
            <a href="{{ $gdrive }}" target="_blank">{{ $gdrive }}</a>
        </div>
        @else
        <div class="alert alert-secondary mb-3"><i class="bi bi-info-circle me-2"></i>Link Google Drive belum diisi.</div>
        @endif

        {{-- Daftar dokumen (nama saja, status hanya admin yang bisa lihat) --}}
        @foreach(['persyaratan_dasar'=>'Persyaratan Dasar','administratif'=>'Administratif'] as $kat=>$label)
        <h6 class="fw-semibold mb-2">{{ $label }}</h6>
        <ul class="list-group list-group-flush mb-3">
            @foreach($aplsatu->buktiKelengkapan->where('kategori',$kat) as $bukti)
            <li class="list-group-item py-2 px-3 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-text text-muted"></i> {{ $bukti->nama_dokumen }}
            </li>
            @endforeach
        </ul>
        @endforeach

        <div class="text-center mt-4">
            <a href="{{ route('asesi.schedule') }}" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Kembali</a>
            @if(in_array($aplsatu->status, ['verified','approved']))
            <a href="{{ route('asesi.apl01.pdf', ['preview'=>1]) }}" target="_blank" class="btn btn-primary me-2"><i class="bi bi-file-pdf"></i> Preview PDF</a>
            <a href="{{ route('asesi.apl01.pdf') }}" class="btn btn-success"><i class="bi bi-download"></i> Download PDF</a>
            @else
            <button class="btn btn-secondary" disabled><i class="bi bi-lock me-1"></i> PDF tersedia setelah admin verifikasi</button>
            @endif
        </div>

        @else
        {{-- ── EDITABLE FORM ── --}}
        <form id="apl01-form" autocomplete="off">
            @csrf

            {{-- ════════ SECTION 1: DATA PRIBADI ════════ --}}
            <div class="form-section active" data-section="1">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Bagian 1: Rincian Data Pemohon Sertifikasi</h6>
                <p class="text-muted small mb-4">Cantumkan data pribadi, data pendidikan formal serta data pekerjaan Anda saat ini.</p>
                <h6 class="fw-semibold mb-3">a. Data Pribadi</h6>
                <div class="row g-3">

                    <div class="col-md-12">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama_lengkap" id="f-nama" class="form-control"
                            value="{{ $aplsatu->nama_lengkap }}" required
                            minlength="3" maxlength="255"
                            oninput="validateNama(this)">
                        <div class="input-hint" id="hint-nama">Masukkan nama lengkap sesuai KTP (min. 3 karakter).</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">No. KTP/NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nik" id="f-nik" class="form-control"
                            value="{{ $aplsatu->nik }}" required
                            maxlength="16" inputmode="numeric" pattern="[0-9]{16}"
                            oninput="validateNIK(this)">
                        <div class="d-flex justify-content-between">
                            <div class="input-hint" id="hint-nik">NIK harus tepat <strong>16 digit angka</strong> sesuai KTP.</div>
                            <span class="char-counter" id="nik-counter">{{ strlen($aplsatu->nik ?? '') }}/16</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" name="tempat_lahir" id="f-tempat" class="form-control"
                            value="{{ $aplsatu->tempat_lahir }}" required
                            maxlength="100"
                            oninput="validateRequired(this, 'hint-tempat', 'Tempat lahir wajib diisi.')">
                        <div class="input-hint" id="hint-tempat">Contoh: Jakarta, Bandung, Surabaya.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lahir" id="f-tgl" class="form-control"
                            value="{{ $aplsatu->tanggal_lahir?->translatedFormat('Y-m-d') }}" required
                            max="{{ now()->subYears(12)->translatedFormat('Y-m-d') }}"
                            oninput="validateTanggalLahir(this)">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" id="f-gender" class="form-select" required
                            onchange="validateRequired(this, 'hint-gender', 'Jenis kelamin wajib dipilih.')">
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" {{ $aplsatu->jenis_kelamin==='Laki-laki' ? 'selected':'' }}>Laki-laki</option>
                            <option value="Perempuan" {{ $aplsatu->jenis_kelamin==='Perempuan' ? 'selected':'' }}>Perempuan</option>
                        </select>
                        <div class="input-hint" id="hint-gender"></div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Kebangsaan</label>
                        <input type="text" name="kebangsaan" class="form-control" value="{{ $aplsatu->kebangsaan ?? 'Indonesia' }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kualifikasi Pendidikan</label>
                        <select name="kualifikasi_pendidikan" class="form-select">
                            <option value="">-- Pilih --</option>
                            @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','D4','S1','S2','S3'] as $edu)
                            <option value="{{ $edu }}" {{ $aplsatu->kualifikasi_pendidikan===$edu ? 'selected':'' }}>{{ $edu }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label">Alamat Rumah <span class="text-danger">*</span></label>
                        <textarea name="alamat_rumah" id="f-alamat" class="form-control" rows="2" required
                            minlength="10"
                            oninput="validateAlamat(this)">{{ $aplsatu->alamat_rumah }}</textarea>
                        <div class="input-hint" id="hint-alamat">Tulis alamat lengkap: jalan, RT/RW, kelurahan, kecamatan, kota (min. 10 karakter).</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Kode Pos</label>
                        <input type="text" name="kode_pos" id="f-kodepos" class="form-control"
                            value="{{ $aplsatu->kode_pos }}"
                            maxlength="5" inputmode="numeric" pattern="[0-9]{5}"
                            oninput="validateKodePos(this)">
                        <div class="input-hint" id="hint-kodepos">5 digit angka. Contoh: 40132</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">No. Telepon Rumah</label>
                        <input type="text" name="telp_rumah" id="f-telpRumah" class="form-control"
                            value="{{ $aplsatu->telp_rumah }}"
                            maxlength="15" inputmode="tel"
                            oninput="validateTelp(this, 'hint-telprumah')">
                        <div class="input-hint" id="hint-telprumah">Contoh: 021-5551234 (opsional).</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">No. HP <span class="text-danger">*</span></label>
                        <input type="text" name="hp" id="f-hp" class="form-control"
                            value="{{ $aplsatu->hp }}" required
                            maxlength="15" inputmode="tel"
                            oninput="validateHP(this)">
                        <div class="input-hint" id="hint-hp">Format: 08xxxxxxxxxx atau +628xxxxxxxxxx (10–15 digit).</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="text" name="email" id="f-email" class="form-control"
                            inputmode="email" autocomplete="email"
                            value="{{ $aplsatu->email }}" required
                            oninput="validateEmail(this)">
                        <div class="input-hint" id="hint-email">Contoh: nama@email.com</div>
                    </div>

                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('asesi.schedule') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Selanjutnya <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            {{-- ════════ SECTION 2: DATA PEKERJAAN ════════ --}}
            <div class="form-section" data-section="2">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">b. Data Pekerjaan Sekarang</h6>
                <p class="text-muted small mb-3">Semua field di bagian ini bersifat opsional.</p>
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Nama Institusi/Perusahaan</label>
                        <input type="text" name="nama_institusi" class="form-control" maxlength="255"
                            value="{{ $aplsatu->nama_institusi }}">
                        <div class="input-hint">Nama instansi/perusahaan tempat bekerja saat ini.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jabatan</label>
                        <input type="text" name="jabatan" class="form-control" maxlength="255"
                            value="{{ $aplsatu->jabatan }}">
                        <div class="input-hint">Jabatan/posisi saat ini.</div>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label">Alamat Kantor</label>
                        <textarea name="alamat_kantor" class="form-control" rows="2">{{ $aplsatu->alamat_kantor }}</textarea>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Kode Pos Kantor</label>
                        <input type="text" name="kode_pos_kantor" id="f-kodeposKantor" class="form-control"
                            maxlength="5" inputmode="numeric" pattern="[0-9]{5}"
                            value="{{ $aplsatu->kode_pos_kantor }}"
                            oninput="validateKodePos(this, 'hint-kodeposKantor')">
                        <div class="input-hint" id="hint-kodeposKantor">5 digit angka (opsional).</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Telepon Kantor</label>
                        <input type="text" name="telp_kantor_detail" id="f-telpKantor" class="form-control"
                            maxlength="20" inputmode="tel"
                            value="{{ $aplsatu->telp_kantor_detail }}"
                            oninput="validateTelp(this, 'hint-telpkantor')">
                        <div class="input-hint" id="hint-telpkantor">Contoh: 022-2031234 (opsional).</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fax</label>
                        <input type="text" name="fax_kantor" class="form-control" maxlength="20"
                            value="{{ $aplsatu->fax_kantor }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Email Kantor</label>
                        <input type="text" name="email_kantor" id="f-emailKantor" class="form-control"
                            inputmode="email"
                            value="{{ $aplsatu->email_kantor }}"
                            oninput="validateEmailOptional(this, 'hint-emailkantor')">
                        <div class="input-hint" id="hint-emailkantor">Email kantor (opsional).</div>
                    </div>

                </div>
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="prevSection()"><i class="bi bi-arrow-left"></i> Sebelumnya</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Selanjutnya <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            {{-- ════════ SECTION 3: DATA SERTIFIKASI ════════ --}}
            <div class="form-section" data-section="3">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Bagian 2: Data Sertifikasi</h6>
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i> Data skema otomatis diisi sesuai skema yang Anda pilih saat pendaftaran.
                </div>
                <table class="table table-bordered mb-3">
                    <tr><td width="220"><strong>Skema Sertifikasi</strong></td><td><strong>{{ $asesmen->skema->name }}</strong></td></tr>
                    <tr><td>Nomor</td><td>{{ $asesmen->skema->nomor_skema ?? $asesmen->skema->code }}</td></tr>
                </table>
                <div class="mb-4">
                    <label class="form-label fw-bold">Tujuan Asesmen <span class="text-danger">*</span></label>
                    @php $tujuanOptions = ['Sertifikasi'=>'Sertifikasi','PKT'=>'Pengakuan Kompetensi Terkini (PKT)','RPL'=>'Rekognisi Pembelajaran Lampau (RPL)','Lainnya'=>'Lainnya']; @endphp
                    @foreach($tujuanOptions as $val=>$lbl)
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tujuan_asesmen" value="{{ $val }}" id="tujuan_{{ $val }}"
                            {{ ($aplsatu->tujuan_asesmen===$val || (!$aplsatu->tujuan_asesmen && $val==='Sertifikasi')) ? 'checked':'' }} required>
                        <label class="form-check-label" for="tujuan_{{ $val }}">{{ $lbl }}</label>
                    </div>
                    @endforeach
                    <input type="text" name="tujuan_asesmen_lainnya" class="form-control mt-2" id="tujuan-lainnya-input"
                        style="{{ $aplsatu->tujuan_asesmen==='Lainnya' ? '' : 'display:none;' }}"
                        value="{{ $aplsatu->tujuan_asesmen_lainnya }}" placeholder="Sebutkan tujuan lainnya..."
                        maxlength="255">
                </div>
                <h6 class="fw-semibold mb-2">Daftar Unit Kompetensi:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr><th width="50">No</th><th width="160">Kode Unit</th><th>Judul Unit Kompetensi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($asesmen->skema->unitKompetensis as $i=>$unit)
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td><small>{{ $unit->kode_unit }}</small></td>
                                <td>{{ $unit->judul_unit }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">Belum ada unit kompetensi</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-secondary" onclick="prevSection()"><i class="bi bi-arrow-left"></i> Sebelumnya</button>
                    <button type="button" class="btn btn-primary" onclick="nextSection()">Selanjutnya <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            {{-- ════════ SECTION 4: BUKTI DOKUMEN ════════ --}}
            <div class="form-section" data-section="4">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Bagian 3: Bukti Kelengkapan Pemohon</h6>

                {{-- Instruksi --}}
                <div class="alert alert-info d-flex align-items-start gap-2 mb-4">
                    <i class="bi bi-info-circle-fill mt-1 flex-shrink-0 fs-5"></i>
                    <div>
                        <strong>Cara melampirkan dokumen:</strong>
                        <ol class="mb-0 mt-1 ps-3">
                            <li>Buka <strong>Google Drive</strong> → buat folder baru (contoh: "APL-01 [Nama Anda]")</li>
                            <li>Upload <strong>semua dokumen</strong> yang dibutuhkan ke folder tersebut</li>
                            <li>Klik kanan folder → <strong>Share</strong> → ubah akses ke <em>"Anyone with the link can view"</em></li>
                            <li>Salin link dan tempelkan di kolom di bawah</li>
                        </ol>
                    </div>
                </div>

                {{-- Google Drive Link --}}
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-google me-2"></i> Link Folder Google Drive
                    </div>
                    <div class="card-body">
                        @php $existingLink = $aplsatu->buktiKelengkapan->whereNotNull('gdrive_file_url')->first()?->gdrive_file_url ?? ''; @endphp
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" id="gdrive-link-global" class="form-control form-control-lg"
                                value="{{ $existingLink }}"
                                placeholder="https://drive.google.com/drive/folders/..."
                                oninput="validateGDriveLink(this)">
                            @if($existingLink)
                            <a href="{{ $existingLink }}" target="_blank" class="btn btn-outline-secondary" id="gdrive-preview-btn">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            @else
                            <button type="button" class="btn btn-outline-secondary d-none" id="gdrive-preview-btn" onclick="previewGDrive()">
                                <i class="bi bi-box-arrow-up-right"></i> Buka
                            </button>
                            @endif
                        </div>
                        <div class="input-hint" id="hint-gdrive">
                            Paste link Google Drive folder Anda. Pastikan link diawali dengan <code>https://drive.google.com/</code>
                        </div>
                    </div>
                </div>

                {{-- Daftar dokumen yang HARUS dilampirkan -- hanya informasi, tanpa ceklis untuk asesi --}}
                <div class="card border-0 bg-light mb-4">
                    <div class="card-header bg-warning bg-opacity-25 border-warning">
                        <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Dokumen yang Harus Dilampirkan</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Pastikan semua dokumen berikut sudah terupload di folder Google Drive Anda.</p>

                        <h6 class="fw-semibold text-secondary mb-2">📋 Persyaratan Dasar</h6>
                        <ul class="list-group list-group-flush mb-3">
                            @foreach($aplsatu->buktiKelengkapan->where('kategori','persyaratan_dasar') as $i => $bukti)
                            <li class="list-group-item bg-transparent d-flex align-items-center gap-2 py-2 px-0">
                                <span class="badge bg-secondary rounded-circle" style="width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:0.7rem;">{{ $i+1 }}</span>
                                <span>{{ $bukti->nama_dokumen }}</span>
                                @if($bukti->deskripsi)
                                <span class="text-muted small ms-1">— {{ $bukti->deskripsi }}</span>
                                @endif
                            </li>
                            @endforeach
                        </ul>

                        <h6 class="fw-semibold text-secondary mb-2">Administratif</h6>
                        <ul class="list-group list-group-flush mb-0">
                            @foreach($aplsatu->buktiKelengkapan->where('kategori','administratif') as $i => $bukti)
                            <li class="list-group-item bg-transparent d-flex align-items-center gap-2 py-2 px-0">
                                <span class="badge bg-secondary rounded-circle" style="width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:0.7rem;">{{ $i+1 }}</span>
                                <span>{{ $bukti->nama_dokumen }}</span>
                                @if($bukti->deskripsi)
                                <span class="text-muted small ms-1">— {{ $bukti->deskripsi }}</span>
                                @endif
                            </li>
                            @endforeach
                        </ul>

                        <div class="alert alert-secondary small mt-3 mb-0">
                            <i class="bi bi-lightbulb me-1"></i>
                            <strong>Catatan:</strong> Status kelengkapan dokumen akan diverifikasi oleh Admin LSP setelah Anda submit.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-secondary" onclick="prevSection()"><i class="bi bi-arrow-left"></i> Sebelumnya</button>
                    <button type="button" class="btn btn-primary" id="btn-next-bukti" onclick="saveBuktiAndNext()">
                        Selanjutnya <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- ════════ SECTION 5: REVIEW & TTD ════════ --}}
            <div class="form-section" data-section="5">
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Review &amp; Tanda Tangan</h6>
                <div class="alert alert-warning mb-4">
                    <h6 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Perhatian Sebelum Submit!</h6>
                    <p class="mb-2">Pastikan semua data sudah benar. <strong>Setelah submit, data tidak dapat diubah.</strong></p>
                    <ul class="mb-0 small">
                        <li>Periksa kembali nama lengkap, NIK, dan tempat/tanggal lahir.</li>
                        <li>Dengan menandatangani, Anda menyatakan data yang diberikan adalah <strong>benar dan dapat dipertanggungjawabkan</strong>.</li>
                        <li>PDF APL-01 baru dapat diunduh setelah Admin LSP melakukan verifikasi.</li>
                    </ul>
                </div>
            
                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-check me-2"></i>Ringkasan Data</h6>
                        <div class="row g-2 small">
                            <div class="col-md-6"><span class="text-muted">Nama Lengkap:</span> <strong id="summary-nama">-</strong></div>
                            <div class="col-md-6"><span class="text-muted">NIK:</span> <strong id="summary-nik">-</strong></div>
                            <div class="col-md-6"><span class="text-muted">Tempat/Tgl Lahir:</span> <strong id="summary-ttl">-</strong></div>
                            <div class="col-md-6"><span class="text-muted">HP:</span> <strong id="summary-hp">-</strong></div>
                            <div class="col-md-6"><span class="text-muted">Email:</span> <strong id="summary-email">-</strong></div>
                            <div class="col-md-6"><span class="text-muted">Tujuan Asesmen:</span> <strong id="summary-tujuan">-</strong></div>
                            <div class="col-md-12"><span class="text-muted">Link Google Drive:</span> <strong id="summary-gdrive" class="text-break">-</strong></div>
                        </div>
                    </div>
                </div>

                {{-- Signature Pad --}}
                <div class="card mb-4">
                    <div class="card-body">
                        @include('partials._signature_pad', [
                            'padId'    => 'asesi',
                            'padLabel' => 'Tanda Tangan Pemohon',
                            'padHeight' => 180,
                            'savedSig' => auth()->user()->signature_image,
                        ])
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="agreement-check">
                    <label class="form-check-label" for="agreement-check">
                        Saya menyatakan bahwa data yang saya isikan adalah <strong>benar dan dapat dipertanggungjawabkan</strong>.
                        Saya memahami bahwa setelah submit, data tidak dapat diubah lagi.
                    </label>
                </div>
            
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevSection()"><i class="bi bi-arrow-left"></i> Sebelumnya</button>
                    <button type="button" class="btn btn-success" id="btn-submit" onclick="submitForm()">
                        <i class="bi bi-check-circle"></i> Submit APL-01
                    </button>
                </div>
            </div>

        </form>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
let currentSection = 1;
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// ── Logger ─────────────────────────────────────────────────
function log(label, data) {
    const ts = new Date().toISOString();
    console.group(`[APL-01 ${ts}] ${label}`);
    if (data !== undefined) console.log(data);
    console.groupEnd();
}
function logError(label, err) {
    console.error(`[APL-01 ${new Date().toISOString()}] ❌ ${label}`, err);
}

// ══════════════════════════════════════════════════════════
// INPUT VALIDATORS
// ══════════════════════════════════════════════════════════

function setHint(hintId, msg, type) {
    const el = document.getElementById(hintId);
    if (!el) return;
    el.textContent = msg;
    el.className = 'input-hint' + (type ? ' ' + type : '');
}

function setInputState(input, valid) {
    input.classList.toggle('is-valid', valid);
    input.classList.toggle('is-invalid', !valid);
}

function validateNama(input) {
    const v = input.value.trim();
    if (v.length < 3) {
        setInputState(input, false);
        setHint('hint-nama', `Nama terlalu pendek (${v.length} karakter, min. 3).`, 'error');
    } else if (/[0-9!@#$%^&*()_+={}\[\]|\\:;"<>?\/]/.test(v)) {
        setInputState(input, false);
        setHint('hint-nama', 'Nama tidak boleh mengandung angka atau karakter khusus.', 'error');
    } else {
        setInputState(input, true);
        setHint('hint-nama', `✓ Nama valid (${v.length} karakter).`, 'success');
    }
}

function validateNIK(input) {
    // Only allow digits
    input.value = input.value.replace(/\D/g, '').slice(0, 16);
    const len = input.value.length;
    const counter = document.getElementById('nik-counter');
    if (counter) {
        counter.textContent = `${len}/16`;
        counter.className = 'char-counter' + (len === 16 ? '' : len > 16 ? ' error' : ' warn');
    }
    if (len === 0) {
        setInputState(input, false);
        setHint('hint-nik', 'NIK wajib diisi — harus tepat 16 digit angka sesuai KTP.', 'error');
    } else if (len < 16) {
        setInputState(input, false);
        setHint('hint-nik', `❌ NIK kurang ${16 - len} digit lagi (sekarang ${len} digit).`, 'error');
    } else {
        setInputState(input, true);
        setHint('hint-nik', '✓ NIK valid (16 digit).', 'success');
    }
}

function validateTanggalLahir(input) {
    if (!input.value) {
        setInputState(input, false);
        setHint('hint-tgl', 'Tanggal lahir wajib diisi.', 'error');
        return;
    }
    const dob = new Date(input.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;

    if (age < 17) {
        setInputState(input, false);
        setHint('hint-tgl', `❌ Usia Anda ${age} tahun. Harus minimal 17 tahun.`, 'error');
    } else if (age > 90) {
        setInputState(input, false);
        setHint('hint-tgl', `❌ Tanggal lahir tampaknya tidak valid (usia ${age} tahun).`, 'error');
    } else {
        setInputState(input, true);
        setHint('hint-tgl', `✓ Usia ${age} tahun.`, 'success');
    }
}

function validateHP(input) {
    const v = input.value.replace(/\s/g, '');
    const re = /^(\+62|62|0)[0-9]{8,13}$/;
    if (!v) {
        setInputState(input, false);
        setHint('hint-hp', 'No. HP wajib diisi.', 'error');
    } else if (!re.test(v)) {
        setInputState(input, false);
        setHint('hint-hp', '❌ Format tidak valid. Contoh: 08123456789 atau +6281234567890', 'error');
    } else {
        setInputState(input, true);
        setHint('hint-hp', '✓ Format HP valid.', 'success');
    }
}

function validateTelp(input, hintId) {
    const v = input.value.trim();
    if (!v) {
        input.classList.remove('is-valid', 'is-invalid');
        setHint(hintId, 'Opsional — kosongkan jika tidak ada.', '');
        return;
    }
    const re = /^(\+62|62|0)[0-9\-\s]{6,14}$/;
    if (!re.test(v)) {
        setInputState(input, false);
        setHint(hintId, '❌ Format tidak valid. Contoh: 022-2031234 atau 08118277506', 'error');
    } else {
        setInputState(input, true);
        setHint(hintId, '✓ Format telepon valid.', 'success');
    }
}

function validateEmail(input) {
    const v = input.value.trim();
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!v) {
        setInputState(input, false);
        setHint('hint-email', 'Email wajib diisi.', 'error');
    } else if (!re.test(v)) {
        setInputState(input, false);
        setHint('hint-email', '❌ Format email tidak valid. Contoh: nama@domain.com', 'error');
    } else {
        setInputState(input, true);
        setHint('hint-email', '✓ Email valid.', 'success');
    }
}

function validateEmailOptional(input, hintId) {
    const v = input.value.trim();
    if (!v) {
        input.classList.remove('is-valid', 'is-invalid');
        setHint(hintId, 'Email kantor (opsional).', '');
        return;
    }
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(v)) {
        setInputState(input, false);
        setHint(hintId, '❌ Format email tidak valid.', 'error');
    } else {
        setInputState(input, true);
        setHint(hintId, '✓ Email valid.', 'success');
    }
}

function validateAlamat(input) {
    const v = input.value.trim();
    if (v.length < 10) {
        setInputState(input, false);
        setHint('hint-alamat', `❌ Alamat terlalu pendek (${v.length} karakter, min. 10).`, 'error');
    } else {
        setInputState(input, true);
        setHint('hint-alamat', `✓ Alamat valid (${v.length} karakter).`, 'success');
    }
}

function validateKodePos(input, hintId = 'hint-kodepos') {
    input.value = input.value.replace(/\D/g, '').slice(0, 5);
    const v = input.value;
    if (!v) {
        input.classList.remove('is-valid', 'is-invalid');
        setHint(hintId, '5 digit angka (opsional).', '');
        return;
    }
    if (v.length !== 5) {
        setInputState(input, false);
        setHint(hintId, `❌ Kode pos harus 5 digit (sekarang ${v.length} digit).`, 'error');
    } else {
        setInputState(input, true);
        setHint(hintId, '✓ Kode pos valid.', 'success');
    }
}

function validateRequired(input, hintId, errorMsg) {
    if (!input.value || input.value.trim() === '') {
        setInputState(input, false);
        setHint(hintId, '❌ ' + errorMsg, 'error');
    } else {
        setInputState(input, true);
        setHint(hintId, '✓', 'success');
    }
}

function validateGDriveLink(input) {
    const v = input.value.trim();
    const btn = document.getElementById('gdrive-preview-btn');

    if (!v) {
        input.classList.remove('is-valid', 'is-invalid');
        setHint('hint-gdrive', 'Paste link Google Drive folder Anda.', '');
        if (btn) btn.classList.add('d-none');
        return;
    }
    const isGDrive = v.startsWith('https://drive.google.com/') || v.startsWith('https://docs.google.com/');
    if (!isGDrive) {
        setInputState(input, false);
        setHint('hint-gdrive', '❌ Link harus berasal dari Google Drive (https://drive.google.com/...)', 'error');
        if (btn) btn.classList.add('d-none');
    } else {
        setInputState(input, true);
        setHint('hint-gdrive', '✓ Link Google Drive valid.', 'success');
        if (btn) {
            btn.classList.remove('d-none');
            btn.href = v;
        }
    }
}

function previewGDrive() {
    const v = document.getElementById('gdrive-link-global')?.value?.trim();
    if (v) window.open(v, '_blank');
}

// ══════════════════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
    log('DOMContentLoaded — init', { currentSection });

    // Tujuan asesmen toggle
    document.querySelectorAll('[name="tujuan_asesmen"]').forEach(r => {
        r.addEventListener('change', function () {
            const el = document.getElementById('tujuan-lainnya-input');
            if (el) el.style.display = this.value === 'Lainnya' ? 'block' : 'none';
        });
    });

    // Run initial validation on pre-filled fields
    const nikInput = document.getElementById('f-nik');
    if (nikInput && nikInput.value) validateNIK(nikInput);

    const hpInput = document.getElementById('f-hp');
    if (hpInput && hpInput.value) validateHP(hpInput);

    const emailInput = document.getElementById('f-email');
    if (emailInput && emailInput.value) validateEmail(emailInput);

    const gdriveInput = document.getElementById('gdrive-link-global');
    if (gdriveInput && gdriveInput.value) validateGDriveLink(gdriveInput);
});


// ── NAVIGATION ─────────────────────────────────────────────
function showSection(num) {
    log(`showSection(${num})`);
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    const target = document.querySelector(`[data-section="${num}"]`);
    if (target) target.classList.add('active');

    document.querySelectorAll('.step').forEach((step, idx) => {
        step.classList.remove('active', 'completed');
        const n = idx + 1;
        if (n < num) step.classList.add('completed');
        if (n === num) step.classList.add('active');
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (num === 5) { fillSummary(); setTimeout(() => SigPadManager.init('asesi', @json(auth()->user()->signature_image)), 150); }

}

async function nextSection() {
    log(`nextSection() — currentSection=${currentSection}`);
    if (!validateCurrentSection()) return;
    const saved = await saveProgress();
    log(`nextSection() — saveProgress: ${saved}`);
    if (!saved) {
        const go = await Swal.fire({
            icon: 'warning', title: 'Peringatan Simpan',
            text: 'Data gagal disimpan otomatis. Tetap lanjutkan?',
            showCancelButton: true, confirmButtonText: 'Lanjutkan', cancelButtonText: 'Coba Lagi',
        });
        if (!go.isConfirmed) return;
    }
    currentSection++;
    showSection(currentSection);
}

function prevSection() {
    currentSection--;
    showSection(currentSection);
}

// ── VALIDATION ─────────────────────────────────────────────
function validateCurrentSection() {
    const section = document.querySelector(`[data-section="${currentSection}"]`);
    if (!section) return true;

    // Run field-specific validators on section 1
    if (currentSection === 1) {
        const nikEl = document.getElementById('f-nik');
        if (nikEl) validateNIK(nikEl);
        const hpEl  = document.getElementById('f-hp');
        if (hpEl)  validateHP(hpEl);
        const emailEl = document.getElementById('f-email');
        if (emailEl) validateEmail(emailEl);
        const tglEl = document.getElementById('f-tgl');
        if (tglEl) validateTanggalLahir(tglEl);
        const alamatEl = document.getElementById('f-alamat');
        if (alamatEl) validateAlamat(alamatEl);
    }

    let valid = true, firstInvalid = null;
    const invalidFields = [];

    section.querySelectorAll('[required]').forEach(input => {
        let ok = true;
        if (input.type === 'radio') {
            ok = [...section.querySelectorAll(`[name="${input.name}"]`)].some(r => r.checked);
        } else {
            ok = input.value.trim() !== '';
        }
        // Also check if already marked invalid by live validators
        if (input.classList.contains('is-invalid')) ok = false;

        if (!ok) {
            input.classList.add('is-invalid');
            if (!firstInvalid) firstInvalid = input;
            invalidFields.push({ name: input.name, value: input.value.substring(0, 30) });
            valid = false;
        }
    });

    log(`validateCurrentSection — section=${currentSection}, valid=${valid}`, invalidFields.length ? invalidFields : 'all OK');

    if (!valid) {
        if (firstInvalid) firstInvalid.focus();
        // Build specific error messages
        const nikEl = document.getElementById('f-nik');
        if (nikEl && nikEl.classList.contains('is-invalid')) {
            Swal.fire({ icon: 'warning', title: 'NIK Tidak Valid', text: `NIK harus tepat 16 digit angka. Saat ini: ${nikEl.value.length} digit.` });
            return false;
        }
        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon lengkapi semua field yang wajib diisi (*) dengan benar.' });
    }
    return valid;
}

// ── SAVE PROGRESS ──────────────────────────────────────────
async function saveProgress() {
    const form = document.getElementById('apl01-form');
    if (!form) return true;
    const formData = new FormData(form);
    try {
        const res = await fetch('{{ route("asesi.apl01.update") }}', {
            method: 'POST', body: formData,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        if (!res.ok) { logError('saveProgress HTTP', res.status); return false; }
        const data = await res.json();
        if (!data.success) { logError('saveProgress fail', data); return false; }
        log('saveProgress ✅');
        return true;
    } catch (e) { logError('saveProgress exception', e); return false; }
}

// ── SAVE BUKTI (hanya link GDrive, tanpa status) ───────────
async function saveBuktiAndNext() {
    const gdriveLink = document.getElementById('gdrive-link-global')?.value?.trim() || '';

    // ── Validasi wajib ──
    if (!gdriveLink) {
        Swal.fire({
            icon: 'warning',
            title: 'Link Google Drive Diperlukan',
            text: 'Mohon isi link folder Google Drive sebelum melanjutkan.',
        });
        document.getElementById('gdrive-link-global')?.focus();
        return;
    }

    const isGDrive = gdriveLink.startsWith('https://drive.google.com/') || gdriveLink.startsWith('https://docs.google.com/');
    if (!isGDrive) {
        Swal.fire({
            icon: 'warning',
            title: 'Link Tidak Valid',
            text: 'Link harus berasal dari Google Drive (https://drive.google.com/...).',
        });
        document.getElementById('gdrive-link-global')?.focus();
        return;
    }

    const btn = document.getElementById('btn-next-bukti');
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...'; }

    log('saveBuktiAndNext — gdrive:', gdriveLink);

    const rows = [];
    document.querySelectorAll('.bukti-id-ref').forEach(el => {
        rows.push({ id: el.dataset.buktiId, status: 'Tidak Ada', link: gdriveLink });
    });

    const hiddenBuktis = document.querySelectorAll('[name="bukti_id[]"]');
    if (hiddenBuktis.length > 0 && rows.length === 0) {
        hiddenBuktis.forEach(inp => {
            rows.push({ id: inp.value, status: 'Tidak Ada', link: gdriveLink });
        });
    }

    log('saveBuktiAndNext — rows:', rows);

    try {
        const res = await fetch('{{ route("asesi.apl01.bukti.save") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ rows }),
        });
        const data = await res.json();
        log('saveBuktiAndNext — response:', data);
    } catch (e) {
        logError('saveBuktiAndNext exception', e);
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = 'Selanjutnya <i class="bi bi-arrow-right"></i>'; }
    }

    currentSection++;
    showSection(currentSection);
}

// ── FILL SUMMARY ───────────────────────────────────────────
function fillSummary() {
    const val   = name => document.querySelector(`[name="${name}"]`)?.value || '-';
    const radio = name => document.querySelector(`[name="${name}"]:checked`)?.value || '-';
    document.getElementById('summary-nama').textContent   = val('nama_lengkap');
    document.getElementById('summary-nik').textContent    = val('nik');
    document.getElementById('summary-ttl').textContent    = val('tempat_lahir') + ', ' + val('tanggal_lahir');
    document.getElementById('summary-hp').textContent     = val('hp');
    document.getElementById('summary-email').textContent  = val('email');
    document.getElementById('summary-tujuan').textContent = radio('tujuan_asesmen');
    document.getElementById('summary-gdrive').textContent = document.getElementById('gdrive-link-global')?.value?.trim() || '(belum diisi)';
}

// ── SUBMIT ─────────────────────────────────────────────────
async function submitForm() {
    if (!document.getElementById('agreement-check')?.checked) {
        Swal.fire({ icon: 'warning', title: 'Persetujuan Diperlukan', text: 'Anda harus mencentang pernyataan persetujuan.' });
        return;
    }
    if (SigPadManager.isEmpty('asesi')){
        Swal.fire({ icon: 'warning', title: 'Tanda Tangan Diperlukan', text: 'Mohon tanda tangan di kotak yang tersedia.' });
        return;
    }

    const nama = document.querySelector('[name="nama_lengkap"]')?.value || '-';
    const nik  = document.querySelector('[name="nik"]')?.value || '-';

    const result = await Swal.fire({
        title: 'Konfirmasi Submit APL-01',
        html: `<div class="text-start">
            <p class="mb-3">Anda akan mensubmit formulir APL-01:</p>
            <div class="bg-light rounded p-3 mb-3">
                <div><strong>Nama:</strong> ${nama}</div>
                <div><strong>NIK:</strong> ${nik}</div>
            </div>
            <div class="alert alert-info py-2 mb-2 small">
                <i class="bi bi-info-circle me-1"></i>
                PDF APL-01 akan tersedia untuk diunduh setelah Admin LSP memverifikasi formulir ini.
            </div>
            <div class="alert alert-danger py-2 mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>Setelah submit, data tidak dapat diubah!</strong>
            </div>
        </div>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Ya, Submit',
        cancelButtonText: 'Periksa Ulang',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    });

    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    const btn = document.getElementById('btn-submit');
    if (btn) btn.disabled = true;

    await saveProgress();

    const submitData = new FormData();
    const signature = await SigPadManager.prepareAndGet('asesi');

    submitData.append('signature', signature);

    try {
        const res = await fetch('{{ route("asesi.apl01.submit") }}', {
            method: 'POST', body: submitData,
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({
                icon: 'success', title: 'APL-01 Berhasil Disubmit!',
                html: 'Formulir APL-01 Anda telah disubmit dan akan diverifikasi oleh Admin LSP.<br><small class="text-muted">PDF dapat diunduh setelah admin melakukan verifikasi.</small>',
                confirmButtonText: 'OK',
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
            if (btn) btn.disabled = false;
        }
    } catch (err) {
        logError('submitForm exception', err);
        Swal.fire('Error', 'Terjadi kesalahan sistem. Silakan coba lagi.', 'error');
        if (btn) btn.disabled = false;
    }
}
</script>

{{-- Hidden inputs untuk semua bukti ID agar bisa dikirim saat saveBuktiAndNext --}}
@if(!$aplsatu || $aplsatu->status === 'draft' || $aplsatu->status === 'returned')
<div id="bukti-id-store" style="display:none;">
    @foreach($aplsatu->buktiKelengkapan as $bukti)
    <input type="hidden" name="bukti_id[]" value="{{ $bukti->id }}">
    @endforeach
</div>
@endif
@endpush