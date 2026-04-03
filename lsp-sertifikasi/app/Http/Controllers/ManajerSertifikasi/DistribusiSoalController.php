<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\DistribusiPortofolio;
use App\Models\DistribusiSoalObservasi;
use App\Models\DistribusiSoalTeori;
use App\Models\BeritaAcara;
use App\Models\BeritaAcaraAsesi;
use App\Models\HasilObservasi;
use App\Models\HasilPortofolio;
use App\Models\Schedule;
use App\Models\PaketSoalObservasi;
use App\Models\Portofolio;
use App\Models\Skema;
use App\Models\SoalObservasi;
use App\Models\SoalTeori;
use App\Models\SoalTeoriAsesi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DistribusiSoalController extends Controller
{
    // =========================================================================
    // BANK SOAL — INDEX PER SKEMA
    // =========================================================================
    
    public function indexBankSoal(): View
    {
        $skemas = Skema::where('is_active', true)->orderBy('id')->get();
    
        // Hitung stats per skema
        $stats = [];
        foreach ($skemas as $skema) {
            $stats[$skema->id] = [
                'observasi'  => SoalObservasi::where('skema_id', $skema->id)->count(),
                'teori'      => SoalTeori::where('skema_id', $skema->id)->count(),
                'portofolio' => Portofolio::where('skema_id', $skema->id)->count(),
            ];
        }
    
        return view('manajer-sertifikasi.bank-soal.index', compact('skemas', 'stats'));
    }
    
    public function showBankSoal(Request $request, Skema $skema): View
    {
        $soalObservasi = SoalObservasi::with('paket')
            ->where('skema_id', $skema->id)
            ->get();
    
        $soalTeori = SoalTeori::where('skema_id', $skema->id)
            ->latest()
            ->paginate(20);
    
        $jumlahTeori = SoalTeori::where('skema_id', $skema->id)->count();
    
        $portofolios = Portofolio::where('skema_id', $skema->id)
            ->latest()
            ->get();
    
        return view('manajer-sertifikasi.bank-soal.show', compact(
            'skema', 'soalObservasi', 'soalTeori', 'jumlahTeori', 'portofolios'
        ));
    }
    
    // =========================================================================
    // BANK SOAL — SOAL OBSERVASI (scoped ke skema)
    // =========================================================================
    
    public function storeSoalObservasiBySkema(Request $request, Skema $skema): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
        ]);
    
        SoalObservasi::create([
            'skema_id'    => $skema->id,
            'judul'       => $request->judul,
            'dibuat_oleh' => Auth::id(),
        ]);
    
        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal observasi berhasil dibuat. Upload paket di bawah.')
            ->withFragment('pane-observasi');
    }
    
    public function destroySoalObservasiBySkema(Skema $skema, SoalObservasi $soalObservasi): RedirectResponse
    {
        foreach ($soalObservasi->paket as $paket) {
            Storage::disk('private')->delete($paket->file_path);
        }
        $soalObservasi->delete();
    
        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal observasi beserta semua paket berhasil dihapus.')
            ->withFragment('pane-observasi');
    }
    
    // =========================================================================
    // BANK SOAL — PAKET OBSERVASI (scoped ke skema)
    // =========================================================================
    
    public function storePaketBySkema(Request $request, Skema $skema, SoalObservasi $soalObservasi): RedirectResponse
    {
        $request->validate([
            'kode_paket' => 'required|string|max:10',
            'file'       => 'required|file|mimes:pdf|max:10240',
        ]);
    
        $kode = strtoupper(trim($request->kode_paket));
    
        if ($soalObservasi->paket()->where('kode_paket', $kode)->exists()) {
            return back()->withErrors(['kode_paket' => "Paket {$kode} sudah ada."]);
        }
    
        $file = $request->file('file');
    
        PaketSoalObservasi::create([
            'soal_observasi_id' => $soalObservasi->id,
            'kode_paket'        => $kode,
            'judul'             => "Paket {$kode}",   // auto-generate
            'file_path'         => $file->store('soal/observasi/paket', 'private'),
            'file_name'         => $file->getClientOriginalName(),
            'dibuat_oleh'       => Auth::id(),
        ]);
    
        return back()->with('success', "Paket {$kode} berhasil diupload.");
    }
    
    public function downloadPaketBySkema(Skema $skema, PaketSoalObservasi $paket): Response
    {
        return Storage::disk('private')->download($paket->file_path, $paket->file_name);
    }
    
    public function destroyPaketBySkema(Skema $skema, PaketSoalObservasi $paket): RedirectResponse
    {
        Storage::disk('private')->delete($paket->file_path);
        $paket->delete();
        return back()->with('success', 'Paket berhasil dihapus.');
    }
    
    // =========================================================================
    // BANK SOAL — SOAL TEORI (scoped ke skema, pilihan a-e)
    // =========================================================================
    
    public function storeSoalTeoriBySkema(Request $request, Skema $skema): RedirectResponse
    {
        $request->validate([
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'pilihan_e'     => 'nullable|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d,e',
        ]);
    
        SoalTeori::create([
            'skema_id'      => $skema->id,
            'pertanyaan'    => $request->pertanyaan,
            'pilihan_a'     => $request->pilihan_a,
            'pilihan_b'     => $request->pilihan_b,
            'pilihan_c'     => $request->pilihan_c,
            'pilihan_d'     => $request->pilihan_d,
            'pilihan_e'     => $request->pilihan_e,
            'jawaban_benar' => $request->jawaban_benar,
            'dibuat_oleh'   => Auth::id(),
        ]);
    
        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal teori berhasil ditambahkan.')
            ->withFragment('pane-teori');
    }
    
    public function updateSoalTeoriBySkema(Request $request, Skema $skema, SoalTeori $soalTeori): RedirectResponse
    {
        $request->validate([
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'pilihan_e'     => 'nullable|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d,e',
        ]);
    
        $soalTeori->update($request->only([
            'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'pilihan_e', 'jawaban_benar',
        ]));
    
        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Soal teori berhasil diperbarui.')
            ->withFragment('pane-teori');
    }
    
    public function destroySoalTeoriBySkema(Skema $skema, SoalTeori $soalTeori): RedirectResponse
    {
        $soalTeori->delete();
        return back()->with('success', 'Soal teori berhasil dihapus.');
    }
    
    // =========================================================================
    // BANK SOAL — PORTOFOLIO (scoped ke skema)
    // =========================================================================
    
    public function storePortofolioBySkema(Request $request, Skema $skema): RedirectResponse
    {
        $request->validate([
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file'      => 'nullable|file|max:20480',
        ]);
    
        $file = $request->file('file');
    
        Portofolio::create([
            'skema_id'    => $skema->id,
            'judul'       => $request->judul,
            'deskripsi'   => $request->deskripsi,
            'file_path'   => $file ? $file->store('portofolio', 'private') : null,
            'file_name'   => $file?->getClientOriginalName(),
            'tipe_file'   => $file?->getClientOriginalExtension(),
            'dibuat_oleh' => Auth::id(),
        ]);
    
        return redirect()->route('manajer-sertifikasi.bank-soal.show', $skema)
            ->with('success', 'Form portofolio berhasil disimpan.')
            ->withFragment('pane-portofolio');
    }
    
    public function downloadPortofolioBySkema(Skema $skema, Portofolio $portofolio): Response
    {
        abort_unless($portofolio->hasFile(), 404, 'File tidak tersedia.');
        return Storage::disk('private')->download($portofolio->file_path, $portofolio->file_name);
    }
    
    public function destroyPortofolioBySkema(Skema $skema, Portofolio $portofolio): RedirectResponse
    {
        if ($portofolio->hasFile()) {
            Storage::disk('private')->delete($portofolio->file_path);
        }
        $portofolio->delete();
        return back()->with('success', 'Form portofolio berhasil dihapus.');
    }
    // =========================================================================
    // DETAIL JADWAL
    // =========================================================================

    public function show(Schedule $schedule): View
    {
        $schedule->load([
            'skema', 'tuk', 'asesor.user', 'asesmens.user',
            'distribusiSoalObservasi.soalObservasi.paket',
            'distribusiSoalTeori.soalAsesi',
            'distribusiPortofolio.portofolio',
        ]);

        $skemaId = $schedule->skema_id;

        return view('manajer-sertifikasi.show', [
            'schedule'               => $schedule,
            'soalObservasiTersedia'  => SoalObservasi::with('paket')->where('skema_id', $skemaId)->get(),
            'portofolioTersedia'     => Portofolio::where('skema_id', $skemaId)->get(),
            'jumlahBankSoalTeori'    => SoalTeori::where('skema_id', $skemaId)->count(),
            'distribusiObservasiIds' => $schedule->distribusiSoalObservasi->pluck('soal_observasi_id'),
            'distribusiPortofolioIds'=> $schedule->distribusiPortofolio->pluck('portofolio_id'),
            'distribusiTeori'        => $schedule->distribusiSoalTeori,
        ]);
    }

    // =========================================================================
    // SOAL OBSERVASI
    // =========================================================================

    public function indexSoalObservasi(Request $request): View
    {
        $query = SoalObservasi::with('skema', 'dibuatOleh', 'paket', 'distribusi');
        if ($request->skema_id) $query->where('skema_id', $request->skema_id);

        return view('manajer-sertifikasi.soal-observasi.index', [
            'soalObservasi' => $query->latest()->paginate(15),
            'skemas'        => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function createSoalObservasi(): View
    {
        return view('manajer-sertifikasi.soal-observasi.create', [
            'skemas' => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeSoalObservasi(Request $request): RedirectResponse
    {
        $request->validate([
            'skema_id' => 'required|exists:skemas,id',
            'judul'    => 'required|string|max:255',
        ]);

        $observasi = SoalObservasi::create([
            'skema_id'    => $request->skema_id,
            'judul'       => $request->judul,
            'dibuat_oleh' => Auth::id(),
        ]);

        // Jika ada redirect_back (dari halaman jadwal), kembali ke sana
        $back = $request->redirect_back;
        if ($back && str_starts_with($back, url('/manajer-sertifikasi'))) {
            return redirect($back)
                ->with('success', 'Soal observasi dibuat. Tambahkan paket sekarang.')
                ->withFragment('pane-observasi');
        }

        return redirect()->route('manajer-sertifikasi.soal-observasi.show', $observasi)
            ->with('success', 'Soal observasi berhasil dibuat. Tambahkan paket di dalamnya.');
    }

    public function showSoalObservasi(SoalObservasi $soalObservasi): View
    {
        $soalObservasi->load('skema', 'paket.dibuatOleh', 'distribusi.schedule.skema');
        return view('manajer-sertifikasi.soal-observasi.show', compact('soalObservasi'));
    }

    public function destroySoalObservasi(SoalObservasi $soalObservasi): RedirectResponse
    {
        foreach ($soalObservasi->paket as $paket) {
            Storage::disk('private')->delete($paket->file_path);
        }
        $soalObservasi->delete();

        return redirect()->route('manajer-sertifikasi.soal-observasi.index')
            ->with('success', 'Soal observasi beserta semua paket berhasil dihapus.');
    }

    // ── Paket di dalam Observasi ──────────────────────────────────────────

    public function storePaketObservasi(Request $request, SoalObservasi $soalObservasi): RedirectResponse
    {
        $request->validate([
            'kode_paket' => 'required|string|max:10',
            'judul'      => 'required|string|max:255',
            'file'       => 'required|file|mimes:pdf|max:10240',
        ]);

        $sudahAda = $soalObservasi->paket()->where('kode_paket', strtoupper($request->kode_paket))->exists();
        if ($sudahAda) {
            return back()->withErrors([
                'kode_paket' => "Paket {$request->kode_paket} sudah ada di observasi ini.",
            ]);
        }

        $file = $request->file('file');

        PaketSoalObservasi::create([
            'soal_observasi_id' => $soalObservasi->id,
            'kode_paket'        => strtoupper($request->kode_paket),
            'judul'             => $request->judul,
            'file_path'         => $file->store('soal/observasi/paket', 'private'),
            'file_name'         => $file->getClientOriginalName(),
            'dibuat_oleh'       => Auth::id(),
        ]);

        return back()->with('success', "Paket {$request->kode_paket} berhasil diupload.");
    }

    public function downloadPaketObservasi(PaketSoalObservasi $paket): Response
    {
        return Storage::disk('private')->download($paket->file_path, $paket->file_name);
    }

    public function destroyPaketObservasi(PaketSoalObservasi $paket): RedirectResponse
    {
        Storage::disk('private')->delete($paket->file_path);
        $paket->delete();
        return back()->with('success', 'Paket berhasil dihapus.');
    }

    // ── Distribusi Observasi ke Jadwal ────────────────────────────────────

    public function distribusiSoalObservasi(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'              => 'required|exists:schedules,id',
            'soal_observasi_id'        => 'required|exists:soal_observasi,id',
            'paket_soal_observasi_id'  => 'required|exists:paket_soal_observasi,id',
        ]);
 
        // Pastikan paket yang dipilih memang milik soal observasi ini
        $paket = \App\Models\PaketSoalObservasi::where('id', $request->paket_soal_observasi_id)
            ->where('soal_observasi_id', $request->soal_observasi_id)
            ->firstOrFail();
 
        DistribusiSoalObservasi::updateOrCreate(
            [
                'schedule_id'       => $request->schedule_id,
                'soal_observasi_id' => $request->soal_observasi_id,
            ],
            [
                'paket_soal_observasi_id' => $paket->id,
                'didistribusikan_oleh'    => Auth::id(),
            ]
        );
 
        return back()->with('success', "Soal observasi '{$paket->soalObservasi->judul}' — Paket {$paket->kode_paket} berhasil didistribusikan.");
    }

    public function hapusDistribusiSoalObservasi(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'       => 'required|exists:schedules,id',
            'soal_observasi_id' => 'required|exists:soal_observasi,id',
        ]);

        DistribusiSoalObservasi::where([
            'schedule_id'       => $request->schedule_id,
            'soal_observasi_id' => $request->soal_observasi_id,
        ])->delete();

        // Redirect eksplisit ke jadwal, bukan back()
        return redirect()
            ->route('manajer-sertifikasi.jadwal.show', $request->schedule_id)
            ->with('success', 'Distribusi soal observasi dihapus.')
            ->withFragment('pane-observasi');
    }

    // =========================================================================
    // PORTOFOLIO
    // =========================================================================

    public function indexPortofolio(Request $request): View
    {
        $query = Portofolio::with('skema', 'dibuatOleh', 'distribusi');
        if ($request->skema_id) $query->where('skema_id', $request->skema_id);

        return view('manajer-sertifikasi.portofolio.index', [
            'portofolios' => $query->latest()->paginate(15),
            'skemas'      => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function createPortofolio(): View
    {
        return view('manajer-sertifikasi.portofolio.create', [
            'skemas' => Skema::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storePortofolio(Request $request): RedirectResponse
    {
        $request->validate([
            'skema_id'  => 'required|exists:skemas,id',
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file'      => 'nullable|file|max:20480',
        ]);

        $file = $request->file('file');

        Portofolio::create([
            'skema_id'    => $request->skema_id,
            'judul'       => $request->judul,
            'deskripsi'   => $request->deskripsi,
            'file_path'   => $file ? $file->store('portofolio', 'private') : null,
            'file_name'   => $file?->getClientOriginalName(),
            'tipe_file'   => $file?->getClientOriginalExtension(),
            'dibuat_oleh' => Auth::id(),
        ]);

        $back = $request->redirect_back;
        if ($back && str_starts_with($back, url('/manajer-sertifikasi'))) {
            return redirect($back)->with('success', 'Portofolio berhasil disimpan.');
        }

        return redirect()->route('manajer-sertifikasi.portofolio.index')
            ->with('success', 'Portofolio berhasil disimpan.');
    }

    public function downloadPortofolio(Portofolio $portofolio): Response
    {
        abort_unless($portofolio->hasFile(), 404, 'File tidak tersedia.');
        return Storage::disk('private')->download($portofolio->file_path, $portofolio->file_name);
    }

    public function destroyPortofolio(Portofolio $portofolio): RedirectResponse
    {
        if ($portofolio->hasFile()) {
            Storage::disk('private')->delete($portofolio->file_path);
        }
        $portofolio->delete();
        return back()->with('success', 'Portofolio berhasil dihapus.');
    }

    public function distribusiPortofolio(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'   => 'required|exists:schedules,id',
            'portofolio_id' => 'required|exists:portofolio,id',
        ]);

        DistribusiPortofolio::updateOrCreate(
            ['schedule_id' => $request->schedule_id, 'portofolio_id' => $request->portofolio_id],
            ['didistribusikan_oleh' => Auth::id()]
        );

        return back()->with('success', 'Portofolio berhasil didistribusikan.');
    }

    public function hapusDistribusiPortofolio(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id'   => 'required|exists:schedules,id',
            'portofolio_id' => 'required|exists:portofolio,id',
        ]);

        DistribusiPortofolio::where([
            'schedule_id'   => $request->schedule_id,
            'portofolio_id' => $request->portofolio_id,
        ])->delete();

        return redirect()
            ->route('manajer-sertifikasi.jadwal.show', $request->schedule_id)
            ->with('success', 'Distribusi portofolio dihapus.')
            ->withFragment('pane-portofolio');
    }

    // =========================================================================
    // SOAL TEORI PG
    // =========================================================================

    public function indexSoalTeori(Request $request): View
    {
        $query = SoalTeori::with('skema', 'dibuatOleh');
        if ($request->skema_id) $query->where('skema_id', $request->skema_id);
        if ($request->q) $query->where('pertanyaan', 'like', '%' . $request->q . '%');

        $ringkasanSkema = SoalTeori::select('soal_teori.skema_id', DB::raw('count(*) as total'))
            ->join('skemas', 'skemas.id', '=', 'soal_teori.skema_id')
            ->addSelect('skemas.name as skema_name')
            ->groupBy('soal_teori.skema_id', 'skemas.name')
            ->get();

        return view('manajer-sertifikasi.soal-teori.index', [
            'soalTeori'      => $query->latest()->paginate(20),
            'skemas'         => Skema::where('is_active', true)->orderBy('name')->get(),
            'ringkasanSkema' => $ringkasanSkema,
        ]);
    }

    public function storeSoalTeori(Request $request): RedirectResponse
    {
        $request->validate([
            'skema_id'      => 'required|exists:skemas,id',
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d',
        ]);

        SoalTeori::create([
            ...$request->only([
                'skema_id', 'pertanyaan',
                'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'jawaban_benar',
            ]),
            'dibuat_oleh' => Auth::id(),
        ]);

        return redirect()->route('manajer-sertifikasi.soal-teori.index')
            ->with('success', 'Soal teori berhasil ditambahkan.');
    }

    public function updateSoalTeori(Request $request, SoalTeori $soalTeori): RedirectResponse
    {
        $request->validate([
            'skema_id'      => 'required|exists:skemas,id',
            'pertanyaan'    => 'required|string',
            'pilihan_a'     => 'required|string|max:500',
            'pilihan_b'     => 'required|string|max:500',
            'pilihan_c'     => 'required|string|max:500',
            'pilihan_d'     => 'required|string|max:500',
            'jawaban_benar' => 'required|in:a,b,c,d',
        ]);

        $soalTeori->update($request->only([
            'skema_id', 'pertanyaan', 'pilihan_a', 'pilihan_b', 'pilihan_c', 'pilihan_d', 'jawaban_benar',
        ]));

        return redirect()->route('manajer-sertifikasi.soal-teori.index')
            ->with('success', 'Soal teori berhasil diperbarui.');
    }

    public function destroySoalTeori(SoalTeori $soalTeori): RedirectResponse
    {
        $soalTeori->delete();
        return back()->with('success', 'Soal teori berhasil dihapus.');
    }

    public function distribusiSoalTeori(Request $request): RedirectResponse
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'jumlah_soal' => 'required|integer|min:1',
        ]);

        $schedule    = Schedule::with('asesmens')->findOrFail($request->schedule_id);
        $bankSoalIds = SoalTeori::where('skema_id', $schedule->skema_id)->pluck('id')->toArray();
        $totalBank   = count($bankSoalIds);

        if ($totalBank < $request->jumlah_soal) {
            return back()->withErrors([
                'jumlah_soal' => "Bank soal hanya punya {$totalBank} soal, tidak cukup untuk {$request->jumlah_soal} soal.",
            ])->withInput();
        }

        DB::transaction(function () use ($request, $schedule, $bankSoalIds) {
            DistribusiSoalTeori::where('schedule_id', $schedule->id)->delete();

            $distribusi = DistribusiSoalTeori::create([
                'schedule_id'          => $schedule->id,
                'jumlah_soal'          => $request->jumlah_soal,
                'didistribusikan_oleh' => Auth::id(),
            ]);

            foreach ($schedule->asesmens as $asesmen) {
                $terpilih = collect($bankSoalIds)->shuffle()->take($request->jumlah_soal)->values();

                SoalTeoriAsesi::insert($terpilih->map(fn ($id, $idx) => [
                    'distribusi_soal_teori_id' => $distribusi->id,
                    'asesmen_id'               => $asesmen->id,
                    'soal_teori_id'            => $id,
                    'urutan'                   => $idx + 1,
                    'jawaban'                  => null,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ])->toArray());
            }
        });

        return redirect()->route('manajer-sertifikasi.jadwal.show', $schedule)
            ->with('success', "{$request->jumlah_soal} soal teori berhasil didistribusikan ke {$schedule->asesmens->count()} asesi.");
    }

    // =========================================================================
    // DAFTAR HADIR
    // =========================================================================
    public function daftarHadir(Schedule $schedule): \Illuminate\Http\Response
    {
        $schedule->load(['tuk', 'skema', 'asesor.user', 'asesmens']);
 
        // TTD asesor diambil dari akun user si asesor
        $ttdAsesor = $schedule->asesor?->user?->signature_image;
 
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.daftar-hadir', [
            'schedule'  => $schedule,
            'asesmens'  => $schedule->asesmens,
            'asesor'    => $schedule->asesor,
            'ttdAsesor' => $ttdAsesor,
        ])->setPaper('A4', 'portrait');
 
        $filename = 'Daftar_Hadir_'
            . str_replace(' ', '_', $schedule->skema->name ?? 'Asesmen')
            . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';
 
        return $pdf->stream($filename);
    }

        public function rekapPenilaian(Schedule $schedule): \Illuminate\View\View
    {
        $schedule->load([
            'skema', 'tuk', 'asesor.user',
            'asesmens.soalTeoriAsesi.soalTeori',
            'distribusiSoalObservasi.soalObservasi',
            'distribusiPortofolio.portofolio',
            'beritaAcara.asesis',
        ]);
 
        $hasilObservasi  = HasilObservasi::where('schedule_id', $schedule->id)->get();
        $hasilPortofolio = HasilPortofolio::where('schedule_id', $schedule->id)->get();
        $beritaAcara     = $schedule->beritaAcara;
 
        $rekomendasiMap = [];
        if ($beritaAcara) {
            foreach ($beritaAcara->asesis as $ba) {
                $rekomendasiMap[$ba->asesmen_id] = $ba->rekomendasi;
            }
        }
 
        return view('manajer-sertifikasi.rekap-penilaian', [
            'schedule'        => $schedule,
            'hasilObservasi'  => $hasilObservasi,
            'hasilPortofolio' => $hasilPortofolio,
            'beritaAcara'     => $beritaAcara,
            'rekomendasiMap'  => $rekomendasiMap,
            'totalObservasi'  => $schedule->distribusiSoalObservasi->count(),
            'totalPortofolio' => $schedule->distribusiPortofolio->count(),
        ]);
    }
 
    public function downloadHasilObservasi(Schedule $schedule, SoalObservasi $soalObservasi): \Illuminate\Http\Response
    {
        $hasil = HasilObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();
 
        return Storage::disk('private')->download($hasil->file_path, $hasil->file_name);
    }
 
    public function downloadHasilPortofolio(Schedule $schedule, Portofolio $portofolio): \Illuminate\Http\Response
    {
        $hasil = HasilPortofolio::where([
            'schedule_id'   => $schedule->id,
            'portofolio_id' => $portofolio->id,
        ])->firstOrFail();
 
        return Storage::disk('private')->download($hasil->file_path, $hasil->file_name);
    }
 
    public function downloadFileBeritaAcara(Schedule $schedule): \Illuminate\Http\Response
    {
        $ba = $schedule->beritaAcara;
        abort_unless($ba && $ba->file_path, 404, 'File tidak tersedia.');
        return Storage::disk('private')->download($ba->file_path, $ba->file_name);
    }
 
    /**
     * Upload form penilaian observasi untuk jadwal tertentu
     * POST /manajer-sertifikasi/jadwal/{schedule}/observasi/{soalObservasi}/form-penilaian
     */
    public function uploadFormPenilaianObservasi(Request $request, Schedule $schedule, SoalObservasi $soalObservasi): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xlsm,xls,pdf|max:20480',
        ]);
 
        $dist = DistribusiSoalObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();
 
        // Hapus file lama jika ada
        if ($dist->form_penilaian_path) {
            Storage::disk('private')->delete($dist->form_penilaian_path);
        }
 
        $file = $request->file('file');
        $path = $file->store("form-penilaian/observasi/{$schedule->id}", 'private');
 
        $dist->update([
            'form_penilaian_path' => $path,
            'form_penilaian_name' => $file->getClientOriginalName(),
        ]);
 
        return back()->with('success', "Form penilaian '{$soalObservasi->judul}' berhasil diupload.");
    }
 
    /**
     * Download form penilaian observasi
     * GET /manajer-sertifikasi/jadwal/{schedule}/observasi/{soalObservasi}/form-penilaian
     */
    public function downloadFormPenilaianObservasi(Schedule $schedule, SoalObservasi $soalObservasi): mixed
    {
        $dist = DistribusiSoalObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();
 
        abort_unless($dist->form_penilaian_path && Storage::disk('private')->exists($dist->form_penilaian_path), 404, 'Form belum diupload.');
 
        return Storage::disk('private')->download($dist->form_penilaian_path, $dist->form_penilaian_name);
    }
 
    /**
     * Hapus form penilaian observasi
     * DELETE /manajer-sertifikasi/jadwal/{schedule}/observasi/{soalObservasi}/form-penilaian
     */
    public function hapusFormPenilaianObservasi(Schedule $schedule, SoalObservasi $soalObservasi): RedirectResponse
    {
        $dist = DistribusiSoalObservasi::where([
            'schedule_id'       => $schedule->id,
            'soal_observasi_id' => $soalObservasi->id,
        ])->firstOrFail();
 
        if ($dist->form_penilaian_path) {
            Storage::disk('private')->delete($dist->form_penilaian_path);
        }
 
        $dist->update([
            'form_penilaian_path' => null,
            'form_penilaian_name' => null,
        ]);
 
        return back()->with('success', 'Form penilaian dihapus.');
    }
}