<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\Schedule;
use App\Models\Tuk;
use App\Models\Skema;
use App\Services\AsesorAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * - Status: pra_pra_asesmen_started (atau status yang setara)
     * - APL-01: sudah diverifikasi (status verified/approved)
     * - APL-02: sudah disubmit (tidak null, status bukan draft)
     * - FR.AK.01: sudah disubmit (tidak null, status bukan draft)
     * - Belum punya schedule_id
     */
    private function readyToScheduleQuery()
    {
        return Asesmen::with(['user', 'tuk', 'skema', 'aplsatu', 'apldua', 'frak01'])
            ->whereNull('schedule_id')
            // APL-01 harus sudah diverifikasi
            ->whereHas('aplsatu', function ($q) {
                $q->where('status', 'verified');
                // Sesuaikan nama kolom/nilai status APL-01 di project Anda
                // Contoh alternatif: $q->whereIn('status', ['verified', 'approved']);
            })
            // APL-02 harus sudah disubmit (bukan draft)
            ->whereHas('apldua', function ($q) {
                $q->whereNotIn('status', ['draft'])
                    ->whereNotNull('submitted_at');
                // Sesuaikan: mungkin kolom submitted_at atau status != 'draft'
            })
            // FR.AK.01 harus sudah disubmit
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

        // Jumlah jadwal menunggu approval (untuk notif)
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

    // Daftar batch kolektif unik untuk dropdown filter
    $batches = $availableAsesmens
        ->pluck('collective_batch_id')
        ->filter()
        ->unique()
        ->values();

    // Auto-hitung nama lembaga dari asesi yang sudah dipilih (modus)
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
     * Status asesi tetap, hanya schedule_id yang diisi.
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

    // Validasi asesi: harus sudah memenuhi kriteria dan belum terjadwal
    $asesmens = $this->readyToScheduleQuery()
        ->whereIn('id', $request->asesmen_ids)
        ->get();

    if ($asesmens->count() !== count($request->asesmen_ids)) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Beberapa asesi tidak memenuhi syarat penjadwalan (APL-01 harus terverifikasi, APL-02 dan FR.AK.01 harus sudah disubmit).');
    }

    // Validasi semua asesi punya skema sama
    $skemaIds = $asesmens->pluck('skema_id')->unique();
    if ($skemaIds->count() > 1) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Asesi dalam satu jadwal harus memiliki skema yang sama.');
    }

    DB::beginTransaction();
    try {
        // Buat jadwal dengan status pending_approval
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
            // Kalau admin kosongkan, hitung otomatis dari institution asesi terpilih
            'institution_name' => $request->input('institution_name')
                                    ?: Schedule::computeInstitutionNameFromAsesmens($asesmens),
        ]);

        // Hubungkan asesi ke jadwal — status asesi BELUM berubah ke 'scheduled'
        foreach ($asesmens as $asesmen) {
            $asesmen->update(['schedule_id' => $schedule->id]);
        }

        // Assign asesor jika dipilih
        if ($request->asesor_id) {
            $asesor = \App\Models\Asesor::findOrFail($request->asesor_id);
            $this->assignmentService->assignAsesor($schedule, $asesor, 'Ditugaskan saat pembuatan jadwal');
        }

        DB::commit();

        Log::info("Admin #{auth()->id()} membuat jadwal #{$schedule->id} untuk {$asesmens->count()} asesi. Menunggu approval Direktur.");

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
     * Detail jadwal.
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

        // Status yang dipakai konsisten di semua bagian halaman
        $asesmenDimulai = (bool) $schedule->assessment_start
            || $peserta->contains(fn($a) => in_array($a->status, [
                'asesmen_started', 'assessed', 'certified', 'certificate_distributed',
            ]));

        // TTD daftar hadir = kolom signed_at ATAU asesor sudah punya TTD profil
        // (download daftar hadir di sisi asesor & manajer pakai TTD profil)
        $daftarHadirSigned = $schedule->isDaftarHadirSigned()
            || filled($schedule->asesor?->user?->signature);

        $progress  = $this->buildPesertaProgress($schedule, $peserta);
        $checklist = $this->buildChecklist($schedule, $peserta, $progress, $asesmenDimulai, $daftarHadirSigned);

        return view('admin.schedules.show', compact(
            'schedule', 'peserta', 'pesertaCount', 'progress', 'checklist',
            'asesmenDimulai', 'daftarHadirSigned'
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

    // Jika sebelumnya ditolak, kembalikan ke pending_approval setelah admin perbaiki
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
     * Hapus jadwal — kembalikan asesi ke status sebelumnya.
     * Hanya bisa dihapus jika belum disetujui.
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
                // Kembalikan asesi — hapus schedule_id, status tetap karena belum berubah
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

        if (!\Illuminate\Support\Facades\Storage::disk('private')->exists($schedule->sk_path)) {
            abort(404, 'File SK tidak ditemukan.');
        }

        $ext      = pathinfo($schedule->sk_path, PATHINFO_EXTENSION);
        $filename = 'SK_' . str_replace('/', '-', $schedule->sk_number) . '.' . $ext;

        return response()->streamDownload(function () use ($schedule) {
            echo \Illuminate\Support\Facades\Storage::disk('private')->get($schedule->sk_path);
        }, $filename, [
            'Content-Type' => $ext === 'pdf' ? 'application/pdf' : 'text/html',
        ]);
    }


    private function buildPesertaProgress(Schedule $schedule, $peserta): array
    {
        $distTeori = $schedule->distribusiSoalTeori;
        $distObs   = $schedule->distribusiSoalObservasi;
        $totalObs  = $distObs->count();
        $rekMap    = $schedule->beritaAcara?->asesis->pluck('rekomendasi', 'asesmen_id') ?? collect();

        $result = [];
        foreach ($peserta as $a) {
            // ── Teori ──
            // Samakan dengan sisi asesi: ambil semua soal milik asesmen ini.
            // Kalau ada soal dari distribusi aktif, pakai itu saja (hindari sisa distribusi lama).
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

            $teori = [
                'status'  => $total === 0
                    ? ($distTeori ? 'kosong' : 'na')
                    : ($submitted ? 'selesai' : (($mulai || $dijawab > 0) ? 'mengerjakan' : 'belum')),
                'total'   => $total,
                'dijawab' => $dijawab,
                'benar'   => $benar,
                'nilai'   => $total > 0 ? round($benar / $total * 100) : null,
            ];

            // ── Observasi: cocokkan per paket aktif (fallback per distribusi) ──
            $obsDone = 0;
            foreach ($distObs as $d) {
                $ada = $a->jawabanObservasi->contains(fn($j) => filled($j->gdrive_link) && (
                    $j->paket_soal_observasi_id == $d->paket_soal_observasi_id
                    || $j->distribusi_soal_observasi_id == $d->id
                ));
                if ($ada) $obsDone++;
            }

            $result[$a->id] = [
                'hadir'       => $a->hadir !== false, // null = default hadir (sama dengan sisi asesor)
                'teori'       => $teori,
                'observasi'   => ['done' => $obsDone, 'total' => $totalObs],
                'ujikom'      => filled($a->apldua?->gdrive_ujikom),
                'umpan_balik' => $a->frAk03 !== null,
                'rekomendasi' => $rekMap[$a->id] ?? null,
            ];
        }

        return $result;
    }

    private function buildChecklist(Schedule $schedule, $peserta, array $progress, bool $asesmenDimulai, bool $daftarHadirSigned): array
    {
        $total     = $peserta->count();
        $dt        = $schedule->distribusiSoalTeori;
        $distObs   = $schedule->distribusiSoalObservasi;
        $distPorto = $schedule->distribusiPortofolio;
        $ba        = $schedule->beritaAcara;
        $p         = collect($progress);
        $adaTeori  = $dt || $p->contains(fn($x) => $x['teori']['total'] > 0);

        // ── Persiapan ──
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

        // ── Pelaksanaan (Asesor) ──
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
        ];

        // ── Rekap Peserta ──
        $rekapPeserta = [];

        if ($adaTeori) {
            $selesai = $p->where('teori.status', 'selesai');
            $avg     = $selesai->avg('teori.nilai');
            $rekapPeserta[] = [
                'label'  => 'Ujian teori selesai',
                'done'   => $total > 0 && $selesai->count() === $total,
                'detail' => "{$selesai->count()}/{$total} peserta" . ($avg !== null ? ' · rata-rata nilai ' . round($avg) : ''),
            ];
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
