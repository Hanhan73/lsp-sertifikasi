<script>
(function() {
    // ── Format rupiah ──────────────────────────────────────────────────────
    function formatRupiah(angka) {
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function unformat(str) {
        return parseInt(str.replace(/\./g, '') || '0', 10);
    }

    // ── Tarif display <-> hidden ───────────────────────────────────────────
    const tarifDisplay = document.getElementById('tarif_display');
    const tarifHidden = document.getElementById('tarif_hidden');

    tarifDisplay.addEventListener('input', function() {
        const raw = unformat(this.value);
        tarifHidden.value = raw;
        this.value = raw ? formatRupiah(raw) : '';
        hitungTotal();
    });

    // ── Hitung total ───────────────────────────────────────────────────────
    function hitungTotal() {
        const tarif = parseInt(tarifHidden.value || '0', 10);
        const jumlah = parseInt(document.getElementById('jumlah').value || '0', 10);
        const total = tarif * jumlah;
        document.getElementById('total_display').value = total ? formatRupiah(total) : '';
    }

    document.getElementById('jumlah').addEventListener('input', hitungTotal);

    // Trigger on load (edit mode)
    hitungTotal();

    // ── Image preview ──────────────────────────────────────────────────────
    function previewImage(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input) return;

        input.addEventListener('change', function() {
            preview.innerHTML = '';
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML =
                        '<img src="' + e.target.result + '" ' +
                        'style="max-height:120px;border-radius:6px;border:1px solid #dee2e6;" ' +
                        'class="mt-1">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    previewImage('bukti_transaksi', 'preview_transaksi');
    previewImage('bukti_kegiatan', 'preview_kegiatan');

    // ── Toggle penerima ────────────────────────────────────────────────────
    const radios = document.querySelectorAll('input[name="tipe_penerima"]');
    const wrapAsesor = document.getElementById('wrap_asesor');
    const wrapManual = document.getElementById('wrap_manual');
    const asesorSel = document.getElementById('asesor_id');
    const namaManual = document.getElementById('nama_manual');
    const namaHidden = document.getElementById('nama_penerima_hidden');

    function applyTipe(tipe) {
        if (tipe === 'asesor') {
            wrapAsesor.style.display = '';
            wrapManual.style.display = 'none';
            // Sync hidden dari dropdown
            const opt = asesorSel.options[asesorSel.selectedIndex];
            namaHidden.value = opt ? (opt.dataset.nama || '') : '';
        } else {
            wrapAsesor.style.display = 'none';
            wrapManual.style.display = '';
            namaHidden.value = namaManual.value;
        }
    }

    radios.forEach(r => r.addEventListener('change', () => applyTipe(r.value)));

    asesorSel.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        namaHidden.value = opt ? (opt.dataset.nama || '') : '';
    });

    namaManual.addEventListener('input', function() {
        namaHidden.value = this.value;
    });

    // Init on load
    const checkedTipe = document.querySelector('input[name="tipe_penerima"]:checked');
    if (checkedTipe) applyTipe(checkedTipe.value);
})();
</script>