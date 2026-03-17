<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skema;
use App\Models\UnitKompetensi;
use App\Models\Elemen;
use App\Models\Kuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SkemaController extends Controller
{
    public function index()
    {
        $skemas = Skema::withCount('asesmens')->get();

        return view('admin.skemas.index', compact('skemas'));
    }

    public function create()
    {
        return view('admin.skemas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'               => 'required|unique:skemas',
            'name'               => 'required|string|max:255',
            'jenis_skema'        => 'required|in:okupasi,kkni,klaster',
            'nomor_skema'        => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'fee'                => 'required|numeric|min:0',
            'duration_days'      => 'required|integer|min:1',
            'tanggal_pengesahan' => 'nullable|date',
            'dokumen_pengesahan' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'is_active'          => 'sometimes|boolean',
        ]);

        [$dokPath, $dokName] = $this->handleDokumenUpload($request);

        Skema::create(array_merge($data, [
            'dokumen_pengesahan_path' => $dokPath,
            'dokumen_pengesahan_name' => $dokName,
            'is_active'               => $request->has('is_active'),
        ]));

        return redirect()->route('admin.skemas')
            ->with('success', 'Skema berhasil ditambahkan!');
    }

    public function show(Skema $skema)
    {
        $skema->load(['unitKompetensis.elemens.kuks', 'asesmens']);

        return view('admin.skemas.show', compact('skema'));
    }

    public function edit(Skema $skema)
    {
        return view('admin.skemas.edit', compact('skema'));
    }

    public function update(Request $request, Skema $skema)
    {
        $data = $request->validate([
            'code'               => 'required|unique:skemas,code,' . $skema->id,
            'name'               => 'required|string|max:255',
            'jenis_skema'        => 'required|in:okupasi,kkni,klaster',
            'nomor_skema'        => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'fee'                => 'required|numeric|min:0',
            'duration_days'      => 'required|integer|min:1',
            'tanggal_pengesahan' => 'nullable|date',
            'dokumen_pengesahan' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        if ($request->hasFile('dokumen_pengesahan')) {
            Storage::disk('public')->delete($skema->dokumen_pengesahan_path ?? '');
            [$dokPath, $dokName]              = $this->handleDokumenUpload($request);
            $skema->dokumen_pengesahan_path   = $dokPath;
            $skema->dokumen_pengesahan_name   = $dokName;
            $skema->save();
        }

        $skema->update(array_merge($data, [
            'is_active' => $request->has('is_active'),
        ]));

        return redirect()->route('admin.skemas.show', $skema)
            ->with('success', 'Skema berhasil diupdate!');
    }

    public function destroy(Skema $skema)
    {
        $hasActive = $skema->asesmens()
            ->whereIn('status', ['registered', 'data_completed', 'verified', 'paid', 'scheduled'])
            ->exists();

        if ($hasActive) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus skema yang masih digunakan oleh asesi aktif!');
        }

        $skema->delete();

        return redirect()->route('admin.skemas')
            ->with('success', 'Skema berhasil dihapus!');
    }

    public function importMuk(Request $request, Skema $skema)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xlsm,xls|max:20480',
        ]);

        try {
            $importer = new \App\Imports\MukImport($skema);
            Excel::import($importer, $request->file('file'));

            return redirect()->route('admin.skemas.show', $skema)
                ->with('success', "Import berhasil! {$importer->unitCount} Unit dan {$importer->kukCount} KUK disimpan.");

        } catch (\Exception $e) {
            Log::error('[SKEMA][importMuk] ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    // =========================================================
    // UNIT KOMPETENSI (AJAX)
    // =========================================================

    public function storeUnit(Request $request, Skema $skema)
    {
        $request->validate([
            'kode_unit'          => 'nullable|string|max:50',
            'judul_unit'         => 'required|string|max:255',
            'standar_kompetensi' => 'nullable|string|max:255',
        ]);

        $unit = $skema->unitKompetensis()->create([
            'kode_unit'          => $request->kode_unit,
            'judul_unit'         => $request->judul_unit,
            'standar_kompetensi' => $request->standar_kompetensi,
            'urutan'             => ($skema->unitKompetensis()->max('urutan') ?? 0) + 1,
        ]);

        return response()->json(['success' => true, 'unit' => $unit->load('elemens')]);
    }

    public function updateUnit(Request $request, UnitKompetensi $unit)
    {
        $request->validate([
            'kode_unit'          => 'nullable|string|max:50',
            'judul_unit'         => 'required|string|max:255',
            'standar_kompetensi' => 'nullable|string|max:255',
        ]);

        $unit->update($request->only('kode_unit', 'judul_unit', 'standar_kompetensi'));

        return response()->json(['success' => true]);
    }

    public function destroyUnit(UnitKompetensi $unit)
    {
        $unit->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================
    // ELEMEN (AJAX)
    // =========================================================

    public function storeElemen(Request $request, UnitKompetensi $unit)
    {
        $request->validate([
            'judul'      => 'required|string|max:255',
            'hint_bukti' => 'nullable|string',
        ]);

        $elemen = $unit->elemens()->create([
            'judul'      => $request->judul,
            'hint_bukti' => $request->hint_bukti,
            'urutan'     => ($unit->elemens()->max('urutan') ?? 0) + 1,
        ]);

        return response()->json(['success' => true, 'elemen' => $elemen->load('kuks')]);
    }

    public function updateElemen(Request $request, Elemen $elemen)
    {
        $request->validate([
            'judul'      => 'required|string|max:255',
            'hint_bukti' => 'nullable|string',
        ]);

        $elemen->update($request->only('judul', 'hint_bukti'));

        return response()->json(['success' => true]);
    }

    public function destroyElemen(Elemen $elemen)
    {
        $elemen->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================
    // KUK (AJAX)
    // =========================================================

    public function storeKuk(Request $request, Elemen $elemen)
    {
        $request->validate(['deskripsi' => 'required|string']);

        $urutan = ($elemen->kuks()->max('urutan') ?? 0) + 1;

        $kuk = $elemen->kuks()->create([
            'kode'      => $elemen->urutan . '.' . $urutan,
            'deskripsi' => $request->deskripsi,
            'urutan'    => $urutan,
        ]);

        return response()->json(['success' => true, 'kuk' => $kuk]);
    }

    public function updateKuk(Request $request, Kuk $kuk)
    {
        $request->validate(['deskripsi' => 'required|string']);

        $kuk->update(['deskripsi' => $request->deskripsi]);

        return response()->json(['success' => true]);
    }

    public function destroyKuk(Kuk $kuk)
    {
        $kuk->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================
    // PRIVATE
    // =========================================================

    private function handleDokumenUpload(Request $request): array
    {
        if (!$request->hasFile('dokumen_pengesahan')) {
            return [null, null];
        }

        $file = $request->file('dokumen_pengesahan');

        return [
            $file->store('skemas/dokumen', 'public'),
            $file->getClientOriginalName(),
        ];
    }
}