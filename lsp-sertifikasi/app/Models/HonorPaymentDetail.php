<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HonorPaymentDetail extends Model
{
    protected $fillable = [
        'honor_payment_id',
        'schedule_id',
        'jumlah_asesi',
        'honor_per_asesi',
        'subtotal',
    ];

    public function honorPayment()
    {
        return $this->belongsTo(HonorPayment::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class)->with(['skema', 'tuk']);
    }
}