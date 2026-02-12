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
        
        $asesmen = Asesmen::with(['tuk', 'skema', 'payment', 'schedule', 'certificate', 'registrar'])
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

    /**
     * Complete Personal Data - Form
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
        
        if ($asesmen && $asesmen->status !== 'registered') {
            return redirect()->route('asesi.dashboard')
                ->with('info', 'Data Anda sudah dilengkapi.');
        }

        $tuks = Tuk::where('is_active', true)->get();
        $skemas = Skema::where('is_active', true)->get();

        // Check if this is collective registration
        $isCollective = $asesmen && $asesmen->is_collective;

        return view('asesi.complete-data', compact('asesmen', 'tuks', 'skemas', 'isCollective'));
    }

    /**
     * Complete Personal Data - Store
     */
    public function storeData(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|unique:asesmens,nik',
            'birth_place' => 'required|string',
            'birth_date' => 'required|date',
            'gender' => 'required|in:L,P',
            'address' => 'required|string',
            'city_code' => 'required|string|size:4',
            'province_code' => 'required|string|size:2',
            'phone' => 'required|string|max:15',
            'education' => 'required|string',
            'occupation' => 'required|string',
            'budget_source' => 'required|string',
            'institution' => 'required|string',
            'tuk_id' => 'nullable|exists:tuks,id',
            'skema_id' => 'nullable|exists:skemas,id',
            'preferred_date' => 'required|date',
            'photo' => 'required|image|max:10240',
            'ktp' => 'required|mimes:pdf|max:10240',
            'document' => 'required|mimes:pdf|max:10240',
            'training_flag' => 'required|boolean',
        ]);

        $user = auth()->user();

        // Upload files
        $photoPath = $request->file('photo')->store('uploads/photos', 'public');
        $ktpPath = $request->file('ktp')->store('uploads/ktp', 'public');
        $documentPath = $request->file('document')->store('uploads/documents', 'public');

        // Check if asesmen exists (collective registration)
        $asesmen = Asesmen::where('user_id', $user->id)->first();

        if ($asesmen && $asesmen->skema_id) {
            $request->merge(['skema_id' => $asesmen->skema_id]);
        }

        if ($asesmen && $asesmen->tuk_id) {
            $request->merge(['tuk_id' => $asesmen->tuk_id]);
        }
        
        if ($asesmen) {
            // Update existing (collective registration)
            $updateData = [
                'full_name' => $validated['full_name'],
                'nik' => $validated['nik'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'city_code' => $validated['city_code'],
                'province_code' => $validated['province_code'],
                'phone' => $validated['phone'],
                'education' => $validated['education'],
                'occupation' => $validated['occupation'],
                'budget_source' => $validated['budget_source'],
                'institution' => $validated['institution'],
                'preferred_date' => $validated['preferred_date'],
                'photo_path' => $photoPath,
                'ktp_path' => $ktpPath,
                'document_path' => $documentPath,
                'status' => 'data_completed',
                'training_flag' => $validated['training_flag'],
            ];

            // Only update TUK and Skema if NOT collective
            if (!$asesmen->is_collective) {
                $updateData['tuk_id'] = $validated['tuk_id'];
                $updateData['skema_id'] = $validated['skema_id'];
            }

            $asesmen->update($updateData);

            $message = 'Data berhasil dilengkapi!';
            
            if ($asesmen->is_collective) {
                $message .= ' (Pendaftaran Kolektif - Menunggu verifikasi Admin LSP)';
                if ($validated['training_flag']) {
                    $message .= ' Anda memilih untuk mengikuti pelatihan. Biaya tambahan Rp 1.500.000 akan ditambahkan ke total biaya.';
                }
            }

        } else {
            // Create new (regular/mandiri registration)
            $skema = Skema::find($validated['skema_id']);
            
            // Auto calculate fee untuk mandiri
            $baseFee = $skema->fee;
            $trainingFee = $validated['training_flag'] ? 1500000 : 0;
            $totalFee = $baseFee + $trainingFee;

            $asesmen = Asesmen::create([
                'user_id' => $user->id,
                'tuk_id' => $validated['tuk_id'],
                'skema_id' => $validated['skema_id'],
                'full_name' => $validated['full_name'],
                'nik' => $validated['nik'],
                'birth_place' => $validated['birth_place'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'city_code' => $validated['city_code'],
                'province_code' => $validated['province_code'],
                'phone' => $validated['phone'],
                'education' => $validated['education'],
                'occupation' => $validated['occupation'],
                'budget_source' => $validated['budget_source'],
                'institution' => $validated['institution'],
                'preferred_date' => $validated['preferred_date'],
                'photo_path' => $photoPath,
                'ktp_path' => $ktpPath,
                'document_path' => $documentPath,
                'registration_date' => now(),
                'status' => 'data_completed',
                'is_collective' => false,
                'training_flag' => $validated['training_flag'],
                'fee_amount' => $totalFee,
            ]);

            // Auto verify untuk mandiri
            $asesmen->update([
                'status' => 'verified',
                'admin_verified_by' => null,
                'admin_verified_at' => now(),
            ]);

            Log::info("Auto-verified mandiri registration - Asesmen #{$asesmen->id}, Fee: Rp {$totalFee}, Training: " . ($validated['training_flag'] ? 'Yes' : 'No'));

            $message = 'Data berhasil dilengkapi dan terverifikasi otomatis! Silakan lakukan pembayaran.';
            
            if ($validated['training_flag']) {
                $message .= ' (Termasuk biaya pelatihan Rp 1.500.000)';
            }
        }

        return redirect()->route('asesi.dashboard')
            ->with('success', $message);
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
    public function tracking()
    {
        $user = auth()->user();
        
        // Check first login
        if ($user->isFirstLogin()) {
            return redirect()->route('asesi.first-login');
        }
        
        $asesmen = Asesmen::with(['tuk', 'skema', 'payment', 'schedule', 'certificate', 'verifier', 'assessor', 'registrar'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Get batch info if collective
        $batchInfo = null;
        if ($asesmen->is_collective) {
            $batchMembers = $asesmen->fullBatch();
            $batchInfo = [
                'batch_id' => $asesmen->collective_batch_id,
                'members' => $batchMembers,
                'payment_phases' => $asesmen->payment_phases,
                'payment_status' => $asesmen->getBatchPaymentStatus(),
            ];
        }

        return view('asesi.tracking', compact('asesmen', 'batchInfo'));
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

        return redirect()->route('asesi.dashboard')
            ->with('success', 'Password berhasil diubah! Silakan lengkapi data pribadi Anda.');
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

        // For collective with 2 phases - get the latest verified payment
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
            // Single phase or mandiri
            $payment = $asesmen->payment;
            
            if (!$payment || $payment->status !== 'verified') {
                return redirect()->route('asesi.dashboard')
                    ->with('error', 'Pembayaran belum terverifikasi');
            }
        }

        // Generate invoice data
        $invoiceNumber = 'INV-' . str_pad($asesmen->id, 6, '0', STR_PAD_LEFT) . '-' . date('Ymd');
        $isCollective = $asesmen->is_collective;
        $phase = $payment->payment_phase ?? 'full';
        $batchId = $asesmen->collective_batch_id;
        $tuk = $asesmen->tuk;
        $asesmens = collect([$asesmen]);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.invoice', compact(
            'payment',
            'invoiceNumber',
            'isCollective',
            'phase',
            'asesmen',
            'asesmens',
            'batchId',
            'tuk'
        ));

        $filename = 'Invoice_' . $asesmen->id . '_' . date('Ymd') . '.pdf';

        return $pdf->download($filename);
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
}