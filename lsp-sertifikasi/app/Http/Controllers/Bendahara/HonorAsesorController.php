<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Asesor;
use App\Models\HonorPayment;
use App\Models\HonorPaymentDetail;
use App\Models\OtherReceivable;
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

        $allHonors = HonorPayment::with(['asesor', 'details'])->get();

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
            'belum_dibuat_count'    => $belumDibuatCount,
            'belum_dibayar_count'   => $allHonors->where('status', 'menunggu_pembayaran')->count(),
            'belum_dibayar_nominal' => $allHonors->where('status', 'menunggu_pembayaran')->sum('total'),
            'sudah_dibayar_count'   => $allHonors->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])->count(),
            'sudah_dibayar_nominal' => $allHonors->whereIn('status', ['sudah_dibayar', 'dikonfirmasi'])->sum('total'),
            'dikonfirmasi_count'    => $allHonors->where('status', 'dikonfirmasi')->count(),
            'dikonfirmasi_nominal'  => $allHonors->where('status', 'dikonfirmasi')->sum('total'),
            'per_asesor'            => $allHonors->groupBy('asesor_id')->map(function ($honors) {
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
            ->with(['skema.honorTiers', 'tuk', 'beritaAcara', 'asesmens'])
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

            $details      = [];
            $total        = 0;
            $tierIds      = $request->input('tier_ids', []);
            $honorAmounts = $request->input('honor_amounts', []);

            foreach ($schedules as $schedule) {
                $jumlahAsesi   = $schedule->asesmens()->count();
                $honorPerAsesi = 0;

                $tierId = $tierIds[$schedule->id] ?? null;
                if ($tierId) {
                    $tier = $schedule->skema->honorTiers->firstWhere('id', (int) $tierId);
                    if ($tier) $honorPerAsesi = $tier->amount;
                }

                if (!$honorPerAsesi && isset($honorAmounts[$schedule->id])) {
                    $honorPerAsesi = (int) str_replace(['.', ','], '', $honorAmounts[$schedule->id]);
                }

                if (!$honorPerAsesi) {
                    $defaultTier = $schedule->skema->honorTiers->firstWhere('is_default', true)
                        ?? $schedule->skema->honorTiers->first();
                    if ($defaultTier) {
                        $honorPerAsesi = $defaultTier->amount;
                    } else {
                        $honorPerAsesi = $schedule->skema->honor_per_asesi ?? 0;
                    }
                }

                $subtotal  = $jumlahAsesi * $honorPerAsesi;
                $total    += $subtotal;

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
            'deductionReceivable',
        ]);

        // Hutang aktif milik asesor ini untuk form cicilan
        $hutangAsesor = OtherReceivable::where('asesor_id', $honor->asesor_id)
            ->whereIn('status', ['outstanding', 'cicilan'])
            ->where('jenis', 'pinjaman')
            ->orderByDesc('tanggal')
            ->get();


        $coaOptions = \App\Models\ChartOfAccount::where('tipe', 'aset')
            ->where('is_active', true)
            ->orderBy('kode')
            ->get();

        return view('bendahara.honor.payment', compact('honor', 'hutangAsesor', 'coaOptions'));
    }

    /**
     * Upload bukti transfer → status jadi sudah_dibayar.
     * Mendukung: upload pertama, ganti bukti, dan cicilan hutang opsional.
     */
    public function uploadBukti(Request $request, HonorPayment $honor)
    {
        // Hanya block kalau masih menunggu tapi tidak ada bukti (harusnya tidak mungkin terjadi)
        // Semua status selain menunggu_pembayaran boleh ganti bukti
        if ($honor->isMenunggu() && $honor->bukti_transfer_path) {
            // edge case: menunggu tapi ada bukti — ini state invalid, izinkan saja
        }

        $request->validate([
            'bukti_transfer'          => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'deduction_receivable_id' => 'nullable|exists:other_receivables,id',
            'deduction_amount'        => 'nullable|numeric|min:1000',
            'deduction_note'          => 'nullable|string|max:500',
        ], [
            'bukti_transfer.required' => 'File bukti transfer wajib diupload.',
            'bukti_transfer.mimes'    => 'Format file harus jpg, png, atau pdf.',
            'bukti_transfer.max'      => 'Ukuran file maksimal 5MB.',
            'deduction_amount.min'    => 'Nominal cicilan minimal Rp 1.000.',
        ]);

        // Validasi deduction
        if ($request->filled('deduction_receivable_id') && $request->filled('deduction_amount')) {
            $receivable = \App\Models\OtherReceivable::find($request->deduction_receivable_id);
            if ($receivable) {
                if ($request->deduction_amount > $receivable->sisa) {
                    return back()->withErrors([
                        'deduction_amount' => 'Nominal cicilan melebihi sisa hutang (Rp ' .
                            number_format($receivable->sisa, 0, ',', '.') . ').',
                    ])->withInput();
                }
                if ($request->deduction_amount >= $honor->total) {
                    return back()->withErrors([
                        'deduction_amount' => 'Nominal cicilan tidak boleh melebihi atau sama dengan total honor.',
                    ])->withInput();
                }
            }
        }

        \DB::beginTransaction();
        try {
            // Hapus file lama
            if ($honor->bukti_transfer_path) {
                \Storage::disk('private')->delete($honor->bukti_transfer_path);
            }

            $file = $request->file('bukti_transfer');
            $path = $file->store("honor/bukti-transfer/{$honor->id}", 'private');

            // Base update data
            $updateData = [
                'bukti_transfer_path'     => $path,
                'bukti_transfer_name'     => $file->getClientOriginalName(),
                'deduction_receivable_id' => null,
                'deduction_amount'        => null,
                'deduction_note'          => null,
            ];

            // Kalau masih menunggu → ubah ke sudah_dibayar + catat waktu bayar
            if ($honor->isMenunggu()) {
                $updateData['status']      = 'sudah_dibayar';
                $updateData['dibayar_at']  = now();
                $updateData['dibayar_oleh'] = \Auth::id();
            }
            // Kalau sudah_dibayar atau dikonfirmasi → JANGAN ubah status
            // Hanya update file bukti saja

            // Simpan cicilan kalau diisi
            if ($request->filled('deduction_receivable_id') && $request->filled('deduction_amount')) {
                $updateData['deduction_receivable_id'] = $request->deduction_receivable_id;
                $updateData['deduction_amount']        = $request->deduction_amount;
                $updateData['deduction_note']          = $request->deduction_note;

                $receivable = \App\Models\OtherReceivable::find($request->deduction_receivable_id);
                if ($receivable) {
                    $newLunas = (float) ($receivable->jumlah_lunas ?? 0) + (float) $request->deduction_amount;
                    $sisa     = (float) $receivable->jumlah - $newLunas;
                    $receivable->update([
                        'jumlah_lunas'  => $newLunas,
                        'status'        => $sisa <= 0 ? 'lunas' : 'cicilan',
                        'tanggal_lunas' => $sisa <= 0 ? now()->toDateString() : null,
                    ]);
                }
            }

            $honor->update($updateData);

            // Jurnal hanya saat upload pertama
            if ($honor->wasChanged('status') && $honor->status === 'sudah_dibayar') {
                try {
                    app(\App\Services\JournalService::class)->jurnalHonorDibayar($honor->fresh(['asesor']));
                } catch (\Exception $e) {
                    \Log::warning('Gagal buat jurnal honor dibayar: ' . $e->getMessage());
                }

                // Notifikasi ke asesor
                if ($honor->asesor && method_exists($honor->asesor, 'notifications')) {
                    $honor->asesor->notifications()->create([
                        'type'    => 'honor_dibayar',
                        'title'   => 'Honor Asesor Telah Ditransfer',
                        'message' => 'Honor asesmen Anda sejumlah Rp ' . number_format($honor->total, 0, ',', '.') .
                            ' telah ditransfer. Silakan konfirmasi penerimaan.',
                        'data'    => json_encode(['honor_payment_id' => $honor->id]),
                    ]);
                }
            }

            \DB::commit();

            $msg = $honor->isDikonfirmasi()
                ? 'Bukti transfer berhasil diperbarui.'
                : ($honor->isMenunggu()
                    ? 'Bukti transfer berhasil diupload.'
                    : 'Bukti transfer berhasil diganti.');

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('[HonorAsesor][uploadBukti] ' . $e->getMessage());
            return back()->with('error', 'Gagal mengupload bukti transfer: ' . $e->getMessage());
        }
    }


    /**
     * Reset kwitansi — hanya boleh kalau belum ada bukti transfer.
     */
    public function resetKwitansi(HonorPayment $honor)
    {
        if (!$honor->can_reset) {
            return response()->json([
                'success' => false,
                'message' => 'Kwitansi tidak dapat direset karena sudah ada bukti transfer atau sudah dikonfirmasi.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $asesorId = $honor->asesor_id;

            $honor->details()->delete();
            $honor->delete();

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Kwitansi berhasil direset. Silakan atur ulang tarif honor.',
                'redirect' => route('bendahara.honor.show', $asesorId),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('[HonorAsesor][resetKwitansi] ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mereset kwitansi.'], 500);
        }
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
     * Asesor hanya bisa akses kalau sudah dibayar/dikonfirmasi.
     */
    public function pdfKwitansi(HonorPayment $honor)
    {
        // Guard untuk asesor
        if (auth()->user()->role === 'asesor') {
            $asesor = auth()->user()->asesor;
            abort_if(!$asesor || $asesor->id !== $honor->asesor_id, 403);
            abort_if(!$honor->asesor_can_view, 403, 'Kwitansi belum tersedia.');
        }

        $honor->load([
            'asesor.user',
            'details.schedule.skema',
            'details.schedule.tuk',
            'details.schedule.asesmens',
            'deductionReceivable',
        ]);

        $isDraft = !$honor->isDikonfirmasi();

        $ttdAsesor = null;
        if (!$isDraft) {
            $ttdPath = $honor->asesor->user?->ttd_path ?? null;
            if ($ttdPath) {
                $fullPath = storage_path('app/private/' . $ttdPath);
                if (file_exists($fullPath)) {
                    $ext       = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                    $mime      = $ext === 'png' ? 'image/png' : 'image/jpeg';
                    $ttdAsesor = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                }
            }
        }

        // ── Generate kwitansi PDF via DomPDF ──────────────────────────────────
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.honor-kwitansi', [
            'honor'     => $honor,
            'isDraft'   => $isDraft,
            'ttdAsesor' => $ttdAsesor,
        ])->setPaper('A4', 'landscape');

        $filename = 'Kwitansi_Honor_' . str_replace('/', '-', $honor->nomor_kwitansi) . '.pdf';
        if ($isDraft) {
            $filename = 'DRAFT_' . $filename;
        }

        // ── Cek apakah ada bukti transfer yang bisa digabung ─────────────────
        $buktiPath = $honor->bukti_transfer_path
            ? storage_path('app/private/' . $honor->bukti_transfer_path)
            : null;

        $buktiExt = $buktiPath
            ? strtolower(pathinfo($buktiPath, PATHINFO_EXTENSION))
            : null;

        // Gabungkan hanya kalau:
        // - Sudah dikonfirmasi (kwitansi final)
        // - Ada file bukti
        // - File bukti ada di disk
        // - FPDI library tersedia
        $shouldMerge = !$isDraft
            && $buktiPath
            && file_exists($buktiPath)
            && class_exists(\setasign\Fpdi\Fpdi::class);

        if ($shouldMerge) {
            try {
                $kwitansiString = $pdf->output();
                $mergedPdf      = app(\App\Services\PdfMergeService::class)
                    ->mergeKwitansiDenganBukti($kwitansiString, $buktiPath);

                if (request()->boolean('preview')) {
                    return response($mergedPdf, 200, [
                        'Content-Type'        => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $filename . '"',
                    ]);
                }

                return response($mergedPdf, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Content-Length'      => strlen($mergedPdf),
                ]);
            } catch (\Exception $e) {
                \Log::warning('[PdfMerge] Gagal merge PDF, fallback ke kwitansi saja: ' . $e->getMessage());
                // Fallback: return kwitansi saja tanpa bukti
            }
        }

        // Default: return kwitansi tanpa merge
        return request()->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    public function updateNomor(Request $request, HonorPayment $honor)
    {
        $request->validate([
            'nomor_kwitansi' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('honor_payments', 'nomor_kwitansi')->ignore($honor->id),
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
