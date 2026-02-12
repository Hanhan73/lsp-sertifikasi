<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asesmen_id',
        'amount',
        'payment_phase', // NEW: full, phase_1, phase_2
        'method',
        'proof_path',
        'status',
        'verified_by',
        'verified_at',
        'notes',
        'transaction_id',
        'order_id',
        'payment_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the asesmen for this payment
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class);
    }

    /**
     * Get the verifier (admin) - null if auto-verified by system
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu Pembayaran',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Check if payment was auto-verified by system
     */
    public function getIsAutoVerifiedAttribute()
    {
        return $this->status === 'verified' && $this->verified_by === null;
    }

    /**
     * Get verification source
     */
    public function getVerificationSourceAttribute()
    {
        if ($this->status !== 'verified') {
            return '-';
        }

        if ($this->verified_by === null) {
            return 'Otomatis (Midtrans)';
        }

        return 'Manual (Admin: ' . ($this->verifier->name ?? 'Unknown') . ')';
    }

    /**
     * NEW: Get payment phase label
     */
    public function getPaymentPhaseLabelAttribute()
    {
        $labels = [
            'full' => 'Pembayaran Penuh',
            'phase_1' => 'Fase 1 (50%)',
            'phase_2' => 'Fase 2 (50%)',
        ];

        return $labels[$this->payment_phase] ?? $this->payment_phase;
    }

    /**
     * NEW: Get phase badge color
     */
    public function getPhaseBadgeAttribute()
    {
        $badges = [
            'full' => 'primary',
            'phase_1' => 'info',
            'phase_2' => 'success',
        ];

        return $badges[$this->payment_phase] ?? 'secondary';
    }

    /**
     * NEW: Check if this is phase 1 payment
     */
    public function isPhase1()
    {
        return $this->payment_phase === 'phase_1';
    }

    /**
     * NEW: Check if this is phase 2 payment
     */
    public function isPhase2()
    {
        return $this->payment_phase === 'phase_2';
    }

    /**
     * NEW: Check if this is full payment
     */
    public function isFullPayment()
    {
        return $this->payment_phase === 'full';
    }
}