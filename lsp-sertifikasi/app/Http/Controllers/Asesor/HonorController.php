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
            ->with(['details.schedule.skema', 'details.schedule.tuk'])
            ->latest()
            ->get();

        return view('asesor.honor.index', compact('honors'));
    }

    /**
     * Detail satu honor payment.
     */
    public function show(HonorPayment $honor)
    {
        $asesor = Auth::user()->asesor;
        abort_if($honor->asesor_id !== $asesor->id, 403);

        $honor->load([
            'details.schedule.skema',
            'details.schedule.tuk',
            'asesor.user',
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
     */
    public function downloadBukti(HonorPayment $honor): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $asesor = Auth::user()->asesor;
        abort_if($honor->asesor_id !== $asesor->id, 403);
        abort_unless($honor->bukti_transfer_path, 404, 'Bukti transfer belum tersedia.');

        return Storage::disk('private')->download($honor->bukti_transfer_path, $honor->bukti_transfer_name);
    }

    /**
     * Download kwitansi PDF (hanya setelah dikonfirmasi).
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
        ]);

        // TTD asesor dari profil user
        $ttdAsesor = Auth::user()->signature_image;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.honor-kwitansi', [
            'honor'     => $honor,
            'ttdAsesor' => $ttdAsesor,
        ])->setPaper('A4', 'portrait');

        $filename = 'Kwitansi_Honor_' . str_replace('/', '-', $honor->nomor_kwitansi) . '.pdf';

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }
}