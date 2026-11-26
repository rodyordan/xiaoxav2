<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PedidoUsuarioController extends Controller
{
    public function index()
    {
        $pedidos = auth()->user()
            ->pedidos()
            ->with([
                'detalles',
                'tipoEntrega',
                'cupon',
            ])
            ->orderBy('fecha_pedido', 'desc')
            ->paginate(5);

        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Descargar el archivo adjunto del pedido (vista cliente web).
     */
    public function descargarArchivo($id)
    {
        $pedido = Pedido::where('id_usuario', Auth::id())->findOrFail($id);

        if (!$pedido->archivo_adjunto) {
            abort(404, 'Este pedido no tiene archivo adjunto.');
        }

        if (!Storage::disk('local')->exists($pedido->archivo_adjunto)) {
            abort(404, 'El archivo no existe en el servidor.');
        }

        $extension = pathinfo($pedido->archivo_adjunto, PATHINFO_EXTENSION);
        $nombreDescarga = 'guia-pedido-' . $pedido->numero_pedido . '.' . $extension;

        return Storage::disk('local')->download(
            $pedido->archivo_adjunto,
            $nombreDescarga
        );
    }
}
