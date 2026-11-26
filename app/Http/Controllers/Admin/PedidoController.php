<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['usuario', 'tipoEntrega']);

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;

            $query->where(function ($q) use ($buscar) {
                $q->where('numero_pedido', 'like', "%{$buscar}%")
                  ->orWhereHas('usuario', function ($q2) use ($buscar) {
                      $q2->where('nombres', 'like', "%{$buscar}%")
                         ->orWhere('apellidos', 'like', "%{$buscar}%")
                         ->orWhere('correo', 'like', "%{$buscar}%");
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado_pedido', $request->estado);
        }

        if ($request->filled('tipo_entrega')) {
            $query->where('id_tipo_entrega', $request->tipo_entrega);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('created_at', $request->fecha);
        }

        $pedidos = $query->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function show($id)
    {
        $pedido = Pedido::with([
            'usuario',
            'tipoEntrega',
            'detalles',
            'cupon',
            'cuponUsado',
        ])->findOrFail($id);

        return view('admin.pedidos.show', compact('pedido'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'estado_pedido'      => 'required|in:Pendiente,En camino,Listo para recoger,Entregado',
            'tiempo_entrega'     => 'nullable|string|max:100',
            'direccion_entrega'  => 'nullable|string|max:500',
            'archivo_adjunto'    => 'nullable|file|max:10240',
            'accion'             => 'nullable|in:guardar,estado',
        ]);

        $pedido = Pedido::findOrFail($id);

        $data = ['estado_pedido' => $request->estado_pedido];

        if ($request->has('tiempo_entrega')) {
            $data['tiempo_entrega'] = $request->input('tiempo_entrega');
        }

        if ($request->has('direccion_entrega')) {
            $data['direccion_entrega'] = $request->input('direccion_entrega');
        }

        if ($request->hasFile('archivo_adjunto')) {
            if ($pedido->archivo_adjunto) {
                Storage::disk('local')->delete($pedido->archivo_adjunto);
            }

            $ruta = $request->file('archivo_adjunto')
                ->store('pedidos/adjuntos', 'local');

            $data['archivo_adjunto'] = $ruta;
        }

        $pedido->update($data);

        if ($request->input('accion') === 'estado') {
            $mensaje = "Pedido {$pedido->numero_pedido} actualizado a '{$request->estado_pedido}'.";
        } else {
            $mensaje = "Pedido {$pedido->numero_pedido} actualizado correctamente.";
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'pedido'  => [
                    'id_pedido'         => $pedido->id_pedido,
                    'estado_pedido'     => $pedido->estado_pedido,
                    'tiempo_entrega'    => $pedido->tiempo_entrega,
                    'direccion_entrega' => $pedido->direccion_entrega,
                    'archivo_adjunto'   => $pedido->archivo_adjunto,
                ],
            ]);
        }

        return back()->with('success', $mensaje);
    }

    public function descargarArchivo($id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!$pedido->archivo_adjunto) {
            abort(404, 'Este pedido no tiene archivo adjunto.');
        }

        $extension = pathinfo($pedido->archivo_adjunto, PATHINFO_EXTENSION);
        $nombreDescarga = 'guia-pedido-' . $pedido->numero_pedido . '.' . $extension;

        return Storage::disk('local')->download(
            $pedido->archivo_adjunto,
            $nombreDescarga
        );
    }
}
