<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Asset;
use App\Models\Strategy;
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
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string'
        ]);

        $asset = $portfolio->assets()->create($request->only('name', 'symbol', 'description', 'value', 'comments'));
        $asset->base_value = $request->value; // Valor inicial como base
        $asset->max_value = $request->value; // Valor inicial como máximo
        $asset->save();

        return redirect()->route('assets.index', $portfolio->id)->with('message', 'Activo creado con éxito');
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset->portfolio);

        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:50',
            'description' => 'nullable|string',
            'value' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string',
            'buy_threshold' => 'nullable|numeric|min:0',
            'sell_threshold' => 'nullable|numeric|min:0'
        ]);

        $asset->update($request->only('name', 'symbol', 'description', 'value', 'comments'));

        if ($request->buy_threshold && $request->sell_threshold) {
            $asset->strategy()->updateOrCreate(
                ['asset_id' => $asset->id],
                ['buy_threshold' => $request->buy_threshold, 'sell_threshold' => $request->sell_threshold]
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