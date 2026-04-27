<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectivePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'batch_id',
        'tuk_id',
        'installment_number',
        'amount',
        'due_date',
        'proof_path',
        'proof_uploaded_at',
        'status',
        'verified_by',
        'verified_at',
        'rejection_notes',
        'notes',
    ];

    protected $casts = [
        'amount'              => 'decimal:2',
        'due_date'            => 'date',
        'proof_uploaded_at'   => 'datetime',
        'verified_at'         => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function tuk()
    {
        return $this->belongsTo(Tuk::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
            default    => 'secondary',
        };
    }

    public function getProofUrlAttribute(): ?string
    {
        return $this->proof_path
            ? asset('storage/' . $this->proof_path)
            : null;
    }
}