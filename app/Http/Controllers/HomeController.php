<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Banner;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check() && (int) Auth::user()->id_rol === 1) {
            return redirect()->route('admin.dashboard');
        }

        $hayFiltros = $request->filled('buscar')
                   || ($request->filled('categoria') && $request->categoria != 'Todo')
                   || $request->has('promocion')
                   || $request->has('nuevo');

        $query = Producto::query()
            ->where('estado_producto', 1)
            ->with(['variantes.imagenes']);

        if ($request->filled('categoria') && $request->categoria != 'Todo') {
            $query->whereHas('categoria', function ($q) use ($request) {
                $q->where('nombre_categoria', $request->categoria);
            });
        }

        if ($request->has('promocion')) {
            $query->enOferta();
        }

        if ($request->has('nuevo')) {
            $query->orderBy('created_at', 'desc')->limit(8);
        }

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);

            $palabras = array_filter(
                array_map(fn($p) => trim($p), explode(' ', $buscar)),
                fn($p) => strlen($p) >= 2
            );

            $query->where(function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $q->where(function ($subQ) use ($palabra) {
                        $subQ->where('nombre_producto', 'like', "%{$palabra}%")
                             ->orWhere('marca', 'like', "%{$palabra}%");
                    });
                }
            });
        }

        if ($hayFiltros) {
            if (!$request->has('nuevo')) {
                $query->orderBy('created_at', 'desc');
            }
            $productos = $query->get();
            $categorias = collect();
        } else {
            $categorias = Categoria::where('estado_categoria', 1)
                ->with(['productos' => function ($q) {
                    $q->where('estado_producto', 1)
                      ->with(['variantes.imagenes'])
                      ->orderBy('created_at', 'desc');
                }])
                ->orderBy('id_categoria', 'asc')
                ->get()
                ->filter(function ($cat) {
                    return $cat->productos->count() > 0;
                });

            $productos = collect();
        }

        $banners = Banner::where('estado', 1)
            ->orderBy('orden', 'asc')
            ->get();

        return view('home.index', compact('productos', 'categorias', 'banners'));
    }

    public function show($id)
    {
        $producto = Producto::with(['variantes.imagenes', 'categoria'])
            ->findOrFail($id);

        $enCarrito = [];
        if (Auth::check()) {
            $carrito = Carrito::where('id_usuario', Auth::id())->first();
            if ($carrito) {
                $enCarrito = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
                    ->pluck('cantidad', 'id_variante')
                    ->toArray();
            }
        }

        return view('producto.detalle', compact('producto', 'enCarrito'));
    }
}