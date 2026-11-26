<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\Categoria;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $productos = Producto::with(['variantes.imagenes', 'categoria'])
            ->whereHas('variantes', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->orderBy('nombre_producto')
            ->get();

        $productosJson = $productos->map(function ($p) {
            $primeraImagen = $p->variantes->flatMap(fn($v) => $v->imagenes)->first();

            $colores = [];

            foreach ($p->variantes->groupBy('color') as $color => $grupo) {
                if (!$color) continue;

                $colores[] = [
                    'nombre' => $color,
                    'hex'    => $grupo->first()->color_hex ?? '#cccccc',
                ];
            }

            $variantesAgrupadas = $p->variantes
                ->groupBy('color')
                ->map(fn($grupo) => $grupo->map(fn($v) => [
                    'id_variante' => $v->id_variante,
                    'talla'       => $v->talla,
                    'stock'       => (int) $v->stock,
                ])->values())
                ->toArray();

            $imagenesPorColor = [];

            foreach ($p->variantes->groupBy('color') as $color => $grupo) {
                if (!$color) continue;

                $urls = $grupo
                    ->flatMap(fn($v) => $v->imagenes)
                    ->map(fn($i) => url('/api/imagen/' . $i->imagen))
                    ->unique()
                    ->values()
                    ->toArray();

                if (count($urls) > 0) {
                    $imagenesPorColor[$color] = $urls;
                }
            }

            return [
                'id_producto'        => $p->id_producto,
                'nombre'             => ucwords(strtolower($p->nombre_producto)),
                'marca'              => $p->marca,
                'categoria'          => optional($p->categoria)->nombre_categoria,
                'precio'             => (float) ($p->precio_oferta ?? $p->precio),
                'precio_normal'      => (float) $p->precio,
                'precio_oferta'      => $p->precio_oferta ? (float) $p->precio_oferta : null,
                'descuento'          => $p->precio_oferta ? (int) abs($p->descuento ?? 0) : 0,
                'imagen'             => $primeraImagen ? url('/api/imagen/' . $primeraImagen->imagen) : null,
                'colores'            => $colores,
                'variantesAgrupadas' => $variantesAgrupadas,
                'imagenesPorColor'   => $imagenesPorColor,
            ];
        });

        $categoriasFiltro = Categoria::orderBy('nombre_categoria')->get();

        $marcasFiltro = Producto::whereNotNull('marca')
            ->where('marca', '!=', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        return view('admin.ventas.index', compact(
            'productosJson',
            'categoriasFiltro',
            'marcasFiltro'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.id_variante' => 'required|exists:producto_variante,id_variante',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            $detalles = [];

            foreach ($request->items as $item) {
                $variante = ProductoVariante::with('producto')
                    ->lockForUpdate()
                    ->findOrFail($item['id_variante']);

                if ($variante->stock < $item['cantidad']) {
                    throw new \Exception(
                        "Stock insuficiente para {$variante->color} / {$variante->talla}. Disponible: {$variante->stock}"
                    );
                }

                $producto = $variante->producto;

                if (!$producto) {
                    throw new \Exception('El producto de una variante ya no existe.');
                }

                $precio = $producto->precio_oferta ?? $producto->precio;
                $cantidad = $item['cantidad'];
                $subtotal = $precio * $cantidad;

                // Guardar también los datos históricos del producto.
                $detalles[] = [
                    'id_variante'     => $variante->id_variante,

                    'nombre_producto' => $producto->nombre_producto,
                    'talla'           => $variante->talla,
                    'color'           => $variante->color,
                    'sku'             => $variante->sku,

                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal'        => $subtotal,
                ];

                $total += $subtotal;

                $variante->stock -= $cantidad;
                $variante->save();
            }

            $venta = Venta::create([
                'total' => $total,
            ]);

            foreach ($detalles as $detalle) {
                $detalle['id_venta'] = $venta->id_venta;

                VentaDetalle::create($detalle);
            }

            DB::commit();

            return redirect()
                ->route('admin.ventas.index')
                ->with('success', 'Venta registrada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}