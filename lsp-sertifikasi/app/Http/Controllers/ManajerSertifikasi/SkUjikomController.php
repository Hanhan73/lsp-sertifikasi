<?php

namespace App\Http\Controllers\ManajerSertifikasi;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\BeritaAcaraAsesi;
use App\Models\Schedule;
use App\Models\SkHasilUjikom;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SkUjikomController extends Controller
{
    /**
     * Daftar semua batch + status SK masing-masing.
     */
    public function index()
    {
        // Ambil semua batch yang punya jadwal approved & setidaknya satu berita acara
        $batches = Asesmen::select('collective_batch_id')
            ->whereNotNull('collective_batch_id')
            ->where('is_collective', true)
            ->whereHas('schedule.beritaAcara')
            ->distinct()
            ->pluck('collective_batch_id');

        $data = $batches->map(function ($batchId) {
            $first = Asesmen::with(['tuk', 'skema'])
                ->where('collective_batch_id', $batchId)
                ->first();

            $schedules = Schedule::with(['beritaAcara'])
                ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
                ->get();

            // Cek semua jadwal di batch sudah punya berita acara submitted
            $totalJadwal  = $schedules->count();
            $totalBA      = $schedules->filter(fn($s) => $s->beritaAcara !== null)->count();
            $siapPengajuan = $totalBA > 0;

            // SK yang sudah ada
            $sk = SkHasilUjikom::where('collective_batch_id', $batchId)->first();

            // Hitung total K & BK dari semua BA dalam batch
            $scheduleIds = $schedules->pluck('id');
            $totalK  = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
                ->where('rekomendasi', 'K')->count();
            $totalBK = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
                ->where('rekomendasi', 'BK')->count();

            return [
                'batch_id'      => $batchId,
                'tuk'           => $first?->tuk,
                'skema'         => $first?->skema,
                'total_asesi'   => Asesmen::where('collective_batch_id', $batchId)->count(),
                'total_jadwal'  => $totalJadwal,
                'total_ba'      => $totalBA,
                'total_k'       => $totalK,
                'total_bk'      => $totalBK,
                'siap'          => $siapPengajuan,
                'sk'            => $sk,
            ];
        })->sortByDesc(fn($d) => $d['sk']?->created_at ?? '0');

        return view('manajer-sertifikasi.sk-ujikom.index', compact('data'));
    }

    /**
     * Form buat pengajuan SK baru untuk satu batch.
     */
    public function create(string $batchId)
    {
        // Cek apakah SK sudah ada untuk batch ini
        $existing = SkHasilUjikom::where('collective_batch_id', $batchId)->first();
        if ($existing) {
            return redirect()->route('manajer-sertifikasi.sk-ujikom.show', $existing)
                ->with('info', 'Pengajuan SK untuk batch ini sudah ada.');
        }

        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $batchId)
            ->firstOrFail();

        $schedules = Schedule::with(['beritaAcara', 'asesor.user', 'tuk'])
            ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $batchId))
            ->get();

        // Ambil peserta K dari semua BA
        $scheduleIds = $schedules->pluck('id');
        $pesertaKompeten = BeritaAcaraAsesi::with(['asesmen'])
            ->whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
            ->where('rekomendasi', 'K')
            ->get()
            ->map(fn($baa) => $baa->asesmen)
            ->filter();

        abort_if($pesertaKompeten->isEmpty(), 422, 'Belum ada peserta kompeten di batch ini.');

        return view('manajer-sertifikasi.sk-ujikom.create', compact(
            'batchId', 'first', 'schedules', 'pesertaKompeten'
        ));
    }

    /**
     * Simpan pengajuan SK (status: submitted langsung).
     */
    public function store(Request $request)
    {
        $request->validate([
            'collective_batch_id' => 'required|string',
            'tanggal_pleno'       => 'required|date',
            'tempat_dikeluarkan'  => 'required|string|max:100',
        ], [
            'tanggal_pleno.required'      => 'Tanggal pleno wajib diisi.',
            'tempat_dikeluarkan.required' => 'Tempat dikeluarkan wajib diisi.',
        ]);
 
        $batchId = $request->collective_batch_id;
 
        abort_if(
            SkHasilUjikom::where('collective_batch_id', $batchId)->exists(),
            422,
            'SK untuk batch ini sudah diajukan.'
        );
 
        $sk = SkHasilUjikom::create([
            'collective_batch_id' => $batchId,
            'nomor_sk'            => SkHasilUjikom::generateNomorSk(),   // ← otomatis
            'tanggal_pleno'       => $request->tanggal_pleno,
            'tempat_dikeluarkan'  => $request->tempat_dikeluarkan,
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'created_by'          => Auth::id(),
        ]);
 
        return redirect()->route('manajer-sertifikasi.sk-ujikom.show', $sk)
            ->with('success', 'Pengajuan SK berhasil dikirim ke Direktur. Nomor SK: ' . $sk->nomor_sk);
    }

    /**
     * Detail pengajuan SK.
     */
    public function show(SkHasilUjikom $skUjikom)
    {
        $schedules = Schedule::with(['beritaAcara', 'asesor.user', 'tuk', 'skema'])
            ->whereHas('asesmens', fn($q) => $q->where('collective_batch_id', $skUjikom->collective_batch_id))
            ->get();

        $scheduleIds = $schedules->pluck('id');

        $pesertaKompeten = BeritaAcaraAsesi::with(['asesmen'])
            ->whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
            ->where('rekomendasi', 'K')
            ->get()
            ->map(fn($baa) => $baa->asesmen)
            ->filter();

        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $skUjikom->collective_batch_id)
            ->first();

        return view('manajer-sertifikasi.sk-ujikom.show', compact(
            'skUjikom', 'schedules', 'pesertaKompeten', 'first'
        ));
    }

    /**
     * Download PDF SK (hanya jika sudah approved).
     */
    public function download(SkHasilUjikom $skUjikom)
    {
        abort_unless($skUjikom->isApproved() && $skUjikom->hasSk(), 403, 'SK belum tersedia.');
        abort_unless(Storage::disk('private')->exists($skUjikom->sk_path), 404, 'File SK tidak ditemukan.');

        $filename = 'SK_Hasil_Ujikom_' . str_replace(['/', ' '], ['-', '_'], $skUjikom->nomor_sk) . '.pdf';

        return response()->streamDownload(function () use ($skUjikom) {
            echo Storage::disk('private')->get($skUjikom->sk_path);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }
}