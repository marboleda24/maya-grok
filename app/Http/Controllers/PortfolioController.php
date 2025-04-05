<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function index()
    {
        $portfolios = Portfolio::where('user_id', auth()->id())->get();
        return Inertia::render('Portfolios/Index', [
            'portfolios' => $portfolios,
            'flash' => session('message') ? ['message' => session('message')] : null
        ]);
    }

    public function create()
    {
        return Inertia::render('Portfolios/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        Portfolio::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status
        ]);

        return redirect()->route('portfolios.index')->with('message', 'Portafolio creado con éxito');
    }

    public function edit(Portfolio $portfolio)
    {
        // Opcional: Autorizar que el usuario sea el dueño
        if ($portfolio->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar este portafolio.');
        }
        return Inertia::render('Portfolios/Edit', [
            'portfolio' => $portfolio
        ]);
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        if ($portfolio->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para actualizar este portafolio.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive'
        ]);

        $portfolio->update($request->all());

        return redirect()->route('portfolios.index')->with('message', 'Portafolio actualizado con éxito');
    }

    public function destroy(Portfolio $portfolio)
    {
        if ($portfolio->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para eliminar este portafolio.');
        }

        $portfolio->delete();
        return redirect()->route('portfolios.index')->with('message', 'Portafolio eliminado con éxito');
    }
}