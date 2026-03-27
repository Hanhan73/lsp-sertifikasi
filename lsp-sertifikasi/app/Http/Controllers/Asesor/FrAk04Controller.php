<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\FrAk04;
use App\Models\Asesmen;
use App\Models\Schedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FrAk04Controller extends Controller
{
    /**
     * Preview / download PDF FR.AK.04 untuk asesor.
     */
    public function previewPdf(Request $request, Schedule $schedule, Asesmen $asesmen)
    {
        $asesor = auth()->user()->asesor;
        abort_if($schedule->asesor_id !== $asesor->id, 403);
        abort_if($asesmen->schedule_id !== $schedule->id, 403);

        $frak04 = $asesmen->frak04;
        abort_if(!$frak04 || $frak04->status !== 'submitted', 404, 'FR.AK.04 belum disubmit.');

        $pdf = Pdf::loadView('pdf.frak04', [
            'frak04'  => $frak04,
            'asesmen' => $asesmen,
        ])->setPaper('A4', 'portrait');

        $filename = 'FR-AK-04_' . str_replace(' ', '_', $asesmen->full_name) . '.pdf';

        return $pdf->stream($filename);
    }
}