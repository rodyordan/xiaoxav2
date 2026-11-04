@extends('layouts.app')

@section('title', $producto->nombre_producto . ' - Xiaoxa')

@section('content')

@php
    $variantesPorColor = $producto->variantes->groupBy('color');
    $colores = $variantesPorColor->keys()->filter();
    $tallas  = $producto->variantes->pluck('talla')->unique();

    $coloresUnicos = [];
    foreach ($colores as $color) {
        $variante = $producto->variantes->where('color', $color)->first();
        $coloresUnicos[] = [
            'nombre' => $color,
            'hex' => $variante->color_hex ?? '#cccccc'
        ];
    }

    $todasLasImagenes = $producto->variantes
        ->flatMap(fn($v) => $v->imagenes->map(fn($i) => [
            'ruta' => url('/api/imagen/' . $i->imagen),
        ]))
        ->unique('ruta')
        ->values();

    $esColorClaro = function ($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) return false;
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b);
        return $luminancia > 230;
    };
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-8 pt-6 pb-2">
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('home') }}" class="flex items-center gap-1" title="Volver al inicio">
            <span class="text-base font-normal text-black">Inicio</span>
            <x-heroicon-o-chevron-right class="w-3 h-3 text-black" />
        </a>

        @if($producto->categoria)
            <a href="{{ route('home', ['categoria' => $producto->categoria->nombre_categoria]) }}" class="flex items-center gap-1">
                <span class="text-base font-normal text-black">
                    {{ ucwords(strtolower($producto->categoria->nombre_categoria)) }}
                </span>
                <x-heroicon-o-chevron-right class="w-3 h-3 text-black" />
            </a>
        @endif

        <span class="text-base font-normal text-black truncate">
            {{ ucwords(strtolower($producto->nombre_producto)) }}
        </span>
    </div>
</div>

