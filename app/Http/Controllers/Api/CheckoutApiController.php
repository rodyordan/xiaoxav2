<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\Cupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class CheckoutApiController extends Controller
{
    public function crearPreferencia(Request $request)
    {
        $request->validate([
            'id_tipo_entrega'   => 'required|integer|in:1,2',
            'id_tipo_documento' => 'required|integer|exists:tipo_documento,id_tipo_documento',
            'numero_documento'  => 'required|string|max:20',
            'telefono'          => 'required|string|max:20',
            'departamento'      => 'nullable|string|max:100',
            'provincia'         => 'nullable|string|max:100',
            'distrito'          => 'nullable|string|max:100',
            'codigo_cupon'      => 'nullable|string|max:50',
        ]);

        $usuario = Auth::user();

        if ((int) $request->id_tipo_entrega === 2) {
            if (empty($request->departamento) || empty($request->provincia) || empty($request->distrito)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes completar departamento, provincia y distrito para el envío.',
                ], 422);
            }
        }

        $usuario->update([
            'id_tipo_documento' => $request->id_tipo_documento,
            'numero_documento'  => $request->numero_documento,
            'telefono'          => $request->telefono,
        ]);

        $carrito = Carrito::with('detalles.variante.producto')
            ->where('id_usuario', $usuario->id_usuario)
            ->first();

        if (!$carrito || $carrito->detalles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tu carrito está vacío.',
            ], 422);
        }

        foreach ($carrito->detalles as $detalle) {
            if ($detalle->variante->stock < $detalle->cantidad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente para: ' . $detalle->variante->producto->nombre_producto,
                ], 422);
            }
        }

        $subtotal = 0;
        $items = [];
        foreach ($carrito->detalles as $detalle) {
            $producto = $detalle->variante->producto;
            $precio = round((float) ($producto->precio_oferta ?? $producto->precio), 2);
            $subtotal += $precio * $detalle->cantidad;

            $items[] = [
                // SE CAMBIA EL TÍTULO PARA QUE DIGA SOLO "Productos"
                'title'       => 'Productos', 
                'quantity'    => (int) $detalle->cantidad,
                'unit_price'  => $precio,
                'currency_id' => 'PEN',
            ];
        }

        $subtotal = round($subtotal, 2);

        // Aplicar cupón
        $descuento = 0;
        $idCupon = null;

        if ($request->filled('codigo_cupon')) {
            $cupon = Cupon::where('codigo_cupon', strtoupper(trim($request->codigo_cupon)))->first();

            if ($cupon && $cupon->esValidoPara($usuario->id_usuario, $subtotal)) {
                $descuento = round((float) $cupon->monto_cupon, 2);
                $idCupon = $cupon->id_cupon;
            }
        }

        $total = round(max(0, $subtotal - $descuento), 2);

        // Ajustar los precios de los items para que sumen $total en MercadoPago
        if ($descuento > 0 && $subtotal > 0) {
            $factor = $total / $subtotal;
            $sumaAjustada = 0;
            $totalItems = count($items);

            foreach ($items as $i => &$item) {
                if ($i === $totalItems - 1) {
                    // Último item: ajustar para que la suma cuadre exactamente
                    $precioRestante = round($total - $sumaAjustada, 2);
                    $cantidad = $item['quantity'];
                    $item['unit_price'] = round($precioRestante / $cantidad, 2);
                } else {
                    $item['unit_price'] = round($item['unit_price'] * $factor, 2);
                    $sumaAjustada += $item['unit_price'] * $item['quantity'];
                }
            }
            unset($item);
        }

        $referencia = 'CHK-' . $usuario->id_usuario . '-' . uniqid();

        $checkoutData = [
            'id_usuario'      => $usuario->id_usuario,
            'subtotal'        => $subtotal,
            'descuento'       => $descuento,
            'id_cupon'        => $idCupon,
            'total'           => $total,
            'id_tipo_entrega' => (int) $request->id_tipo_entrega,
            'departamento'    => $request->departamento,
            'provincia'       => $request->provincia,
            'distrito'        => $request->distrito,
            'items'           => collect($carrito->detalles)->map(fn($d) => [
                'id_variante'     => $d->id_variante,
                'cantidad'        => $d->cantidad,
                'precio_unitario' => round((float) ($d->variante->producto->precio_oferta
                    ?? $d->variante->producto->precio), 2),
            ])->toArray(),
        ];

        Cache::put("checkout:{$referencia}", $checkoutData, now()->addHours(2));

        // Log para poder copiar la referencia y probar la URL de éxito manualmente
        \Log::info('Checkout API guardado', [
            'referencia'  => $referencia,
            'id_usuario'  => $usuario->id_usuario,
            'subtotal'    => $subtotal,
            'descuento'   => $descuento,
            'total'       => $total,
            'items_count' => count($checkoutData['items']),
        ]);

        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
        $client = new PreferenceClient();

        $preferenceData = [
            'items'              => $items,
            'payer'              => [
                'name'  => $usuario->nombres,
                'email' => $usuario->correo,
            ],
            'back_urls'          => [
                'success' => route('pago.exito'),
                'failure' => route('pago.fallo'),
                'pending' => route('pago.pendiente'),
            ],
            'notification_url'   => route('pago.webhook'),
            'external_reference' => $referencia,
            'payment_methods'    => [
                'installments'         => 1,
                'default_installments' => 1,
            ],
        ];

        try {
            $preference = $client->create($preferenceData);
        } catch (\Throwable $e) {
            \Log::error('MP preferencia error', [
                'msg'     => $e->getMessage(),
                'usuario' => $usuario->id_usuario,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la preferencia de pago.',
            ], 500);
        }

        $url = app()->environment('production')
            ? $preference->init_point
            : $preference->sandbox_init_point;

        return response()->json([
            'success' => true,
            'data' => [
                'referencia' => $referencia,
                'init_point' => $url,
            ],
        ]);
    }

    public function estadoPago(string $referencia)
    {
        $usuario = Auth::user();

        if (!str_starts_with($referencia, 'CHK-' . $usuario->id_usuario . '-')) {
            return response()->json([
                'success' => false,
                'message' => 'Referencia inválida.',
            ], 403);
        }

        $completado = Cache::get("checkout-pagado:{$referencia}");
        if ($completado) {
            return response()->json([
                'success' => true,
                'data' => array_merge(['estado' => 'completado'], $completado),
            ]);
        }

        $pendiente = Cache::get("checkout:{$referencia}");
        if ($pendiente) {
            return response()->json([
                'success' => true,
                'data' => ['estado' => 'pendiente'],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => ['estado' => 'no_encontrado'],
        ]);
    }
}