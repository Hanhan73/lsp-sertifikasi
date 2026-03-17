<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleAsesorHistory extends Model
{
    protected $fillable = [
        'schedule_id', 'asesor_id', 'assigned_by',
        'action', 'notes', 'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function asesor()
    {
        return $this->belongsTo(Asesor::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'assigned'   => 'Ditugaskan',
            'reassigned' => 'Ditugaskan Ulang',
            'unassigned' => 'Pembatalan Tugas',
            default      => '-',
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            'assigned'   => 'success',
            'reassigned' => 'warning',
            'unassigned' => 'danger',
            default      => 'secondary',
        };
    }
}