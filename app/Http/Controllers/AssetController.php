<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Asset;
use App\Services\AssetValueService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AssetController extends Controller
{
    use AuthorizesRequests;

    public function index(Portfolio $portfolio)
    {
        $this->authorize('view', $portfolio);
        $assets = $portfolio->assets()->with('strategy')->get();
        return Inertia::render('Assets/Index', [
            'portfolio' => $portfolio,
            'assets' => $assets
        ]);
    }

    public function store(Request $request, Portfolio $portfolio)
    {
        $this->authorize('update', $portfolio);

        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:50',
            'comments' => 'nullable|string'
        ]);

        // Consultar valor actual desde la API
        $service = new AssetValueService();
        $currentValue = $service->getCurrentValue($request->symbol);

        if (!$currentValue) {
            return redirect()->back()->withErrors(['symbol' => 'No se pudo obtener el valor actual del símbolo proporcionado.']);
        }

        // Crear el activo con valores iniciales
        $asset = $portfolio->assets()->create([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'current_price' => $currentValue,        // VALOR ACTUAL
            'highest_price_reached' => $currentValue, // TECHO
            'lowest_price_bought' => $currentValue,  // PISO
            'monitoring_point' => $currentValue,     // MONITOREO
            'comments' => $request->comments
        ]);

        return redirect()->route('assets.index', $portfolio->id)->with('message', 'Activo creado con éxito');
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset->portfolio);

        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:50',
            'comments' => 'nullable|string',
            'buy_threshold' => 'nullable|numeric|min:0|max:1',
            'sell_threshold' => 'nullable|numeric|min:0',
            'techo_threshold' => 'nullable|numeric|min:0|max:1'
        ]);

        $asset->update($request->only('name', 'symbol', 'comments'));

        if ($request->buy_threshold || $request->sell_threshold || $request->techo_threshold) {
            $asset->strategy()->updateOrCreate(
                ['asset_id' => $asset->id],
                [
                    'buy_threshold' => $request->buy_threshold ?? 0.05,
                    'sell_threshold' => $request->sell_threshold ?? 1.0,
                    'techo_threshold' => $request->techo_threshold ?? 0.10
                ]
            );
        }

        return redirect()->route('assets.index', $asset->portfolio->id)->with('message', 'Activo actualizado con éxito');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset->portfolio);
        $portfolioId = $asset->portfolio->id;
        $asset->delete();
        return redirect()->route('assets.index', $portfolioId)->with('message', 'Activo eliminado con éxito');
    }
}