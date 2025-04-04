<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Añadimos el trait

class PortfolioController extends Controller
{
    use AuthorizesRequests; // Incluimos el trait

    //Mostramos el listado de los portafolios que tiene el usuario
    public function index()
    {
        $portfolios = Portfolio::where('user_id', auth()->id())->get();
        \Log::info('Portfolios enviados: ' . json_encode($portfolios));
        if ($portfolios->isEmpty()) {
            $portfolios = [];
        }
        return Inertia::render('Portfolios/Index', [
            'portfolios' => $portfolios
        ]);
    }

    //Almacenamos los portaforlios que va creando un usuario
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive'
        ]);

        $portfolio = auth()->user()->portfolios()->create($request->only('name', 'description', 'status'), ['user_id' => auth()->id()]);
        \Log::info('Portfolio creado: ID ' . $portfolio->id . ' | User ID: ' . $portfolio->user_id);

        return redirect()->route('portfolios.index')->with('message', 'Portafolio creado con éxito');
    }

    //Función para editar los portafolios del usuario
    public function update(Request $request, Portfolio $portfolio)
    {
        $this->authorize('update', $portfolio); // Asegura que solo el dueño pueda editar

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:active,inactive'
        ]);

        $portfolio->update($request->only('name', 'description', 'status'));
        return redirect()->route('portfolios.index')->with('message', 'Portafolio actualizado con éxito');
    }

    public function destroy(Portfolio $portfolio)
    {
        \Log::info('Destroy recibido - Portfolio ID: ' . ($portfolio->id ?? 'null') . ' | User ID: ' . ($portfolio->user_id ?? 'null') . ' | Auth User ID: ' . auth()->id());
        $this->authorize('delete', $portfolio); // Asegura que solo el dueño pueda eliminar

        $portfolio->delete();
        return redirect()->route('portfolios.index')->with('message', 'Portafolio eliminado con éxito');
    }
}
