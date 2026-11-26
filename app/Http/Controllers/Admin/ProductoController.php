<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\ProductoVarianteImagen;
use App\Models\Categoria;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\PusherBeamsService;

class ProductoController extends Controller
{
    protected $pusherBeams;

    public function __construct(PusherBeamsService $pusherBeams)
    {
        $this->pusherBeams = $pusherBeams;
    }

    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $perPage = $request->get('perPage', 10);

        $query = Producto::with(['variantes.imagenes'])
            ->where('nombre_producto', 'LIKE', '%' . $buscar . '%');

        if ($request->filled('categoria')) {
            $query->where('id_categoria', $request->categoria);
        }

        if ($request->filled('marca')) {
            $query->where('marca', $request->marca);
        }

        if ($request->filled('stock')) {
            switch ($request->stock) {
                case 'agotado':
                    $query->whereDoesntHave('variantes', fn($q) => $q->where('stock', '>', 0));
                    break;

                case 'bajo':
                    $query->whereHas('variantes', fn($q) => $q->where('stock', '>', 0))
                        ->withSum('variantes as stock_total', 'stock')
                        ->having('stock_total', '<=', 10);
                    break;

                case 'disponible':
                    $query->whereHas('variantes', fn($q) => $q->where('stock', '>', 0))
                        ->withSum('variantes as stock_total', 'stock')
                        ->having('stock_total', '>', 10);
                    break;
            }
        }

        $productos = $query->paginate($perPage)->withQueryString();

        $categoriasFiltro = Categoria::orderBy('nombre_categoria')->get();

