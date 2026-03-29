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
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'approved_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'assigned_at'     => 'datetime',
    ];

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
}