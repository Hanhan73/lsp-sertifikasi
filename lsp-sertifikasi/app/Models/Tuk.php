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
        'treasurer_name', // ✅ NEW
        'staff_name',
        'logo_path',
        'sk_document_path', // ✅ NEW
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
     * Get schedules for this TUK
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
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

    /**
     * ✅ NEW: Get SK document URL
     */
    public function getSkDocumentUrlAttribute()
    {
        if ($this->sk_document_path) {
            return asset('storage/' . $this->sk_document_path);
        }
        return null;
    }

    /**
     * ✅ NEW: Check if TUK has SK document
     */
    public function hasSkDocument()
    {
        return !empty($this->sk_document_path);
    }
}