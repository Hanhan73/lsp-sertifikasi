<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'sequence_number',
        'invoice_year',
        'batch_id',
        'tuk_id',
        'issued_by',
        'issued_at',
        'recipient_name',
        'recipient_address',
        'items',
        'total_amount',
        'notes',
        'status',
    ];

    protected $casts = [
        'items'       => 'array',
        'issued_at'   => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function tuk()
    {
        return $this->belongsTo(Tuk::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function collectivePayments()
    {
        return $this->hasMany(CollectivePayment::class)->orderBy('installment_number');
    }

    // =========================================================================
    // Static: Generate nomor invoice
    // Format: {urutan}/LSP-KAP/KU.00.01/{bulan-romawi}/{tahun}
    // =========================================================================

    public static function generateNumber(): array
    {
        $year = (int) now()->format('Y');

        $lastSequence = self::where('invoice_year', $year)->max('sequence_number') ?? 0;
        $sequence     = $lastSequence + 1;

        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $month = (int) now()->format('n');

        $number = $sequence . '/LSP-KAP/KU.00.01/' . $romans[$month] . '/' . $year;

        return [
            'invoice_number'  => $number,
            'sequence_number' => $sequence,
            'invoice_year'    => $year,
        ];
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->collectivePayments()
            ->where('status', 'verified')
            ->sum('amount');
    }

    public function getRemainingAttribute(): float
    {
        return (float) $this->total_amount - $this->total_paid;
    }

    public function isFullyPaid(): bool
    {
        return $this->remaining <= 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'sent'  => 'Terkirim',
            'paid'  => 'Lunas',
            default => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'sent'  => 'primary',
            'paid'  => 'success',
            default => 'secondary',
        };
    }
}