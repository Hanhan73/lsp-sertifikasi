<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\FrAk03UmpanBalik;
use App\Models\Schedule;
use App\Models\Tuk;
use App\Models\Skema;
use App\Services\AsesorAssignmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * AdminScheduleController
 *
 * Admin membuat jadwal asesmen dan menugaskan asesor.
 * Setelah jadwal dibuat, harus menunggu persetujuan Direktur.
 * Status asesi baru berubah ke 'scheduled' setelah Direktur menyetujui.
 */
class AdminScheduleController extends Controller
{
    public function __construct(
        private AsesorAssignmentService $assignmentService
    ) {}

    /**
     * Kriteria asesi siap dijadwalkan:
     * - APL-01: sudah diverifikasi
     * - APL-02: sudah disubmit (bukan draft)
     * - FR.AK.01: sudah disubmit (bukan draft)
     * - Belum punya schedule_id
     */
    private function readyToScheduleQuery()
    {
        return Asesmen::with(['user', 'tuk', 'skema', 'aplsatu', 'apldua', 'frak01'])
            ->whereNull('schedule_id')
            ->whereHas('aplsatu', function ($q) {
                $q->where('status', 'verified');
            })
            ->whereHas('apldua', function ($q) {
                $q->whereNotIn('status', ['draft'])
                    ->whereNotNull('submitted_at');
            })
            ->whereHas('frak01', function ($q) {
                $q->whereNotIn('status', ['draft'])
                    ->whereNotNull('submitted_at');
            });
    }

    /**
     * Daftar semua jadwal + asesi siap dijadwalkan.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $readyToSchedule = $this->readyToScheduleQuery()
            ->orderBy('full_name')
            ->get()
            ->groupBy('tuk_id');

        $schedulesQuery = Schedule::with(['tuk', 'skema', 'asesor', 'asesmens.user'])
            ->orderBy('assessment_date', 'desc');

        if ($search) {
            $schedulesQuery->where(function ($q) use ($search) {
                $q->whereHas('skema', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('tuk', fn($tq) => $tq->where('name', 'like', "%{$search}%"))
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('asesor', fn($aq) => $aq->where('nama', 'like', "%{$search}%"));
            });
        }

        $schedules = $schedulesQuery->paginate(20)->appends($request->only('search'));

        $tuks   = Tuk::where('is_active', true)->orderBy('name')->get();
        $skemas = Skema::where('is_active', true)->orderBy('name')->get();

        $pendingApprovalCount = Schedule::pendingApproval()->count();

        return view('admin.schedules.index', compact(
            'readyToSchedule',
            'schedules',
            'tuks',
            'skemas',
            'pendingApprovalCount'
        ));
    }

    /**
     * Form buat jadwal baru.
     */
    public function create(Request $request)
    {
        $selectedIds = $request->input('asesmen_ids', []);
        $selectedAsesmens = $selectedIds
            ? Asesmen::with(['tuk', 'skema'])->whereIn('id', $selectedIds)->get()
            : collect();

        $tuks   = Tuk::where('is_active', true)->orderBy('name')->get();
        $skemas = Skema::where('is_active', true)->orderBy('name')->get();

        $availableAsesmens = $this->readyToScheduleQuery()
            ->orderBy('full_name')
            ->get();

        $batches = $availableAsesmens
            ->pluck('collective_batch_id')
            ->filter()
            ->unique()
            ->values();

        $autoInstitutionName = Schedule::computeInstitutionNameFromAsesmens($selectedAsesmens);

        return view('admin.schedules.create', compact(
            'selectedAsesmens',
            'availableAsesmens',
            'tuks',
            'skemas',
            'batches',
            'autoInstitutionName'
        ));
    }

