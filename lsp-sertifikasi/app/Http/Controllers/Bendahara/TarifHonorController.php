<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Skema;
use App\Models\SkemaHonorTier;
use Illuminate\Http\Request;

class TarifHonorController extends Controller
{
    public function index()
    {
        $skemas = Skema::where('is_active', true)
            ->with(['honorTiers' => fn($q) => $q->orderBy('amount')])
            ->orderBy('code')
            ->get();

        return view('bendahara.tarif-honor.index', compact('skemas'));
    }

    /**
     * AJAX — ambil tiers milik satu skema (untuk dropdown di form honor).
     */
    public function tiersForSkema(Skema $skema)
    {
        return response()->json(
            $skema->honorTiers()->orderBy('amount')->get(['id', 'label', 'amount', 'is_default'])
        );
    }

    public function store(Request $request, Skema $skema)
    {
        $request->validate([
            'label'      => 'required|string|max:100',
            'amount'     => 'required|integer|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = !empty($request->is_default);

        if ($isDefault) {
            $skema->honorTiers()->update(['is_default' => false]);
        }

        $tier = $skema->honorTiers()->create([
            'label'      => $request->label,
            'amount'     => (int) $request->amount,
            'is_default' => $isDefault,
        ]);

        // Sync honor_per_asesi di skema kalau tier ini default
        if ($isDefault) {
            $skema->update(['honor_per_asesi' => $tier->amount]);
        }

        return response()->json(['success' => true, 'tier' => $tier]);
    }

    public function update(Request $request, Skema $skema, SkemaHonorTier $tier)
    {
        abort_if($tier->skema_id !== $skema->id, 403);

        $request->validate([
            'label'      => 'required|string|max:100',
            'amount'     => 'required|integer|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        $isDefault = !empty($request->is_default);

        if ($isDefault) {
            $skema->honorTiers()->where('id', '!=', $tier->id)->update(['is_default' => false]);
        }

        $tier->update([
            'label'      => $request->label,
            'amount'     => (int) $request->amount,
            'is_default' => $isDefault,
        ]);

        if ($isDefault) {
            $skema->update(['honor_per_asesi' => $tier->amount]);
        }

        return response()->json(['success' => true, 'tier' => $tier->fresh()]);
    }

    public function destroy(Skema $skema, SkemaHonorTier $tier)
    {
        abort_if($tier->skema_id !== $skema->id, 403);
        $tier->delete();
        return response()->json(['success' => true]);
    }
}