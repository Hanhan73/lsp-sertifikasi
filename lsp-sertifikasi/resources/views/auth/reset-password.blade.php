<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - LSP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-card {
            background: white;
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .auth-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.5rem;
            color: white;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group-text {
            background: white;
            border: 1.5px solid #e2e8f0;
            border-left: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.2s;
        }

        .input-group-text:hover {
            color: #667eea;
        }

        .input-group .form-control {
            border-radius: 8px 0 0 8px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn-primary-custom:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-primary-custom:active {
            transform: translateY(0);
        }

        .back-link {
            color: #667eea;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .footer-text {
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            margin-top: 1.5rem;
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<div>
    <div class="auth-card">

        <div class="auth-icon">
            <i class="bi bi-shield-lock"></i>
        </div>

        <h4 class="text-center fw-bold mb-1">Buat Password Baru</h4>
        <p class="text-center text-muted small mb-4">
            Masukkan password baru untuk akun Anda
        </p>

        {{-- Alert error --}}
        @if ($errors->any())
        <div class="alert alert-danger py-2 mb-3">
            <i class="bi bi-exclamation-circle me-1"></i>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Alert success --}}
        @if (session('success'))
        <div class="alert alert-success py-2 mb-3">
            <i class="bi bi-check-circle me-1"></i>
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            {{-- Token hidden --}}
            <input type="hidden" name="token" value="{{ $token }}">

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text" style="border-right:none;border-radius:8px 0 0 8px;">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email', request()->email) }}"
                        placeholder="your@email.com"
                        required
                        autocomplete="email"
                        style="border-left:none;border-radius:0 8px 8px 0;">
                </div>
                @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password Baru</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        id="password"
                        placeholder="Minimal 8 karakter"
                        required
                        autocomplete="new-password">
                    <span class="input-group-text" onclick="togglePassword('password', 'icon-pw')">
                        <i class="bi bi-eye" id="icon-pw"></i>
                    </span>
                </div>
                <div class="password-strength bg-light" id="strength-bar"></div>
                <div class="small mt-1" id="strength-text" style="color:#aaa;"></div>
                @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-4">
                <label class="form-label small fw-semibold">Konfirmasi Password</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control"
                        name="password_confirmation"
                        id="password_confirmation"
                        placeholder="Ulangi password baru"
                        required
                        autocomplete="new-password">
                    <span class="input-group-text" onclick="togglePassword('password_confirmation', 'icon-pc')">
                        <i class="bi bi-eye" id="icon-pc"></i>
                    </span>
                </div>
                <div class="small mt-1" id="match-text"></div>
            </div>

            <button type="submit" class="btn btn-primary btn-primary-custom w-100 text-white">
                <i class="bi bi-check-circle me-1"></i> Reset Password
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="back-link">
                ← Kembali ke Login
            </a>
        </div>

    </div>

    <p class="footer-text">© {{ date('Y') }} LSP System. All rights reserved.</p>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const field = document.getElementById(fieldId);
    const icon  = document.getElementById(iconId);
    if (field.type === 'password') {
        field.type = 'password' === field.type ? 'text' : 'password';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function () {
    const val = this.value;
    const bar = document.getElementById('strength-bar');
    const txt = document.getElementById('strength-text');

    let score = 0;
    if (val.length >= 8)              score++;
    if (/[A-Z]/.test(val))            score++;
    if (/[0-9]/.test(val))            score++;
    if (/[^A-Za-z0-9]/.test(val))     score++;

    const levels = [
        { color: '#ef4444', label: 'Sangat lemah', width: '25%' },
        { color: '#f97316', label: 'Lemah',        width: '50%' },
        { color: '#eab308', label: 'Cukup',        width: '75%' },
        { color: '#22c55e', label: 'Kuat',         width: '100%' },
    ];

    if (val.length === 0) {
        bar.style.width     = '0';
        bar.style.background = '#e2e8f0';
        txt.textContent     = '';
        return;
    }

    const lvl = levels[score - 1] || levels[0];
    bar.style.width      = lvl.width;
    bar.style.background = lvl.color;
    txt.textContent      = lvl.label;
    txt.style.color      = lvl.color;
});

// Password match indicator
document.getElementById('password_confirmation').addEventListener('input', function () {
    const pw   = document.getElementById('password').value;
    const conf = this.value;
    const txt  = document.getElementById('match-text');

    if (conf.length === 0) {
        txt.textContent = '';
        return;
    }

    if (pw === conf) {
        txt.textContent = 'Password cocok';
        txt.style.color = '#22c55e';
    } else {
        txt.textContent = 'Password tidak cocok';
        txt.style.color = '#ef4444';
    }
});
</script>

</body>
</html>