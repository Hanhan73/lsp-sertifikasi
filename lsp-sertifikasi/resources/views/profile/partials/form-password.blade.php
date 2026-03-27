{{-- resources/views/profile/partials/form-password.blade.php --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold">
        <i class="bi bi-key me-2 text-primary"></i>Ganti Password
    </div>
    <div class="card-body p-4">
        <form action="{{ route('profile.update-password') }}" method="POST" id="form-ganti-password">
            @csrf @method('PUT')

            <div class="row g-3">
                {{-- Password Lama --}}
                <div class="col-12">
                    <label class="form-label small fw-semibold">
                        Password Lama <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" name="current_password" id="pw-current"
                               class="form-control @error('current_password') is-invalid @enderror"
                               placeholder="Masukkan password lama"
                               autocomplete="current-password" required>
                        <button type="button" class="btn btn-outline-secondary toggle-pw"
                                data-target="pw-current" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Password Baru --}}
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">
                        Password Baru <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" id="pw-new"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimal 8 karakter"
                               autocomplete="new-password" required>
                        <button type="button" class="btn btn-outline-secondary toggle-pw"
                                data-target="pw-new" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Strength bar --}}
                    <div id="pw-strength-wrap" class="mt-2" style="display:none;">
                        <div class="progress" style="height:4px;">
                            <div class="progress-bar" id="pw-strength-bar" style="width:0%;transition:width .3s,background-color .3s;"></div>
                        </div>
                        <small id="pw-strength-label" class="text-muted"></small>
                    </div>
                </div>

                {{-- Konfirmasi --}}
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">
                        Konfirmasi Password <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="pw-confirm"
                               class="form-control"
                               placeholder="Ulangi password baru"
                               autocomplete="new-password" required>
                        <button type="button" class="btn btn-outline-secondary toggle-pw"
                                data-target="pw-confirm" tabindex="-1">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div id="pw-match-msg" class="form-text" style="display:none;"></div>
                </div>
            </div>

            <div class="mt-3 d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary btn-sm" id="btn-pw-submit">
                    <i class="bi bi-shield-lock me-1"></i> Simpan Password
                </button>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>Min. 8 karakter
                </small>
            </div>
        </form>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    // ── Toggle show/hide password ──────────────────────────
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            const icon  = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    // ── Strength indicator ────────────────────────────────
    const pwNew      = document.getElementById('pw-new');
    const sBar       = document.getElementById('pw-strength-bar');
    const sLabel     = document.getElementById('pw-strength-label');
    const sWrap      = document.getElementById('pw-strength-wrap');

    if (pwNew) {
        pwNew.addEventListener('input', function () {
            const v = this.value;
            if (!v) { sWrap.style.display = 'none'; return; }
            sWrap.style.display = 'block';

            let score = 0;
            if (v.length >= 8)           score++;
            if (/[A-Z]/.test(v))         score++;
            if (/[a-z]/.test(v))         score++;
            if (/[0-9]/.test(v))         score++;
            if (/[^A-Za-z0-9]/.test(v))  score++;

            const levels = [
                { pct: 20,  bg: '#dc3545', txt: 'Sangat lemah' },
                { pct: 40,  bg: '#fd7e14', txt: 'Lemah' },
                { pct: 60,  bg: '#ffc107', txt: 'Cukup' },
                { pct: 80,  bg: '#20c997', txt: 'Kuat' },
                { pct: 100, bg: '#198754', txt: 'Sangat kuat' },
            ];
            const lvl = levels[score - 1] ?? levels[0];
            sBar.style.width            = lvl.pct + '%';
            sBar.style.backgroundColor  = lvl.bg;
            sLabel.textContent          = lvl.txt;
            sLabel.style.color          = lvl.bg;

            checkMatch();
        });
    }

    // ── Match checker ─────────────────────────────────────
    const pwConfirm = document.getElementById('pw-confirm');
    const matchMsg  = document.getElementById('pw-match-msg');

    function checkMatch() {
        if (!pwConfirm || !pwConfirm.value) { if(matchMsg) matchMsg.style.display = 'none'; return; }
        matchMsg.style.display = 'block';
        const ok = pwNew.value === pwConfirm.value;
        matchMsg.textContent = ok ? '✅ Password cocok' : '❌ Password tidak cocok';
        matchMsg.className   = 'form-text ' + (ok ? 'text-success' : 'text-danger');
        pwConfirm.classList.toggle('is-valid',   ok);
        pwConfirm.classList.toggle('is-invalid', !ok);
    }

    if (pwNew)     pwNew.addEventListener('input', checkMatch);
    if (pwConfirm) pwConfirm.addEventListener('input', checkMatch);

    // ── Loading state + validasi sebelum submit ───────────
    const form     = document.getElementById('form-ganti-password');
    const submitBtn = document.getElementById('btn-pw-submit');

    if (form) {
        form.addEventListener('submit', function (e) {
            if (pwNew.value && pwConfirm.value && pwNew.value !== pwConfirm.value) {
                e.preventDefault();
                pwConfirm.classList.add('is-invalid');
                pwConfirm.focus();
                return;
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
            }
        });
    }
})();
</script>
@endpush
@endonce