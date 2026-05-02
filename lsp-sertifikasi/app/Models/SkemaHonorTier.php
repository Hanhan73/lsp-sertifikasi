<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkemaHonorTier extends Model
{
    protected $fillable = ['skema_id', 'label', 'amount', 'is_default'];

    protected $casts = [
        'amount'     => 'integer',
        'is_default' => 'boolean',
    ];

    public function skema()
    {
        return $this->belongsTo(Skema::class);
    }
}