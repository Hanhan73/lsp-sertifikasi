<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'email',
        'phone',
        'manager_name',
        'staff_name',
        'logo_path',
        'user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user (account) for this TUK
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get asesmens for this TUK
     */
    public function asesmens()
    {
        return $this->hasMany(Asesmen::class);
    }

    /**
     * Get logo URL
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return asset('images/default-logo.png'); // Default logo
    }
}