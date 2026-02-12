<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'asesmen_id',
        'assessment_date',
        'start_time',
        'end_time',
        'location',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'assessment_date' => 'date',
    ];

    /**
     * Get the asesmen for this schedule
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class);
    }

    /**
     * Get the creator (TUK user)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->assessment_date->format('d F Y');
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute()
    {
        return $this->start_time . ' - ' . $this->end_time;
    }

    /**
     * Check if schedule is upcoming
     */
    public function isUpcoming()
    {
        return $this->assessment_date->isFuture();
    }

    /**
     * Check if schedule is today
     */
    public function isToday()
    {
        return $this->assessment_date->isToday();
    }

    /**
     * Check if schedule is past
     */
    public function isPast()
    {
        return $this->assessment_date->isPast();
    }

    /**
     * Scope: Upcoming schedules
     */
    public function scopeUpcoming($query)
    {
        return $query->where('assessment_date', '>=', now()->toDateString())
            ->orderBy('assessment_date', 'asc')
            ->orderBy('start_time', 'asc');
    }

    /**
     * Scope: Past schedules
     */
    public function scopePast($query)
    {
        return $query->where('assessment_date', '<', now()->toDateString())
            ->orderBy('assessment_date', 'desc');
    }

    /**
     * Scope: Today's schedules
     */
    public function scopeToday($query)
    {
        return $query->whereDate('assessment_date', now()->toDateString())
            ->orderBy('start_time', 'asc');
    }

    /**
     * Scope: By TUK
     */
    public function scopeByTuk($query, $tukId)
    {
        return $query->whereHas('asesmen', function($q) use ($tukId) {
            $q->where('tuk_id', $tukId);
        });
    }
}