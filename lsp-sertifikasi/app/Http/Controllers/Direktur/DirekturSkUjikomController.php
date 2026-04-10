<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\BeritaAcaraAsesi;
use App\Models\Schedule;
use App\Models\SkHasilUjikom;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DirekturSkUjikomController extends Controller
{
    /**
     * Daftar semua pengajuan SK — tab pending & history.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $pending = SkHasilUjikom::where('status', 'submitted')
            ->orderBy('submitted_at')
            ->get()
            ->map(fn($sk) => $this->withBatchInfo($sk));

        $history = SkHasilUjikom::whereIn('status', ['approved', 'rejected'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($sk) => $this->withBatchInfo($sk));

        return view('direktur.sk-ujikom.index', compact('pending', 'history', 'tab'));
    }

    /**
     * Detail satu pengajuan SK.
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

        return view('direktur.sk-ujikom.show', compact(
            'skUjikom', 'schedules', 'pesertaKompeten', 'first'
        ));
    }

    /**
     * Preview PDF SK tanpa TTD (untuk review direktur sebelum approve).
     */
    public function preview(SkHasilUjikom $skUjikom)
    {
        $schedules = Schedule::with(['tuk', 'skema', 'asesor.user', 'beritaAcara'])
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

        $pdf = Pdf::loadView('pdf.sk-hasil-ujikom', [
            'skUjikom'        => $skUjikom,
            'pesertaKompeten' => $pesertaKompeten,
            'schedules'       => $schedules,
            'first'           => $first,
            'direktur'        => auth()->user(),
            'preview'         => true,   // ← tidak load TTD
        ])->setPaper('A4', 'portrait');

        $filename = 'DRAFT_SK_' . $skUjikom->collective_batch_id . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Approve pengajuan → generate PDF SK.
     */
    public function approve(Request $request, SkHasilUjikom $skUjikom)
    {
        abort_unless($skUjikom->isSubmitted(), 422, 'Pengajuan ini sudah diproses.');

        DB::beginTransaction();
        try {
            $skUjikom->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'catatan_direktur' => $request->input('catatan'),
            ]);

            // Generate PDF
            $skPath = $this->generatePdf($skUjikom);
            $skUjikom->update(['sk_path' => $skPath]);

            DB::commit();

            Log::info("Direktur #{Auth::id()} approve SK Ujikom #{$skUjikom->id} batch {$skUjikom->collective_batch_id}");

            return back()->with('success', 'SK berhasil disetujui dan dokumen PDF telah digenerate.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve SK Ujikom error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reject pengajuan.
     */
    public function reject(Request $request, SkHasilUjikom $skUjikom)
    {
        $request->validate([
            'catatan_direktur' => 'required|string|min:10',
        ], [
            'catatan_direktur.required' => 'Alasan penolakan wajib diisi.',
            'catatan_direktur.min'      => 'Alasan penolakan minimal 10 karakter.',
        ]);

        abort_unless($skUjikom->isSubmitted(), 422, 'Pengajuan ini sudah diproses.');

        $skUjikom->update([
            'status'           => 'rejected',
            'catatan_direktur' => $request->catatan_direktur,
            'rejected_at'      => now(),
        ]);

        Log::info("Direktur #{Auth::id()} reject SK Ujikom #{$skUjikom->id}. Alasan: {$request->catatan_direktur}");

        return back()->with('success', 'Pengajuan SK ditolak. Manajer akan diberitahu.');
    }


    /**
     * Re-generate PDF SK (hanya jika sudah approved).
     */
    public function regenerate(SkHasilUjikom $skUjikom)
    {
        abort_unless($skUjikom->isApproved(), 422, 'SK hanya bisa di-regenerate setelah disetujui.');

        try {
            $skPath = $this->generatePdf($skUjikom);
            $skUjikom->update(['sk_path' => $skPath]);

            Log::info("Direktur #{Auth::id()} regenerate SK Ujikom #{$skUjikom->id}");

            return back()->with('success', 'SK berhasil di-generate ulang.');
        } catch (\Exception $e) {
            Log::error('Regenerate SK Ujikom error: ' . $e->getMessage());
            return back()->with('error', 'Gagal re-generate SK: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF SK (direktur juga bisa).
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

    // =========================================================================
    // Private Helpers
    // =========================================================================

    private function withBatchInfo(SkHasilUjikom $sk): array
    {
        $first = Asesmen::with(['tuk', 'skema'])
            ->where('collective_batch_id', $sk->collective_batch_id)
            ->first();

        $scheduleIds = Schedule::whereHas(
            'asesmens', fn($q) => $q->where('collective_batch_id', $sk->collective_batch_id)
        )->pluck('id');

        $totalK = BeritaAcaraAsesi::whereHas('beritaAcara', fn($q) => $q->whereIn('schedule_id', $scheduleIds))
            ->where('rekomendasi', 'K')->count();

        return [
            'sk'        => $sk,
            'tuk'       => $first?->tuk,
            'skema'     => $first?->skema,
            'total_k'   => $totalK,
        ];
    }

    private function generatePdf(SkHasilUjikom $skUjikom): string
    {
        $schedules = Schedule::with(['tuk', 'skema', 'asesor.user', 'beritaAcara'])
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

        $direktur = auth()->user();

        $pdf = Pdf::loadView('pdf.sk-hasil-ujikom', [
            'skUjikom'        => $skUjikom,
            'pesertaKompeten' => $pesertaKompeten,
            'schedules'       => $schedules,
            'first'           => $first,
            'direktur'        => $direktur,
        ])->setPaper('A4', 'portrait');

        $path = "sk-hasil-ujikom/{$skUjikom->collective_batch_id}.pdf";
        Storage::disk('private')->put($path, $pdf->output());

        return $path;
    }
}