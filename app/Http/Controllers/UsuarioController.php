<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function actualizar(Request $request)
    {
        /** @var Usuario $usuario */
        $usuario = Auth::user();

        $request->merge([
            'nombres' => trim((string) $request->nombres),
            'apellidos' => trim((string) $request->apellidos),
            'telefono' => $request->filled('telefono') ? trim((string) $request->telefono) : null,
            'numero_documento' => $request->filled('numero_documento') ? trim((string) $request->numero_documento) : null,
        ]);

        $request->validate([
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'telefono' => 'nullable|numeric|digits:9',
            'numero_documento' => 'nullable|numeric|digits_between:8,12',
            'id_tipo_documento' => 'nullable|integer|exists:tipo_documento,id_tipo_documento',
        ]);

        $usuario->update([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
            'numero_documento' => $request->numero_documento,
            'id_tipo_documento' => $request->id_tipo_documento,
        ]);

        return back()->with('success', 'Datos actualizados correctamente');
    }
}