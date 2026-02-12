<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tuk;
use App\Models\Skema;
use App\Models\Asesmen;
use App\Models\Payment;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_asesi' => Asesmen::count(),
            'total_tuk' => Tuk::count(),
            'total_skema' => Skema::count(),
            'pending_verification' => Asesmen::where('status', 'data_completed')->count(),
            'pending_payment' => Payment::where('status', 'pending')->count(),
            'certified' => Asesmen::where('status', 'certified')->count(),
        ];

        $asesmens = Asesmen::with(['user', 'tuk', 'skema'])
            ->latest()
            ->take(10)
            ->get();

        $batchInfo = null;
        $batchId = Asesmen::whereNotNull('collective_batch_id')
            ->latest()
            ->value('collective_batch_id');

        if ($batchId) {
            $batch = Asesmen::with(['tuk', 'registrar', 'payment'])
                ->where('collective_batch_id', $batchId)
                ->get();

            $batchInfo = [
                'batch_id' => $batchId,
                'total_members' => $batch->count(),
                'tuk' => $batch->first()->tuk,
                'registered_by' => $batch->first()->registrar,
                'payment_timing' => $batch->first()->collective_payment_timing,
                'payment_status' => $batch->every(fn ($a) => $a->payment?->status === 'verified')
                    ? 'paid'
                    : ($batch->contains(fn ($a) => $a->payment?->status === 'pending')
                        ? 'pending'
                        : 'not_paid'),
            ];
        }

        return view('admin.dashboard', compact('stats', 'asesmens', 'batchInfo'));
    }

    // ==================== TUK MANAGEMENT ====================

    /**
     * Manage TUK - List
     */
    public function tuks()
    {
        $tuks = Tuk::with('user')->withCount('asesmens')->get();
        return view('admin.tuks.index', compact('tuks'));
    }

    /**
     * Manage TUK - Create Form
     */
    public function createTuk()
    {
        return view('admin.tuks.create');
    }

    /**
     * Manage TUK - Store
     */
    public function storeTuk(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:tuks',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'manager_name' => 'nullable|string|max:255',
            'staff_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            // Create user account for TUK
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'tuk',
                'is_active' => true,
            ]);

            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos', 'public');
            }

            // Create TUK
            Tuk::create([
                'code' => $request->code,
                'name' => $request->name,
                'address' => $request->address,
                'manager_name' => $request->manager_name,
                'staff_name' => $request->staff_name,
                'logo_path' => $logoPath,
                'user_id' => $user->id,
                'is_active' => $request->has('is_active'),
            ]);

            DB::commit();

            // TODO: Send email with credentials
            Log::info("TUK Created - Email: {$request->email}, Password: [hidden]");

            return redirect()->route('admin.tuks')
                ->with('success', 'TUK berhasil ditambahkan! Kredensial login telah dikirim ke email.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating TUK: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Manage TUK - Edit Form
     */
    public function editTuk(Tuk $tuk)
    {
        $tuk->load('user', 'asesmens');
        return view('admin.tuks.edit', compact('tuk'));
    }

    /**
     * Manage TUK - Update
     */
    public function updateTuk(Request $request, Tuk $tuk)
    {
        $request->validate([
            'code' => 'required|unique:tuks,code,' . $tuk->id,
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'manager_name' => 'nullable|string|max:255',
            'staff_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($tuk->logo_path) {
                    Storage::disk('public')->delete($tuk->logo_path);
                }
                $logoPath = $request->file('logo')->store('logos', 'public');
                $tuk->logo_path = $logoPath;
            }

            // Update TUK
            $tuk->update([
                'code' => $request->code,
                'name' => $request->name,
                'address' => $request->address,
                'manager_name' => $request->manager_name,
                'staff_name' => $request->staff_name,
                'is_active' => $request->has('is_active'),
            ]);

            // Update user name
            if ($tuk->user) {
                $tuk->user->update(['name' => $request->name]);
            }

            DB::commit();

            return redirect()->route('admin.tuks')
                ->with('success', 'TUK berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating TUK: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Manage TUK - Delete
     */
    public function deleteTuk(Tuk $tuk)
    {
        DB::beginTransaction();
        try {
            // Check if has active asesmens
            if ($tuk->asesmens()->whereIn('status', ['registered', 'data_completed', 'verified', 'paid', 'scheduled'])->exists()) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus TUK yang masih memiliki asesi aktif!');
            }

            // Delete logo if exists
            if ($tuk->logo_path) {
                Storage::disk('public')->delete($tuk->logo_path);
            }

            // Delete user account
            if ($tuk->user) {
                $tuk->user->delete();
            }

            // Delete TUK
            $tuk->delete();

            DB::commit();

            return redirect()->route('admin.tuks')
                ->with('success', 'TUK berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting TUK: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ==================== SKEMA MANAGEMENT ====================

    /**
     * Manage Skema - List
     */
    public function skemas()
    {
        $skemas = Skema::withCount('asesmens')->get();
        return view('admin.skemas.index', compact('skemas'));
    }

    /**
     * Manage Skema - Create Form
     */
    public function createSkema()
    {
        return view('admin.skemas.create');
    }

    /**
     * Manage Skema - Store
     */
    public function storeSkema(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:skemas',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        Skema::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'fee' => $request->fee,
            'duration_days' => $request->duration_days,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.skemas')
            ->with('success', 'Skema berhasil ditambahkan!');
    }

    /**
     * Manage Skema - Edit Form
     */
    public function editSkema(Skema $skema)
    {
        $skema->load('asesmens');
        return view('admin.skemas.edit', compact('skema'));
    }

    /**
     * Manage Skema - Update
     */
    public function updateSkema(Request $request, Skema $skema)
    {
        $request->validate([
            'code' => 'required|unique:skemas,code,' . $skema->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $skema->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'fee' => $request->fee,
            'duration_days' => $request->duration_days,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.skemas')
            ->with('success', 'Skema berhasil diupdate!');
    }

    /**
     * Manage Skema - Delete
     */
    public function deleteSkema(Skema $skema)
    {
        // Check if has active asesmens
        if ($skema->asesmens()->whereIn('status', ['registered', 'data_completed', 'verified', 'paid', 'scheduled'])->exists()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus skema yang masih digunakan oleh asesi aktif!');
        }

        $skema->delete();

        return redirect()->route('admin.skemas')
            ->with('success', 'Skema berhasil dihapus!');
    }

    // ==================== VERIFICATIONS ====================

    public function verifications()
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'tukVerifier'])
            ->where('status', 'data_completed')
            ->get();

        return view('admin.verifications.index', compact('asesmens'));
    }

    public function verifyAsesmen(Asesmen $asesmen)
    {
        return view('admin.verifications.verify', compact('asesmen'));
    }

    public function processVerification(Request $request, Asesmen $asesmen)
    {
        $request->validate([
            'fee_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate phase amounts if two_phase
        $updateData = [
            'fee_amount' => $request->fee_amount,
            'admin_verified_by' => auth()->id(),
            'admin_verified_at' => now(),
            'status' => 'verified',
        ];

        if ($asesmen->payment_phases === 'two_phase') {
            $updateData['phase_1_amount'] = $request->fee_amount / 2;
            $updateData['phase_2_amount'] = $request->fee_amount / 2;
        }

        $asesmen->update($updateData);

        if ($request->notes) {
            Log::info("Verification notes for Asesmen #{$asesmen->id}: {$request->notes}");
        }

        return redirect()->route('admin.verifications')
            ->with('success', 'Asesi berhasil diverifikasi! Biaya: Rp ' . number_format($request->fee_amount, 0, ',', '.'));
    }

    public function processBatchVerification(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|string',
            'fee_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $asesmens = Asesmen::where('collective_batch_id', $request->batch_id)
            ->where('status', 'data_completed')
            ->get();

        if ($asesmens->isEmpty()) {
            return redirect()->route('admin.verifications')
                ->with('error', 'Tidak ada asesi yang perlu diverifikasi dalam batch ini.');
        }

        $firstAsesmen = $asesmens->first();
        $count = 0;

        foreach ($asesmens as $asesmen) {
            $updateData = [
                'fee_amount' => $request->fee_amount,
                'admin_verified_by' => auth()->id(),
                'admin_verified_at' => now(),
                'status' => 'verified',
            ];

            if ($firstAsesmen->payment_phases === 'two_phase') {
                $updateData['phase_1_amount'] = $request->fee_amount / 2;
                $updateData['phase_2_amount'] = $request->fee_amount / 2;
            }

            $asesmen->update($updateData);
            $count++;
        }

        $totalAmount = $request->fee_amount * $count;

        Log::info("Batch verification for {$request->batch_id}: {$count} asesmens, Rp {$totalAmount} total. Notes: {$request->notes}");

        return redirect()->route('admin.verifications')
            ->with('success', "Batch berhasil diverifikasi! {$count} asesi dengan total biaya Rp " . number_format($totalAmount, 0, ',', '.'));
    }

    // ==================== PAYMENTS ====================

    public function payments()
    {
        $payments = Payment::with(['asesmen.user', 'asesmen.tuk', 'asesmen.skema'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes' => 'nullable|string',
        ]);

        if ($payment->status === 'verified' && $payment->verified_by === null) {
            return redirect()->route('admin.payments')
                ->with('warning', 'Pembayaran sudah terverifikasi otomatis oleh sistem.');
        }

        $payment->update([
            'status' => $request->status,
            'notes' => ($payment->notes ?? '') . ' | Manual verification by admin: ' . $request->notes,
            'verified_by' => auth()->id(),
            'verified_at' => $payment->verified_at ?? now(),
        ]);

        if ($request->status === 'verified') {
            $payment->asesmen->update(['status' => 'paid']);
        }

        return redirect()->route('admin.payments')
            ->with('success', 'Pembayaran berhasil diverifikasi manual!');
    }

    // ==================== ASSESSMENTS ====================

    public function assessments()
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'schedule'])
            ->whereIn('status', ['pre_assessment_completed', 'scheduled'])
            ->get();

        return view('admin.assessments.index', compact('asesmens'));
    }

    public function inputAssessment(Request $request, Asesmen $asesmen)
    {
        $request->validate([
            'result' => 'required|in:kompeten,belum_kompeten',
            'result_notes' => 'nullable|string',
        ]);

        $asesmen->update([
            'result' => $request->result,
            'result_notes' => $request->result_notes,
            'assessed_by' => auth()->id(),
            'assessed_at' => now(),
            'status' => 'assessed',
        ]);

        if ($request->result === 'kompeten') {
            $this->generateCertificate($asesmen);
        }

        return redirect()->route('admin.assessments')->with('success', 'Hasil asesmen berhasil disimpan!');
    }

    protected function generateCertificate(Asesmen $asesmen)
    {
        $certificateNumber = Certificate::generateCertificateNumber();
        
        $data = [
            'asesmen' => $asesmen,
            'certificate_number' => $certificateNumber,
            'issued_date' => now(),
        ];

        $pdf = Pdf::loadView('certificates.template', $data);
        $filename = 'certificate_' . $asesmen->id . '_' . time() . '.pdf';
        $path = 'certificates/' . $filename;
        
        Storage::put('public/' . $path, $pdf->output());

        Certificate::create([
            'asesmen_id' => $asesmen->id,
            'certificate_number' => $certificateNumber,
            'issued_date' => now(),
            'valid_until' => now()->addYears(3),
            'pdf_path' => $path,
            'generated_by' => auth()->id(),
        ]);

        $asesmen->update(['status' => 'certified']);
    }

    // ==================== ALL ASESI ====================

    public function allAsesi()
    {
        $asesmens = Asesmen::with(['user', 'tuk', 'skema', 'payment', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.asesi.index', compact('asesmens'));
    }

    // ==================== REPORTS ====================

    public function reports()
    {
        $data = [
            'by_status' => Asesmen::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->get(),
            'by_tuk' => Asesmen::selectRaw('tuk_id, count(*) as total')
                ->with('tuk')
                ->groupBy('tuk_id')
                ->get(),
            'by_skema' => Asesmen::selectRaw('skema_id, count(*) as total')
                ->with('skema')
                ->groupBy('skema_id')
                ->get(),
            'monthly_registrations' => Asesmen::selectRaw('YEAR(registration_date) as year, MONTH(registration_date) as month, count(*) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->take(12)
                ->get(),
        ];

        return view('admin.reports.index', compact('data'));
    }
}