<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'asesmen_id',
        'certificate_number',
        'issued_date',
        'valid_until',
        'pdf_path',
        'qr_code_path',
        'generated_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Get the asesmen for this certificate
     */
    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class);
    }

    /**
     * Get the generator (admin)
     */
    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Generate certificate number
     */
    public static function generateCertificateNumber()
    {
        $year = date('Y');
        $month = date('m');
        $count = self::whereYear('issued_date', $year)
                    ->whereMonth('issued_date', $month)
                    ->count() + 1;
        
        return sprintf('CERT/%s/%s/%04d', $year, $month, $count);
    }
}