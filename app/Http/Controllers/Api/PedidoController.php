<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PedidoController extends Controller
{
    /**
     * Obtener todos los pedidos del usuario autenticado.
     */
    public function misPedidos(Request $request)
    {
        $user = Auth::user();

        $pedidos = Pedido::where('id_usuario', $user->id_usuario)
            ->with([
                'detalles',
                'tipoEntrega',
                'cupon',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pedido) {
                return $this->formatearPedido($pedido);
            });

        return response()->json([
            'success' => true,
            'data'    => $pedidos,
        ]);
    }

    /**
     * Obtener un pedido específico del usuario.
     */
    public function show($id)
    {
        $user = Auth::user();

        $pedido = Pedido::where('id_usuario', $user->id_usuario)
            ->with([
                'detalles',
                'tipoEntrega',
                'cupon',
            ])
            ->find($id);

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatearPedido($pedido),
        ]);
    }

    /**
     * Descargar el archivo adjunto del pedido.
     */
    public function descargarArchivo($id)
    {
        $user = Auth::user();

        $pedido = Pedido::where('id_usuario', $user->id_usuario)
            ->find($id);

        if (!$pedido) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado',
            ], 404);
        }

        if (!$pedido->archivo_adjunto) {
            return response()->json([
                'success' => false,
                'message' => 'Este pedido no tiene archivo adjunto',
            ], 404);
        }

        if (!Storage::disk('local')->exists($pedido->archivo_adjunto)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo no existe en el servidor',
            ], 404);
        }

        $extension = pathinfo($pedido->archivo_adjunto, PATHINFO_EXTENSION);
        $nombreDescarga = 'guia-pedido-' . $pedido->numero_pedido . '.' . $extension;

        return Storage::disk('local')->download(
            $pedido->archivo_adjunto,
            $nombreDescarga
        );
    }

    /**
     * Formatear un pedido para la respuesta JSON.
     */
    private function formatearPedido($pedido): array
    {
        return [
            'id_pedido'          => $pedido->id_pedido,
            'numero_pedido'      => $pedido->numero_pedido,
            'fecha_pedido'       => $pedido->created_at?->format('Y-m-d H:i:s'),

            // Económico
            'subtotal'           => (float) $pedido->subtotal,
            'descuento'          => (float) $pedido->descuento,
            'total_pedido'       => (float) $pedido->total_pedido,

            // Estado
            'estado_pedido'      => $pedido->estado_pedido,

            // Tipo de entrega
            'id_tipo_entrega'    => $pedido->id_tipo_entrega,
            'tipo_entrega'       => $pedido->tipoEntrega?->nombre_tipo_entrega,

            // Ubicación del cliente
            'departamento'       => $pedido->departamento,
            'provincia'          => $pedido->provincia,
            'distrito'            => $pedido->distrito,

            // Información de entrega
            'direccion_entrega'  => $pedido->direccion_entrega,
            'tiempo_entrega'     => $pedido->tiempo_entrega,

            // Archivo adjunto
            'tiene_archivo'      => !empty($pedido->archivo_adjunto),
            'archivo_url'        => !empty($pedido->archivo_adjunto)
                ? url("/api/pedidos/{$pedido->id_pedido}/descargar")
                : null,

            // Cupón
            'cupon' => $pedido->cupon ? [
                'codigo' => $pedido->cupon->codigo_cupon,
                'monto'  => (float) $pedido->cupon->monto_cupon,
            ] : null,

            // Detalles históricos
            'detalles' => $pedido->detalles->map(function ($detalle) {
                return [
                    'id_detalle'      => $detalle->id_detalle_pedido,
                    'id_variante'     => $detalle->id_variante,
                    'cantidad'        => $detalle->cantidad,
                    'precio_unitario' => (float) $detalle->precio_unitario,
                    'subtotal'        => (float) $detalle->subtotal,

                    // Datos del producto guardados en el pedido
                    'producto' => [
                        'id'       => null,
                        'nombre'   => $detalle->nombre_producto,
                        'marca'    => null,
                        'imagen'   => null,
                    ],

                    // Datos de la variante guardados en el pedido
                    'color' => $detalle->color,
                    'talla' => $detalle->talla,
                    'sku'   => $detalle->sku,
                ];
            }),
        ];
    }
}
