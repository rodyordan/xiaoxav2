<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductoResource;
use App\Http\Resources\VarianteResource;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::activos()
            ->with(['categoria', 'variantes.imagenes']);

        if ($request->filled('categoria')) {
            $cat = $request->categoria;

            if (is_numeric($cat)) {
                $query->where('id_categoria', $cat);
            } else {
                $query->whereHas('categoria', function ($q) use ($cat) {
                    $q->where('nombre_categoria', $cat);
                });
            }
        }

        if ($request->filled('talla')) {
            $query->whereHas('variantes', function ($q) use ($request) {
                $q->where('talla', $request->talla)
                  ->where('stock', '>', 0);
            });
        }

        if ($request->filled('color')) {
            $query->whereHas('variantes', function ($q) use ($request) {
                $q->where('color', $request->color)
                  ->where('stock', '>', 0);
            });
        }

        if ($request->filled('busqueda')) {
            $query->buscar($request->busqueda);
        }

        if ($request->boolean('promocion')) {
            $query->enOferta();
        }

        $esNuevo = $request->boolean('nuevo');

        if ($request->boolean('con_stock', true)) {
            $query->conStock();
        }

        if ($esNuevo) {
            $query->orderBy('created_at', 'desc');
        } else {
            $orden = $request->get('orden', 'created_at');
            $direccion = $request->get('direccion', 'desc');

            $ordenPermitido = ['created_at', 'nombre_producto', 'precio', 'id_producto'];
            if (!in_array($orden, $ordenPermitido)) {
                $orden = 'created_at';
            }
            if (!in_array($direccion, ['asc', 'desc'])) {
                $direccion = 'desc';
            }

            $query->orderBy($orden, $direccion);
        }

        $productos = $query->paginate($request->get('limit', 10));

        return response()->json([
            'success'      => true,
            'data'         => ProductoResource::collection($productos),
            'current_page' => $productos->currentPage(),
            'last_page'    => $productos->lastPage(),
            'total'        => $productos->total(),
        ]);
    }

    public function show($id)
    {
        $producto = Producto::activos()
            ->with(['categoria', 'variantes.imagenes'])
            ->find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductoResource($producto)
        ]);
    }

    public function variantes($id)
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $variantes = $producto->variantes()
            ->with('imagenes')
            ->where('stock', '>', 0)
            ->get();

        return response()->json([
            'success' => true,
            'data' => VarianteResource::collection($variantes)
        ]);
    }

    public function ofertas(Request $request)
    {
        try {
            $query = Producto::activos()
                ->enOferta()
                ->with(['categoria', 'variantes.imagenes'])
                ->conStock();

            $productos = $query->paginate($request->get('limit', 10));

            return response()->json([
                'success'      => true,
                'data'         => ProductoResource::collection($productos),
                'current_page' => $productos->currentPage(),
                'last_page'    => $productos->lastPage(),
                'total'        => $productos->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en ofertas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar productos en oferta'
            ], 500);
        }
    }

    public function buscar(Request $request)
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2'
            ]);

            $query = Producto::activos()
                ->buscar($request->q)
                ->with(['categoria', 'variantes.imagenes'])
                ->conStock();

            $productos = $query->paginate($request->get('limit', 10));

            return response()->json([
                'success'      => true,
                'data'         => ProductoResource::collection($productos),
                'current_page' => $productos->currentPage(),
                'last_page'    => $productos->lastPage(),
                'total'        => $productos->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en buscar: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }

    public function porTalla(Request $request, $talla)
    {
        try {
            $query = Producto::activos()
                ->whereHas('variantes', function ($q) use ($talla) {
                    $q->where('talla', $talla)
                      ->where('stock', '>', 0);
                })
                ->with(['categoria', 'variantes.imagenes']);

            $productos = $query->paginate($request->get('limit', 10));

            return response()->json([
                'success'      => true,
                'data'         => ProductoResource::collection($productos),
                'current_page' => $productos->currentPage(),
                'last_page'    => $productos->lastPage(),
                'total'        => $productos->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en porTalla: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar por talla'
            ], 500);
        }
    }

    public function porColor(Request $request, $color)
    {
        try {
            $query = Producto::activos()
                ->whereHas('variantes', function ($q) use ($color) {
                    $q->where('color', $color)
                      ->where('stock', '>', 0);
                })
                ->with(['categoria', 'variantes.imagenes']);

            $productos = $query->paginate($request->get('limit', 10));

            return response()->json([
                'success'      => true,
                'data'         => ProductoResource::collection($productos),
                'current_page' => $productos->currentPage(),
                'last_page'    => $productos->lastPage(),
                'total'        => $productos->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en porColor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al filtrar por color'
            ], 500);
        }
    }
}