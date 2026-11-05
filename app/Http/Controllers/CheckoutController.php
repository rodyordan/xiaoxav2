<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\ProductoVariante;
use App\Models\TipoEntrega;
use App\Models\TipoDocumento;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class CheckoutController extends Controller
{
    public function index()
    {
        if (session()->has('carrito')) {
            $carrito = Carrito::firstOrCreate(['id_usuario' => Auth::id()]);

            foreach (session('carrito') as $idVariante => $item) {
                \App\Models\DetalleCarrito::updateOrCreate(
                    ['id_carrito' => $carrito->id_carrito, 'id_variante' => $idVariante],
                    ['cantidad'   => $item['cantidad']]
                );
            }

            session()->forget('carrito');
        }

        $carrito        = Carrito::with('detalles.variante.producto')
            ->where('id_usuario', Auth::id())
            ->first();
        $tiposEntrega   = TipoEntrega::all();
        $tiposDocumento = TipoDocumento::all();
        $departamentos  = Departamento::orderBy('nombre_departamento')->get();

        return view('carrito.checkout', compact(
            'carrito',
            'tiposEntrega',
            'tiposDocumento',
            'departamentos'
        ));
    }

    public function getProvincias($id_departamento)
    {
        return Provincia::where('id_departamento', $id_departamento)
            ->orderBy('nombre_provincia')
            ->get();
    }

    public function getDistritos($id_provincia)
    {
        return Distrito::where('id_provincia', $id_provincia)
            ->orderBy('nombre_distrito')
            ->get();
    }

    public function confirmar(Request $request)
    {
        $request->validate([
            'id_tipo_entrega'    => 'required|integer|in:1,2',
            'id_tipo_documento'  => 'required|integer|exists:tipo_documento,id_tipo_documento',
            'numero_documento'   => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    $tipo = \App\Models\TipoDocumento::find($request->id_tipo_documento);
                    
                    if ($tipo) {
                        $nombre = strtolower($tipo->nombre_tipo_documento);
                        $longitud = strlen($value);

                        if (str_contains($nombre, 'dni')) {
                            if ($longitud !== 8) {
                                $fail('El documento debe tener 8 dígitos.');
                            }
                        } elseif (str_contains($nombre, 'ruc')) {
                            if ($longitud !== 11) {
                                $fail('El documento debe tener 11 dígitos.');
                            }
                        } elseif (str_contains($nombre, 'carnet') || str_contains($nombre, 'extranjería') || str_contains($nombre, 'ce')) {
                            if ($longitud < 9 || $longitud > 12) {
                                $fail('El documento debe tener entre 9 y 12 dígitos.');
                            }
                        } else {
                            if ($longitud < 8 || $longitud > 12) {
                                $fail('El documento debe tener entre 8 y 12 dígitos.');
                            }
                        }
                    }
                },
            ],
            'telefono'           => ['required', 'numeric', 'regex:/^9\d{8}$/'],
            'id_departamento'    => 'nullable|integer|exists:departamento,id_departamento',
            'id_provincia'       => 'nullable|integer|exists:provincia,id_provincia',
            'id_distrito'        => 'nullable|integer|exists:distrito,id_distrito',
        ], [
            'numero_documento.required' => 'El número de documento es obligatorio.',
            'numero_documento.numeric'  => 'El documento debe contener solo números.',
            'telefono.required'         => 'El teléfono es obligatorio.',
            'telefono.numeric'          => 'El teléfono debe contener solo números.',
            'telefono.regex'            => 'El teléfono debe comenzar con 9 y tener 9 dígitos.',
        ]);

        $user = Auth::user();
        $user->id_tipo_documento = $request->id_tipo_documento;
        $user->numero_documento  = $request->numero_documento;
        $user->telefono          = $request->telefono;
        $user->save();

        $carrito = Carrito::with('detalles.variante.producto')
            ->where('id_usuario', Auth::id())
            ->firstOrFail();

        if ($carrito->detalles->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío.');
        }

        foreach ($carrito->detalles as $detalle) {
            if ($detalle->variante->stock < $detalle->cantidad) {
                return back()->with('error',
                    'Stock insuficiente para: ' . $detalle->variante->producto->nombre_producto
                );
            }
        }

        $subtotal = 0;
        $items    = [];

        foreach ($carrito->detalles as $detalle) {
            $producto = $detalle->variante->producto;
            $precio   = $producto->precio_oferta ?? $producto->precio;
            $subtotal += $precio * $detalle->cantidad;

            $items[] = [
                "title"       => "Productos",
                "quantity"    => (int) $detalle->cantidad,
                "unit_price"  => (float) $precio,
                "currency_id" => "PEN",
            ];
        }

        $total = $subtotal;

        $referencia = 'CHK-' . Auth::id() . '-' . uniqid();

        $checkoutData = [
            'id_usuario'        => Auth::id(),
            'subtotal'          => $subtotal,
            'total'             => $total,
            'id_tipo_entrega'   => (int) $request->id_tipo_entrega,
            'id_departamento'   => $request->id_departamento,
            'id_provincia'      => $request->id_provincia,
            'id_distrito'       => $request->id_distrito,
            'items'             => collect($carrito->detalles)->map(fn($d) => [
                'id_variante'     => $d->id_variante,
                'cantidad'        => $d->cantidad,
                'precio_unitario' => $d->variante->producto->precio_oferta
                                     ?? $d->variante->producto->precio,
            ])->toArray(),
        ];

        Cache::put("checkout:{$referencia}", $checkoutData, now()->addHours(2));

        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
        $client = new PreferenceClient();

        $preferenceData = [
            "items"              => $items,
            "payer"              => [
                "name"  => Auth::user()->nombres,
                "email" => Auth::user()->correo,
            ],
            "back_urls"          => [
                "success" => route('pago.exito'),
                "failure" => route('pago.fallo'),
                "pending" => route('pago.pendiente'),
            ],
            "notification_url"   => route('pago.webhook'),
            "external_reference" => $referencia,
            "payment_methods"    => [
                "installments"         => 1,
                "default_installments" => 1,
            ],
        ];

        if (app()->environment('production') && str_starts_with(config('app.url'), 'https://')) {
            $preferenceData['auto_return'] = 'approved';
        }

        try {
            $preference = $client->create($preferenceData);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            dd([
                'mensaje'   => $e->getMessage(),
                'respuesta' => $e->getApiResponse()->getContent(),
            ]);
        }

        $url = app()->environment('production')
            ? $preference->init_point
            : $preference->sandbox_init_point;

        return redirect($url);
    }
}