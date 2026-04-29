<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Asesor;
use App\Models\HonorPayment;
use App\Models\HonorPaymentDetail;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\JournalService;


class HonorAsesorController extends Controller
{
    /**
     * List asesor yang punya berita acara (jadwal selesai).
     */
    public function index()
    {
        // Asesor yang minimal punya 1 jadwal dengan berita acara terupload
        $asesors = Asesor::whereHas('schedules', function ($q) {
            $q->whereHas('beritaAcara');
        })
            ->with([
                'schedules' => function ($q) {
                    $q->whereHas('beritaAcara')
                        ->with(['skema', 'tuk', 'beritaAcara']);
                },
            ])
            ->orderBy('nama')
            ->get();

        return view('bendahara.honor.index', compact('asesors'));
    }

    /**
     * Detail asesor: list jadwal yang bisa dipilih untuk dibayar.
     */
    public function show(Asesor $asesor)
    {
        // Jadwal dengan berita acara tapi belum masuk honor payment yang dikonfirmasi/dibayar
        $jadwalTersedia = Schedule::where('asesor_id', $asesor->id)
            ->whereHas('beritaAcara')
            ->whereDoesntHave('honorPaymentDetails', function ($q) {
                $q->whereHas('honorPayment', function ($q2) {
                    $q2->whereIn('status', ['menunggu_pembayaran', 'sudah_dibayar', 'dikonfirmasi']);
                });
            })
            ->with(['skema', 'tuk', 'beritaAcara', 'asesmens'])
            ->orderBy('assessment_date')
            ->get();

        // Riwayat honor payment asesor ini
        $riwayat = HonorPayment::where('asesor_id', $asesor->id)
            ->with(['details.schedule.skema', 'details.schedule.tuk'])
            ->latest()
            ->get();

        return view('bendahara.honor.show', compact('asesor', 'jadwalTersedia', 'riwayat'));
    }

    /**
     * Buat honor payment baru (pilih jadwal-jadwal yang mau dibayar).
     */
    public function store(Request $request, Asesor $asesor)
    {
        $request->validate([
            'schedule_ids'   => 'required|array|min:1',
            'schedule_ids.*' => 'required|exists:schedules,id',
        ], [
            'schedule_ids.required' => 'Pilih minimal 1 jadwal.',
            'schedule_ids.min'      => 'Pilih minimal 1 jadwal.',
        ]);

        DB::beginTransaction();
        try {
            $schedules = Schedule::whereIn('id', $request->schedule_ids)
                ->where('asesor_id', $asesor->id)
                ->whereHas('beritaAcara')
                ->with(['skema', 'asesmens'])
                ->get();

            abort_if($schedules->isEmpty(), 422, 'Tidak ada jadwal valid yang dipilih.');

            $details = [];
            $total   = 0;

            foreach ($schedules as $schedule) {
                $jumlahAsesi   = $schedule->asesmens()->count();
                $honorPerAsesi = $schedule->skema->honor_per_asesi ?? 0;
                $subtotal      = $jumlahAsesi * $honorPerAsesi;
                $total        += $subtotal;

                $details[] = [
                    'schedule_id'    => $schedule->id,
                    'jumlah_asesi'   => $jumlahAsesi,
                    'honor_per_asesi' => $honorPerAsesi,
                    'subtotal'       => $subtotal,
                ];
            }

            $honor = HonorPayment::create([
                'asesor_id'        => $asesor->id,
                'nomor_kwitansi'   => HonorPayment::generateNomor(),
                'tanggal_kwitansi' => now()->toDateString(),
                'total'            => $total,
                'status'           => 'menunggu_pembayaran',
                'dibuat_oleh'      => Auth::id(),
            ]);

            foreach ($details as $d) {
                HonorPaymentDetail::create(array_merge(['honor_payment_id' => $honor->id], $d));
            }

            DB::commit();

            return redirect()
                ->route('bendahara.honor.payment.show', $honor)
                ->with('success', 'Kwitansi honor berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Detail 1 honor payment.
     */
    public function showPayment(HonorPayment $honor)
    {
        $honor->load([
            'asesor.user',
            'details.schedule.skema',
            'details.schedule.tuk',
            'dibuatOleh',
            'dibayarOleh',
        ]);

        return view('bendahara.honor.payment', compact('honor'));
    }

    /**
     * Upload bukti transfer → status jadi sudah_dibayar.
     */
    public function uploadBukti(Request $request, HonorPayment $honor)
    {
        abort_if(!$honor->isMenunggu(), 422, 'Status tidak valid untuk upload bukti.');

        $request->validate([
            'bukti_transfer' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'bukti_transfer.required' => 'File bukti transfer wajib diupload.',
            'bukti_transfer.mimes'    => 'Format file harus jpg, png, atau pdf.',
        ]);

        if ($honor->bukti_transfer_path) {
            Storage::disk('private')->delete($honor->bukti_transfer_path);
        }

        $file = $request->file('bukti_transfer');
        $path = $file->store("honor/bukti-transfer/{$honor->id}", 'private');

        $honor->update([
            'bukti_transfer_path' => $path,
            'bukti_transfer_name' => $file->getClientOriginalName(),
            'status'              => 'sudah_dibayar',
            'dibayar_at'          => now(),
            'dibayar_oleh'        => Auth::id(),
        ]);

        try {
            app(JournalService::class)->jurnalHonorDibayar($honor->fresh(['asesor']));
        } catch (\Exception $e) {
            \Log::warning('Gagal buat jurnal honor: ' . $e->getMessage());
        }

        // Notifikasi ke asesor
        if ($honor->asesor) {
            $honor->asesor->notifications()->create([
                'type'    => 'honor_dibayar',
                'title'   => 'Honor Asesor Telah Ditransfer',
                'message' => "Honor asesmen Anda sejumlah Rp " . number_format($honor->total, 0, ',', '.') . " telah ditransfer. Silakan konfirmasi penerimaan.",
                'data'    => json_encode(['honor_payment_id' => $honor->id]),
            ]);
        }

        return back()->with('success', 'Bukti transfer berhasil diupload. Notifikasi telah dikirim ke asesor.');
    }

    /**
     * Download bukti transfer.
     */
    public function downloadBukti(HonorPayment $honor, Request $request): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless($honor->bukti_transfer_path, 404, 'Bukti transfer belum diupload.');

        $disk = app()->environment('production')
            ? 'public_html'
            : 'private';

        $path = Storage::disk($disk)->path($honor->bukti_transfer_path);
        abort_unless(file_exists($path), 404, 'File tidak ditemukan.');

        return $request->boolean('download')
            ? response()->download($path, $honor->bukti_transfer_name)
            : response()->file($path);
    }

    /**
     * Generate PDF kwitansi honor.
     */
    public function pdfKwitansi(HonorPayment $honor)
    {
        abort_if($honor->isMenunggu(), 422, 'Kwitansi hanya bisa digenerate setelah pembayaran dikonfirmasi asesor.');

        $honor->load([
            'asesor.user',
            'details.schedule.skema',
            'details.schedule.tuk',
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.honor-kwitansi', [
            'honor' => $honor,
        ])->setPaper('A4', 'portrait');

        $filename = 'Kwitansi_Honor_' . str_replace('/', '-', $honor->nomor_kwitansi) . '.pdf';

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }
}
