<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skema extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'fee',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get asesmens for this skema
     */
    public function asesmens()
    {
        return $this->hasMany(Asesmen::class);
    }
}