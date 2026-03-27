<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrAk04;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FrAk04Controller extends Controller
{
    /**
     * Preview / download PDF FR.AK.04 untuk admin.
     */
    public function adminPdf(Request $request, FrAk04 $frak04)
    {
        $frak04->load(['asesmen.skema', 'asesmen.tuk', 'asesmen.schedule.asesor']);

        $pdf = Pdf::loadView('pdf.frak04', [
            'frak04'  => $frak04,
            'asesmen' => $frak04->asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'FR-AK-04_' . str_replace(' ', '_', $frak04->asesmen->full_name) . '.pdf';

        return $request->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }
}