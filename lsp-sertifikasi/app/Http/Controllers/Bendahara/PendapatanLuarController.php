<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\PendapatanLuar;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PendapatanLuarController extends Controller
{
    public function index(Request $request)
    {
        $query = PendapatanLuar::with(['coa', 'creator'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('uraian', 'like', '%' . $request->search . '%')
                  ->orWhere('kategori', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        $pendapatans = $query->paginate(20)->withQueryString();

        $totalFiltered = $query->sum('jumlah');

        // Akun pendapatan untuk dropdown (tipe = pendapatan, aktif)
        $coaOptions = ChartOfAccount::where('tipe', 'pendapatan')
            ->where('is_active', true)
            ->orderBy('kode')
            ->get();

        // Tahun tersedia untuk filter
        $tahunList = PendapatanLuar::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $rowData = $pendapatans->keyBy('id')->map(function ($i) {
            return [
                'id'       => $i->id,
                'tanggal'  => $i->tanggal->format('Y-m-d'),
                'uraian'   => $i->uraian,
                'kategori' => $i->kategori,
                'jumlah'   => $i->jumlah,
                'coa_id'   => $i->coa_id,
                'catatan'  => $i->catatan,
            ];
        })->values()->keyBy('id');

        return view('bendahara.pendapatan-luar.index', compact(
            'pendapatans', 'totalFiltered', 'coaOptions', 'tahunList', 'rowData'
        ));
    }

      public function store(Request $request)
    {
        $modeBaru = $request->filled('coa_baru_kode') && $request->filled('coa_baru_nama');
 
        $request->validate([
            'tanggal'       => 'required|date',
            'uraian'        => 'required|string|max:255',
            'kategori'      => 'nullable|string|max:100',
            'jumlah'        => 'required|integer|min:1',
            // coa_id wajib HANYA kalau tidak mode baru
            'coa_id'        => $modeBaru ? 'nullable' : 'required|integer|exists:chart_of_accounts,id',
            'bukti'         => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'catatan'       => 'nullable|string|max:1000',
            'coa_baru_kode' => $modeBaru ? 'required|string|max:20|unique:chart_of_accounts,kode' : 'nullable',
            'coa_baru_nama' => $modeBaru ? 'required|string|max:255' : 'nullable',
        ], [
            'bukti.required'        => 'Bukti dokumen wajib diupload.',
            'bukti.mimes'           => 'Format file harus jpg, png, atau pdf.',
            'coa_id.required'       => 'Pilih akun pendapatan.',
            'coa_baru_kode.unique'  => 'Kode akun sudah digunakan.',
            'coa_baru_kode.required'=> 'Kode akun baru wajib diisi.',
            'coa_baru_nama.required'=> 'Nama akun baru wajib diisi.',
        ]);
 
        DB::beginTransaction();
        try {
            $coaId = (int) $request->coa_id;
 
            if ($modeBaru) {
                $newCoa = ChartOfAccount::create([
                    'kode'      => $request->coa_baru_kode,
                    'nama'      => $request->coa_baru_nama,
                    'tipe'      => 'pendapatan',
                    'sub_tipe'  => null,
                    'is_active' => true,
                    'is_system' => false,
                    'urutan'    => ChartOfAccount::where('tipe', 'pendapatan')->max('urutan') + 10,
                ]);
                $coaId = $newCoa->id;
            }
 
            $file = $request->file('bukti');
            $path = $file->store('pendapatan-luar/' . now()->format('Y/m'), 'private');
 
            $pendapatan = PendapatanLuar::create([
                'tanggal'    => $request->tanggal,
                'uraian'     => $request->uraian,
                'kategori'   => $request->kategori,
                'jumlah'     => (int) $request->jumlah,
                'coa_id'     => $coaId,
                'bukti_path' => $path,
                'bukti_name' => $file->getClientOriginalName(),
                'catatan'    => $request->catatan,
                'created_by' => auth()->id(),
            ]);
 
            app(JournalService::class)->jurnalPendapatanLuar($pendapatan->load('coa'));
 
            DB::commit();
 
            return redirect()->route('bendahara.pendapatan-luar.index')
                ->with('success', 'Pendapatan berhasil dicatat dan jurnal telah dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[PENDAPATAN-LUAR] ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, PendapatanLuar $pendapatanLuar)
    {
        // Cek apakah jurnal sudah dibuat — kalau sudah, data finansial tidak bisa diubah
        $sudahJurnal = \App\Models\JournalEntry::existsFor(
            PendapatanLuar::class, $pendapatanLuar->id
        );

        $rules = [
            'uraian'   => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'catatan'  => 'nullable|string|max:1000',
            'bukti'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        // Kalau belum jurnal (edge case), boleh ubah data finansial juga
        if (!$sudahJurnal) {
            $rules['tanggal'] = 'required|date';
            $rules['jumlah']  = 'required|integer|min:1';
            $rules['coa_id']  = 'required|integer|exists:chart_of_accounts,id';
        }

        $request->validate($rules);

        $data = [
            'uraian'   => $request->uraian,
            'kategori' => $request->kategori,
            'catatan'  => $request->catatan,
        ];

        if (!$sudahJurnal) {
            $data['tanggal'] = $request->tanggal;
            $data['jumlah']  = (int) $request->jumlah;
            $data['coa_id']  = (int) $request->coa_id;
        }

        // Ganti bukti kalau ada upload baru
        if ($request->hasFile('bukti')) {
            Storage::disk('private')->delete($pendapatanLuar->bukti_path);
            $file              = $request->file('bukti');
            $data['bukti_path'] = $file->store('pendapatan-luar/' . now()->format('Y/m'), 'private');
            $data['bukti_name'] = $file->getClientOriginalName();
        }

        $pendapatanLuar->update($data);

        return redirect()->route('bendahara.pendapatan-luar.index')
            ->with('success', 'Data pendapatan berhasil diperbarui.');
    }

    public function destroy(PendapatanLuar $pendapatanLuar)
    {
        // Tidak boleh hapus kalau sudah ada jurnal
        abort_if(
            \App\Models\JournalEntry::existsFor(PendapatanLuar::class, $pendapatanLuar->id),
            403,
            'Data tidak dapat dihapus karena sudah masuk jurnal keuangan.'
        );

        Storage::disk('private')->delete($pendapatanLuar->bukti_path);
        $pendapatanLuar->delete();

        return redirect()->route('bendahara.pendapatan-luar.index')
            ->with('success', 'Data pendapatan berhasil dihapus.');
    }

    public function downloadBukti(PendapatanLuar $pendapatanLuar, Request $request)
    {
        $path = $pendapatanLuar->bukti_path;
        abort_unless($path && Storage::disk('private')->exists($path), 404, 'File tidak ditemukan.');

        $ext      = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $isImage  = in_array($ext, ['jpg', 'jpeg', 'png']);
        $filename = $pendapatanLuar->bukti_name;

        if ($request->boolean('download')) {
            return Storage::disk('private')->download($path, $filename);
        }

        return response(Storage::disk('private')->get($path), 200, [
            'Content-Type'        => $isImage ? 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext) : 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}