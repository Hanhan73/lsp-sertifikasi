@extends('layouts.app')

@section('title', 'Ubah Password')
@section('page-title', 'Ubah Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-lock" style="font-size: 4rem; color: #ffc107;"></i>

                <h3 class="mt-4">Selamat Datang!</h3>
                <p class="text-muted">
                    Ini adalah login pertama Anda. Untuk keamanan, silakan ubah password default Anda.
                </p>

                <div class="alert alert-info mt-4 mb-4 text-start">
                    <i class="bi bi-info-circle"></i>
                    <strong>Catatan Penting:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Anda telah didaftarkan melalui <strong>Pendaftaran Kolektif</strong> oleh TUK</li>
                        <li>Password default telah diberikan oleh TUK Anda</li>
                        <li>Password baru harus minimal 8 karakter</li>
                        <li>Gunakan kombinasi huruf, angka, dan simbol untuk keamanan lebih baik</li>
                        <li>Jangan berikan password Anda kepada siapapun</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('asesi.first-login') }}" class="text-start">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required minlength="8">
                        <small class="text-muted">Minimal 8 karakter</small>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                            name="password_confirmation" required minlength="8">
                        <small class="text-muted">Ketik ulang password baru Anda</small>
                        @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Ubah Password & Lanjutkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-question-circle"></i> Langkah Selanjutnya</h6>
                <p class="mb-0 small text-muted">
                    Setelah mengubah password, Anda akan diarahkan ke dashboard untuk melengkapi data pribadi Anda.
                    Silakan siapkan dokumen-dokumen berikut:
                </p>
                <ul class="small text-muted mt-2 mb-0">
                    <li>Pas Foto formal 3x4 (latar merah)</li>
                    <li>Scan KTP</li>
                    <li>Scan Ijazah/Transkrip terakhir</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show/Hide password toggle
    $('<button type="button" class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-3" style="z-index: 10;"><i class="bi bi-eye"></i></button>')
        .insertAfter('input[type="password"]')
        .on('click', function() {
            const input = $(this).prev('input');
            const icon = $(this).find('i');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

    // Position button correctly
    $('input[type="password"]').parent().css('position', 'relative');
});
</script>
@endpush