<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\AsesorRekening;
use Illuminate\Http\Request;

class AsesorRekeningController extends Controller
{
    // ── Helper: ambil asesor berdasarkan konteks role ──────────────────────

    /**
     * Asesor mengakses rekening milik dirinya sendiri.
     */
    private function asesorSelf(): Asesor
    {
        return auth()->user()->asesor ?? abort(403);
    }

    /**
     * Bendahara mengakses rekening asesor tertentu.
     * Memvalidasi bahwa $asesor memang ada.
     */
    private function asesorForBendahara(Asesor $asesor): Asesor
    {
        return $asesor;
    }

    // ── ASESOR: aksi dari halaman profile ─────────────────────────────────

    public function asesorStore(Request $request)
    {
        $asesor = $this->asesorSelf();
        $data   = $this->validated($request);

        $this->handleUtama($asesor, $data);

        $asesor->rekenings()->create($data);

        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function asesorUpdate(Request $request, AsesorRekening $rekening)
    {
        $asesor = $this->asesorSelf();
        abort_if($rekening->asesor_id !== $asesor->id, 403);

        $data = $this->validated($request);

        $this->handleUtama($asesor, $data, $rekening->id);

        $rekening->update($data);

        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function asesorDestroy(AsesorRekening $rekening)
    {
        $asesor = $this->asesorSelf();
        abort_if($rekening->asesor_id !== $asesor->id, 403);

        $rekening->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }

    // ── BENDAHARA: aksi untuk asesor tertentu ─────────────────────────────

    /**
     * Halaman daftar semua asesor (dengan info rekening).
     */
    public function bendaharaIndex()
    {
        $asesors = Asesor::with('rekenings')
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('bendahara.rekening.index', compact('asesors'));
    }

    /**
     * Halaman rekening per-asesor (bendahara).
     */
    public function bendaharaShow(Asesor $asesor)
    {
        $asesor->load('rekenings');
        return view('bendahara.rekening.show', compact('asesor'));
    }

    public function bendaharaStore(Request $request, Asesor $asesor)
    {
        $data = $this->validated($request);

        $this->handleUtama($asesor, $data);

        $asesor->rekenings()->create($data);

        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function bendaharaUpdate(Request $request, Asesor $asesor, AsesorRekening $rekening)
    {
        abort_if($rekening->asesor_id !== $asesor->id, 403);

        $data = $this->validated($request);

        $this->handleUtama($asesor, $data, $rekening->id);

        $rekening->update($data);

        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function bendaharaDestroy(Asesor $asesor, AsesorRekening $rekening)
    {
        abort_if($rekening->asesor_id !== $asesor->id, 403);

        $rekening->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }

    // ── Private helpers ────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_bank'      => 'required|string|max:100',
            'nomor_rekening' => 'required|string|max:50',
            'nama_pemilik'   => 'required|string|max:255',
            'cabang'         => 'nullable|string|max:150',
            'is_utama'       => 'nullable|boolean',
        ]);
    }

    /**
     * Jika rekening baru/diupdate di-set utama, lepas flag utama dari yang lain.
     */
    private function handleUtama(Asesor $asesor, array &$data, ?int $excludeId = null): void
    {
        $data['is_utama'] = !empty($data['is_utama']);

        if ($data['is_utama']) {
            $query = $asesor->rekenings()->where('is_utama', true);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $query->update(['is_utama' => false]);
        }
    }
}