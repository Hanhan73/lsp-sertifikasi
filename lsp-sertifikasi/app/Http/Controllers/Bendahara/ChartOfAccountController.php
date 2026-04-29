<?php
// app/Http/Controllers/Bendahara/ChartOfAccountController.php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $tipe  = $request->get('tipe');
        $query = ChartOfAccount::query()->orderBy('urutan')->orderBy('kode');

        if ($tipe) {
            $query->where('tipe', $tipe);
        }

        $akuns     = $query->get();
        $tipeList  = ChartOfAccount::tipeList();
        $grouped   = $akuns->groupBy('tipe');

        return view('bendahara.coa.index', compact('akuns', 'tipeList', 'grouped', 'tipe'));
    }

    public function create()
    {
        $tipeList    = ChartOfAccount::tipeList();
        $subTipeList = ChartOfAccount::subTipeList();

        // Auto-suggest kode berikutnya
        $kodeSuggest = $this->nextKode(request('tipe', 'aset'));

        return view('bendahara.coa.create', compact('tipeList', 'subTipeList', 'kodeSuggest'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'       => 'required|string|max:20|unique:chart_of_accounts,kode',
            'nama'       => 'required|string|max:255',
            'tipe'       => 'required|in:aset,kewajiban,ekuitas,pendapatan,beban',
            'sub_tipe'   => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:500',
            'urutan'     => 'nullable|integer|min:0',
            'is_active'  => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_system'] = false;
        $validated['urutan']    = $validated['urutan'] ?? 99;

        ChartOfAccount::create($validated);

        return redirect()->route('bendahara.coa.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(ChartOfAccount $coa)
    {
        $tipeList    = ChartOfAccount::tipeList();
        $subTipeList = ChartOfAccount::subTipeList();

        return view('bendahara.coa.edit', compact('coa', 'tipeList', 'subTipeList'));
    }

    public function update(Request $request, ChartOfAccount $coa)
    {
        $validated = $request->validate([
            'kode'       => 'required|string|max:20|unique:chart_of_accounts,kode,' . $coa->id,
            'nama'       => 'required|string|max:255',
            'tipe'       => 'required|in:aset,kewajiban,ekuitas,pendapatan,beban',
            'sub_tipe'   => 'nullable|string|max:50',
            'keterangan' => 'nullable|string|max:500',
            'urutan'     => 'nullable|integer|min:0',
            'is_active'  => 'boolean',
        ]);

        // Akun sistem tidak boleh ganti tipe & kode
        if ($coa->is_system) {
            unset($validated['kode'], $validated['tipe']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $coa->update($validated);

        return redirect()->route('bendahara.coa.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(ChartOfAccount $coa)
    {
        if ($coa->is_system) {
            return back()->with('error', 'Akun sistem tidak dapat dihapus.');
        }

        $coa->delete();

        return redirect()->route('bendahara.coa.index')
            ->with('success', 'Akun berhasil dihapus.');
    }

    // ── Helper: generate kode berikutnya per tipe ─────────────────────────
    private function nextKode(string $tipe): string
    {
        $prefix = match ($tipe) {
            'aset'       => '1',
            'kewajiban'  => '2',
            'ekuitas'    => '3',
            'pendapatan' => '4',
            'beban'      => '5',
            default      => '9',
        };

        $last = ChartOfAccount::where('kode', 'like', $prefix . '-%')
            ->orderByDesc('kode')
            ->value('kode');

        if (!$last) return $prefix . '-001';

        $num = (int) substr($last, 2) + 1;
        return $prefix . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