    /**
     * Simpan jadwal baru.
     * Status asesi TIDAK berubah ke 'scheduled' dulu — menunggu approval Direktur.
     */
    public function store(Request $request)
    {
        $request->validate([
            'asesmen_ids'      => 'required|array|min:1',
            'asesmen_ids.*'    => 'exists:asesmens,id',
            'tuk_id'           => 'required|exists:tuks,id',
            'asesor_id'        => 'nullable|exists:asesors,id',
            'assessment_date'  => 'required|date|after_or_equal:today',
            'start_time'       => 'required',
            'end_time'         => 'required|after:start_time',
            'location_type'    => 'required|in:offline,online',
            'location'         => 'required|string|max:255',
            'meeting_link'     => 'nullable|url|max:500|required_if:location_type,online',
            'notes'            => 'nullable|string',
            'institution_name' => 'nullable|string|max:255',
        ]);

        $asesmens = $this->readyToScheduleQuery()
            ->whereIn('id', $request->asesmen_ids)
            ->get();

        if ($asesmens->count() !== count($request->asesmen_ids)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Beberapa asesi tidak memenuhi syarat penjadwalan (APL-01 harus terverifikasi, APL-02 dan FR.AK.01 harus sudah disubmit).');
        }

        $skemaIds = $asesmens->pluck('skema_id')->unique();
        if ($skemaIds->count() > 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Asesi dalam satu jadwal harus memiliki skema yang sama.');
        }

        DB::beginTransaction();
        try {
            $schedule = Schedule::create([
                'tuk_id'           => $request->tuk_id,
                'skema_id'         => $skemaIds->first(),
                'assessment_date'  => $request->assessment_date,
                'start_time'       => $request->start_time,
                'end_time'         => $request->end_time,
                'location'         => $request->location,
                'location_type'    => $request->location_type,
                'meeting_link'     => $request->location_type === 'online'
                                        ? $request->meeting_link
                                        : null,
                'notes'            => $request->notes,
                'created_by'       => auth()->id(),
                'approval_status'  => 'pending_approval',
                'asesor_id'        => $request->asesor_id ?: null,
                'institution_name' => $request->input('institution_name')
                                        ?: Schedule::computeInstitutionNameFromAsesmens($asesmens),
            ]);

            foreach ($asesmens as $asesmen) {
                $asesmen->update(['schedule_id' => $schedule->id]);
            }

            if ($request->asesor_id) {
                $asesor = \App\Models\Asesor::findOrFail($request->asesor_id);
                $this->assignmentService->assignAsesor($schedule, $asesor, 'Ditugaskan saat pembuatan jadwal');
            }

            DB::commit();

            Log::info("Admin #" . auth()->id() . " membuat jadwal #{$schedule->id} untuk {$asesmens->count()} asesi. Menunggu approval Direktur.");

            return redirect()->route('admin.schedules.index')
                ->with('success', "Jadwal berhasil dibuat untuk {$asesmens->count()} asesi dan sedang menunggu persetujuan Direktur.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin create schedule error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Detail jadwal + progress asesmen + dokumen.
     */
    public function show(Schedule $schedule)
    {
        $schedule->load([
            'tuk', 'skema', 'asesor.user', 'approvedBy',
            'asesmens.user', 'asesmens.aplsatu', 'asesmens.apldua', 'asesmens.frak01',
            'asesmens.frAk03',
            'asesmens.soalTeoriAsesi.soalTeori',
            'asesmens.jawabanObservasi',
            'distribusiSoalTeori.paketSoalTeori',
            'distribusiSoalObservasi.soalObservasi',
            'distribusiPortofolio.portofolio',
            'hasilObservasi',
            'hasilPortofolio',
            'beritaAcara.asesis',
        ]);

        $peserta      = $schedule->asesmens->sortBy('full_name')->values();
        $pesertaCount = $peserta->count();

        $asesmenDimulai = (bool) $schedule->assessment_start
            || $peserta->contains(fn($a) => in_array($a->status, [
                'asesmen_started', 'assessed', 'certified', 'certificate_distributed',
            ]));

        // TTD daftar hadir = kolom signed_at ATAU asesor sudah punya TTD profil
        $daftarHadirSigned = $schedule->isDaftarHadirSigned()
            || filled($schedule->asesor?->user?->signature);

        $progress  = $this->buildPesertaProgress($schedule, $peserta);
        $checklist = $this->buildChecklist($schedule, $peserta, $progress, $asesmenDimulai, $daftarHadirSigned);

        [$umpanBalik, $rekapUmpanBalik] = $this->buildUmpanBalik($peserta);
        $pertanyaanUmpanBalik = FrAk03UmpanBalik::PERTANYAAN;

        return view('admin.schedules.show', compact(
            'schedule', 'peserta', 'pesertaCount', 'progress', 'checklist',
            'asesmenDimulai', 'daftarHadirSigned',
            'umpanBalik', 'rekapUmpanBalik', 'pertanyaanUmpanBalik'
        ));
    }

    /**
     * Edit jadwal — hanya bisa diedit jika masih pending atau ditolak.
     */
    public function edit(Schedule $schedule)
    {
        if ($schedule->isApproved()) {
            return redirect()->route('admin.schedules.show', $schedule)
                ->with('error', 'Jadwal yang sudah disetujui tidak dapat diedit.');
        }

        $schedule->load(['tuk', 'skema', 'asesor', 'asesmens']);
        $tuks = Tuk::where('is_active', true)->orderBy('name')->get();

        return view('admin.schedules.edit', compact('schedule', 'tuks'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        if ($schedule->isApproved()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Jadwal sudah disetujui, tidak dapat diedit.'], 403);
            }
            return redirect()->back()->with('error', 'Jadwal sudah disetujui, tidak dapat diedit.');
        }

        $request->validate([
            'assessment_date'  => 'required|date',
            'start_time'       => 'required',
            'end_time'         => 'required|after:start_time',
            'location_type'    => 'required|in:offline,online',
            'location'         => 'required|string|max:255',
            'meeting_link'     => 'nullable|url|max:500|required_if:location_type,online',
            'notes'            => 'nullable|string',
            'institution_name' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'assessment_date', 'start_time', 'end_time', 'location',
            'location_type', 'notes', 'institution_name',
        ]);
        $data['meeting_link'] = $request->location_type === 'online' ? $request->meeting_link : null;

        if ($schedule->isRejected()) {
            $data['approval_status'] = 'pending_approval';
            $data['approval_notes']  = null;
            $data['rejected_at']     = null;
        }

        $schedule->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Jadwal berhasil diupdate dan dikembalikan ke antrian persetujuan Direktur.',
                'schedule' => $schedule->fresh()->toArray(),
            ]);
        }

        return redirect()->route('admin.schedules.show', $schedule)
            ->with('success', 'Jadwal berhasil diupdate dan dikembalikan ke antrian persetujuan Direktur.');
    }

    /**
     * Hapus jadwal — hanya bisa dihapus jika belum disetujui.
     */
    public function destroy(Schedule $schedule)
    {
        if ($schedule->isApproved()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Jadwal yang sudah disetujui tidak dapat dihapus.'], 403);
            }
            return redirect()->back()->with('error', 'Jadwal yang sudah disetujui tidak dapat dihapus.');
        }

        DB::beginTransaction();
        try {
            foreach ($schedule->asesmens as $asesmen) {
                $asesmen->update(['schedule_id' => null]);
            }
            $schedule->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Jadwal dihapus.']);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * AJAX: Available asesors untuk jadwal tertentu.
     */
    public function availableAsesors(Schedule $schedule)
    {
        $asesors = $this->assignmentService->getAvailableAsesors($schedule);

        return response()->json([
            'success' => true,
            'asesors' => $asesors->map(fn($a) => [
                'id'         => $a->id,
                'nama'       => $a->nama,
                'no_reg_met' => $a->no_reg_met,
                'email'      => $a->email,
                'foto_url'   => $a->foto_url,
            ]),
        ]);
    }

    /**
     * AJAX: Assign asesor ke jadwal.
     */
    public function assignAsesor(Request $request, Schedule $schedule)
    {
        $request->validate(['asesor_id' => 'required|exists:asesors,id']);

        $asesor = \App\Models\Asesor::findOrFail($request->asesor_id);
        $this->assignmentService->assignAsesor($schedule, $asesor);

        return response()->json([
            'success' => true,
            'message' => "Asesor {$asesor->nama} berhasil ditugaskan.",
        ]);
    }

    /**
     * AJAX: Unassign asesor dari jadwal.
     */
    public function unassignAsesor(Schedule $schedule)
    {
        $schedule->update(['asesor_id' => null, 'assigned_by' => null, 'assigned_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Asesor berhasil dilepas.',
        ]);
    }

    public function downloadSk(Schedule $schedule)
    {
        if (!$schedule->hasSk()) {
            abort(404, 'SK belum tersedia untuk jadwal ini.');
        }

        if (!Storage::disk('private')->exists($schedule->sk_path)) {
            abort(404, 'File SK tidak ditemukan.');
        }

        $ext      = pathinfo($schedule->sk_path, PATHINFO_EXTENSION);
        $filename = 'SK_' . str_replace('/', '-', $schedule->sk_number) . '.' . $ext;

        return response()->streamDownload(function () use ($schedule) {
            echo Storage::disk('private')->get($schedule->sk_path);
        }, $filename, [
            'Content-Type' => $ext === 'pdf' ? 'application/pdf' : 'text/html',
        ]);
    }

    // =========================================================================
    // DOKUMEN ASESMEN — daftar hadir, berita acara, foto dokumentasi
    // =========================================================================

    /**
     * Daftar hadir PDF. ?preview=1 → tampil di browser, default → download.
     */
    public function daftarHadir(Request $request, Schedule $schedule)
    {
        $schedule->load(['tuk', 'skema', 'asesor.user', 'asesmens.user']);

        $pdf = Pdf::loadView('pdf.daftar-hadir', [
            'schedule'  => $schedule,
            'asesmens'  => $schedule->asesmens,
            'asesor'    => $schedule->asesor,
            'ttdAsesor' => $schedule->asesor?->user?->signature_image,
        ])->setPaper('A4', 'portrait');

        $filename = 'Daftar_Hadir_' . $this->safeName($schedule->skema->name ?? 'Asesmen')
            . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }

    /**
     * Berita acara PDF (generate dari data BA). ?preview=1 → tampil di browser.
     */
    public function beritaAcaraPdf(Request $request, Schedule $schedule)
    {
        $schedule->load(['skema', 'tuk', 'asesor.user', 'asesmens', 'beritaAcara.asesis']);

        $ba = $schedule->beritaAcara;
        abort_unless($ba, 404, 'Berita acara belum tersedia.');

        $pdf = Pdf::loadView('pdf.berita-acara', [
            'schedule'    => $schedule,
            'beritaAcara' => $ba,
            'rekMap'      => $ba->asesis->pluck('rekomendasi', 'asesmen_id'),
            'asesor'      => $schedule->asesor,
        ])->setPaper('A4', 'portrait');

        $filename = 'Berita_Acara_' . $this->safeName($schedule->skema->name ?? 'Asesmen')
            . '_' . $schedule->assessment_date->format('d-m-Y') . '.pdf';

        return $request->boolean('preview') ? $pdf->stream($filename) : $pdf->download($filename);
    }

    /**
     * File berita acara asli yang diupload asesor.
     */
    public function beritaAcaraFile(Schedule $schedule)
    {
        $ba = $schedule->beritaAcara;

        abort_unless($ba && $ba->file_path, 404, 'File berita acara belum diupload.');
        abort_unless(Storage::disk('private')->exists($ba->file_path), 404, 'File berita acara tidak ditemukan di storage.');

        return Storage::disk('private')->download($ba->file_path, $ba->file_name);
    }

    /**
     * Foto dokumentasi (slot 1/2). ?download=1 → unduh, default → tampil.
     */
    public function foto(Request $request, Schedule $schedule, int $slot)
    {
        abort_unless(in_array($slot, [1, 2]), 404);

        $path = $schedule->{"foto_dokumentasi_{$slot}"};
        abort_unless($path && Storage::disk('private')->exists($path), 404, 'Foto tidak ditemukan.');

        $mime = Storage::disk('private')->mimeType($path) ?: 'image/jpeg';
        $ext  = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';

        $headers = [
            'Content-Type'  => $mime,
            'Cache-Control' => 'private, max-age=300',
        ];

        if ($request->boolean('download')) {
            $nama = 'Foto_' . $slot . '_' . $this->safeName($schedule->skema->name ?? 'Asesmen')
                . '_' . $schedule->assessment_date->format('Ymd') . '.' . $ext;
            $headers['Content-Disposition'] = "attachment; filename=\"{$nama}\"";
        }

        return response(Storage::disk('private')->get($path), 200, $headers);
    }

    private function safeName(string $name): string
    {
        return preg_replace('/[\/\\\\\s]+/', '_', $name);
    }

    // =========================================================================
    // PROGRESS ASESMEN — helper untuk halaman detail jadwal
    // =========================================================================

    /**
     * Umpan balik FR.AK.03 dianggap terisi kalau sudah submit atau jawabannya ada.
     */
    private function umpanBalikTerisi(?FrAk03UmpanBalik $f): bool
    {
        return $f !== null && ($f->isSubmitted() || !empty($f->jawaban));
    }

    /**
     * Progress per peserta: hadir, teori, observasi, dok. ujikom, umpan balik, rekomendasi BA.
     *
     * Status teori:
     *  - selesai        : data soal ada & sudah submit
     *  - selesai_arsip  : jadwal lama (distribusi tanpa paket), data soal tidak tersimpan,
     *                     tapi asesi sudah punya rekomendasi di BA → dianggap selesai
     *  - mengerjakan    : sudah mulai/menjawab tapi belum submit
     *  - belum          : sudah dapat soal, belum mulai
     *  - hilang         : distribusi ada, data soal seluruh jadwal tidak ada, BA belum keluar
     *  - kosong         : distribusi ada, peserta ini saja yang tidak dapat soal
     *  - na             : jadwal tidak memakai teori
     */
    private function buildPesertaProgress(Schedule $schedule, $peserta): array
    {
        $distTeori = $schedule->distribusiSoalTeori;
        $distObs   = $schedule->distribusiSoalObservasi;
        $totalObs  = $distObs->count();
        $rekMap    = $schedule->beritaAcara?->asesis->pluck('rekomendasi', 'asesmen_id') ?? collect();

        $teoriHilang = $distTeori && $peserta->every(fn($a) => $a->soalTeoriAsesi->isEmpty());
        $teoriLegacy = $distTeori && $distTeori->paket_soal_teori_id === null;

        $result = [];
        foreach ($peserta as $a) {
            // ── Teori ──
            $semuaSoal = $a->soalTeoriAsesi;
            $soal = $distTeori && $semuaSoal->contains('distribusi_soal_teori_id', $distTeori->id)
                ? $semuaSoal->where('distribusi_soal_teori_id', $distTeori->id)
                : $semuaSoal;

            $total     = $soal->count();
            $dijawab   = $soal->filter(fn($s) => filled($s->jawaban))->count();
            $submitted = $total > 0 && $soal->contains(fn($s) => $s->submitted_at !== null);
            $mulai     = $soal->contains(fn($s) => $s->started_at !== null);
            $benar     = $soal->filter(fn($s) => filled($s->jawaban) && $s->soalTeori
                            && strtolower($s->jawaban) === strtolower($s->soalTeori->jawaban_benar))->count();

            if ($total === 0) {
                if ($teoriHilang) {
                    $statusTeori = ($teoriLegacy && filled($rekMap[$a->id] ?? null)) ? 'selesai_arsip' : 'hilang';
                } else {
                    $statusTeori = $distTeori ? 'kosong' : 'na';
                }
            } else {
                $statusTeori = $submitted ? 'selesai' : (($mulai || $dijawab > 0) ? 'mengerjakan' : 'belum');
            }

            $teori = [
                'status'  => $statusTeori,
                'total'   => $total,
                'dijawab' => $dijawab,
                'benar'   => $benar,
                'nilai'   => $total > 0 ? round($benar / $total * 100) : null,
            ];

            // ── Observasi ──
            $obsDone = 0;
            foreach ($distObs as $d) {
                $ada = $a->jawabanObservasi->contains(fn($j) => filled($j->gdrive_link) && (
                    $j->paket_soal_observasi_id == $d->paket_soal_observasi_id
                    || $j->distribusi_soal_observasi_id == $d->id
                ));
                if ($ada) $obsDone++;
            }

            $result[$a->id] = [
                'hadir'       => $a->hadir !== false, // null = default hadir
                'teori'       => $teori,
                'observasi'   => ['done' => $obsDone, 'total' => $totalObs],
                'ujikom'      => filled($a->apldua?->gdrive_ujikom),
                'umpan_balik' => $this->umpanBalikTerisi($a->frAk03),
                'rekomendasi' => $rekMap[$a->id] ?? null,
            ];
        }

        return $result;
    }

    /**
     * Detail umpan balik per asesi + rekap per pertanyaan.
     *
     * @return array{0: array, 1: array}
     */
    private function buildUmpanBalik($peserta): array
    {
        $normalisasi = function ($v): ?string {
            $v = strtolower(trim((string) $v));
            if (in_array($v, ['ya', 'y', '1', 'true', 'yes'], true))     return 'ya';
            if (in_array($v, ['tidak', 't', '0', 'false', 'no'], true)) return 'tidak';
            return null;
        };

        $detail = [];
        $rekap  = [];

        foreach ($peserta as $a) {
            $f = $a->frAk03;
            if (!$this->umpanBalikTerisi($f)) continue;

            $jawabanRaw = $f->jawaban ?? [];
            ksort($jawabanRaw);

            $items = [];
            foreach (array_values($jawabanRaw) as $i => $row) {
                $val     = is_array($row) ? ($row['jawaban'] ?? null) : $row;
                $catatan = is_array($row) ? ($row['catatan'] ?? null) : null;
                $jawab   = $normalisasi($val);

                $items[] = ['jawaban' => $jawab, 'catatan' => $catatan];

                $rekap[$i]['ya']    = ($rekap[$i]['ya'] ?? 0) + ($jawab === 'ya' ? 1 : 0);
                $rekap[$i]['tidak'] = ($rekap[$i]['tidak'] ?? 0) + ($jawab === 'tidak' ? 1 : 0);
                $rekap[$i]['catatan'] = $rekap[$i]['catatan'] ?? [];
                if (filled($catatan)) {
                    $rekap[$i]['catatan'][] = ['nama' => $a->full_name, 'catatan' => $catatan];
                }
            }

            $detail[$a->id] = [
                'nama'         => $a->full_name,
                'submitted_at' => $f->submitted_at?->translatedFormat('d M Y H:i'),
                'jawaban'      => $items,
                'catatan_lain' => $f->catatan_lain,
            ];
        }

        ksort($rekap);

        return [$detail, $rekap];
    }

    /**
     * Checklist progress jadwal: persiapan (manajer), pelaksanaan (asesor), rekap peserta.
     */
    private function buildChecklist(Schedule $schedule, $peserta, array $progress, bool $asesmenDimulai, bool $daftarHadirSigned): array
    {
        $total     = $peserta->count();
        $dt        = $schedule->distribusiSoalTeori;
        $distObs   = $schedule->distribusiSoalObservasi;
        $distPorto = $schedule->distribusiPortofolio;
        $ba        = $schedule->beritaAcara;
        $p         = collect($progress);
        $adaTeori  = $dt || $p->contains(fn($x) => $x['teori']['total'] > 0);

        // ── Persiapan ──────────────────────────────────────────
        $persiapan = [
            [
                'label'  => 'Asesor ditugaskan',
                'done'   => (bool) $schedule->asesor_id,
                'detail' => $schedule->asesor?->nama,
            ],
            [
                'label'  => 'Jadwal disetujui Direktur',
                'done'   => $schedule->isApproved(),
                'detail' => $schedule->sk_number ? 'SK ' . $schedule->sk_number : null,
            ],
            [
                'label'  => 'Soal teori didistribusikan',
                'done'   => $adaTeori,
                'detail' => $dt
                    ? ($dt->paketSoalTeori ? 'Paket ' . $dt->paketSoalTeori->kode_paket . ' · ' : '')
                      . "{$dt->jumlah_soal} soal · " . ($dt->durasi_menit ?? 30) . ' menit'
                    : ($adaTeori ? 'Soal sudah ada di peserta' : null),
            ],
            [
                'label'  => 'Soal observasi / portofolio didistribusikan',
                'done'   => $distObs->isNotEmpty() || $distPorto->isNotEmpty(),
                'detail' => collect([
                    $distObs->isNotEmpty() ? $distObs->count() . ' observasi' : null,
                    $distPorto->isNotEmpty() ? $distPorto->count() . ' portofolio' : null,
                ])->filter()->implode(' · ') ?: null,
            ],
        ];

        if ($distObs->isNotEmpty()) {
            $withForm = $distObs->filter(fn($d) => filled($d->form_penilaian_path))->count();
            $persiapan[] = [
                'label'  => 'Form penilaian observasi',
                'done'   => $withForm === $distObs->count(),
                'detail' => "{$withForm}/{$distObs->count()} diupload",
            ];
        }

        if ($distPorto->isNotEmpty()) {
            $c        = $distPorto->count();
            $withForm = $distPorto->filter(fn($d) => filled($d->form_penilaian_path))->count();
            $withKisi = $distPorto->filter(fn($d) => filled($d->kisi_kisi_path))->count();
            $persiapan[] = [
                'label'  => 'Kisi-kisi & form penilaian portofolio',
                'done'   => $withForm === $c && $withKisi === $c,
                'detail' => "Kisi-kisi {$withKisi}/{$c} · Form {$withForm}/{$c}",
            ];
        }

        // ── Pelaksanaan (Asesor) ───────────────────────────────
        $hadir = $p->where('hadir', true)->count();

        $pelaksanaan = [
            [
                'label' => 'Asesmen dimulai',
                'done'  => $asesmenDimulai,
            ],
            [
                'label'  => 'Daftar hadir ditandatangani',
                'done'   => $daftarHadirSigned,
                'detail' => "{$hadir}/{$total} hadir"
                    . ($schedule->daftar_hadir_signed_at
                        ? ' · ' . $schedule->daftar_hadir_signed_at->translatedFormat('d M Y H:i')
                        : ($daftarHadirSigned ? ' · TTD asesor tersedia' : '')),
            ],
        ];

        foreach ($distObs as $d) {
            $hasil = $schedule->hasilObservasi->firstWhere('soal_observasi_id', $d->soal_observasi_id);
            $pelaksanaan[] = [
                'label'  => 'Hasil observasi: ' . ($d->soalObservasi->judul ?? '-'),
                'done'   => (bool) $hasil,
                'detail' => $hasil?->file_name,
            ];
        }

        foreach ($distPorto as $d) {
            $hasil = $schedule->hasilPortofolio->firstWhere('portofolio_id', $d->portofolio_id);
            $pelaksanaan[] = [
                'label'  => 'Hasil portofolio: ' . ($d->portofolio->judul ?? '-'),
                'done'   => (bool) $hasil,
                'detail' => $hasil?->file_name,
            ];
        }

        $rekCount = $ba ? $ba->asesis->filter(fn($x) => filled($x->rekomendasi))->count() : 0;
        $k        = $ba ? $ba->asesis->where('rekomendasi', 'K')->count() : 0;
        $bk       = $ba ? $ba->asesis->where('rekomendasi', 'BK')->count() : 0;

        $pelaksanaan[] = [
            'label'  => 'Berita acara',
            'done'   => $ba && $total > 0 && $rekCount >= $total,
            'detail' => $ba
                ? "{$rekCount}/{$total} direkomendasikan · K: {$k} · BK: {$bk}" . ($ba->file_name ? " · {$ba->file_name}" : '')
                : 'Belum dibuat',
        ];

        $foto = collect([$schedule->foto_dokumentasi_1, $schedule->foto_dokumentasi_2])->filter()->count();
        $pelaksanaan[] = [
            'label'  => 'Foto dokumentasi',
            'done'   => $foto === 2,
            'detail' => "{$foto}/2 foto",
        ];

        $pelaksanaan[] = [
            'label'    => 'Catatan asesor',
            'done'     => filled($schedule->catatan_asesor),
            'optional' => true,
            'detail'   => $schedule->catatan_asesor ? \Illuminate\Support\Str::limit($schedule->catatan_asesor, 60) : null,
            'full'     => $schedule->catatan_asesor,
        ];

        // ── Rekap Peserta ──────────────────────────────────────
        $rekapPeserta = [];

        if ($adaTeori) {
            $selesaiReal  = $p->where('teori.status', 'selesai');
            $selesaiArsip = $p->where('teori.status', 'selesai_arsip');
            $jmlSelesai   = $selesaiReal->count() + $selesaiArsip->count();
            $semuaHilang  = $p->isNotEmpty() && $p->every(fn($x) => $x['teori']['status'] === 'hilang');

            if ($semuaHilang) {
                $rekapPeserta[] = [
                    'label'    => 'Ujian teori',
                    'done'     => false,
                    'optional' => true,
                    'detail'   => 'Data jawaban tidak tersedia di sistem',
                ];
            } else {
                $avg = $selesaiReal->avg('teori.nilai');
                $rekapPeserta[] = [
                    'label'  => 'Ujian teori selesai',
                    'done'   => $total > 0 && $jmlSelesai === $total,
                    'detail' => "{$jmlSelesai}/{$total} peserta"
                        . ($avg !== null ? ' · rata-rata nilai ' . round($avg) : '')
                        . ($selesaiArsip->isNotEmpty() ? ' · ' . $selesaiArsip->count() . ' dari data BA (nilai tidak tersimpan)' : ''),
                ];
            }
        }

        if ($distObs->isNotEmpty()) {
            $lengkap = $p->filter(fn($x) => $x['observasi']['done'] >= $x['observasi']['total'])->count();
            $rekapPeserta[] = [
                'label'  => 'Link observasi terkumpul',
                'done'   => $total > 0 && $lengkap === $total,
                'detail' => "{$lengkap}/{$total} peserta lengkap",
            ];
        }

        if ($distPorto->isNotEmpty()) {
            $ujikom = $p->where('ujikom', true)->count();
            $rekapPeserta[] = [
                'label'  => 'Dokumen ujikom / portofolio terkumpul',
                'done'   => $total > 0 && $ujikom === $total,
                'detail' => "{$ujikom}/{$total} peserta",
            ];
        }

        $umpanBalik = $p->where('umpan_balik', true)->count();
        $rekapPeserta[] = [
            'label'  => 'Umpan balik (FR.AK.03)',
            'done'   => $total > 0 && $umpanBalik === $total,
            'detail' => "{$umpanBalik}/{$total} peserta",
        ];

        return [
            'Persiapan'          => $persiapan,
            'Pelaksanaan Asesor' => $pelaksanaan,
            'Peserta'            => $rekapPeserta,
        ];
    }
}