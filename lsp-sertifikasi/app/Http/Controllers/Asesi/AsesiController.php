<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asesmen;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Apl01Service;
use App\Models\AplSatu;
use App\Models\AplSatuBukti;
use App\Models\AplDua;
use App\Models\AplDuaJawaban;

class AsesiController extends Controller
{
    /**
     * Dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Check first login - redirect if password not changed
        if ($user->isFirstLogin()) {
            return redirect()->route('asesi.first-login');
        }

        $asesmen = Asesmen::with([
            'tuk', 'skema', 'payment', 'schedule', 'certificate', 'registrar',
            'soalTeoriAsesi',
            'jawabanObservasi',
            'schedule.distribusiSoalObservasi.soalObservasi.paket',
        ])
        ->where('user_id', $user->id)
        ->first();

        // Get batch info if collective
        $batchInfo = null;
        if ($asesmen && $asesmen->is_collective) {
            $batchMembers = $asesmen->fullBatch();
            $batchInfo = [
                'batch_id' => $asesmen->collective_batch_id,
                'total_members' => $batchMembers->count(),
                'payment_phases' => $asesmen->payment_phases,
                'payment_status' => $asesmen->getBatchPaymentStatus(),
                'registered_by' => $asesmen->registrar,
                'tuk' => $asesmen->tuk,
            ];
        }

        return view('asesi.dashboard', compact('asesmen', 'batchInfo'));
    }

    /* Complete Personal Data - Form
    */
    public function completeData()
    {
        $user = auth()->user();

        // Check first login
        if ($user->isFirstLogin()) {
            return redirect()->route('asesi.first-login');
        }

        // Check if already has asesmen
        $asesmen = Asesmen::where('user_id', $user->id)->first();

        // If no asesmen, create new for mandiri self-registration
        if (!$asesmen) {
            $asesmen = Asesmen::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'registration_date' => now(),
                'status' => 'registered',
                'is_collective' => false,
            ]);
        }

        // ✅ PERUBAHAN: Jika status sudah bukan 'registered', tampilkan dalam mode view-only
        $viewOnly = $asesmen->status !== 'registered' && !$asesmen->biodata_needs_revision;

        $tuks = Tuk::where('is_active', true)->get();
        $skemas = Skema::where('is_active', true)->get();

        // Check if this is collective registration
        $isCollective = $asesmen && $asesmen->is_collective;

        return view('asesi.complete-data', compact('asesmen', 'tuks', 'skemas', 'isCollective', 'viewOnly'));
    }
    
    /**
     * Complete Personal Data - Store - UPDATED
     */
