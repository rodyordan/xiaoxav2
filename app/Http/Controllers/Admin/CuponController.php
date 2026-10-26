<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cupon;
use App\Models\CuponUsado;
use App\Services\PusherBeamsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CuponController extends Controller
{
    protected $pusherBeams;

    public function __construct(PusherBeamsService $pusherBeams)
    {
        $this->pusherBeams = $pusherBeams;
    }

    public function index(Request $request)
    {
        $query = Cupon::query();

        if ($request->filled('buscar')) {
            $query->where('codigo_cupon', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('estado')) {
            $query->where('estado_cupon', $request->estado);
        }

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_vencimiento', $request->fecha);
        }

        $cupones = $query->orderBy('fecha_vencimiento', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.cupones.index', compact('cupones'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'codigo_cupon' => mb_strtoupper(trim((string) $request->codigo_cupon), 'UTF-8'),
        ]);

        $data = $request->validate([
            'codigo_cupon'        => 'required|unique:cupones,codigo_cupon|max:50',
            'monto_cupon'         => 'required|numeric|min:0|lt:monto_compra_minima',
            'monto_compra_minima' => 'required|numeric|min:0',
            'fecha_vencimiento'   => 'required|date|after:today',
            'estado_cupon'        => 'nullable|boolean',
        ], [
            'codigo_cupon.required' => 'El código del cupón es obligatorio.',
            'codigo_cupon.unique'   => 'Ya existe un cupón con ese código.',
            'codigo_cupon.max'      => 'El código del cupón no puede tener más de 50 caracteres.',
            'monto_cupon.required'  => 'El valor del cupón es obligatorio.',
            'monto_cupon.numeric'   => 'El valor del cupón debe ser un número.',
            'monto_cupon.min'       => 'El valor del cupón no puede ser negativo.',
            'monto_cupon.lt'        => 'El valor del cupón debe ser menor que el monto mínimo de compra.',
            'monto_compra_minima.required' => 'El monto mínimo de compra es obligatorio.',
            'monto_compra_minima.numeric'  => 'El monto mínimo de compra debe ser un número.',
            'monto_compra_minima.min'      => 'El monto mínimo de compra no puede ser negativo.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date'     => 'La fecha de vencimiento no es válida.',
            'fecha_vencimiento.after'    => 'La fecha de vencimiento debe ser posterior a hoy.',
        ]);

        $data['estado_cupon'] = $request->input('estado_cupon', 1);

        $cupon = Cupon::create($data);

        if ($data['estado_cupon']) {
            $this->pusherBeams->enviarCupon(
                $data['codigo_cupon'],
                $data['monto_compra_minima'],
                $data['monto_cupon']
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cupón creado correctamente.',
            ]);
        }

        return redirect()->route('admin.cupones.index')
            ->with('success', 'Cupón creado correctamente.');
    }

    public function edit($id)
    {
        $cupon = Cupon::findOrFail($id);
        return view('admin.cupones.edit', compact('cupon'));
    }

    public function update(Request $request, $id)
    {
        $cupon = Cupon::findOrFail($id);

        $request->merge([
            'codigo_cupon' => mb_strtoupper(trim((string) $request->codigo_cupon), 'UTF-8'),
        ]);

        $data = $request->validate([
            'codigo_cupon'        => 'required|unique:cupones,codigo_cupon,' . $id . ',id_cupon|max:50',
            'monto_cupon'         => 'required|numeric|min:0|lt:monto_compra_minima',
            'monto_compra_minima' => 'required|numeric|min:0',
            'fecha_vencimiento'   => 'required|date',
            'estado_cupon'        => 'nullable|boolean',
        ], [
            'codigo_cupon.required' => 'El código del cupón es obligatorio.',
            'codigo_cupon.unique'   => 'Ya existe un cupón con ese código.',
            'codigo_cupon.max'      => 'El código del cupón no puede tener más de 50 caracteres.',
            'monto_cupon.required'  => 'El valor del cupón es obligatorio.',
            'monto_cupon.numeric'   => 'El valor del cupón debe ser un número.',
            'monto_cupon.min'       => 'El valor del cupón no puede ser negativo.',
            'monto_cupon.lt'        => 'El valor del cupón debe ser menor que el monto mínimo de compra.',
            'monto_compra_minima.required' => 'El monto mínimo de compra es obligatorio.',
            'monto_compra_minima.numeric'  => 'El monto mínimo de compra debe ser un número.',
            'monto_compra_minima.min'      => 'El monto mínimo de compra no puede ser negativo.',
            'fecha_vencimiento.required' => 'La fecha de vencimiento es obligatoria.',
            'fecha_vencimiento.date'     => 'La fecha de vencimiento no es válida.',
        ]);

        if ($request->has('estado_cupon')) {
            $data['estado_cupon'] = $request->estado_cupon;
        }

        $cupon->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cupón actualizado correctamente.',
            ]);
        }

        return back()->with('success', 'Cupón actualizado correctamente.');
    }

    public function toggle($id)
    {
        $cupon = Cupon::findOrFail($id);

        $cupon->estado_cupon = !$cupon->estado_cupon;
        $cupon->save();

        return back()->with('success', $cupon->estado_cupon
            ? 'Cupón activado.'
            : 'Cupón desactivado.');
    }

    public function destroy($id)
    {
        $cupon = Cupon::findOrFail($id);

        CuponUsado::where('id_cupon', $id)->delete();

        $cupon->delete();

        return back()->with('success', 'Cupón eliminado correctamente.');
    }
}