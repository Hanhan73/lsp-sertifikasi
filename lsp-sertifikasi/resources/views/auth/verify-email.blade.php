<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - LSP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #dfdfdf 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body p-5 text-center">
                        @if(Auth::user()->hasVerifiedEmail())
                        {{-- ✅ Jika sudah verified --}}
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        </div>

                        <h3 class="mb-3 text-success">Email Sudah Terverifikasi!</h3>

                        <p class="text-muted mb-4">
                            Email Anda sudah berhasil diverifikasi. Anda akan diarahkan ke dashboard dalam <span
                                id="countdown">3</span> detik...
                        </p>

                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>

                        <p>
                            <small class="text-muted">
                                Atau <a href="{{ route('asesi.dashboard') }}" class="text-decoration-none">klik di
                                    sini</a> untuk langsung ke dashboard.
                            </small>
                        </p>

                        <script>
                        // Auto redirect setelah 3 detik
                        let countdown = 3;
                        const countdownElement = document.getElementById('countdown');

                        const interval = setInterval(() => {
                            countdown--;
                            countdownElement.textContent = countdown;

                            if (countdown <= 0) {
                                clearInterval(interval);
                                window.location.href = "{{ route('asesi.dashboard') }}";
                            }
                        }, 1000);
                        </script>
                        @else
                        {{-- ✅ Jika belum verified --}}
                        <div class="mb-4">
                            <i class="bi bi-envelope-check" style="font-size: 4rem; color: #667eea;"></i>
                        </div>

                        <h3 class="mb-3">Verifikasi Email Anda</h3>

                        <p class="text-muted mb-4">
                            Terima kasih telah mendaftar! Kami telah mengirimkan link verifikasi ke email Anda.
                            Silakan cek inbox atau folder spam Anda.
                        </p>

                        @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> Link verifikasi baru telah dikirim ke email Anda!
                        </div>
                        @endif

                        @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                        </div>
                        @endif

                        @if (session('warning'))
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
                        </div>
                        @endif

                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                Belum menerima email?
                            </small>
                        </div>

                        <form method="POST" action="{{ route('verification.send') }}" id="resendForm">
                            @csrf
                            <button type="submit" class="btn btn-primary mb-3" id="resendBtn">
                                <i class="bi bi-arrow-repeat"></i> Kirim Ulang Email Verifikasi
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-left"></i> Logout
                            </button>
                        </form>

                        <hr class="my-4">

                        <small class="text-muted">
                            Jika Anda tidak mendaftar, abaikan email ini dan hubungi administrator.
                        </small>

                        <script>
                        // Disable button sementara setelah klik untuk prevent spam
                        document.getElementById('resendForm').addEventListener('submit', function() {
                            const btn = document.getElementById('resendBtn');
                            btn.disabled = true;
                            btn.innerHTML =
                                '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

                            // Re-enable after 60 seconds (rate limit)
                            setTimeout(() => {
                                btn.disabled = false;
                                btn.innerHTML =
                                    '<i class="bi bi-arrow-repeat"></i> Kirim Ulang Email Verifikasi';
                            }, 60000);
                        });
                        </script>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>