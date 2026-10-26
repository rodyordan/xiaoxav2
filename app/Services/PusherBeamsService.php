<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;
use Throwable;

class PusherBeamsService
{
    public function enviarOferta($nombreProducto, $precioOferta)
    {
        $precio = number_format($precioOferta, 2);

        return $this->publicar(
            'ofertas',
            '¡OFERTA ESPECIAL!',
            "$nombreProducto ahora a solo S/ $precio"
        );
    }

    public function enviarLanzamiento($nombreProducto)
    {
        return $this->publicar(
            'lanzamientos',
            '¡NUEVO PRODUCTO!',
            "Ya disponible: $nombreProducto"
        );
    }

    public function enviarCupon($codigo, $montoCompraMinima, $valorCupon)
    {
        $minimo = number_format($montoCompraMinima, 2);
        $valor  = number_format($valorCupon, 2);

        return $this->publicar(
            'cupones',
            "¡NUEVO CUPON: $codigo!",
            "Por compras mayores a S/ $minimo llévate de regalo S/ $valor de descuento"
        );
    }

    protected function publicar(string $interest, string $titulo, string $mensaje)
    {
        try {
            $beams = new PushNotifications([
                'instanceId' => config('services.pusher_beams.instance_id'),
                'secretKey'  => config('services.pusher_beams.secret_key'),
            ]);

            return $beams->publishToInterests(
                [$interest],
                [
                    'fcm' => [
                        'notification' => [
                            'title' => $titulo,
                            'body'  => $mensaje,
                        ],
                    ],
                ]
            );
        } catch (Throwable $e) {
            Log::error('Error enviando notificación Beams: ' . $e->getMessage(), [
                'interest' => $interest,
            ]);
            return null;
        }
    }
}