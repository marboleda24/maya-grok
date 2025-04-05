<?php

namespace App\Http\Controllers;

use App\Models\Strategy;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StrategyController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $strategies = Strategy::all();
        return Inertia::render('Strategies/Index', [
            'strategies' => $strategies
        ]);
    }

    public function create()
    {
        return Inertia::render('Strategies/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'buy_threshold' => 'required|numeric|min:0|max:1',
            'sell_threshold' => 'required|numeric|min:0',
            'techo_threshold' => 'required|numeric|min:0|max:1',
            'minimum_open_operations' => 'required|integer|min:1',
            'comments' => 'nullable|string'
        ]);

        Strategy::create($request->all());

        return redirect()->route('strategies.index')->with('message', 'Estrategia creada con éxito');
    }

    public function edit(Strategy $strategy)
    {
        return Inertia::render('Strategies/Edit', [
            'strategy' => $strategy
        ]);
    }

    public function update(Request $request, Strategy $strategy)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'buy_threshold' => 'required|numeric|min:0|max:1',
            'sell_threshold' => 'required|numeric|min:0',
            'techo_threshold' => 'required|numeric|min:0|max:1',
            'minimum_open_operations' => 'required|integer|min:1',
            'comments' => 'nullable|string'
        ]);

        $strategy->update($request->all());

        return redirect()->route('strategies.index')->with('message', 'Estrategia actualizada con éxito');
    }

    public function destroy(Strategy $strategy)
    {
        $strategy->delete();
        return redirect()->route('strategies.index')->with('message', 'Estrategia eliminada con éxito');
    }
}