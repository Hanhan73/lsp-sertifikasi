<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'password_changed_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_changed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ========== RELATIONSHIPS ==========
    
    public function asesmen()
    {
        return $this->hasOne(Asesmen::class);
    }

    public function asesmens()
    {
        return $this->hasMany(Asesmen::class);
    }

    public function tuk()
    {
        return $this->hasOne(Tuk::class);
    }

    public function registeredAsesmens()
    {
        return $this->hasMany(Asesmen::class, 'registered_by');
    }

    // ========== EMAIL VERIFICATION ==========

    public function mustVerifyEmail(): bool
    {
        // Admin & TUK never require verification
        if (in_array($this->role, ['admin', 'tuk'])) {
            return false;
        }

        // Asesi mandiri harus verifikasi
        if ($this->role === 'asesi') {
            // Load asesmen jika belum di-load
            if (!$this->relationLoaded('asesmen')) {
                $this->load('asesmen');
            }
            
            // Collective asesi skip verification
            if ($this->asesmen && $this->asesmen->is_collective) {
                return false;
            }
        }

        return true;
    }

    public function hasVerifiedEmail(): bool
    {
        if (!$this->mustVerifyEmail()) {
            return true;
        }

        return !is_null($this->email_verified_at);
    }

    /**
     * ✅ Mark email as verified
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * ✅ Get email for verification
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * ✅ Override sendEmailVerificationNotification untuk custom email
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    // ========== HELPER METHODS ==========

    public function isFirstLogin(): bool
    {
        if (!$this->isAsesi()) {
            return false;
        }
        
        // Load asesmen jika belum
        if (!$this->relationLoaded('asesmen')) {
            $this->load('asesmen');
        }
        
        return $this->asesmen && 
            $this->asesmen->is_collective && 
            !$this->password_changed_at;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTuk(): bool
    {
        return $this->role === 'tuk';
    }

    public function isAsesi(): bool
    {
        return $this->role === 'asesi';
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path ?? false) {
            return asset('storage/' . $this->photo_path);
        }
        return asset('images/default-avatar.png');
    }
}