        $marcasFiltro = Producto::whereNotNull('marca')
            ->where('marca', '!=', '')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        return view('admin.productos.index', compact('productos', 'categoriasFiltro', 'marcasFiltro'));
    }

    public function create()
    {
        $categorias = Categoria::all();

        return view('admin.productos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'nombre_producto' => mb_convert_case(trim((string) $request->nombre_producto), MB_CASE_TITLE, 'UTF-8'),
            'marca' => $request->filled('marca') ? mb_strtoupper(trim((string) $request->marca), 'UTF-8') : null,
        ]);

        $detalles = collect($request->detalles ?? [])
            ->map(fn($d) => trim((string) $d))
            ->filter(fn($d) => $d !== '' && $d !== null)
            ->values()
            ->toArray();

        $request->merge(['detalles' => $detalles]);

        $variantes = collect($request->variantes ?? [])->map(function ($v) {
            return array_merge($v, [
                'talla' => trim((string) ($v['talla'] ?? '')),
                'color' => isset($v['color']) ? trim((string) $v['color']) : null,
                'sku' => isset($v['sku']) && $v['sku'] !== '' ? trim((string) $v['sku']) : null,
            ]);
        })->toArray();

        $request->merge(['variantes' => $variantes]);

        $request->validate([
            'nombre_producto' => 'required|string|max:150|unique:producto,nombre_producto',
            'id_categoria' => 'required|integer|exists:categoria,id_categoria',
            'precio' => 'required|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0|lt:precio',
            'marca' => 'nullable|string|max:50',
            'detalles' => 'nullable|array',
            'detalles.*' => 'string|max:200',
            'variantes' => 'required|array|min:1',
            'variantes.*.talla' => 'required|string|max:50',
            'variantes.*.color' => 'nullable|string|max:50',
            'variantes.*.color_hex' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'variantes.*.stock' => 'required|integer|min:0',
            'variantes.*.sku' => 'nullable|string|max:50|distinct|unique:producto_variante,sku',
            'variantes.*.imagenes' => 'nullable|array',
            'variantes.*.imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'nombre_producto.max' => 'El nombre del producto no puede tener más de 150 caracteres.',
            'nombre_producto.unique' => 'Ya existe un producto con ese nombre.',
            'id_categoria.required' => 'Debes seleccionar una categoría.',
            'id_categoria.exists' => 'La categoría seleccionada no es válida.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio no puede ser negativo.',
            'precio_oferta.numeric' => 'El precio de oferta debe ser un número.',
            'precio_oferta.lt' => 'El precio de oferta debe ser menor que el precio.',
            'marca.max' => 'La marca no puede tener más de 50 caracteres.',
            'detalles.*.max' => 'Cada detalle no puede tener más de 200 caracteres.',
            'variantes.required' => 'Debes agregar al menos una variante.',
            'variantes.min' => 'Debes agregar al menos una variante.',
            'variantes.*.talla.required' => 'La talla es obligatoria en cada variante.',
            'variantes.*.talla.max' => 'La talla no puede tener más de 50 caracteres.',
            'variantes.*.color.max' => 'El color no puede tener más de 50 caracteres.',
            'variantes.*.color_hex.regex' => 'El color debe estar en formato hex válido (#RRGGBB).',
            'variantes.*.stock.required' => 'El stock es obligatorio en cada variante.',
            'variantes.*.stock.integer' => 'El stock debe ser un número entero.',
            'variantes.*.stock.min' => 'El stock no puede ser negativo.',
            'variantes.*.sku.distinct' => 'No puedes repetir el mismo SKU en varias variantes.',
            'variantes.*.sku.unique' => 'El SKU ya está en uso.',
            'variantes.*.imagenes.*.image' => 'Cada archivo debe ser una imagen válida.',
            'variantes.*.imagenes.*.mimes' => 'Solo se permiten JPG, JPEG, PNG o WEBP.',
            'variantes.*.imagenes.*.max' => 'Cada imagen no debe pesar más de 4MB.',
        ]);

        $combinaciones = collect($request->variantes)->map(fn($v) =>
            strtolower($v['talla']) . '-' . strtolower($v['color'] ?? '')
        );

        if ($combinaciones->duplicates()->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'variantes' => ['No puedes repetir la misma combinación de Talla y Color.'],
            ]);
        }

        $producto = Producto::create(array_merge($request->only([
            'nombre_producto',
            'precio',
            'precio_oferta',
            'marca',
            'id_categoria'
        ]), [
            'detalles' => $request->detalles
        ]));

        foreach ($request->variantes as $index => $v) {
            $variante = $producto->variantes()->create([
                'talla' => $v['talla'],
                'color' => $v['color'] ?? null,
                'color_hex' => $v['color_hex'] ?? null,
                'stock' => $v['stock'],
                'sku' => $v['sku'] ?? $this->generarSku($producto->nombre_producto, $v['talla']),
            ]);

            if (isset($v['imagenes']) && is_array($v['imagenes'])) {
                foreach ($v['imagenes'] as $orden => $foto) {
                    if (!$foto instanceof \Illuminate\Http\UploadedFile) {
                        continue;
                    }

                    $nombre = $this->cargarArchivo($foto, 'variantes');

                    ProductoVarianteImagen::create([
                        'id_variante' => $variante->id_variante,
                        'imagen' => $nombre,
                        'orden' => $orden
                    ]);
                }
            }
        }

        $this->pusherBeams->enviarLanzamiento($producto->nombre_producto);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto creado exitosamente.',
            ]);
        }

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit($id)
    {
        $producto = Producto::with(['variantes.imagenes'])->findOrFail($id);
        $categorias = Categoria::all();

        return view('admin.productos.edit', compact('producto', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::with(['variantes.imagenes'])->findOrFail($id);

        $teniaOfertaAntes = !is_null($producto->precio_oferta) && $producto->precio_oferta > 0;
        $precioOfertaAntes = $producto->precio_oferta;

        $request->merge([
            'nombre_producto' => mb_convert_case(trim((string) $request->nombre_producto), MB_CASE_TITLE, 'UTF-8'),
            'marca' => $request->filled('marca') ? mb_strtoupper(trim((string) $request->marca), 'UTF-8') : null,
        ]);

        $detalles = collect($request->detalles ?? [])
            ->map(fn($d) => trim((string) $d))
            ->filter(fn($d) => $d !== '' && $d !== null)
            ->values()
            ->toArray();

        $request->merge(['detalles' => $detalles]);

        $variantes = collect($request->variantes ?? [])->map(function ($v) {
            return array_merge($v, [
                'talla' => trim((string) ($v['talla'] ?? '')),
                'color' => isset($v['color']) ? trim((string) $v['color']) : null,
                'sku' => isset($v['sku']) && $v['sku'] !== '' ? trim((string) $v['sku']) : null,
            ]);
        })->toArray();

        $request->merge(['variantes' => $variantes]);

        $rules = [
            'nombre_producto' => 'required|string|max:150|unique:producto,nombre_producto,' . $producto->id_producto . ',id_producto',
            'precio' => 'required|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0|lt:precio',
            'marca' => 'nullable|string|max:50',
            'estado_producto' => 'nullable|in:0,1',
            'detalles' => 'nullable|array',
            'detalles.*' => 'string|max:500',
            'variantes' => 'required|array|min:1',
            'variantes.*.talla' => 'required|string|max:50',
            'variantes.*.color' => 'nullable|string|max:50',
            'variantes.*.color_hex' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'variantes.*.stock' => 'required|integer|min:0',
            'variantes.*.imagenes' => 'nullable|array',
            'variantes.*.imagenes.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];

        foreach ($request->variantes as $index => $v) {
            $rules["variantes.{$index}.sku"] = [
                'nullable',
                'string',
                'max:50',
                'distinct',
                Rule::unique('producto_variante', 'sku')
                    ->ignore($v['id_variante'] ?? null, 'id_variante'),
            ];
        }

        $request->validate($rules, [
            'nombre_producto.required' => 'El nombre del producto es obligatorio.',
            'nombre_producto.max' => 'El nombre del producto no puede tener más de 150 caracteres.',
            'nombre_producto.unique' => 'Ya existe un producto con ese nombre.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio no puede ser negativo.',
            'precio_oferta.numeric' => 'El precio de oferta debe ser un número.',
            'precio_oferta.lt' => 'El precio de oferta debe ser menor que el precio.',
            'marca.max' => 'La marca no puede tener más de 50 caracteres.',
            'detalles.*.max' => 'Cada detalle no puede tener más de 500 caracteres.',
            'variantes.required' => 'Debes agregar al menos una variante.',
            'variantes.min' => 'Debes agregar al menos una variante.',
            'variantes.*.talla.required' => 'La talla es obligatoria en cada variante.',
            'variantes.*.talla.max' => 'La talla no puede tener más de 50 caracteres.',
            'variantes.*.color.max' => 'El color no puede tener más de 50 caracteres.',
            'variantes.*.color_hex.regex' => 'El color debe estar en formato hex válido (#RRGGBB).',
            'variantes.*.stock.required' => 'El stock es obligatorio en cada variante.',
            'variantes.*.stock.integer' => 'El stock debe ser un número entero.',
            'variantes.*.stock.min' => 'El stock no puede ser negativo.',
            'variantes.*.sku.distinct' => 'No puedes repetir el mismo SKU en varias variantes.',
            'variantes.*.sku.unique' => 'El SKU ya está en uso.',
            'variantes.*.imagenes.*.image' => 'Cada archivo debe ser una imagen válida.',
            'variantes.*.imagenes.*.mimes' => 'Solo se permiten JPG, JPEG, PNG o WEBP.',
            'variantes.*.imagenes.*.max' => 'Cada imagen no debe pesar más de 4MB.',
        ]);

        $combinaciones = collect($request->variantes)->map(fn($v) =>
            strtolower($v['talla']) . '-' . strtolower($v['color'] ?? '')
        );

        if ($combinaciones->duplicates()->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'variantes' => ['Hay combinaciones de Talla y Color duplicadas.'],
            ]);
        }

        /*
         * IDENTIFICAR LAS VARIANTES QUE SE QUIEREN ELIMINAR.
         *
         * Son las variantes que existen actualmente en la base de datos,
         * pero cuyos ID ya no vienen en el formulario.
         */
        $idsEnviadosFormulario = collect($request->variantes)
            ->pluck('id_variante')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->values();

        $variantesAEliminar = $producto->variantes()
            ->with('imagenes')
            ->whereNotIn('id_variante', $idsEnviadosFormulario)
            ->get();

        /*
         * IMPEDIR LA ELIMINACIÓN DE VARIANTES CON STOCK.
         *
         * Esta comprobación se hace antes de actualizar el producto
         * o modificar cualquiera de sus variantes.
         */
        if ($variantesAEliminar->contains(fn($variante) => $variante->stock > 0)) {
            $mensaje = 'No puedes eliminar una variante que todavía tiene stock. Primero debe agotarse su stock.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $mensaje,
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $mensaje);
        }

        $datos = $request->only([
            'nombre_producto',
            'precio',
            'precio_oferta',
            'marca',
            'estado_producto',
            'id_categoria'
        ]);

        $datos['detalles'] = $request->detalles;

        $producto->update($datos);

        $idsEnviados = [];

        foreach ($request->variantes as $index => $v) {
            $varianteExistente = !empty($v['id_variante'])
                ? $producto->variantes->firstWhere('id_variante', $v['id_variante'])
                : null;

            $sku = $v['sku']
                ?? $varianteExistente?->sku
                ?? $this->generarSku($producto->nombre_producto, $v['talla']);

            $variante = $producto->variantes()->updateOrCreate(
                ['id_variante' => $v['id_variante'] ?? null],
                [
                    'talla' => $v['talla'],
                    'color' => $v['color'] ?? null,
                    'color_hex' => $v['color_hex'] ?? null,
                    'stock' => $v['stock'],
                    'sku' => $sku,
                ]
            );

            $idsEnviados[] = $variante->id_variante;

            if (isset($v['imagenes_eliminar']) && is_array($v['imagenes_eliminar'])) {
                foreach ($v['imagenes_eliminar'] as $idImagen) {
                    $img = ProductoVarianteImagen::find($idImagen);

                    if ($img && $img->id_variante == $variante->id_variante) {
                        $ruta = storage_path('app/public/variantes/' . $img->imagen);

                        if (File::exists($ruta)) {
                            File::delete($ruta);
                        }

                        $img->delete();
                    }
                }
            }

            if (isset($v['imagenes']) && is_array($v['imagenes'])) {
                $ordenMax = $variante->imagenes()->max('orden') ?? -1;

                foreach ($v['imagenes'] as $foto) {
                    if ($foto instanceof \Illuminate\Http\UploadedFile) {
                        $ordenMax++;

                        $nombre = $this->cargarArchivo($foto, 'variantes');

                        ProductoVarianteImagen::create([
                            'id_variante' => $variante->id_variante,
                            'imagen' => $nombre,
                            'orden' => $ordenMax
                        ]);
                    }
                }
            }
        }

        /*
         * ELIMINAR LAS VARIANTES SIN STOCK QUE SE QUITARON DEL FORMULARIO.
         *
         * Primero se eliminan de los carritos para evitar referencias
         * a variantes que ya no existen.
         */
        $idsVariantesAEliminar = $variantesAEliminar->pluck('id_variante');

        if ($idsVariantesAEliminar->isNotEmpty()) {
            DB::table('detalle_carrito')
                ->whereIn('id_variante', $idsVariantesAEliminar)
                ->delete();
        }

        foreach ($variantesAEliminar as $vElim) {
            foreach ($vElim->imagenes as $img) {
                $ruta = storage_path('app/public/variantes/' . $img->imagen);

                if (File::exists($ruta)) {
                    File::delete($ruta);
                }

                $img->delete();
            }

            $vElim->delete();
        }

        $tieneOfertaAhora = !is_null($request->precio_oferta) && $request->precio_oferta > 0;

        if ($tieneOfertaAhora && (!$teniaOfertaAntes || $precioOfertaAntes != $request->precio_oferta)) {
            $this->pusherBeams->enviarOferta($producto->nombre_producto, $request->precio_oferta);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente.',
            ]);
        }

        return redirect()->route('admin.productos.index')
            ->with('success', 'Producto actualizado correctamente');
    }

    private function cargarArchivo($file, $carpeta = 'productos')
    {
        $nombre = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();

        $file->move(storage_path('app/public/' . $carpeta), $nombre);

        return $nombre;
    }

    private function generarSku($nombreProducto, $talla): string
    {
        $letras = preg_replace('/[^A-Za-z]/', '', $nombreProducto);
        $primera = strtoupper(substr($letras, 0, 1) ?: 'X');
        $talla = strtoupper($talla ?? '');

        $faltan = 8 - 1 - strlen($talla);
        $faltan = max($faltan, 0);

        do {
            $rand = '';

            for ($i = 0; $i < $faltan; $i++) {
                $rand .= random_int(0, 9);
            }

            $sku = $primera . $rand . $talla;

        } while (ProductoVariante::where('sku', $sku)->exists());

        return $sku;
    }

    public function destroy($id)
    {
        $imagenesEliminar = [];
        $idCat = null;

        try {
            DB::transaction(function () use ($id, &$imagenesEliminar, &$idCat) {

                $producto = Producto::lockForUpdate()->findOrFail($id);

                $variantes = $producto->variantes()
                    ->with('imagenes')
                    ->lockForUpdate()
                    ->get();

                if ($variantes->contains(fn($variante) => $variante->stock > 0)) {
                    throw new \RuntimeException(
                        'No se puede eliminar este producto porque todavía tiene variantes con stock.'
                    );
                }

                $idCat = $producto->id_categoria;

                $idsVariantes = $variantes->pluck('id_variante');

                foreach ($variantes as $variante) {
                    foreach ($variante->imagenes as $imagen) {
                        $imagenesEliminar[] = $imagen->imagen;
                    }
                }

                DB::table('detalle_carrito')
                    ->whereIn('id_variante', $idsVariantes)
                    ->delete();

                DB::table('favoritos')
                    ->where('id_producto', $producto->id_producto)
                    ->delete();

                foreach ($variantes as $variante) {
                    $variante->delete();
                }

                $producto->delete();
            });

            foreach ($imagenesEliminar as $nombreImagen) {
                $ruta = storage_path('app/public/variantes/' . $nombreImagen);

                if (File::exists($ruta)) {
                    File::delete($ruta);
                }
            }

            $this->actualizarEstadoCategoria($idCat);

            return redirect()
                ->route('admin.productos.index')
                ->with('success', 'Producto eliminado definitivamente.');

        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());

        } catch (\Throwable $e) {
            \Log::error('Error al eliminar producto', [
                'producto_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'No se pudo eliminar el producto. Inténtalo nuevamente.');
        }
    }

    private function actualizarEstadoCategoria($id_categoria)
    {
        $categoria = Categoria::find($id_categoria);

        if ($categoria) {
            $tieneStock = $categoria->productos()
                ->whereHas('variantes', function ($q) {
                    $q->where('stock', '>', 0);
                })
                ->exists();

            $categoria->estado_categoria = $tieneStock ? 1 : 0;
            $categoria->save();
        }
    }
}
