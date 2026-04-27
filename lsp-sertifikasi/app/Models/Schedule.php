<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tuk_id',
        'skema_id',
        'assessment_date',
        'asesor_id',
        'assigned_by',
        'assigned_at',
        'assignment_notes',
        'start_time',
        'end_time',
        'location',
        'location_type',   // ← baru: 'offline' | 'online'
        'meeting_link',    // ← baru: URL meeting jika online
        'notes',
        'created_by',
        // Approval workflow
        'approval_status',
        'approval_notes',
        'approved_by',
        'approved_at',
        'rejected_at',
        // SK
        'sk_number',
        'sk_path',
        'assessment_start',
        // Daftar hadir sign lock
        'daftar_hadir_signed_at',
        'daftar_hadir_signed_by',
        // Dokumentasi foto
        'foto_dokumentasi_1',
        'foto_dokumentasi_2',
        'foto_uploaded_by',
        'foto_uploaded_at',
        // Catatan asesor
        'catatan_asesor',
    ];

    protected $casts = [
        'assessment_date'         => 'date',
        'approved_at'             => 'datetime',
        'rejected_at'             => 'datetime',
        'assigned_at'             => 'datetime',
        'assessment_start'        => 'boolean',
        'daftar_hadir_signed_at'  => 'datetime',
    ];

    public function isDaftarHadirSigned(): bool
    {
        return $this->daftar_hadir_signed_at !== null;
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function asesmens()
    {
        return $this->hasMany(Asesmen::class);
    }

    public function tuk()
    {
        return $this->belongsTo(Tuk::class);
    }

    public function skema()
    {
        return $this->belongsTo(Skema::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function asesor()
    {
        return $this->belongsTo(Asesor::class, 'asesor_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignmentHistories()
    {
        return $this->hasMany(ScheduleAsesorHistory::class)->orderBy('action_at', 'desc');
    }

    // =========================================================================
    // Location helpers
    // =========================================================================

    public function isOnline(): bool
    {
        return $this->location_type === 'online';
    }

    public function isOffline(): bool
    {
        return $this->location_type === 'offline' || is_null($this->location_type);
    }

    /**
     * Label jenis lokasi, e.g. "Online (Zoom)" atau "Offline"
     */
    public function getLocationTypeLabelAttribute(): string
    {
        return $this->isOnline() ? 'Online' : 'Offline';
    }

    public function getLocationTypeBadgeAttribute(): string
    {
        return $this->isOnline() ? 'primary' : 'secondary';
    }

    /**
     * Teks lokasi yang ditampilkan ke user:
     * - Offline → nama gedung/ruangan
     * - Online  → nama platform dari URL, atau URL-nya langsung
     */
    public function getLocationDisplayAttribute(): string
    {
        if ($this->isOffline()) {
            return $this->location ?? '-';
        }

        // Online: coba ekstrak nama platform dari URL
        if ($this->meeting_link) {
            $platform = $this->detectPlatform($this->meeting_link);
            return $platform ? "{$platform}: {$this->location}" : $this->location;
        }

        return $this->location ?? '-';
    }

    private function detectPlatform(string $url): ?string
    {
        $host = parse_url(strtolower($url), PHP_URL_HOST) ?? '';
        return match (true) {
            str_contains($host, 'zoom')   => 'Zoom',
            str_contains($host, 'meet')   => 'Google Meet',
            str_contains($host, 'teams')  => 'MS Teams',
            str_contains($host, 'webex')  => 'Webex',
            default                        => null,
        };
    }

    // =========================================================================
    // Approval helpers
    // =========================================================================

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function getApprovalStatusLabelAttribute(): string
    {
        return [
            'pending_approval' => 'Menunggu Persetujuan',
            'approved'         => 'Disetujui',
            'rejected'         => 'Ditolak',
        ][$this->approval_status] ?? $this->approval_status;
    }

    public function getApprovalStatusBadgeAttribute(): string
    {
        return [
            'pending_approval' => 'warning',
            'approved'         => 'success',
            'rejected'         => 'danger',
        ][$this->approval_status] ?? 'secondary';
    }

    public function hasSk(): bool
    {
        return !empty($this->sk_path);
    }

    // =========================================================================
    // Date helpers
    // =========================================================================

    public function getIsAssignedAttribute(): bool
    {
        return $this->asesor_id !== null;
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->assessment_date->format('d F Y');
    }

    public function getTimeRangeAttribute(): string
    {
        return $this->start_time . ' - ' . $this->end_time;
    }

    public function isUpcoming(): bool
    {
        return $this->assessment_date->isFuture();
    }

    public function isToday(): bool
    {
        return $this->assessment_date->isToday();
    }

    public function isPast(): bool
    {
        return $this->assessment_date->isPast();
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeUpcoming($query)
    {
        return $query->where('assessment_date', '>=', now()->toDateString())
            ->orderBy('assessment_date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('assessment_date', '<', now()->toDateString())
            ->orderBy('assessment_date', 'desc');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('assessment_date', now()->toDateString())
            ->orderBy('start_time', 'asc');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function distribusiSoalObservasi()
    {
        return $this->hasMany(\App\Models\DistribusiSoalObservasi::class);
    }
 
    public function distribusiPaketSoal()
    {
        return $this->hasMany(\App\Models\DistribusiPaketSoal::class);
    }

    public function hasilObservasi()  
    { 
        return $this->hasMany(HasilObservasi::class); 
    }

    public function hasilPortofolio() 
    { 
        return $this->hasMany(HasilPortofolio::class);
    }
    
    public function beritaAcara()
    {
        return $this->hasOne(BeritaAcara::class);
    }
 
    /**
     * Satu schedule hanya punya satu konfigurasi distribusi soal teori.
     */
    public function distribusiSoalTeori()
    {
        return $this->hasOne(\App\Models\DistribusiSoalTeori::class);
    }
    public function distribusiPortofolio()
    {
        return $this->hasMany(\App\Models\DistribusiPortofolio::class);
    }

    public function hasFotoDokumentasi(): bool
    {
        return $this->foto_dokumentasi_1 && $this->foto_dokumentasi_2;
    }

    public function honorPaymentDetails()
    {
        return $this->hasMany(HonorPaymentDetail::class);
    }
}