public function storeData(Request $request)
{
    $user    = auth()->user();
    $asesmen = Asesmen::where('user_id', $user->id)->first();
 
    // Validasi NIK — ignore NIK milik asesi itu sendiri saat resubmit revision
    $nikRule = $asesmen
        ? 'required|string|size:16|unique:asesmens,nik,' . $asesmen->id
        : 'required|string|size:16|unique:asesmens,nik';
 
    // File upload: wajib hanya jika belum ada file sebelumnya
    $photoRule    = ($asesmen?->photo_path)    ? 'nullable|image|max:10240'        : 'required|image|max:10240';
    $ktpRule      = ($asesmen?->ktp_path)      ? 'nullable|mimes:pdf|max:10240'    : 'required|mimes:pdf|max:10240';
    $documentRule = ($asesmen?->document_path) ? 'nullable|mimes:pdf|max:10240'    : 'required|mimes:pdf|max:10240';
 
    $validated = $request->validate([
        'full_name'      => 'required|string|max:255',
        'nik'            => $nikRule,
        'birth_place'    => 'required|string',
        'birth_date'     => ['required', 'date'],
        'gender'         => 'required|in:L,P',
        'address'        => 'required|string',
        'city_code'      => 'required|string|size:4',
        'province_code'  => 'required|string|size:2',
        'phone'          => 'required|string|max:15',
        'education'      => 'required|string',
        'occupation'     => 'required|string',
        'budget_source'  => 'required|string',
        'institution'    => 'required|string',
        'tuk_id'         => 'required_if:is_collective,false|nullable|exists:tuks,id',
        'skema_id'       => 'required|exists:skemas,id',
        'preferred_date' => 'required_if:is_collective,false|nullable|date',
        'photo'          => $photoRule,
        'ktp'            => $ktpRule,
        'document'       => $documentRule,
        'training_flag'  => 'required_if:is_collective,false|boolean',
    ]);
 
    // Upload file hanya jika ada file baru yang di-upload
    $photoPath    = $request->hasFile('photo')
        ? $request->file('photo')->store('uploads/photos', 'public_html')
        : $asesmen?->photo_path;

    $ktpPath      = $request->hasFile('ktp')
        ? $request->file('ktp')->store('uploads/ktp', 'public_html')
        : $asesmen?->ktp_path;

    $documentPath = $request->hasFile('document')
        ? $request->file('document')->store('uploads/documents', 'public_html')
        : $asesmen?->document_path;
 
    if ($asesmen && $asesmen->skema_id) {
        $request->merge(['skema_id' => $asesmen->skema_id]);
    }
    if ($asesmen && $asesmen->tuk_id) {
        $request->merge(['tuk_id' => $asesmen->tuk_id]);
    }
 
    if ($asesmen) {
        $updateData = [
            'full_name'     => $validated['full_name'],
            'nik'           => $validated['nik'],
            'birth_place'   => $validated['birth_place'],
            'birth_date'    => $validated['birth_date'],
            'gender'        => $validated['gender'],
            'address'       => $validated['address'],
            'city_code'     => $validated['city_code'],
            'province_code' => $validated['province_code'],
            'phone'         => $validated['phone'],
            'education'     => $validated['education'],
            'occupation'    => $validated['occupation'],
            'budget_source' => $validated['budget_source'],
            'institution'   => $validated['institution'],
            'photo_path'    => $photoPath,
            'ktp_path'      => $ktpPath,
            'document_path' => $documentPath,
        ];
 
        // Jangan reset status jika sedang dalam revision — pertahankan status saat ini
        if (!$asesmen->biodata_needs_revision) {
            $updateData['status'] = 'data_completed';
        }
 
        // Kolektif: preferred_date & training_flag diatur oleh TUK
        if (!$asesmen->is_collective) {
            $updateData['preferred_date'] = $validated['preferred_date'];
            $updateData['training_flag']  = $validated['training_flag'];
            $updateData['tuk_id']         = $validated['tuk_id'];
            $updateData['skema_id']       = $validated['skema_id'];
        }
 
        $asesmen->update($updateData);
 
        // Clear revision flag setelah resubmit
        if ($asesmen->biodata_needs_revision) {
            $asesmen->update(['biodata_needs_revision' => false]);
            Log::info("Asesi #{$user->id} submitted biodata revision for Asesmen #{$asesmen->id}");
        }
 
        $message = 'Data berhasil dilengkapi!';
        if ($asesmen->is_collective) {
            $message .= ' (Pendaftaran Kolektif - Menunggu verifikasi TUK)';
        }
 
    } else {
        $skema       = Skema::find($validated['skema_id']);
        $baseFee     = $skema->fee;
        $trainingFee = $validated['training_flag'] ? 1500000 : 0;
        $totalFee    = $baseFee + $trainingFee;
 
        $asesmen = Asesmen::create([
            'user_id'           => $user->id,
            'tuk_id'            => null,
            'skema_id'          => $validated['skema_id'],
            'full_name'         => $validated['full_name'],
            'nik'               => $validated['nik'],
            'birth_place'       => $validated['birth_place'],
            'birth_date'        => $validated['birth_date'],
            'gender'            => $validated['gender'],
            'address'           => $validated['address'],
            'city_code'         => $validated['city_code'],
            'province_code'     => $validated['province_code'],
            'phone'             => $validated['phone'],
            'education'         => $validated['education'],
            'occupation'        => $validated['occupation'],
            'budget_source'     => $validated['budget_source'],
            'institution'       => $validated['institution'],
            'preferred_date'    => $validated['preferred_date'],
            'photo_path'        => $photoPath,
            'ktp_path'          => $ktpPath,
            'document_path'     => $documentPath,
            'registration_date' => now(),
            'status'            => 'data_completed',
            'is_collective'     => false,
            'training_flag'     => $validated['training_flag'],
            'fee_amount'        => $totalFee,
        ]);
 
        Log::info("Mandiri registration - Asesmen #{$asesmen->id}, Fee: Rp {$totalFee}");
 
        $message = 'Data berhasil dilengkapi! Menunggu verifikasi dan assignment ke TUK oleh Admin LSP.';
        if ($validated['training_flag']) {
            $message .= ' (Termasuk biaya pelatihan Rp 1.500.000)';
        }
    }
 
    return redirect()->route('asesi.dashboard')->with('success', $message);
}

    /**
     * Payment - Form
     */
    public function payment()
    {
        $user = auth()->user();

        // Check first login
        if ($user->isFirstLogin()) {
            return redirect()->route('asesi.first-login');
        }

        $asesmen = Asesmen::with(['payment', 'skema', 'tuk'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($asesmen->shouldSkipPayment()) {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Pembayaran akan dilakukan oleh TUK untuk pendaftaran kolektif.');
        }

        if ($asesmen->status !== 'verified') {
            return redirect()->route('asesi.dashboard')
                ->with('warning', 'Data Anda belum diverifikasi.');
        }

        return view('asesi.payment', compact('asesmen'));
    }

    /**
     * Pre-Assessment - Form
     */
    public function preAssessment()
    {
        $user = auth()->user();

        // Check first login
        if ($user->isFirstLogin()) {
            return redirect()->route('asesi.first-login');
        }

        $asesmen = Asesmen::where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->firstOrFail();

        return view('asesi.pre-assessment', compact('asesmen'));
    }

    /**
     * Pre-Assessment - Submit
     */
    public function submitPreAssessment(Request $request)
    {
        $validated = $request->validate([
            'pre_assessment_file' => 'nullable|mimes:pdf,doc,docx|max:10240',
            'pre_assessment_data' => 'nullable|string',
        ]);

        $user = auth()->user();
        $asesmen = Asesmen::where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->firstOrFail();

        $updateData = [
            'status' => 'pre_assessment_completed',
        ];

        if ($request->hasFile('pre_assessment_file')) {
            $filePath = $request->file('pre_assessment_file')->store('uploads/pre-assessments', 'public');
            $updateData['pre_assessment_file'] = $filePath;
        }

        if ($request->pre_assessment_data) {
            $updateData['pre_assessment_data'] = $request->pre_assessment_data;
        }

        $asesmen->update($updateData);

        return redirect()->route('asesi.dashboard')
            ->with('success', 'Pra-asesmen berhasil disubmit!');
    }

    /**
     * Schedule - Halaman jadwal asesmen asesi
     */
    public function schedule()
    {
        $user = auth()->user();

        $asesmen = Asesmen::with(['tuk', 'skema', 'schedule.asesor', 'aplsatu.buktiKelengkapan', 'apldua.jawabans'])
            ->where('user_id', $user->id)
            ->whereNotNull('schedule_id')
            ->firstOrFail();

        $schedule = $asesmen->schedule;
        $aplsatu = $asesmen->aplsatu;
        $apldua  = $asesmen->apldua;
        return view('asesi.schedule.index', compact('asesmen', 'schedule', 'aplsatu', 'apldua'));
    }


    /**
     * Halaman utama Dokumen Pra-Asesmen
     */
    public function documents()
    {
        $user    = auth()->user();
        $asesmen = Asesmen::with([
            'tuk', 'skema', 'schedule.asesor',
            'aplsatu.buktiKelengkapan',
            'apldua.jawabans',
            'frak01',
            'frak04',
        ])->where('user_id', $user->id)->first();

        abort_if(!$asesmen, 404, 'Data asesmen tidak ditemukan.');
        abort_if(!in_array($asesmen->status, [
            'pra_asesmen_started', 'scheduled',
            'pre_assessment_completed', 'assessed', 'certified'
        ]), 403, 'Halaman ini belum tersedia.');

        $aplsatu = $asesmen->aplsatu;
        $apldua  = $asesmen->apldua;
        $frak01  = $asesmen->frak01;
        $frak04  = $asesmen->frak04;

        return view('asesi.documents.index', compact(
            'asesmen', 'aplsatu', 'apldua', 'frak01', 'frak04'
        ));
    }

    /**
     * View Certificate
     */
    public function certificate()
    {
        $user = auth()->user();
        $asesmen = Asesmen::with('certificate')
            ->where('user_id', $user->id)
            ->where('status', 'certified')
            ->firstOrFail();

        return view('asesi.certificate', compact('asesmen'));
    }

    /**
     * Download Certificate
     */
    public function downloadCertificate()
    {
        $user = auth()->user();
        $asesmen = Asesmen::with('certificate')
            ->where('user_id', $user->id)
            ->where('status', 'certified')
            ->firstOrFail();

        if (!$asesmen->certificate) {
            abort(404, 'Sertifikat tidak ditemukan');
        }

        $path = storage_path('app/public/' . $asesmen->certificate->pdf_path);

        if (!file_exists($path)) {
            abort(404, 'File sertifikat tidak ditemukan');
        }

        return response()->download($path, 'Sertifikat_' . $asesmen->full_name . '.pdf');
    }

    /**
     * Tracking Status
     */
    public function tracking() {
        return redirect()->route('asesi.dashboard');
    }

    /**
     * First Login - Show password change form
     */
    public function showFirstLogin()
    {
        $user = auth()->user();

        // If already changed password, redirect to dashboard
        if (!$user->isFirstLogin()) {
            return redirect()->route('asesi.dashboard');
        }

        return view('auth.first-login');
    }

    /**
     * First Login - Update password
     */
    public function updateFirstPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        Log::info("First login password changed - User #{$user->id}");

        // ✅ FIX: Perbaiki route name
        return redirect()->route('asesi.dashboard')
            ->with('success', 'Password berhasil diubah! Silakan lengkapi data pribadi Anda.');
    }

    /**
     * Batch Info - untuk asesi kolektif
     */
    public function batchInfo() {
        return redirect()->route('asesi.dashboard');
    }

    public function downloadInvoice()
    {
        $user = auth()->user();

        $asesmen = Asesmen::with(['payment', 'payments', 'skema', 'tuk'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$asesmen->payment && $asesmen->payments->isEmpty()) {
            return redirect()->route('asesi.dashboard')
                ->with('error', 'Belum ada pembayaran yang terverifikasi');
        }

        if ($asesmen->is_collective && $asesmen->payment_phases === 'two_phase') {
            $payment = $asesmen->payments()
                ->where('status', 'verified')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$payment) {
                return redirect()->route('asesi.dashboard')
                    ->with('error', 'Belum ada pembayaran yang terverifikasi');
            }
        } else {
            $payment = $asesmen->payment;

            if (!$payment || $payment->status !== 'verified') {
                return redirect()->route('asesi.dashboard')
                    ->with('error', 'Pembayaran belum terverifikasi');
            }
        }

        $invoiceNumber = 'INV-' . str_pad($asesmen->id, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd');
        $isCollective = $asesmen->is_collective;
        $phase = $payment->payment_phase ?? 'full';
        $batchId = $asesmen->collective_batch_id;
        $tuk = $asesmen->tuk;
        $asesmens = collect([$asesmen]);

        // ✅ Generate PDF tanpa public path dependency
        $pdf = Pdf::loadView('pdf.invoice', compact(
            'payment',
            'invoiceNumber',
            'isCollective',
            'phase',
            'asesmen',
            'asesmens',
            'batchId',
            'tuk'
        ))->setPaper('a4', 'portrait');

        
        $filename = 'Invoice_' . $asesmen->id . '_' . date('Ymd') . '.pdf';

        // ✅ Use stream with attachment
        return $pdf->stream($filename, ['Attachment' => true]);
    }

    public function paymentStatus()
    {
        $user = auth()->user();

        // Check first login
        if ($user->isFirstLogin()) {
            return redirect()->route('asesi.first-login');
        }

        $asesmen = Asesmen::with(['payment', 'payments', 'skema', 'tuk'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        return view('asesi.payment-status', compact('asesmen'));
    }

    /**
     * APL-01 form page
     */
    public function aplsatuForm()
    {
        $asesmen = auth()->user()->asesmens()->with(['skema.unitKompetensis', 'tuk'])->latest()->first();

        if (!$asesmen) {
            return redirect()->route('asesi.dashboard')
                ->with('error', 'Belum ada data asesmen.');
        }

        $service = new Apl01Service();
        $aplsatu = $service->getOrCreateApl01($asesmen);

        // Initialize bukti dokumen jika belum ada
        if ($aplsatu->buktiKelengkapan->isEmpty()) {
            $service->initializeBuktiDokumen($aplsatu);
            $aplsatu->load('buktiKelengkapan');
        }

        return view('asesi.aplsatu.form', compact('asesmen', 'aplsatu'));
    }

    public function aplsatuUpdate(Request $request)
    {
        Log::info('[APL01-UPDATE] Request masuk', [
            'user_id'    => auth()->id(),
            'ip'         => $request->ip(),
            'all_keys'   => array_keys($request->all()),
            'payload'    => $request->except(['_token']),
        ]);

        $validated = null;
        try {
            $validated = $request->validate([
                'nama_lengkap'           => 'required|string|max:255',
                'nik'                    => 'required|string|size:16',
                'tempat_lahir'           => 'required|string',
                'tanggal_lahir'          => 'required|date',
                'jenis_kelamin'          => 'required|in:Laki-laki,Perempuan',
                'kebangsaan'             => 'nullable|string',
                'alamat_rumah'           => 'required|string',
                'kode_pos'               => 'nullable|string|max:10',
                'telp_rumah'             => 'nullable|string|max:20',
                'hp'                     => 'required|string|max:20',
                'email' => 'required|string|max:255',
                'kualifikasi_pendidikan' => 'nullable|string',
                'nama_institusi'         => 'nullable|string|max:255',
                'jabatan'                => 'nullable|string|max:255',
                'alamat_kantor'          => 'nullable|string',
                'kode_pos_kantor'        => 'nullable|string|max:10',
                'telp_kantor_detail'     => 'nullable|string|max:20',
                'fax_kantor'             => 'nullable|string|max:20',
                'email_kantor'           => 'nullable|string|max:255',
                'tujuan_asesmen'         => 'required|in:Sertifikasi,PKT,RPL,Lainnya',
                'tujuan_asesmen_lainnya' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('[APL01-UPDATE] Validation FAILED', [
                'user_id' => auth()->id(),
                'errors'  => $e->errors(),
                'payload' => $request->except(['_token']),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $e->errors(),
            ], 422);
        }

        Log::info('[APL01-UPDATE] Validation OK', ['validated' => $validated]);

        $asesmen = auth()->user()->asesmens()->latest()->first();

        if (!$asesmen) {
            Log::error('[APL01-UPDATE] Asesmen tidak ditemukan untuk user ' . auth()->id());
            return response()->json(['success' => false, 'message' => 'Asesmen tidak ditemukan.'], 404);
        }

        Log::info('[APL01-UPDATE] Asesmen found', ['asesmen_id' => $asesmen->id]);

        $service = new Apl01Service();
        $aplsatu = $asesmen->aplsatu;

        if (!$aplsatu) {
            Log::info('[APL01-UPDATE] APL-01 belum ada, creating...');
            $aplsatu = $service->getOrCreateApl01($asesmen);
        }

        Log::info('[APL01-UPDATE] APL-01 status', ['aplsatu_id' => $aplsatu->id, 'status' => $aplsatu->status]);

        if ($aplsatu->status !== 'draft' && $aplsatu->status !== 'returned') {
            Log::warning('[APL01-UPDATE] APL-01 bukan draft atau returned, update ditolak', ['status' => $aplsatu->status]);
            return response()->json(['success' => false, 'message' => 'APL-01 sudah tidak dapat diubah.'], 403);
        }

        $updateData = $request->only([
            'nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'kebangsaan', 'alamat_rumah', 'kode_pos',
            'telp_rumah', 'hp', 'email', 'kualifikasi_pendidikan',
            'nama_institusi', 'jabatan', 'alamat_kantor', 'kode_pos_kantor',
            'telp_kantor_detail', 'fax_kantor', 'email_kantor',
            'tujuan_asesmen', 'tujuan_asesmen_lainnya',
        ]);

        Log::info('[APL01-UPDATE] Calling updateApl01 with data', ['updateData' => $updateData]);

        $result = $service->updateApl01($aplsatu, $updateData);

        Log::info('[APL01-UPDATE] updateApl01 result', ['result' => $result]);

        if ($result) {
            // Verify actual DB values after save
            $aplsatu->refresh();
            Log::info('[APL01-UPDATE] ✅ After save — DB values', $aplsatu->only(array_keys($updateData)));

            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan!']);
        }

        Log::error('[APL01-UPDATE] ❌ updateApl01 returned false');
        return response()->json(['success' => false, 'message' => 'Gagal menyimpan data.'], 500);
    }

// ── aplsatuBuktiSave ── ganti isi method dengan ini:

public function aplsatuBuktiSave(Request $request)
    {
        Log::info('[APL01-BUKTI] Request masuk', [
            'user_id' => auth()->id(),
            'payload' => $request->all(),
        ]);

        try {
            $request->validate([
                'rows'        => 'required|array',
                'rows.*.id'   => 'required|integer',
                // Status dari asesi selalu 'Tidak Ada' — admin yang update
                'rows.*.status' => 'nullable|in:Ada Memenuhi Syarat,Ada Tidak Memenuhi Syarat,Tidak Ada',
                'rows.*.link'   => 'nullable|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('[APL01-BUKTI] Validation FAILED', ['errors' => $e->errors()]);
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        }

        $asesmen = auth()->user()->asesmens()->latest()->first();
        if (!$asesmen || !$asesmen->aplsatu) {
            return response()->json(['success' => false, 'message' => 'APL-01 tidak ditemukan.'], 404);
        }

        $aplsatu  = $asesmen->aplsatu;
        $validIds = $aplsatu->buktiKelengkapan->pluck('id')->toArray();

        if ($aplsatu->status !== 'draft' && $aplsatu->status !== 'returned') {
            return response()->json(['success' => false, 'message' => 'APL-01 sudah tidak dapat diubah.'], 403);
        }

        $updated = 0;
        $gdriveLink = null;

        foreach ($request->rows as $row) {
            if (!in_array((int)$row['id'], $validIds)) continue;

            // Ambil link GDrive dari baris pertama yang ada linknya
            if (!empty($row['link'])) {
                $gdriveLink = $row['link'];
            }

            // Asesi HANYA menyimpan link GDrive. Status tidak diubah dari sisi asesi.
            $affected = \App\Models\AplSatuBukti::where('id', $row['id'])->update([
                'gdrive_file_url' => $row['link'] ?: null,
                'uploaded_by'     => auth()->id(),
                'uploaded_at'     => now(),
                // Status TIDAK diubah — tetap default 'Tidak Ada', admin yang verifikasi
            ]);

            Log::info('[APL01-BUKTI] Update gdrive link', [
                'bukti_id' => $row['id'],
                'link'     => $row['link'] ?: '(empty)',
                'affected' => $affected,
            ]);

            $updated += $affected;
        }

        Log::info('[APL01-BUKTI] ✅ Done', ['updated' => $updated, 'gdrive_link' => $gdriveLink]);

        return response()->json([
            'success' => true,
            'message' => "Link Google Drive berhasil disimpan.",
        ]);
    }

    /**
     * Submit APL-01 dengan tanda tangan - FIXED
     */
    public function aplsatuSubmit(Request $request)
    {
        $request->validate([
            'signature' => 'required|string', // base64 image
        ]);

        $asesmen = auth()->user()->asesmens()->latest()->first();

        if (!$asesmen || !$asesmen->aplsatu) {
            return response()->json(['success' => false, 'message' => 'APL-01 tidak ditemukan.'], 404);
        }

        $aplsatu = $asesmen->aplsatu;

        if (!in_array($aplsatu->status, ['draft', 'returned'])) {
            return response()->json([
                'success' => false,
                'message' => 'APL-01 sudah tidak dapat disubmit.',
            ], 400);
        }

        // Strip data URI prefix if present
        $signature = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $service = new Apl01Service();

        if ($service->submitApl01($aplsatu, $signature)) {
            return response()->json([
                'success' => true,
                'message' => 'APL-01 berhasil disubmit!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal submit APL-01.',
        ], 500);
    }

    /**
     * Preview / Download PDF APL-01 - FIXED
     */
    public function aplsatuPdf(Request $request)
    {
        $asesmen = auth()->user()->asesmens()->latest()->first();

        if (!$asesmen) {
            abort(404, 'Asesmen tidak ditemukan');
        }

        $aplsatu = $asesmen->aplsatu()->with('buktiKelengkapan')->first();

        if (!$aplsatu) {
            abort(404, 'APL-01 belum dibuat');
        }

        $asesmen->load(['skema.unitKompetensis', 'tuk']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.aplsatu', [
            'aplsatu' => $aplsatu,
            'asesmen' => $asesmen,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $filename = 'APL-01_' . str_replace(' ', '_', $aplsatu->nama_lengkap) . '.pdf';

        if ($request->get('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }


    public function apldua()
    {
        $user    = auth()->user();
        $asesmen = $user->asesmens()
            ->with(['skema.unitKompetensis.elemens.kuks'])
            ->latest()
            ->firstOrFail();

            $apldua = $asesmen->apldua ?? \App\Models\AplDua::create([
                'asesmen_id' => $asesmen->id,
                'status'     => 'draft',
            ]);

            // ✅ Selalu pastikan SEMUA elemen punya row — bukan hanya kalau kosong
            $units = $asesmen->skema->unitKompetensis->load('elemens');
            foreach ($units as $unit) {
                foreach ($unit->elemens as $elemen) {
                    \App\Models\AplDuaJawaban::firstOrCreate([
                        'apl_02_id' => $apldua->id,
                        'elemen_id' => $elemen->id,
                    ]);
                }
            }
            $apldua->load('jawabans');

            $jawabanMap = $apldua->jawabans->keyBy('elemen_id');

        return view('asesi.apldua.form', compact('asesmen', 'apldua', 'jawabanMap'));
    }

    /**
     * Auto-save jawaban APL-02 (AJAX)
     */
    public function apldua_save(Request $request)
    {
        $request->validate([
            'rows'              => 'required|array',
            'rows.*.elemen_id'  => 'required|integer|exists:elemens,id',
            'rows.*.jawaban'    => 'nullable|in:K,BK',
            'rows.*.bukti'      => 'nullable|string|max:1000',
        ]);
    
        // ✅ Hapus whereNotNull('schedule_id') — sama seperti method apldua() GET
        $asesmen = auth()->user()->asesmens()->latest()->first();
        if (!$asesmen) {
            return response()->json(['success' => false, 'message' => 'Asesmen tidak ditemukan.'], 404);
        }
    
        $apldua = $asesmen->apldua;
        if (!$apldua) {
            return response()->json(['success' => false, 'message' => 'APL-02 tidak ditemukan.'], 404);
        }
        if (!$apldua->is_editable) {
            return response()->json(['success' => false, 'message' => 'APL-02 sudah tidak dapat diubah.'], 403);
        }
    
        foreach ($request->rows as $row) {
            \App\Models\AplDuaJawaban::updateOrCreate(
                ['apl_02_id' => $apldua->id, 'elemen_id' => $row['elemen_id']],
                ['jawaban' => $row['jawaban'] ?? null, 'bukti' => $row['bukti'] ?? null]
            );
        }
    
        // Hitung progress
        $total    = $apldua->jawabans()->count();
        $answered = $apldua->jawabans()->whereNotNull('jawaban')->count();
    
        \Log::info('[APL02-SAVE] Saved', [
            'apldua_id' => $apldua->id,
            'rows'      => count($request->rows),
            'progress'  => "{$answered}/{$total}",
        ]);
    
        return response()->json([
            'success'  => true,
            'message'  => 'Tersimpan.',
            'progress' => ['answered' => $answered, 'total' => $total],
        ]);
    }

    /**
     * Submit APL-02 dengan tanda tangan asesi
     */
    public function apldua_submit(Request $request)
    {
        $request->validate([
            'signature' => 'required|string',
            'rows'      => 'nullable|array',
            'rows.*.elemen_id' => 'integer|exists:elemens,id',
            'rows.*.jawaban'   => 'nullable|in:K,BK',
            'rows.*.bukti'     => 'nullable|string|max:1000',
        ]);

        $asesmen = auth()->user()->asesmens()->latest()->first();
        if (!$asesmen) {
            return response()->json(['success' => false, 'message' => 'Asesmen tidak ditemukan.'], 404);
        }

        $apldua = $asesmen->apldua;
        if (!$apldua) {
            return response()->json(['success' => false, 'message' => 'APL-02 tidak ditemukan.'], 404);
        }
        if (!$apldua->is_editable) {
            return response()->json(['success' => false, 'message' => 'APL-02 sudah disubmit.'], 400);
        }

        // ✅ Simpan jawaban dari request sebelum validasi (atomic)
        if ($request->has('rows') && is_array($request->rows)) {
            foreach ($request->rows as $row) {
                AplDuaJawaban::updateOrCreate(
                    ['apl_02_id' => $apldua->id, 'elemen_id' => $row['elemen_id']],
                    ['jawaban' => $row['jawaban'] ?? null, 'bukti' => $row['bukti'] ?? null]
                );
            }
        }

        // Validasi semua elemen sudah dijawab
        $total    = $apldua->jawabans()->count();
        $answered = $apldua->jawabans()->whereNotNull('jawaban')->count();
        if ($answered < $total) {
            return response()->json([
                'success' => false,
                'message' => "Semua elemen harus dijawab terlebih dahulu. ({$answered}/{$total} sudah diisi)",
            ], 422);
        }

        $sig = preg_replace('/^data:image\/\w+;base64,/', '', $request->signature);

        $apldua->update([
            'status'            => 'submitted',
            'ttd_asesi'         => $sig,
            'nama_ttd_asesi'    => $asesmen->full_name,
            'tanggal_ttd_asesi' => now(),
            'submitted_at'      => now(),
        ]);

        Log::info('[APL02-SUBMIT] Submitted', ['apldua_id' => $apldua->id]);

        return response()->json(['success' => true, 'message' => 'APL-02 berhasil disubmit!']);
    }
        
    
    /**
     * Preview / Download PDF APL-02 (hanya jika sudah verified)
     */ 
    public function aplduaPdf(Request $request)
    {
        $asesmen = auth()->user()->asesmens()
            ->with([
                'skema.unitKompetensis.elemens.kuks',
                'schedule.asesor',
                'apldua.jawabans',
            ])
            ->whereNotNull('schedule_id')
            ->latest()
            ->firstOrFail();

        $apldua = $asesmen->apldua;

        if (!$apldua || !in_array($apldua->status, ['verified', 'approved'])) {
            abort(403, 'APL-02 belum diverifikasi.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.apldua', [
            'apldua'  => $apldua,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'APL-02_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf';

        return $request->get('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

}