<main class="max-w-7xl mx-auto px-4 sm:px-8 py-8">
    <div class="flex flex-col lg:flex-row items-start">

        <div class="w-full lg:w-[65%] min-w-0">
            <div class="flex min-w-0">

                <div class="flex flex-col gap-2 w-[72px] shrink-0 max-h-[800px] overflow-y-auto scroll-gallery pr-1">
                    @foreach($todasLasImagenes as $index => $img)
                        <button type="button"
                                onclick="cambiarImagen(this, '{{ $img['ruta'] }}')"
                                class="thumbnail-btn {{ $index === 0 ? 'thumbnail-active' : '' }} w-[72px] h-[110px] flex-shrink-0 border {{ $index === 0 ? 'border-2' : 'border-gray-200' }} overflow-hidden bg-white p-0.5 relative">
                            <img src="{{ $img['ruta'] }}" class="absolute inset-0 w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>

                <div class="flex-1 min-w-0 relative overflow-hidden flex items-start justify-center">
                    <div class="imagen-principal-wrapper">
                        <img id="imagen-principal"
                             src="{{ $todasLasImagenes->first()['ruta'] ?? '' }}"
                             alt="{{ $producto->nombre_producto }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-[35%] space-y-0">

            <div class="pb-5">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-[0.2em] mb-2">{{ $producto->marca }}</p>
                <h1 class="text-[35px] font-normal leading-snug text-gray-800">
                    {{ ucwords(strtolower($producto->nombre_producto)) }}
                </h1>
            </div>
           
            @if($producto->precio_oferta)
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-[40px] font-normal text-red-500">
                        S/ {{ number_format($producto->precio_oferta, 2) }}
                    </span>
                    <span class="text-[30px] text-gray-500 line-through">
                        S/ {{ number_format($producto->precio, 2) }}
                    </span>

                    <span class="bg-red-500 text-white text-[15px] font-semibold leading-none min-w-[46px] text-center px-1 py-1.5 rounded-md shadow-sm">
                        -{{ abs($producto->descuento) }}%
                    </span>
                </div>
            @else
                <span class="text-[40px] font-normal text-gray-900">
                    S/ {{ number_format($producto->precio, 2) }}
                </span>
            @endif
            
            @if(!empty($producto->detalles) && count($producto->detalles) > 0)
            <div class="py-5">
                <p class="text-lg font-semibold mb-3">Detalles</p>
                <ul class="space-y-2">
                    @foreach($producto->detalles as $detalle)
                        @if(!empty(trim($detalle)))
                        <li class="flex items-start gap-2 text-sm leading-relaxed">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-700 flex-shrink-0 mt-2"></span>
                            <span>{{ $detalle }}</span>
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @endif

            @if(count($coloresUnicos) > 0)
            <div class="py-4">
                <div class="flex items-baseline gap-2 mb-3">
                    <p class="text-sm font-bold text-gray-900">Color:</p>
                    <span id="color-seleccionado" class="text-sm text-gray-700"></span>
                </div>

                <div class="flex flex-wrap gap-3">
                    @foreach($coloresUnicos as $c)
                        <button type="button"
                                onclick="seleccionarColor(this, '{{ $c['nombre'] }}', '{{ $c['hex'] }}')"
                                class="color-btn w-10 h-10 rounded-full transition-all box-border {{ $esColorClaro($c['hex']) ? 'color-claro' : '' }}"
                                style="--color: {{ $c['hex'] }}; background-color: var(--color);"
                                data-color="{{ $c['nombre'] }}"
                                data-color-hex="{{ $c['hex'] }}"
                                data-claro="{{ $esColorClaro($c['hex']) ? '1' : '0' }}"
                                title="{{ $c['nombre'] }}">
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="py-4">
                <p class="text-sm font-bold mb-3">Talla:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($tallas as $talla)
                    <button type="button"
                            onclick="selectTalla(this)"
                            class="talla-btn w-12 h-9 border-2 border-gray-400 rounded-lg text-sm font-semibold text-gray-900 bg-white"
                            data-talla="{{ $talla }}">
                        {{ $talla }}
                    </button>
                    @endforeach
                </div>
                {{-- Mensaje de error idéntico al del checkout, persistente --}}
                <p id="toast-error" class="text-xs text-red-600 mt-1.5 font-medium opacity-0 transition-opacity duration-300 pointer-events-none"></p>
            </div>

            <div class="pt-2">
                <form id="form-carrito" action="{{ route('carrito.add', $producto->id_producto) }}" method="POST">
                    @csrf
                    <input type="hidden" name="color"       id="input-color">
                    <input type="hidden" name="talla"       id="input-talla">
                    <input type="hidden" name="id_variante" id="input-variante">
                    <input type="hidden" name="cantidad"    id="input-cantidad" value="1">

                    <div class="flex items-center gap-3">
                        {{-- Selector de cantidad --}}
                        <div id="quantity-controls" class="flex items-center border-2 border-gray-400 rounded-lg overflow-hidden opacity-50 cursor-not-allowed transition-all">
                            <button type="button" id="btn-minus" onclick="disminuirCantidad()" disabled
                                    class="w-9 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition disabled:cursor-not-allowed disabled:hover:bg-transparent">
                                <x-heroicon-o-minus class="w-4 h-4" />
                            </button>
                            <span id="cantidad-display" class="w-8 text-center text-sm font-semibold">1</span>
                            <button type="button" id="btn-plus" onclick="aumentarCantidad()" disabled
                                    class="w-9 h-11 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition disabled:cursor-not-allowed disabled:hover:bg-transparent">
                                <x-heroicon-o-plus class="w-4 h-4" />
                            </button>
                        </div>

                        <button type="submit"
                                class="flex-1 bg-gray-900 text-white rounded-lg h-11 text-xs font-bold uppercase tracking-wider
                                       hover:bg-gray-800 active:scale-[0.99] transition-all">
                            Agregar a la bolsa
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

@endsection

