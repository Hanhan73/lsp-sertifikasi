@extends('layouts.app')
@section('title', 'Tambah Surat Keluar')
@section('page-title', 'Tambah Surat Keluar')
@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="card" style="max-width:720px">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-envelope-arrow-up me-2"></i>Tambah Surat Keluar</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.surat.keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.surat.keluar._form', ['surat' => null])
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.surat.keluar.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const tree = {
        ADM: {
            '00': { label: 'BNSP', items: { '01': 'Sosialisasi LSP' } },
        },
        OG: {
            '00': { label: 'Tempat Uji Kompetensi', items: { '01': 'Sosialisasi Kegiatan', '02': 'Komite Skema', '03': 'Komite Teknis', '04': 'Penanda tangan sertifikat' } },
            '10': { label: 'Tata Naskah', items: { '01': 'Logo LSPKAP' } },
            '20': { label: 'Personil LSP', items: { '01': 'Dewan Pengarah', '02': 'Komite Skema', '03': 'Asesor', '04': 'Staf' } },
        },
        KU: {
            '00': { label: 'Pelaksanaan Ujikom', items: { '01': 'Invoice', '02': 'Pembayaran UJK', '03': 'Honor Asesor', '04': 'Honor panitia ujikom', '05': 'Pembagian alokasi keuangan', '06': 'Fee marketing', '07': 'Distribusi sertifikat', '08': 'Pembayaran TUK' } },
            '10': { label: 'Operasional LSP', items: { '01': 'Honor kegiatan', '02': 'Honor pengurus', '03': 'Akomodasi', '04': 'Transportasi', '05': 'Konsumsi', '06': 'Pengadaan ATK', '07': 'Pengadaan peralatan/perlengkapan kantor' } },
        },
        SER: {
            '00': { label: 'Kerja sama/MoU', items: {} },
            '10': { label: 'Tempat Uji Kompetensi', items: { '01': 'Penawaran menjadi TUK', '02': 'Pendaftaran menjadi TUK', '03': 'Verifikasi TUK', '04': 'Penetapan TUK Terverifikasi', '05': 'Personil TUK', '06': 'Peminjaman TUK' } },
            '20': { label: 'Uji Kompetensi', items: { '01': 'Sosialisasi', '02': 'Penawaran UJK', '03': 'Pendaftaran UJK', '04': 'Pra Asesmen', '05': 'Penugasan Asesor', '06': 'Pleno Hasil Ujikom', '07': 'Penetapan Kelulusan', '08': 'Pengajuan Blanko', '09': 'Sertifikat Kompetensi', '10': 'Materi Uji Kompetensi' } },
            '30': { label: 'Asesor', items: { '01': 'Pelatihan Asesor Kompetensi BNSP', '02': 'RCC Asesor Kompetensi BNSP', '03': 'Sertifikat Pelatihan/Upgrading', '04': 'Penetapan Asesor LSP', '05': 'Pelatihan Teknis Calon Asesor LSP' } },
            '40': { label: 'Skema Sertifikasi', items: { '01': 'Penambahan ruang lingkup', '02': 'Verifikasi skema', '03': 'Perangkat asesmen/MUK', '04': 'Uji coba Asesmen', '05': 'Full asesmen', '06': 'Witness' } },
        },
        MT: {
            '00': { label: 'Panduan Mutu', items: {} },
        },
    };

    const selGrup  = document.getElementById('selGrup');
    const selSub   = document.getElementById('selSub');
    const selItem  = document.getElementById('selItem');
    const inputKode = document.getElementById('inputKode');
    const kodePreview = document.getElementById('kodePreview');
    const btnReset = document.getElementById('btnResetKode');

    function buildKode() {
        const g = selGrup.value;
        const s = selSub.value;
        const i = selItem.value;
        if (!g) return '';
        if (!s) return g;
        if (!i) return `${g}.${s}`;
        return `${g}.${s}.${i}`;
    }

    function updateKode() {
        const kode = buildKode();
        inputKode.value    = kode;
        kodePreview.textContent = kode || '—';
        btnReset.style.display  = kode ? 'inline-block' : 'none';
    }

    function resetSelect(el, placeholder) {
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled  = true;
    }

    selGrup.addEventListener('change', function () {
        resetSelect(selSub, '— Pilih Sub —');
        resetSelect(selItem, '— Pilih Item (opsional) —');
        updateKode();

        const grup = tree[this.value];
        if (!grup) return;

        Object.entries(grup).forEach(([subKode, sub]) => {
            const opt = new Option(`${this.value}.${subKode} — ${sub.label}`, subKode);
            selSub.add(opt);
        });
        selSub.disabled = false;
    });

    selSub.addEventListener('change', function () {
        resetSelect(selItem, '— Pilih Item (opsional) —');
        updateKode();

        const grup    = tree[selGrup.value];
        const subData = grup?.[this.value];
        if (!subData || !Object.keys(subData.items).length) return;

        Object.entries(subData.items).forEach(([itemKode, item]) => {
            const opt = new Option(`${selGrup.value}.${this.value}.${itemKode} — ${item}`, itemKode);
            selItem.add(opt);
        });
        selItem.disabled = false;
    });

    selItem.addEventListener('change', updateKode);

    btnReset.addEventListener('click', function () {
        selGrup.value = '';
        resetSelect(selSub, '— Pilih Sub —');
        resetSelect(selItem, '— Pilih Item (opsional) —');
        updateKode();
    });

    // Restore nilai saat edit
    const existing = inputKode.value;
    if (existing) {
        const parts = existing.split('.');
        const g = parts[0], s = parts[1], it = parts[2];

        if (g && tree[g]) {
            selGrup.value = g;
            selGrup.dispatchEvent(new Event('change'));

            if (s && tree[g][s]) {
                selSub.value = s;
                selSub.dispatchEvent(new Event('change'));

                if (it) {
                    selItem.value = it;
                }
            }
        }
        updateKode();
    }
})();
</script>
@endpush