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
     * Sekaligus hitung rekap statistik honor untuk tab Rekap.
     */
    public function index()
    {
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

        // ── Rekap statistik honor ─────────────────────────────────────────────
        $allHonors = HonorPayment::with(['asesor', 'details'])->get();

        // Asesor yang punya jadwal dengan BA tapi ada jadwal yang BELUM masuk kwitansi manapun
        $belumDibuatCount = Asesor::whereHas('schedules', function ($q) {
            $q->whereHas('beritaAcara')
              ->whereDoesntHave('honorPaymentDetails', function ($q2) {
                  $q2->whereHas('honorPayment', function ($q3) {
                      $q3->whereIn('status', ['menunggu_pembayaran', 'sudah_dibayar', 'dikonfirmasi']);
                  });
              });
        })->count();

        $rekapStats = [
            'total_honor'           => $allHonors->count(),
            'total_asesor_honor'    => $allHonors->pluck('asesor_id')->unique()->count(),
            'total_nominal'         => $allHonors->sum('total'),

            // Asesor yang masih punya jadwal belum dibuatkan kwitansi
            'belum_dibuat_count'    => $belumDibuatCount,

            // Belum dibayar = menunggu_pembayaran
            'belum_dibayar_count'   => $allHonors->where('status', 'menunggu_pembayaran')->count(),
            'belum_dibayar_nominal' => $allHonors->where('status', 'menunggu_pembayaran')->sum('total'),

            // Sudah dibayar (termasuk dikonfirmasi)
            'sudah_dibayar_count'   => $allHonors->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])->count(),
            'sudah_dibayar_nominal' => $allHonors->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])->sum('total'),

            // Dikonfirmasi asesor
            'dikonfirmasi_count'    => $allHonors->where('status', 'dikonfirmasi')->count(),
            'dikonfirmasi_nominal'  => $allHonors->where('status', 'dikonfirmasi')->sum('total'),

            // Per asesor — untuk tabel rekap
            'per_asesor' => $allHonors->groupBy('asesor_id')->map(function ($honors) {
                $asesor = $honors->first()->asesor;
                return [
                    'asesor_id'        => $asesor?->id,
                    'nama'             => $asesor?->nama ?? '-',
                    'no_reg_met'       => $asesor?->no_reg_met ?? '-',
                    'total_kwitansi'   => $honors->count(),
                    'menunggu'         => $honors->where('status', 'menunggu_pembayaran')->count(),
                    'sudah_dibayar'    => $honors->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])->count(),
                    'dikonfirmasi'     => $honors->where('status', 'dikonfirmasi')->count(),
                    'total_nominal'    => $honors->sum('total'),
                    'dibayar_nominal'  => $honors->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])->sum('total'),
                    'menunggu_nominal' => $honors->where('status', 'menunggu_pembayaran')->sum('total'),
                ];
            })->sortByDesc('total_nominal')->values(),
        ];

        return view('bendahara.honor.index', compact('asesors', 'rekapStats'));
    }

    /**
     * Detail asesor: list jadwal yang bisa dipilih untuk dibayar.
     */
    public function show(Asesor $asesor)
    {
        $asesor->load('rekenings');
 
        $jadwalTersedia = Schedule::where('asesor_id', $asesor->id)
            ->whereHas('beritaAcara')
            ->whereDoesntHave('honorPaymentDetails', function ($q) {
                $q->whereHas('honorPayment', function ($q2) {
                    $q2->whereIn('status', ['menunggu_pembayaran', 'sudah_dibayar', 'dikonfirmasi']);
                });
            })
            ->with([
                'skema.honorTiers',
                'tuk',
                'beritaAcara',
                'asesmens',
            ])
            ->orderBy('assessment_date')
            ->get();
 
        $riwayat = HonorPayment::where('asesor_id', $asesor->id)
            ->with(['details.schedule.skema', 'details.schedule.tuk'])
            ->latest()
            ->get();
 
        return view('bendahara.honor.show', compact('asesor', 'jadwalTersedia', 'riwayat'));
    }
 
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
                ->with(['skema.honorTiers', 'asesmens'])
                ->get();
 
            abort_if($schedules->isEmpty(), 422, 'Tidak ada jadwal valid yang dipilih.');
 
            $details = [];
            $total   = 0;
 
            $tierIds      = $request->input('tier_ids', []);
            $honorAmounts = $request->input('honor_amounts', []);
 
            foreach ($schedules as $schedule) {
                $jumlahAsesi = $schedule->asesmens()->count();
 
                $honorPerAsesi = 0;
                $tierLabel     = null;
 
                $tierId = $tierIds[$schedule->id] ?? null;
                if ($tierId) {
                    $tier = $schedule->skema->honorTiers->firstWhere('id', (int) $tierId);
                    if ($tier) {
                        $honorPerAsesi = $tier->amount;
                        $tierLabel     = $tier->label;
                    }
                }
 
                if (!$honorPerAsesi && isset($honorAmounts[$schedule->id])) {
                    $honorPerAsesi = (int) str_replace(['.', ','], '', $honorAmounts[$schedule->id]);
                }
 
                if (!$honorPerAsesi) {
                    $defaultTier = $schedule->skema->honorTiers->firstWhere('is_default', true)
                                   ?? $schedule->skema->honorTiers->first();
                    if ($defaultTier) {
                        $honorPerAsesi = $defaultTier->amount;
                        $tierLabel     = $defaultTier->label;
                    } else {
                        $honorPerAsesi = $schedule->skema->honor_per_asesi ?? 0;
                    }
                }
 
                $subtotal = $jumlahAsesi * $honorPerAsesi;
                $total   += $subtotal;
 
                $details[] = [
                    'schedule_id'     => $schedule->id,
                    'jumlah_asesi'    => $jumlahAsesi,
                    'honor_per_asesi' => $honorPerAsesi,
                    'subtotal'        => $subtotal,
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
 
            try {
                app(JournalService::class)->jurnalHonorDibuat($honor->fresh(['asesor']));
            } catch (\Exception $e) {
                \Log::warning('Gagal buat jurnal honor dibuat: ' . $e->getMessage());
            }
 
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
            'asesor.rekenings',
            'details.schedule.skema',
            'details.schedule.tuk',
            'details.schedule.asesmens',
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
     * Generate PDF kwitansi honor.
     */
    public function pdfKwitansi(HonorPayment $honor)
    {
        $honor->load([
            'asesor.user',
            'details.schedule.skema',
            'details.schedule.tuk',
            'details.schedule.asesmens', // untuk ambil collective_batch_id
        ]);

        $isDraft = !$honor->isDikonfirmasi();

        $ttdAsesor = null;
        if (!$isDraft) {
            $ttdPath = $honor->asesor->user?->ttd_path ?? null;
            if ($ttdPath) {
                $fullPath = storage_path('app/private/' . $ttdPath);
                if (file_exists($fullPath)) {
                    $ext       = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    $mime      = in_array($ext, ['png']) ? 'image/png' : 'image/jpeg';
                    $ttdAsesor = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.honor-kwitansi', [
            'honor'     => $honor,
            'isDraft'   => $isDraft,
            'ttdAsesor' => $ttdAsesor,
        ])->setPaper('A4', 'landscape');

        $filename = 'Kwitansi_Honor_' . str_replace('/', '-', $honor->nomor_kwitansi) . '.pdf';
        if ($isDraft) {
            $filename = 'DRAFT_' . $filename;
        }

        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    public function updateNomor(Request $request, HonorPayment $honor)
    {
        $request->validate([
            'nomor_kwitansi' => [
                'required', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('honor_payments', 'nomor_kwitansi')
                    ->ignore($honor->id),
            ],
        ], [
            'nomor_kwitansi.required' => 'Nomor kwitansi wajib diisi.',
            'nomor_kwitansi.unique'   => 'Nomor sudah digunakan kwitansi lain.',
        ]);

        $honor->update(['nomor_kwitansi' => $request->nomor_kwitansi]);

        return response()->json([
            'success' => true,
            'nomor'   => $honor->nomor_kwitansi,
            'message' => 'Nomor kwitansi berhasil diperbarui.',
        ]);
    }
}