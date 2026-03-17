<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['asesmen.user', 'asesmen.tuk', 'asesmen.skema'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Modal detail pembayaran (AJAX).
     */
    public function detail(Payment $payment)
    {
        $payment->load(['asesmen.user', 'asesmen.tuk', 'asesmen.skema', 'verifier']);

        $html = view('admin.payments.partials.detail-modal', compact('payment'))->render();

        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Verifikasi manual jika diperlukan (backup dari Midtrans webhook).
     */
    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected',
            'notes'  => 'nullable|string',
        ]);

        if ($payment->status === 'verified' && $payment->verified_by === null) {
            return redirect()->route('admin.payments')
                ->with('warning', 'Pembayaran sudah terverifikasi otomatis oleh sistem.');
        }

        $payment->update([
            'status'      => $request->status,
            'notes'       => trim(($payment->notes ?? '') . ' | Admin: ' . $request->notes),
            'verified_by' => auth()->id(),
            'verified_at' => $payment->verified_at ?? now(),
        ]);

        if ($request->status === 'verified') {
            $payment->asesmen->update(['status' => 'paid']);
        }

        return redirect()->route('admin.payments')
            ->with('success', 'Pembayaran berhasil diverifikasi manual!');
    }
}