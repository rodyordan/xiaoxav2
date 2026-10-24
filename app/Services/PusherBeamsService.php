<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;
use Throwable;

class PusherBeamsService
{
    public function enviarOferta($nombreProducto, $precioOferta, $categoria = '')
    {
        $precio = number_format($precioOferta, 2);

        $mensaje = $categoria
            ? "$nombreProducto en $categoria ahora a solo S/ $precio"
            : "$nombreProducto ahora a solo S/ $precio";

        return $this->publicar('ofertas', 'OFERTA ESPECIAL', $mensaje);
    }

    public function enviarLanzamiento($nombreProducto, $categoria = '')
    {
        $mensaje = $categoria
            ? "Ya disponible: $nombreProducto en $categoria"
            : "Ya disponible: $nombreProducto";

        return $this->publicar('lanzamientos', 'NUEVO PRODUCTO!', $mensaje);
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
            // Un fallo de notificaciones no debe romper el guardado del producto.
            Log::error('Error enviando notificación Beams: ' . $e->getMessage(), [
                'interest' => $interest,
            ]);
            return null;
        }
    }
}