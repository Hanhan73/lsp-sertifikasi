<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo_path',
        'password_changed_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_changed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is TUK
     */
    public function isTuk(): bool
    {
        return $this->role === 'tuk';
    }

    /**
     * Check if user is Asesi
     */
    public function isAsesi(): bool
    {
        return $this->role === 'asesi';
    }

    /**
     * Get TUK associated with this user
     */
    public function tuk()
    {
        return $this->hasOne(Tuk::class);
    }

    /**
     * Get asesmens for this user (asesi)
     */
    public function asesmens()
    {
        return $this->hasMany(Asesmen::class);
    }

    /**
     * Get asesmens registered by this user (TUK)
     */
    public function registeredAsesmens()
    {
        return $this->hasMany(Asesmen::class, 'registered_by');
    }

    /**
     * Get photo URL
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Check if this is first login (for collective registration)
     */
    public function isFirstLogin(): bool
    {
        // Only for asesi who were registered collectively (has registered_by)
        if (!$this->isAsesi()) {
            return false;
        }
        
        // Check if user has asesmen with collective registration
        $asesmen = $this->asesmens()->first();
        
        return $asesmen && 
            $asesmen->is_collective && 
            !$this->password_changed_at;
    }
}