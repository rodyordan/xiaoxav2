<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')->get();

        return view('admin.categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre_categoria' => trim((string) $request->nombre_categoria),
        ]);

        $data = $request->validate([
            'nombre_categoria' => 'required|string|max:50|unique:categoria,nombre_categoria',
        ], [
            'nombre_categoria.required' => 'El nombre de la categoría es obligatorio.',
            'nombre_categoria.string'   => 'El nombre de la categoría debe ser texto.',
            'nombre_categoria.max'      => 'El nombre de la categoría no puede tener más de 50 caracteres.',
            'nombre_categoria.unique'   => 'Ya existe una categoría con ese nombre.',
        ]);

        Categoria::create([
            'nombre_categoria' => $data['nombre_categoria'],
            'estado_categoria' => 1,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Categoría creada correctamente.',
            ]);
        }

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->merge([
            'nombre_categoria' => trim((string) $request->nombre_categoria),
        ]);

        $data = $request->validate([
            'nombre_categoria' => 'required|string|max:50|unique:categoria,nombre_categoria,' . $categoria->id_categoria . ',id_categoria',
            'estado_categoria' => 'nullable|in:0,1',
        ], [
            'nombre_categoria.required' => 'El nombre de la categoría es obligatorio.',
            'nombre_categoria.string'   => 'El nombre de la categoría debe ser texto.',
            'nombre_categoria.max'      => 'El nombre de la categoría no puede tener más de 50 caracteres.',
            'nombre_categoria.unique'   => 'Ya existe una categoría con ese nombre.',
            'estado_categoria.in'       => 'El estado de la categoría no es válido.',
        ]);

        $datos = [
            'nombre_categoria' => $data['nombre_categoria'],
        ];

        if ($request->has('estado_categoria')) {
            $datos['estado_categoria'] = $data['estado_categoria'];
        }

        $categoria->update($datos);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada correctamente.',
            ]);
        }

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function toggle($id)
    {
        $categoria = Categoria::with('productos')->findOrFail($id);

        $tieneStock = $categoria->productos()
            ->whereHas('variantes', function ($q) {
                $q->where('stock', '>', 0);
            })
            ->exists();

        if ($tieneStock && $categoria->estado_categoria) {
            return redirect()->back()->with(
                'error',
                'No se puede desactivar porque tiene productos con stock.'
            );
        }

        $categoria->estado_categoria = !$categoria->estado_categoria;
        $categoria->save();

        return redirect()->back()->with(
            'success',
            'Estado actualizado correctamente.'
        );
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);

        // No se permite eliminar una categoría que todavía tenga productos.
        if ($categoria->productos()->exists()) {
            return redirect()->back()->with(
                'error',
                'No se puede eliminar esta categoría porque todavía tiene productos asociados. Primero elimina sus productos.'
            );
        }

        $categoria->delete();

        return redirect()
            ->route('admin.categorias.index')
            ->with('success', 'Categoría eliminada correctamente.');
    }
}
