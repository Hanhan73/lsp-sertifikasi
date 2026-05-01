<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratController extends Controller
{
    // ─── SURAT MASUK ──────────────────────────────────────────────────────

    public function masukIndex()
    {
        $surats = SuratMasuk::orderBy('nomor_urut')->get();
        return view('admin.surat.masuk.index', compact('surats'));
    }

    public function masukCreate()
    {
        $nextNo = (SuratMasuk::max('nomor_urut') ?? 0) + 1;
        return view('admin.surat.masuk.create', compact('nextNo'));
    }

    public function masukStore(Request $request)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'dari'           => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('surat/masuk', 'public_html');
        }

        $data['created_by'] = auth()->id();
        unset($data['file']);

        SuratMasuk::create($data);
        return redirect()->route('admin.surat.masuk.index')->with('success', 'Surat masuk berhasil ditambahkan.');
    }

    public function masukEdit(SuratMasuk $suratMasuk)
    {
        return view('admin.surat.masuk.edit', ['surat' => $suratMasuk]);
    }

    public function masukUpdate(Request $request, SuratMasuk $suratMasuk)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'dari'           => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($suratMasuk->file_path) {
                Storage::disk('public_html')->delete($suratMasuk->file_path);
            }
            $data['file_path'] = $request->file('file')->store('surat/masuk', 'public_html');
        }

        unset($data['file']);
        $suratMasuk->update($data);
        return redirect()->route('admin.surat.masuk.index')->with('success', 'Surat masuk berhasil diperbarui.');
    }

    public function masukDestroy(SuratMasuk $suratMasuk)
    {
        if ($suratMasuk->file_path) {
            Storage::disk('public_html')->delete($suratMasuk->file_path);
        }
        $suratMasuk->delete();
        return back()->with('success', 'Surat masuk berhasil dihapus.');
    }

    public function masukDownload(SuratMasuk $suratMasuk)
    {
        abort_unless($suratMasuk->file_path && Storage::disk('public_html')->exists($suratMasuk->file_path), 404);
        return Storage::disk('public_html')->download($suratMasuk->file_path);
    }

    // ─── SURAT KELUAR ─────────────────────────────────────────────────────

    public function keluarIndex()
    {
        $surats = SuratKeluar::orderBy('nomor_urut')->get();
        return view('admin.surat.keluar.index', compact('surats'));
    }

    public function keluarCreate()
    {
        $nextNo = (SuratKeluar::max('nomor_urut') ?? 0) + 1;
        return view('admin.surat.keluar.create', compact('nextNo'));
    }

    public function keluarStore(Request $request)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'kepada'         => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('surat/keluar', 'public_html');
        }

        $data['created_by'] = auth()->id();
        unset($data['file']);

        SuratKeluar::create($data);
        return redirect()->route('admin.surat.keluar.index')->with('success', 'Surat keluar berhasil ditambahkan.');
    }

    public function keluarEdit(SuratKeluar $suratKeluar)
    {
        return view('admin.surat.keluar.edit', ['surat' => $suratKeluar]);
    }

    public function keluarUpdate(Request $request, SuratKeluar $suratKeluar)
    {
        $data = $request->validate([
            'nomor_urut'     => 'required|integer',
            'tanggal_agenda' => 'required|date',
            'nomor_surat'    => 'required|string|max:255',
            'tanggal_surat'  => 'required|date',
            'kepada'         => 'required|string|max:255',
            'isi_ringkas'    => 'required|string',
            'file'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($suratKeluar->file_path) {
                Storage::disk('public_html')->delete($suratKeluar->file_path);
            }
            $data['file_path'] = $request->file('file')->store('surat/keluar', 'public_html');
        }

        unset($data['file']);
        $suratKeluar->update($data);
        return redirect()->route('admin.surat.keluar.index')->with('success', 'Surat keluar berhasil diperbarui.');
    }

    public function keluarDestroy(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->file_path) {
            Storage::disk('public_html')->delete($suratKeluar->file_path);
        }
        $suratKeluar->delete();
        return back()->with('success', 'Surat keluar berhasil dihapus.');
    }

    public function keluarDownload(SuratKeluar $suratKeluar)
    {
        abort_unless($suratKeluar->file_path && Storage::disk('public_html')->exists($suratKeluar->file_path), 404);
        return Storage::disk('public_html')->download($suratKeluar->file_path);
    }

    public function masukPreview(SuratMasuk $suratMasuk)
    {
        abort_unless($suratMasuk->file_path && Storage::disk('public_html')->exists($suratMasuk->file_path), 404);

        $path     = Storage::disk('public_html')->path($suratMasuk->file_path);
        $mime     = Storage::disk('public_html')->mimeType($suratMasuk->file_path);
        $filename = basename($suratMasuk->file_path);

        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function keluarPreview(SuratKeluar $suratKeluar)
    {
        abort_unless($suratKeluar->file_path && Storage::disk('public_html')->exists($suratKeluar->file_path), 404);

        $path     = Storage::disk('public_html')->path($suratKeluar->file_path);
        $mime     = Storage::disk('public_html')->mimeType($suratKeluar->file_path);
        $filename = basename($suratKeluar->file_path);

        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}