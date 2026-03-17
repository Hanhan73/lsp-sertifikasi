<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesorNotification extends Model
{
    protected $fillable = [
        'asesor_id', 'type', 'title', 'message', 'data', 'is_read', 'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function asesor()
    {
        return $this->belongsTo(Asesor::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'assignment'       => 'bi-person-check',
            'schedule_update'  => 'bi-calendar-event',
            'reminder'         => 'bi-bell',
            'document_request' => 'bi-file-earmark',
            default            => 'bi-info-circle',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match($this->type) {
            'assignment'       => 'primary',
            'schedule_update'  => 'info',
            'reminder'         => 'warning',
            'document_request' => 'success',
            default            => 'secondary',
        };
    }
}