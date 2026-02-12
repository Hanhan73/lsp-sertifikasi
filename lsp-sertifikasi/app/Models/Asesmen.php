<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asesmen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tuk_id',
        'skema_id',
        'full_name',
        'nik',
        'birth_place',
        'birth_date',
        'gender',
        'address',
        'city_code',
        'province_code',
        'phone',
        'email',
        'education',
        'occupation',
        'budget_source',
        'institution',
        'photo_path',
        'ktp_path',
        'document_path',
        'preferred_date',
        'registration_date',
        'status',
        'fee_amount',
        'admin_verified_by',
        'admin_verified_at',
        'tuk_verified_by',
        'tuk_verified_at',
        'tuk_verification_notes',
        'result',
        'result_notes',
        'assessed_by',
        'assessed_at',
        'pre_assessment_data',
        'pre_assessment_file',
        'registered_by',
        'is_collective',
        'collective_batch_id',
        'payment_phases', // NEW: single or two_phase
        'phase_1_amount', // NEW: untuk 2 fase
        'phase_2_amount', // NEW: untuk 2 fase
        'collective_paid_by_tuk',
        'skip_payment',
        'training_flag'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'preferred_date' => 'date',
        'registration_date' => 'date',
        'verified_at' => 'datetime',
        'assessed_at' => 'datetime',
        'fee_amount' => 'decimal:2',
        'phase_1_amount' => 'decimal:2',
        'phase_2_amount' => 'decimal:2',
        'is_collective' => 'boolean',
        'collective_paid_by_tuk' => 'boolean',
        'skip_payment' => 'boolean',
        'tuk_verified_at' => 'date:d-m-Y',
        'training_flag' => 'boolean'
    ];

    /**
     * Get the user (asesi) for this asesmen
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the TUK for this asesmen
     */
    public function tuk()
    {
        return $this->belongsTo(Tuk::class);
    }

    /**
     * Get the skema for this asesmen
     */
    public function skema()
    {
        return $this->belongsTo(Skema::class);
    }

    /**
     * Get the verifier (admin)
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the assessor (admin)
     */
    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    /**
     * Get the registrar (TUK) for collective registration
     */
    public function registrar()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Get the verifier (TUK) for this asesmen
     */
    public function tukVerifier()
    {
        return $this->belongsTo(User::class, 'tuk_verified_by');
    }

    /**
     * Get the assessor registrar
     */
    public function assessorRegistrar()
    {
        return $this->belongsTo(User::class, 'admin_verified_by');
    }

    /**
     * Get payment for this asesmen
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get all payments (untuk 2 fase)
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get schedule for this asesmen
     */
    public function schedule()
    {
        return $this->hasOne(Schedule::class);
    }

    /**
     * Get certificate for this asesmen
     */
    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    /**
     * Get all asesmens in the same batch
     */
    public function batchMembers()
    {
        if (!$this->is_collective || !$this->collective_batch_id) {
            return collect([]);
        }

        return self::where('collective_batch_id', $this->collective_batch_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Get all asesmens in batch including this one
     */
    public function fullBatch()
    {
        if (!$this->is_collective || !$this->collective_batch_id) {
            return collect([$this]);
        }

        return self::where('collective_batch_id', $this->collective_batch_id)->get();
    }

    /**
     * Check if this asesi should skip payment (TUK pays)
     */
    public function shouldSkipPayment()
    {
        return $this->is_collective && $this->collective_paid_by_tuk;
    }

    /**
     * Check if batch is ready for payment (Phase 1)
     * UPDATED: Only for collective
     */
    public function isBatchReadyForPayment()
    {
        if (!$this->is_collective) {
            return false;
        }

        $batch = $this->fullBatch();

        // All must be verified by admin and have fee set
        return $batch->every(function ($asesmen) {
            return $asesmen->status === 'verified' && $asesmen->fee_amount > 0;
        });
    }

    /**
     * NEW: Check if batch is ready for Phase 2 payment
     * Only for two_phase collective
     */
    public function isBatchReadyForPhase2Payment()
    {
        if (!$this->is_collective || $this->payment_phases !== 'two_phase') {
            return false;
        }

        $batch = $this->fullBatch();

        // All must be assessed/certified AND phase 1 must be paid
        $allAssessed = $batch->every(function ($asesmen) {
            return in_array($asesmen->status, ['assessed', 'certified']);
        });

        $phase1Paid = $batch->every(function ($asesmen) {
            return $asesmen->payments()
                ->where('payment_phase', 'phase_1')
                ->where('status', 'verified')
                ->exists();
        });

        return $allAssessed && $phase1Paid;
    }

    /**
     * Get batch payment status
     * UPDATED: Handle phases
     */
    public function getBatchPaymentStatus()
    {
        if (!$this->is_collective) {
            return null;
        }

        $batch = $this->fullBatch();

        // Check payment phases
        if ($this->payment_phases === 'single') {
            // Single phase - check full payment
            $allPaid = $batch->every(function ($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'full')
                    ->where('status', 'verified')
                    ->exists();
            });

            $anyPending = $batch->some(function ($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'full')
                    ->where('status', 'pending')
                    ->exists();
            });

            if ($allPaid) {
                return 'paid';
            } elseif ($anyPending) {
                return 'pending';
            } else {
                return 'not_paid';
            }
        } else {
            // Two phase
            $phase1Paid = $batch->every(function ($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'phase_1')
                    ->where('status', 'verified')
                    ->exists();
            });

            $phase2Paid = $batch->every(function ($asesmen) {
                return $asesmen->payments()
                    ->where('payment_phase', 'phase_2')
                    ->where('status', 'verified')
                    ->exists();
            });

            if ($phase1Paid && $phase2Paid) {
                return 'fully_paid';
            } elseif ($phase1Paid) {
                return 'phase_1_paid';
            } else {
                return 'not_paid';
            }
        }
    }

    /**
     * Status labels
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'registered' => 'Terdaftar',
            'data_completed' => 'Data Lengkap',
            'verified' => 'Terverifikasi',
            'paid' => 'Sudah Bayar',
            'scheduled' => 'Terjadwal',
            'pre_assessment_completed' => 'Pra-Asesmen Selesai',
            'assessed' => 'Sudah Diases',
            'certified' => 'Tersertifikasi',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'registered' => 'secondary',
            'data_completed' => 'info',
            'verified' => 'primary',
            'paid' => 'success',
            'scheduled' => 'warning',
            'pre_assessment_completed' => 'info',
            'assessed' => 'primary',
            'certified' => 'success',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get next action for asesi
     * UPDATED: Handle 2 phase payment
     */
    public function getNextActionAttribute()
    {
        // Collective asesi workflow
        if ($this->is_collective && $this->shouldSkipPayment()) {
            switch ($this->status) {
                case 'registered':
                    return 'Lengkapi data pribadi';
                case 'data_completed':
                    return 'Menunggu verifikasi Admin LSP';
                case 'verified':
                    if ($this->payment_phases === 'single') {
                        return 'Menunggu TUK melakukan pembayaran';
                    } else {
                        return 'Menunggu TUK melakukan pembayaran fase 1 (50%)';
                    }
                case 'paid':
                    return 'Menunggu jadwal asesmen dari TUK';
                case 'scheduled':
                    return 'Lengkapi pra-asesmen';
                case 'pre_assessment_completed':
                    return 'Menunggu proses asesmen';
                case 'assessed':
                    if ($this->payment_phases === 'two_phase') {
                        // Check if phase 2 paid
                        $phase2Paid = $this->payments()
                            ->where('payment_phase', 'phase_2')
                            ->where('status', 'verified')
                            ->exists();
                        
                        if (!$phase2Paid) {
                            return 'Menunggu TUK melakukan pembayaran fase 2 (50%)';
                        }
                    }
                    return 'Menunggu penerbitan sertifikat';
                case 'certified':
                    return 'Unduh sertifikat';
            }
        }

        // Regular asesi workflow (UPDATED: Auto-verified setelah data completed)
        switch ($this->status) {
            case 'registered':
                return 'Lengkapi data pribadi';
            case 'data_completed':
                return 'Menunggu verifikasi otomatis'; // Will be instant for mandiri
            case 'verified':
                return 'Lakukan pembayaran';
            case 'paid':
                return 'Menunggu jadwal asesmen dari TUK';
            case 'scheduled':
                return 'Lengkapi pra-asesmen';
            case 'pre_assessment_completed':
                return 'Menunggu proses asesmen';
            case 'assessed':
                return 'Menunggu penerbitan sertifikat';
            case 'certified':
                return 'Unduh sertifikat';
            default:
                return '-';
        }
    }
}