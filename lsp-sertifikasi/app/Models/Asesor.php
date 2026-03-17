<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asesor extends Model
{
    use HasFactory;

    protected $fillable = [
        'no', 'nama', 'nik', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'alamat', 'kota', 'provinsi',
        'telepon', 'email', 'no_reg_met', 'no_blanko',
        'siap_kerja', 'keterangan', 'status_reg', 'expire_date',
        'foto_path', 'user_id', 'is_active',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'expire_date'   => 'date',
        'is_active'     => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skemas()
    {
        return $this->belongsToMany(Skema::class, 'asesor_skema')
            ->withTimestamps();
    }

    public function asesmens()
    {
        return $this->hasMany(Asesmen::class, 'asesor_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'asesor_id');
    }

    public function activeSchedules()
    {
        return $this->hasMany(Schedule::class, 'asesor_id')
                    ->where('assessment_date', '>=', now())
                    ->orderBy('assessment_date');
    }

    public function notifications()
    {
        return $this->hasMany(AsesorNotification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(AsesorNotification::class)
                    ->where('is_read', false)
                    ->orderBy('created_at', 'desc');
    }

    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    // Accessor: label status
    public function getStatusLabelAttribute()
    {
        return match($this->status_reg) {
            'aktif'   => 'Aktif',
            'expire'  => 'Expire',
            default   => 'Nonaktif',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status_reg) {
            'aktif'   => 'success',
            'expire'  => 'warning',
            default   => 'danger',
        };
    }

    public function getFotoUrlAttribute()
    {
        return $this->foto_path
            ? asset('storage/' . $this->foto_path)
            : asset('images/default-avatar.png');
    }
}