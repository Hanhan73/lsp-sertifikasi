<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\HonorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HonorController extends Controller
{
    /**
     * List semua honor payment milik asesor yang login.
     */
    public function index()
    {
        $asesor = Auth::user()->asesor;
        abort_unless($asesor, 403);

        $honors = HonorPayment::where('asesor_id', $asesor->id)
            ->with(['details.schedule.skema', 'details.schedule.tuk', 'deductionReceivable'])
            ->latest()
            ->get();

        // Ringkasan hutang aktif (pinjaman) milik asesor ini — supaya asesor
        // bisa lihat sendiri sisa hutangnya tanpa perlu tanya ke bendahara.
        $hutangAktif = \App\Models\OtherReceivable::where('asesor_id', $asesor->id)
            ->where('jenis', 'pinjaman')
            ->whereIn('status', ['outstanding', 'cicilan'])
            ->orderByDesc('tanggal')
            ->get();

        $totalHutangAwal   = $hutangAktif->sum('jumlah');
        $totalSudahDicicil = $hutangAktif->sum('jumlah_lunas');
        $totalSisaHutang   = $hutangAktif->sum('sisa');

        return view('asesor.honor.index', compact(
            'honors', 'hutangAktif', 'totalHutangAwal', 'totalSudahDicicil', 'totalSisaHutang'
        ));
    }

    /**
     * Detail satu honor payment.
     * Guard: asesor hanya bisa lihat kalau sudah_dibayar atau dikonfirmasi.
     */
    public function show(HonorPayment $honor)
    {
        $asesor = Auth::user()->asesor;
        abort_if($honor->asesor_id !== $asesor->id, 403);

        // ── BARU: hide dari asesor sebelum sudah_dibayar ──
        if (!$honor->asesor_can_view) {
            return redirect()->route('asesor.honor.index')
                ->with('info', 'Kwitansi belum tersedia. Tunggu hingga bendahara menyelesaikan pembayaran.');
        }

        $honor->load([
            'details.schedule.skema',
            'details.schedule.tuk',
            'asesor.user',
            'deductionReceivable',
        ]);

        return view('asesor.honor.show', compact('honor'));
    }

    /**
     * Asesor konfirmasi menerima honor → status jadi dikonfirmasi.
     */
    public function konfirmasi(HonorPayment $honor)
    {
        $asesor = Auth::user()->asesor;
        abort_if($honor->asesor_id !== $asesor->id, 403);
        abort_if(!$honor->isSudahDibayar(), 422, 'Honor belum dibayarkan oleh bendahara.');

        $honor->update([
            'status'          => 'dikonfirmasi',
            'dikonfirmasi_at' => now(),
        ]);

        return back()->with('success', 'Penerimaan honor berhasil dikonfirmasi. Kwitansi Anda sudah siap didownload.');
    }

    /**
     * Download bukti transfer dari bendahara.
     * Guard: hanya bisa diakses kalau sudah_dibayar atau dikonfirmasi.
     */
    public function downloadBukti(HonorPayment $honor, Request $request)
    {
        $asesor = Auth::user()->asesor;
        abort_if($honor->asesor_id !== $asesor->id, 403);
        abort_if(!$honor->asesor_can_view, 403, 'Bukti transfer belum tersedia.');
        abort_unless($honor->bukti_transfer_path, 404, 'Bukti transfer belum tersedia.');

        $path = storage_path('app/private/' . $honor->bukti_transfer_path);
        abort_unless(file_exists($path), 404, 'File tidak ditemukan.');

        $ext      = strtolower(pathinfo($honor->bukti_transfer_path, PATHINFO_EXTENSION));
        $isImage  = in_array($ext, ['jpg', 'jpeg', 'png']);
        $filename = $honor->bukti_transfer_name ?? 'bukti-honor.' . $ext;

        if ($request->boolean('download')) {
            return response()->download($path, $filename);
        }

        return response()->file($path, [
            'Content-Type' => $isImage
                ? 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext)
                : 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Download kwitansi PDF.
     * Guard: hanya setelah dikonfirmasi.
     */
    public function downloadKwitansi(HonorPayment $honor)
    {
        $asesor = Auth::user()->asesor;
        abort_if($honor->asesor_id !== $asesor->id, 403);
        abort_if(!$honor->isDikonfirmasi(), 422, 'Kwitansi baru tersedia setelah konfirmasi penerimaan.');

        $honor->load([
            'asesor.user',
            'details.schedule.skema',
            'details.schedule.tuk',
            'details.schedule.asesmens',
            'deductionReceivable',
        ]);

        $isDraft   = false;
        $ttdAsesor = Auth::user()->signature_image;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.honor-kwitansi', [
            'honor'     => $honor,
            'isDraft'   => $isDraft,
            'ttdAsesor' => $ttdAsesor,
        ])->setPaper('A4', 'landscape');

        $filename = 'Kwitansi_Honor_' . str_replace('/', '-', $honor->nomor_kwitansi) . '.pdf';

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }
}