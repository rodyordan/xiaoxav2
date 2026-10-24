<?php
// app/Http/Controllers/Api/CarritoController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarritoResource;
use App\Models\Cupon;
use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Models\ProductoVariante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarritoController extends Controller
{
    /**
     * Relations que se cargan SIEMPRE con el carrito.
     * Incluye variantes.imagenes del producto para poder sacar la imagen principal.
     */
    private function relations(): array
    {
        return [
            'detalles.variante.producto.variantes.imagenes',
            'detalles.variante.producto.categoria',
        ];
    }

    public function obtener(Request $request)
    {
        $carrito = Carrito::where('id_usuario', $request->id_usuario)
            ->with($this->relations())
            ->first();

        return response()->json([
            'success' => true,
            'data'    => $carrito ? new CarritoResource($carrito) : null,
        ]);
    }

    public function crear(Request $request)
    {
        $carrito = Carrito::create([
            'id_usuario' => $request->id_usuario,
        ]);

        $carrito->load($this->relations());

        return response()->json([
            'success' => true,
            'data'    => new CarritoResource($carrito),
        ]);
    }

    public function agregar(Request $request)
    {
        $carrito = Carrito::where('id_usuario', $request->id_usuario)->first();

        if (!$carrito) {
            $carrito = Carrito::create(['id_usuario' => $request->id_usuario]);
        }

        $variante = ProductoVariante::find($request->id_variante);
        if (!$variante) {
            return response()->json([
                'success' => false,
                'message' => 'Variante no encontrada',
            ], 404);
        }

        $detalle = DetalleCarrito::updateOrCreate(
            [
                'id_carrito'  => $carrito->id_carrito,
                'id_variante' => $request->id_variante,
            ],
            [
                'cantidad' => $request->cantidad,
            ]
        );

        $carrito = Carrito::with($this->relations())
            ->find($carrito->id_carrito);

        return response()->json([
            'success' => true,
            'data'    => new CarritoResource($carrito),
        ]);
    }

    public function actualizar(Request $request)
    {
        $detalle = DetalleCarrito::find($request->id_detalle_carrito);

        if (!$detalle) {
            return response()->json([
                'success' => false,
                'message' => 'Detalle no encontrado',
            ], 404);
        }

        $detalle->update(['cantidad' => $request->cantidad]);

        $carrito = Carrito::with($this->relations())
            ->find($detalle->id_carrito);

        return response()->json([
            'success' => true,
            'data'    => new CarritoResource($carrito),
        ]);
    }

    public function eliminar(Request $request)
    {
        $detalle = DetalleCarrito::find($request->id_detalle_carrito);

        if (!$detalle) {
            return response()->json([
                'success' => false,
                'message' => 'Detalle no encontrado',
            ], 404);
        }

        $idCarrito = $detalle->id_carrito;
        $detalle->delete();

        $carrito = Carrito::with($this->relations())
            ->find($idCarrito);

        return response()->json([
            'success' => true,
            'data'    => $carrito ? new CarritoResource($carrito) : null,
        ]);
    }

    public function limpiar(Request $request)
    {
        $carrito = Carrito::where('id_usuario', $request->id_usuario)->first();

        if ($carrito) {
            $carrito->detalles()->delete();

            $carrito = Carrito::with($this->relations())
                ->find($carrito->id_carrito);
        }

        return response()->json([
            'success' => true,
            'data'    => $carrito ? new CarritoResource($carrito) : null,
            'message' => 'Carrito limpiado',
        ]);
    }

    public function total($idCarrito)
    {
        $carrito = Carrito::with($this->relations())->find($idCarrito);

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado',
            ], 404);
        }

        $total = 0;
        foreach ($carrito->detalles as $detalle) {
            $precio = $detalle->variante->producto->precio_oferta
                   ?? $detalle->variante->producto->precio;
            $total += $precio * $detalle->cantidad;
        }

        return response()->json([
            'success' => true,
            'total'   => $total,
        ]);
    }

    public function verificarStock($idVariante, Request $request)
    {
        $variante = ProductoVariante::find($idVariante);

        if (!$variante) {
            return response()->json([
                'success'    => false,
                'disponible' => false,
            ], 404);
        }

        $disponible = $variante->stock >= ($request->cantidad ?? 1);

        return response()->json([
            'success'    => true,
            'disponible' => $disponible,
            'stock'      => $variante->stock,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // CUPONES
    // ══════════════════════════════════════════════════════════

    /**
     * Lista los cupones disponibles.
     * - Si el usuario está logueado: solo los que NO ha usado
     * - Si NO está logueado: todos los activos y vigentes
     */
    public function cuponesDisponibles()
    {
        $idUsuario = Auth::id(); // null si no está logueado

        $query = Cupon::where('estado_cupon', 1)
            ->where('fecha_vencimiento', '>=', now());

        // Solo excluir los usados si hay usuario logueado
        if ($idUsuario) {
            $query->whereDoesntHave('usos', function ($q) use ($idUsuario) {
                $q->where('id_usuario', $idUsuario);
            });
        }

        $cupones = $query
            ->orderBy('fecha_vencimiento', 'asc')
            ->get()
            ->map(function ($cupon) {
                return [
                    'id_cupon'          => $cupon->id_cupon,
                    'codigo'            => $cupon->codigo_cupon,
                    'monto'             => (float) $cupon->monto_cupon,
                    'monto_minimo'      => (float) $cupon->monto_compra_minima,
                    'fecha_vencimiento' => $cupon->fecha_vencimiento->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $cupones,
        ]);
    }

    public function aplicarCupon(Request $request)
    {
        $request->validate([
            'codigo_cupon' => 'required|string|max:50',
        ]);

        $idUsuario = Auth::id();
        $codigo    = strtoupper(trim($request->codigo_cupon));

        $cupon = Cupon::where('codigo_cupon', $codigo)->first();

        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'El cupón no existe.',
            ], 404);
        }

        $carrito = Carrito::with('detalles.variante.producto')
            ->where('id_usuario', $idUsuario)
            ->first();

        if (!$carrito || $carrito->detalles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'El carrito está vacío.',
            ], 400);
        }

        $subtotal = 0;
        foreach ($carrito->detalles as $detalle) {
            $producto  = $detalle->variante->producto;
            $precio    = $producto->precio_oferta ?? $producto->precio;
            $subtotal += $precio * $detalle->cantidad;
        }

        if (!$cupon->esValidoPara($idUsuario, $subtotal)) {
            if (!$cupon->estado_cupon) {
                $mensaje = 'El cupón está inactivo.';
            } elseif ($cupon->fecha_vencimiento < now()) {
                $mensaje = 'El cupón está vencido.';
            } elseif ($subtotal < $cupon->monto_compra_minima) {
                $mensaje = 'El monto mínimo es S/ '
                    . number_format($cupon->monto_compra_minima, 2);
            } elseif ($cupon->yaFueUsadoPor($idUsuario)) {
                $mensaje = 'Ya usaste este cupón.';
            } else {
                $mensaje = 'El cupón no es válido.';
            }

            return response()->json([
                'success' => false,
                'message' => $mensaje,
            ], 400);
        }

        session([
            'cupon_id'        => $cupon->id_cupon,
            'cupon_codigo'    => $cupon->codigo_cupon,
            'cupon_descuento' => (float) $cupon->monto_cupon,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Cupón aplicado correctamente.',
            'id_cupon'    => $cupon->id_cupon,
            'codigo'      => $cupon->codigo_cupon,
            'descuento'   => (float) $cupon->monto_cupon,
            'subtotal'    => (float) $subtotal,
            'nuevo_total' => (float) max(0, $subtotal - $cupon->monto_cupon),
        ]);
    }

    public function quitarCupon()
    {
        session()->forget(['cupon_id', 'cupon_codigo', 'cupon_descuento']);

        return response()->json([
            'success' => true,
            'message' => 'Cupón removido.',
        ]);
    }
}