@push('styles')
<style>
    .imagen-principal-wrapper {
        position: relative;
        width: 100%;
        max-width: 640px;
        aspect-ratio: 4 / 5;
        overflow: hidden;
        background-color: #fff;
    }

    .imagen-principal-wrapper img#imagen-principal {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }

    .thumbnail-btn { transition: all 0.2s ease; }
    .thumbnail-active { border-color: #111 !important; border-width: 2px !important; }

    .color-btn {
        box-sizing: border-box;
        border: 0;
        transition: box-shadow 0.2s ease;
    }

    .color-btn.color-activo {
        box-shadow: inset 0 0 0 3px var(--color), inset 0 0 0 5px #fff;
    }

    .color-btn.color-activo-negro {
        box-shadow: inset 0 0 0 3px var(--color), inset 0 0 0 5px #aaa;
    }

    .color-btn.color-claro {
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08);
    }
    .color-btn.color-claro.color-activo {
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08), inset 0 0 0 4px var(--color), inset 0 0 0 5.5px #fff;
    }
    .color-btn.color-claro.color-activo-negro {
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08), inset 0 0 0 4px var(--color), inset 0 0 0 5.5px #aaa;
    }

    .talla-btn {
        box-sizing: border-box;
        font-weight: 600;
        color: #000 !important;
        background-color: #fff;
        border-color: #9ca3af;
        text-decoration: none !important;
    }
    .talla-btn.talla-activa {
        background-color: #000 !important;
        color: #fff !important;
        border-color: #000 !important;
    }
    .talla-btn:disabled,
    .talla-btn.talla-bloqueada {
        color: #000 !important;
        text-decoration: none !important;
        cursor: not-allowed;
        opacity: 0.5;
    }

    .scroll-gallery { overflow-x: hidden; }
    .scroll-gallery::-webkit-scrollbar { width: 3px; height: 0; }
    .scroll-gallery::-webkit-scrollbar-track { background: transparent; }
    .scroll-gallery::-webkit-scrollbar-thumb { background: #ccc; border-radius: 99px; }
</style>
@endpush

@push('scripts')
<script>
    const variantes = @json($producto->variantes);
    const enCarrito = @json($enCarrito ?? []);
    const isAuth = {{ Auth::check() ? 'true' : 'false' }};

    @php
        $variantesConImagenes = $producto->variantes->map(function ($v) {
            return [
                'id_variante' => $v->id_variante,
                'color'       => $v->color,
                'talla'       => $v->talla,
                'stock'       => $v->stock,
                'imagenes'    => $v->imagenes
                    ->map(fn($i) => url('/api/imagen/' . $i->imagen))
                    ->toArray(),
            ];
        });
    @endphp

    const variantesConImagenes = @json($variantesConImagenes);

    let colorSeleccionado = "";
    let tallaSeleccionada = "";
    let cantidad = 1;
    let stockMaximo = 0;
    let stockEnCarrito = 0;

    function cambiarImagen(elemento, ruta) {
        document.getElementById('imagen-principal').src = ruta;

        document.querySelectorAll('.thumbnail-btn').forEach(btn => {
            btn.classList.remove('thumbnail-active', 'border-2');
            btn.classList.add('border', 'border-gray-200');
        });
        elemento.classList.add('thumbnail-active', 'border-2');
        elemento.classList.remove('border', 'border-gray-200');
    }

    function mostrarPrimeraImagenDelColor(color) {
        const variante = variantesConImagenes.find(v =>
            v.color && v.color.toLowerCase() === color.toLowerCase() && v.imagenes.length > 0
        );
        if (!variante) return;

        const ruta = variante.imagenes[0];
        document.getElementById('imagen-principal').src = ruta;

        document.querySelectorAll('.thumbnail-btn').forEach(btn => {
            const img = btn.querySelector('img');
            if (img && img.src === ruta) {
                btn.classList.add('thumbnail-active', 'border-2');
                btn.classList.remove('border', 'border-gray-200');
            } else {
                btn.classList.remove('thumbnail-active', 'border-2');
                btn.classList.add('border', 'border-gray-200');
            }
        });
    }

    function reiniciarCantidad() {
        cantidad = 1;
        document.getElementById('cantidad-display').innerText = cantidad;
        document.getElementById('input-cantidad').value = cantidad;
    }

    function actualizarControlesCantidad() {
        const controls = document.getElementById('quantity-controls');
        const btnMinus = document.getElementById('btn-minus');
        const btnPlus = document.getElementById('btn-plus');

        if (!tallaSeleccionada || stockMaximo === 0) {
            controls.classList.add('opacity-50', 'cursor-not-allowed');
            controls.classList.remove('opacity-100');
            btnMinus.disabled = true;
            btnPlus.disabled = true;
            return;
        }

        controls.classList.remove('opacity-50', 'cursor-not-allowed');
        controls.classList.add('opacity-100');

        btnMinus.disabled = (cantidad <= 1);
        btnPlus.disabled = (cantidad >= stockMaximo);
    }

    function seleccionarColor(elemento, nombre, hex) {
        colorSeleccionado = nombre;
        document.getElementById('color-seleccionado').innerText = nombre;
        document.getElementById('input-color').value = nombre;

        document.querySelectorAll('.color-btn').forEach(btn => {
            btn.classList.remove('color-activo', 'color-activo-negro');
        });

        if (elemento.dataset.claro === '1') {
            elemento.classList.add('color-activo-negro');
        } else {
            elemento.classList.add('color-activo');
        }

        mostrarPrimeraImagenDelColor(nombre);

        document.querySelectorAll('.talla-btn').forEach(btn => {
            btn.classList.remove('talla-activa');
        });
        tallaSeleccionada = "";
        document.getElementById('input-talla').value = "";
        reiniciarCantidad();

        // Limpiamos el mensaje de error al cambiar de color
        document.getElementById('toast-error').classList.remove('opacity-100');
        document.getElementById('toast-error').classList.add('opacity-0');

        bloquearTallasPorColor();
        actualizarVarianteYStock();
    }

    function selectTalla(elemento) {
        if (elemento.disabled) return;
        tallaSeleccionada = elemento.dataset.talla;
        document.getElementById('input-talla').value = tallaSeleccionada;

        document.querySelectorAll('.talla-btn').forEach(btn => {
            btn.classList.remove('talla-activa');
        });
        elemento.classList.add('talla-activa');

        // Ocultamos el mensaje de error al seleccionar una talla
        const toast = document.getElementById('toast-error');
        toast.classList.remove('opacity-100');
        toast.classList.add('opacity-0');

        reiniciarCantidad();
        actualizarVarianteYStock();
    }

    function actualizarVarianteYStock() {
        if (!colorSeleccionado || !tallaSeleccionada) {
            stockMaximo = 0;
            actualizarControlesCantidad();
            return;
        }

        const variante = variantes.find(v =>
            v.color && v.talla &&
            v.color.trim().toLowerCase() === colorSeleccionado.trim().toLowerCase() &&
            v.talla.trim().toLowerCase() === tallaSeleccionada.trim().toLowerCase()
        );

        if (!variante) {
            stockMaximo = 0;
            actualizarControlesCantidad();
            return;
        }

        document.getElementById('input-variante').value = variante.id_variante;
        
        stockEnCarrito = parseInt(enCarrito[variante.id_variante] || 0);
        const totalStock = parseInt(variante.stock) || 0;
        stockMaximo = Math.max(totalStock - stockEnCarrito, 0);

        actualizarControlesCantidad();
    }

    function bloquearTallasPorColor() {
        document.querySelectorAll('.talla-btn').forEach(btn => {
            const talla = btn.dataset.talla;
            const variante = variantes.find(v =>
                v.color && v.talla &&
                v.color.trim().toLowerCase() === colorSeleccionado.trim().toLowerCase() &&
                v.talla.trim().toLowerCase() === talla.trim().toLowerCase()
            );
            
            if (!variante) {
                btn.disabled = true;
                btn.classList.add('talla-bloqueada');
            } else {
                const enCarritoCant = parseInt(enCarrito[variante.id_variante] || 0);
                const disponible = (parseInt(variante.stock) || 0) - enCarritoCant;
                
                if (disponible <= 0) {
                    btn.disabled = true;
                    btn.classList.add('talla-bloqueada');
                } else {
                    btn.disabled = false;
                    btn.classList.remove('talla-bloqueada');
                }
            }
        });
    }

    function aumentarCantidad() {
        if (cantidad >= stockMaximo) return;
        cantidad++;
        document.getElementById('cantidad-display').innerText = cantidad;
        document.getElementById('input-cantidad').value = cantidad;
        actualizarControlesCantidad();
    }

    function disminuirCantidad() {
        if (cantidad > 1) {
            cantidad--;
            document.getElementById('cantidad-display').innerText = cantidad;
            document.getElementById('input-cantidad').value = cantidad;
            actualizarControlesCantidad();
        }
    }

    function mostrarToast(mensaje) {
        const toast = document.getElementById('toast-error');
        toast.textContent = mensaje;
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
    }

    document.getElementById('form-carrito').addEventListener('submit', function(e) {
        const color = document.getElementById('input-color').value;
        const talla = document.getElementById('input-talla').value;

        if (!isAuth) {
            return; 
        }

        if (!talla) {
            e.preventDefault();
            mostrarToast('Debes seleccionar una talla antes de añadir.');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const primera = variantesConImagenes[0];
        if (!primera) return;

        const btnColor = document.querySelector(`.color-btn[data-color="${primera.color}"]`);

        if (btnColor) {
            colorSeleccionado = primera.color;
            document.getElementById('color-seleccionado').innerText = primera.color;
            document.getElementById('input-color').value = primera.color;

            document.querySelectorAll('.color-btn').forEach(b => b.classList.remove('color-activo', 'color-activo-negro'));

            if (btnColor.dataset.claro === '1') {
                btnColor.classList.add('color-activo-negro');
            } else {
                btnColor.classList.add('color-activo');
            }

            bloquearTallasPorColor();
        }
        
        tallaSeleccionada = "";
        document.getElementById('input-talla').value = "";
        actualizarControlesCantidad();
    });
</script>
@endpush