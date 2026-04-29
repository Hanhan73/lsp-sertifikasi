<?php

namespace App\Http\Controllers\Asesor;

use App\Http\Controllers\Controller;
use App\Models\BiayaOperasional;

class BiayaOperasionalAsesorController extends Controller
{
    public function index()
    {
        $asesor = auth()->user()->asesor;

        if (!$asesor) {
            abort(403);
        }

        $biayaList = BiayaOperasional::where('asesor_id', $asesor->id)
            ->orderByDesc('tanggal')
            ->paginate(15);

        $total = BiayaOperasional::where('asesor_id', $asesor->id)->sum('total');

        return view('asesor.biaya-operasional.index', compact('biayaList', 'total', 'asesor'));
    }
}