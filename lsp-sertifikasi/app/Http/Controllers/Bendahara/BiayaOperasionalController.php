<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\BiayaOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\JournalService;

class BiayaOperasionalController extends Controller
{
    public function index(Request $request)
    {
        $query = BiayaOperasional::with(['creator', 'asesor'])->orderByDesc('tanggal')->orderByDesc('id');

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        $biayaList = $query->paginate(20)->withQueryString();

        $totalKeseluruhan = BiayaOperasional::when($request->filled('bulan'), fn($q) => $q->whereMonth('tanggal', $request->bulan))
            ->when($request->filled('tahun'), fn($q) => $q->whereYear('tanggal', $request->tahun))
            ->sum('total');

        $tahunList = BiayaOperasional::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->orderByDesc('tahun')->pluck('tahun');

        $asesors = \App\Models\Asesor::orderBy('nama')->get(['id', 'nama']); // ← untuk dropdown

        return view('bendahara.biaya-operasional.index', compact(
            'biayaList',
            'totalKeseluruhan',
            'tahunList',
            'asesors'
        ));
    }

    public function create()
    {
        $asesors = \App\Models\Asesor::orderBy('nama')->get(['id', 'nama']);
        return view('bendahara.biaya-operasional.create', compact('asesors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'         => 'required|date',
            'uraian'          => 'required|string|max:255',
            'tipe_penerima'   => 'required|in:asesor,manual',
            'asesor_id'       => 'nullable|exists:asesors,id',
            'nama_penerima'   => 'required|string|max:255',
            'tarif'           => 'required|integer|min:1',
            'jumlah'          => 'required|integer|min:1',
            'keterangan'      => 'nullable|string|max:1000',
            'bukti_transaksi' => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
            'bukti_kegiatan'  => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
        ]);

        $validated['total']      = $validated['tarif'] * $validated['jumlah'];
        $validated['nomor']      = BiayaOperasional::generateNomor();
        $validated['created_by'] = auth()->id();

        // Kalau tipe manual, asesor_id di-null
        if ($request->tipe_penerima === 'manual') {
            $validated['asesor_id'] = null;
        }

        if ($request->hasFile('bukti_transaksi')) {
            $validated['bukti_transaksi'] = $request->file('bukti_transaksi')
                ->store('biaya-operasional/transaksi', 'public');
        }
        if ($request->hasFile('bukti_kegiatan')) {
            $validated['bukti_kegiatan'] = $request->file('bukti_kegiatan')
                ->store('biaya-operasional/kegiatan', 'public');
        }

        unset($validated['tipe_penerima']); // bukan kolom DB
        $biaya = BiayaOperasional::create($validated);

        // Inject jurnal otomatis
        try {
            app(JournalService::class)->jurnalBiayaOperasional($biaya);
        } catch (\Exception $e) {
            \Log::warning('Gagal buat jurnal biaya ops: ' . $e->getMessage());
        }

        return redirect()->route('bendahara.biaya-operasional.index')
            ->with('success', 'Biaya operasional berhasil ditambahkan.');
    }

    public function edit(BiayaOperasional $biayaOperasional)
    {
        $asesors = \App\Models\Asesor::orderBy('nama')->get(['id', 'nama']);
        return view('bendahara.biaya-operasional.edit', compact('biayaOperasional', 'asesors'));
    }

    public function update(Request $request, BiayaOperasional $biayaOperasional)
    {
        $validated = $request->validate([
            'tanggal'         => 'required|date',
            'uraian'          => 'required|string|max:255',
            'tipe_penerima'   => 'required|in:asesor,manual',
            'asesor_id'       => 'nullable|exists:asesors,id',
            'nama_penerima'   => 'required|string|max:255',
            'tarif'           => 'required|integer|min:1',
            'jumlah'          => 'required|integer|min:1',
            'keterangan'      => 'nullable|string|max:1000',
            'bukti_transaksi' => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
            'bukti_kegiatan'  => 'nullable|image|mimes:jpg,jpeg,png|max:3072',
        ]);

        $validated['total'] = $validated['tarif'] * $validated['jumlah'];

        if ($request->tipe_penerima === 'manual') {
            $validated['asesor_id'] = null;
        }

        if ($request->hasFile('bukti_transaksi')) {
            if ($biayaOperasional->bukti_transaksi) {
                Storage::disk('public')->delete($biayaOperasional->bukti_transaksi);
            }
            $validated['bukti_transaksi'] = $request->file('bukti_transaksi')
                ->store('biaya-operasional/transaksi', 'public');
        }
        if ($request->hasFile('bukti_kegiatan')) {
            if ($biayaOperasional->bukti_kegiatan) {
                Storage::disk('public')->delete($biayaOperasional->bukti_kegiatan);
            }
            $validated['bukti_kegiatan'] = $request->file('bukti_kegiatan')
                ->store('biaya-operasional/kegiatan', 'public');
        }

        unset($validated['tipe_penerima']);
        $biayaOperasional->update($validated);

        return redirect()->route('bendahara.biaya-operasional.index')
            ->with('success', 'Biaya operasional berhasil diperbarui.');
    }
}
