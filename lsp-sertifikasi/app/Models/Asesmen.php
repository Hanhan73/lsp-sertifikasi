<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AplSatu;

class Asesmen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tuk_id',
        'assigned_tuk_id',
        'assigned_at',
        'assigned_by',
        'skema_id',
        'schedule_id',
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
        'payment_phases',
        'phase_1_percentage',
        'phase_2_percentage',
        'phase_1_amount',
        'phase_2_amount',
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
        'assigned_at' => 'datetime',
        'fee_amount' => 'decimal:2',
        'phase_1_amount' => 'decimal:2',
        'phase_2_amount' => 'decimal:2',
        'phase_1_percentage' => 'decimal:2',
        'phase_2_percentage' => 'decimal:2',
        'is_collective' => 'boolean',
        'collective_paid_by_tuk' => 'boolean',
        'skip_payment' => 'boolean',
        'tuk_verified_at' => 'date:d-m-Y',
        'training_flag' => 'boolean',
        'admin_verified_at' => 'date:d-m-Y'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tuk()
    {
        return $this->belongsTo(Tuk::class);
    }

    // NEW: Assigned TUK relationship
    public function assignedTuk()
    {
        return $this->belongsTo(Tuk::class, 'assigned_tuk_id');
    }

    // NEW: Assigner relationship
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function skema()
    {
        return $this->belongsTo(Skema::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function registrar()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function tukVerifier()
    {
        return $this->belongsTo(User::class, 'tuk_verified_by');
    }

    public function adminVerifier()
    {
        return $this->belongsTo(User::class, 'admin_verified_by');
    }
    
    public function assessorRegistrar()
    {
        return $this->belongsTo(User::class, 'admin_verified_by');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    
    public function aplsatu()
    {
        return $this->hasOne(AplSatu::class);
    }

    public function hasApl01(): bool
    {
        return $this->aplsatu()->exists();
    }

    public function apldua()
    {
        return $this->hasOne(\App\Models\AplDua::class);
    }

    public function frak01()
    {
        return $this->hasOne(\App\Models\FrAk01::class, 'asesmen_id');
    }

    public function frak04()
    {
        return $this->hasOne(\App\Models\FrAk04::class, 'asesmen_id');
    }

    
    // Helper Methods
    
    /**
     * NEW: Calculate phase amounts based on percentage
     */
    public function calculatePhaseAmounts()
    {
        if ($this->payment_phases !== 'two_phase' || !$this->fee_amount) {
            return;
        }

        $this->phase_1_amount = ($this->fee_amount * $this->phase_1_percentage) / 100;
        $this->phase_2_amount = ($this->fee_amount * $this->phase_2_percentage) / 100;
    }

    /**
     * NEW: Get effective TUK (assigned or original)
     */
    public function getEffectiveTuk()
    {
        return $this->assignedTuk ?? $this->tuk;
    }

    /**
     * NEW: Check if asesi is assigned to TUK
     */
    public function isAssignedToTuk()
    {
        return !is_null($this->assigned_tuk_id);
    }

    /**
     * Check if batch is ready for payment (Phase 1)
     */
    public function isBatchReadyForPayment()
    {
        if (!$this->is_collective) {
            return false;
        }

        $batch = $this->fullBatch();

        return $batch->every(function ($asesmen) {
            return $asesmen->status === 'verified' && $asesmen->fee_amount > 0;
        });
    }

    /**
     * Check if batch is ready for Phase 2 payment
     */
    public function isBatchReadyForPhase2Payment()
    {
        if (!$this->is_collective || $this->payment_phases !== 'two_phase') {
            return false;
        }

        $batch = $this->fullBatch();

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
     */
    public function getBatchPaymentStatus()
    {
        if (!$this->is_collective) {
            return null;
        }

        $batch = $this->fullBatch();

        if ($this->payment_phases === 'single') {
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

            if ($allPaid) return 'paid';
            if ($anyPending) return 'pending';
            return 'not_paid';
        } else {
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

            if ($phase1Paid && $phase2Paid) return 'fully_paid';
            if ($phase1Paid) return 'phase_1_paid';
            return 'not_paid';
        }
    }

    public function batchMembers()
    {
        if (!$this->is_collective || !$this->collective_batch_id) {
            return collect([]);
        }

        return self::where('collective_batch_id', $this->collective_batch_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    public function fullBatch()
    {
        if (!$this->is_collective || !$this->collective_batch_id) {
            return collect([$this]);
        }

        return self::where('collective_batch_id', $this->collective_batch_id)->get();
    }

    public function shouldSkipPayment()
    {
        return $this->is_collective && $this->collective_paid_by_tuk;
    }

    // Status labels and badges
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
     * Get next action for asesi - UPDATED
     */
    public function getNextActionAttribute()
    {
        // Collective asesi workflow
        if ($this->is_collective && $this->shouldSkipPayment()) {
            switch ($this->status) {
                case 'registered':
                    return 'Lengkapi data pribadi';
                case 'data_completed':
                    return 'Menunggu verifikasi TUK';
                case 'verified':
                    if ($this->payment_phases === 'single') {
                        return 'Menunggu TUK melakukan pembayaran';
                    } else {
                        return 'Menunggu TUK melakukan pembayaran fase 1';
                    }
                case 'paid':
                    return 'Menunggu jadwal asesmen dari TUK';
                case 'scheduled':
                    return 'Lengkapi pra-asesmen';
                case 'pre_assessment_completed':
                    return 'Menunggu proses asesmen';
                case 'assessed':
                    if ($this->payment_phases === 'two_phase') {
                        $phase2Paid = $this->payments()
                            ->where('payment_phase', 'phase_2')
                            ->where('status', 'verified')
                            ->exists();
                        
                        if (!$phase2Paid) {
                            return 'Menunggu TUK melakukan pembayaran fase 2';
                        }
                    }
                    return 'Menunggu penerbitan sertifikat';
                case 'certified':
                    return 'Unduh sertifikat';
            }
        }

        // NEW: Mandiri asesi workflow (verifikasi langsung oleh Admin)
        switch ($this->status) {
            case 'registered':
                return 'Lengkapi data pribadi';
            case 'data_completed':
                return 'Menunggu verifikasi Admin LSP';
            case 'verified':
                if ($this->isAssignedToTuk()) {
                    return 'Lakukan pembayaran';
                } else {
                    return 'Menunggu assignment ke TUK oleh Admin';
                }
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