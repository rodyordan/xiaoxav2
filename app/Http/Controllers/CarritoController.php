<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Models\ProductoVariante;
use App\Models\ProductoVarianteImagen;

class CarritoController extends Controller
{
    private function obtenerImagen($variante)
    {
        $primeraImagen = $variante->imagenes->first();
        if ($primeraImagen && !empty($primeraImagen->imagen)) {
            return $primeraImagen->imagen;
        }

        $imagenPorColor = ProductoVarianteImagen::whereHas('variante', function ($q) use ($variante) {
                $q->where('id_producto', $variante->producto->id_producto)
                  ->where('color', $variante->color);
            })
            ->whereNotNull('imagen')
            ->first();

        if ($imagenPorColor && !empty($imagenPorColor->imagen)) {
            return $imagenPorColor->imagen;
        }

        return $variante->producto->imagen ?? null;
    }

    private function obtenerDescuento($producto)
    {
        if (!$producto->precio_oferta || $producto->precio_oferta <= 0) {
            return null;
        }
        if (!$producto->precio || $producto->precio <= 0) {
            return null;
        }
        return round((1 - ($producto->precio_oferta / $producto->precio)) * 100);
    }

    public function add(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $id_variante = $request->id_variante;
        $cantidad    = max(1, (int) ($request->cantidad ?? 1));

        $variante = ProductoVariante::with(['producto', 'imagenes'])->findOrFail($id_variante);

        if ($variante->stock <= 0) {
            return redirect()->route('carrito.index');
        }

        $carrito = Carrito::firstOrCreate([
            'id_usuario' => Auth::id()
        ]);

        $detalle = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
            ->where('id_variante', $id_variante)
            ->first();

        $yaEnCarrito = $detalle ? $detalle->cantidad : 0;
        $nuevaCantidad = min($yaEnCarrito + $cantidad, $variante->stock);

        if ($nuevaCantidad <= $yaEnCarrito && $detalle) {
            return redirect()->route('carrito.index');
        }

        if ($detalle) {
            $detalle->cantidad = $nuevaCantidad;
            $detalle->save();
        } else {
            DetalleCarrito::create([
                'id_carrito'  => $carrito->id_carrito,
                'id_variante' => $id_variante,
                'cantidad'    => $nuevaCantidad
            ]);
        }

        return redirect()->route('carrito.index');
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $items = [];
        $total = 0;

        $carrito = Carrito::with(['detalles.variante.producto', 'detalles.variante.imagenes'])
            ->where('id_usuario', Auth::id())
            ->first();

        if ($carrito) {
            foreach ($carrito->detalles as $detalle) {

                $variante = $detalle->variante;
                $producto = $variante->producto;

                $items[$detalle->id_variante] = [
                    "id_variante"   => $detalle->id_variante,
                    "id_producto"   => $producto->id_producto,
                    "nombre"        => $producto->nombre_producto,
                    "cantidad"      => $detalle->cantidad,
                    "precio"        => $producto->precio_oferta ?? $producto->precio,
                    "precio_normal" => $producto->precio,
                    "precio_oferta" => $producto->precio_oferta,
                    "descuento"     => $this->obtenerDescuento($producto),
                    "imagen"        => $this->obtenerImagen($variante),
                    "talla"         => $variante->talla,
                    "color"         => $variante->color,
                    "stock"         => $variante->stock,
                ];

                $total += $items[$detalle->id_variante]['precio'] * $detalle->cantidad;
            }
        }

        return view('carrito.index', compact('items', 'total'));
    }

    public function aumentar($id_variante)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $variante = ProductoVariante::findOrFail($id_variante);
        $carrito  = Carrito::where('id_usuario', Auth::id())->first();

        if ($carrito) {
            $detalle = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
                ->where('id_variante', $id_variante)
                ->first();

            if ($detalle && $detalle->cantidad < $variante->stock) {
                $detalle->cantidad++;
                $detalle->save();
            }
        }

        return redirect()->route('carrito.index');
    }

    public function disminuir($id_variante)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $carrito = Carrito::where('id_usuario', Auth::id())->first();

        if ($carrito) {
            $detalle = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
                ->where('id_variante', $id_variante)
                ->first();

            if ($detalle) {
                if ($detalle->cantidad > 1) {
                    $detalle->cantidad--;
                    $detalle->save();
                } else {
                    $detalle->delete();
                }
            }
        }

        return redirect()->route('carrito.index');
    }

    public function eliminar($id_variante)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $carrito = Carrito::where('id_usuario', Auth::id())->first();

        if ($carrito) {
            DetalleCarrito::where('id_carrito', $carrito->id_carrito)
                ->where('id_variante', $id_variante)
                ->delete();
        }

        return redirect()->route('carrito.index');
    }
}