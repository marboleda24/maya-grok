<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use App\Models\Asset;
use App\Models\Strategy;
use App\Services\AssetValueService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AssetController extends Controller
{
    use AuthorizesRequests;

    public function index($portfolioId)
    {
        $portfolio = Portfolio::findOrFail($portfolioId);
        $assets = Asset::with('operations.asset')->where('portfolio_id', $portfolio->id)->get();
        $strategies = Strategy::all();
    
        return Inertia::render('Assets/Index', [
            'portfolio' => $portfolio,
            'assets' => $assets,
            'strategies' => $strategies,
            'flash' => session('message') ? ['message' => session('message')] : null,
        ]);
    }

    public function store(Request $request, Portfolio $portfolio)
    {
        $this->authorize('update', $portfolio);

        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:50',
            'strategy_id' => 'nullable|exists:strategies,id',
            'comments' => 'nullable|string'
        ]);

        $service = new AssetValueService();
        $currentValue = $service->getCurrentValue($request->symbol);

        if (!$currentValue) {
            return redirect()->back()->withErrors(['symbol' => 'No se pudo obtener el valor actual del símbolo proporcionado.']);
        }

        $asset = $portfolio->assets()->create([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'strategy_id' => $request->strategy_id,
            'current_price' => $currentValue,
            'highest_price_reached' => $currentValue,
            'lowest_price_bought' => $currentValue,
            'monitoring_point' => $currentValue,
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
            'strategy_id' => 'nullable|exists:strategies,id',
            'comments' => 'nullable|string'
        ]);

        $asset->update($request->only('name', 'symbol', 'strategy_id', 'comments'));

        return redirect()->route('assets.index', $asset->portfolio->id)->with('message', 'Activo actualizado con éxito');
    }

    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset->portfolio);
        $portfolioId = $asset->portfolio->id;
        $asset->delete();
        return redirect()->route('portfolios.assets.index', $portfolioId)->with('message', 'Activo eliminado con éxito');
    }

    public function operate(Request $request, Asset $asset)
    {
        \Log::info('Datos recibidos en operate:', $request->all());

        if ($request->has('purchase_price')) {
            // Compra
            $validated = $request->validate([
                'purchase_price' => 'required|numeric|min:0',
                'quantity' => 'required|numeric|min:0',
                'exchange' => 'required|string|max:255',
                'buy_commission' => 'nullable|numeric|min:0',
                'comments' => 'nullable|string',
            ]);

            $operation = $asset->operations()->create([
                'type' => 'buy',
                'purchase_price' => $validated['purchase_price'],
                'quantity' => $validated['quantity'],
                'exchange' => $validated['exchange'],
                'buy_commission' => $validated['buy_commission'] ?? null,
                'comments' => $validated['comments'],
                'user_id' => auth()->id(),
                'status' => 'open',
            ]);
        } elseif ($request->has('sale_price')) {
            // Venta
            $validated = $request->validate([
                'sale_price' => 'required|numeric|min:0',
                'sell_commission' => 'nullable|numeric|min:0',
                'comments' => 'nullable|string',
                'operation_id' => 'required|exists:operations,id',
            ]);

            $operation = $asset->operations()->findOrFail($validated['operation_id']);
            if ($operation->status !== 'open') {
                return redirect()->back()->withErrors(['operation' => 'Esta operación ya está cerrada']);
            }

            $operation->update([
                'sale_price' => $validated['sale_price'],
                'sell_commission' => $validated['sell_commission'] ?? null,
                'comments' => $validated['comments'],
                'status' => 'closed',
                'closed_at' => now(),
                'profitability' => ($validated['sale_price'] - $operation->purchase_price) / $operation->purchase_price,
            ]);
        }

        \Log::info('Operación registrada:', $operation->toArray());

        return redirect()->route('portfolios.assets.index', $asset->portfolio_id)
            ->with('message', 'Operación registrada exitosamente');
    }
}