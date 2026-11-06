@extends('layouts.app')

@section('title', 'Mi bolsa - Xiaoxa')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-8 pt-6 pb-2">
    <div class="flex items-center justify-center gap-1 sm:gap-2 flex-wrap">
        <a href="{{ route('carrito.index') }}" class="flex items-center gap-1">
            <span class="text-sm sm:text-[20px] font-bold text-gray-800">Mi bolsa</span>
        </a>
        <x-heroicon-o-chevron-right class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 shrink-0" />
        <span class="text-sm sm:text-[20px] font-normal text-gray-800">Datos de compra</span>
        <x-heroicon-o-chevron-right class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 shrink-0" />
        <span class="text-sm sm:text-[20px] font-normal text-gray-800">Compra finalizada</span>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8" x-data="carritoData()" x-cloak>

    @if(count($items) > 0)

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

        <div class="lg:col-span-8 min-w-0">

            <div class="hidden sm:grid grid-cols-12 gap-4 pb-4 mb-2 border-b border-gray-200 text-sm text-gray-800">
                <div class="col-span-7">Producto</div>
                <div class="col-span-2 text-center flex justify-center">Precio</div>
                <div class="col-span-1 text-center flex justify-center">Cantidad</div>
                <div class="col-span-2 text-center">Subtotal</div>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($items as $id => $detalles)
                <div class="grid grid-cols-2 sm:grid-cols-12 gap-x-4 gap-y-3 sm:gap-4 items-center py-6"
                     data-item-id="{{ $id }}"
                     data-precio="{{ $detalles['precio'] }}">

                    <a href="{{ route('producto.show', $detalles['id_producto'] ?? '') }}"
                       class="col-span-2 sm:col-span-7 flex items-center gap-4 min-w-0">
                        <div class="relative shrink-0">
                            @if(!empty($detalles['imagen']))
                                <img src="{{ url('/api/imagen/' . $detalles['imagen']) }}"
                                     alt="{{ $detalles['nombre'] }}"
                                     class="w-24 h-32 object-cover bg-gray-100">
                            @else
                                <div class="w-24 h-32 bg-gray-100 flex items-center justify-center">
                                    <x-heroicon-o-photo class="w-7 h-7 text-gray-300" />
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col justify-center min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-sm text-gray-800 leading-tight">
                                    {{ ucwords(strtolower($detalles['nombre'])) }}
                                </h3>

                                @if(!empty($detalles['precio_oferta']))
                                    <span class="bg-red-500 text-white text-[11px] font-semibold leading-none min-w-[1px] text-center px-1 py-1 rounded-md shrink-0">
                                        -{{ abs($detalles['descuento'] ?? 50) }}%
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-gray-800 mt-1">
                                Talla: {{ $detalles['talla'] ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-800">
                                Color: {{ $detalles['color'] ?? '—' }}
                            </p>
                        </div>
                    </a>

                    <div class="col-span-1 sm:col-span-2 text-left sm:text-center">
                        <span class="sm:hidden text-xs text-gray-400 mr-1">Precio:</span>

                        @if(!empty($detalles['precio_oferta']))
                            <div class="inline-flex flex-col items-start sm:items-center leading-tight align-top">
                                <span class="text-xs text-gray-700 line-through">
                                    S/ {{ number_format($detalles['precio_normal'], 2) }}
                                </span>
                                <span class="text-sm text-red-500 font-semibold">
                                    S/ {{ number_format($detalles['precio'], 2) }}
                                </span>
                            </div>
                        @else
                            <span class="text-sm text-gray-900">
                                S/ {{ number_format($detalles['precio'], 2) }}
                            </span>
                        @endif
                    </div>

                    <div class="col-span-1 sm:col-span-1 flex flex-col items-end sm:items-center gap-1">
                        <div class="selector-cantidad flex items-center border-2 border-gray-400 rounded-lg overflow-hidden transition-opacity">
                            <button type="button"
                                    onclick="cambiarCantidad('{{ $id }}', -1, this)"
                                    class="w-8 h-8 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition">
                                <x-heroicon-o-minus class="w-3.5 h-3.5" />
                            </button>

                            <span class="w-8 text-center text-sm text-gray-800 cantidad-display">
                                {{ $detalles['cantidad'] }}
                            </span>

                            <button type="button"
                                    onclick="cambiarCantidad('{{ $id }}', 1, this)"
                                    data-stock="{{ $detalles['stock'] ?? 0 }}"
                                    class="btn-aumentar w-8 h-8 flex items-center justify-center text-gray-600 transition
                                           {{ ($detalles['cantidad'] ?? 0) >= ($detalles['stock'] ?? 0) ? 'cursor-not-allowed' : 'hover:bg-gray-100' }}"
                                    {{ ($detalles['cantidad'] ?? 0) >= ($detalles['stock'] ?? 0) ? 'disabled' : '' }}>
                                <x-heroicon-o-plus class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-2 text-right sm:text-center">
                        <span class="sm:hidden text-xs text-gray-400 mr-1">Subtotal:</span>
                        <span class="text-sm text-gray-800 subtotal-display">
                            S/ {{ number_format($detalles['precio'] * $detalles['cantidad'], 2) }}
                        </span>
                    </div>

                    <div class="col-span-2 sm:col-span-12 flex justify-end mt-1 sm:mt-0">
                        <button type="button" 
                                onclick="eliminarProducto('{{ $id }}', this.closest('[data-item-id]'))"
                                class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
                            Eliminar
                        </button>
                    </div>

                </div>
                @endforeach
            </div>

        </div>

        <div class="lg:col-span-4">
            <div class="lg:sticky lg:top-24 pt-4 lg:pt-16">

                <div class="p-0 sm:p-7 bg-transparent">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-800 text-sm">Total</span>
                        <span class="font-semibold text-gray-800 text-sm total-global">
                            S/ {{ number_format($total, 2) }}
                        </span>
                    </div>

                    {{-- Contenedor en web, apilado en móvil --}}
                    <div class="mt-7 flex flex-col-reverse sm:flex-row gap-3">
                        {{-- Botón Seguir Comprando: en web a la izq, en móvil abajo --}}
                        <a href="{{ route('home') }}" 
                           class="flex items-center justify-center w-full sm:w-auto bg-white border border-gray-300 text-gray-800 rounded-full py-4 px-8 text-[14px] font-semibold transition-all uppercase hover:bg-gray-50">
                            Seguir comprando
                        </a>

                        {{-- Botón Ir a Pagar: en web a la der, en móvil arriba --}}
                        <a href="{{ route('checkout.index') }}"
                           class="flex items-center justify-center w-full sm:flex-1 bg-gray-900 text-white rounded-full py-4 text-[14px] font-semibold transition-all uppercase">
                            Ir a pagar
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>

    @else

    <div class="flex flex-col items-center justify-center text-center py-24">
        <x-heroicon-o-shopping-bag class="w-16 h-16 mx-auto mb-4 text-gray-200" />
        <h3 class="text-lg font-bold text-gray-800 mb-2">Tu bolsa está vacía</h3>
        <p class="text-sm text-gray-500 max-w-xs mx-auto">
            Aún no has agregado ningún producto. Descubre nuestra colección y encuentra algo que te guste.
        </p>
    </div>

    @endif

</div>

@endsection

@push('scripts')
<script>
    // ... (Tu script de carrito se mantiene exactamente igual)
    function carritoData() {
        return {}
    }

    async function cambiarCantidad(idVariante, delta, btn) {
        const fila = btn.closest('[data-item-id]');
        if (!fila) return;

        const cantidadEl = fila.querySelector('.cantidad-display');
        const subtotalEl = fila.querySelector('.subtotal-display');
        const btnAumentar = fila.querySelector('.btn-aumentar');
        const selectorEl = fila.querySelector('.selector-cantidad');
        const precio = parseFloat(fila.dataset.precio) || 0;

        const cantidadActual = parseInt(cantidadEl.textContent.trim());
        const stock = btnAumentar ? parseInt(btnAumentar.dataset.stock) : 999;

        const nuevaCantidad = cantidadActual + delta;

        if (nuevaCantidad < 1) {
            await eliminarProducto(idVariante, fila);
            return;
        }

        if (nuevaCantidad > stock) return;

        if (selectorEl) {
            selectorEl.style.opacity = '0.4';
            selectorEl.style.pointerEvents = 'none';
        }

        try {
            let url;
            if (delta > 0) {
                url = `{{ url('/carrito/aumentar') }}/${idVariante}`;
            } else {
                url = `{{ url('/carrito/disminuir') }}/${idVariante}`;
            }

            await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            cantidadEl.textContent = nuevaCantidad;
            const nuevoSubtotal = precio * nuevaCantidad;
            subtotalEl.textContent = 'S/ ' + nuevoSubtotal.toFixed(2);

            recalcularTotalGlobal();

        } catch (error) {
        } finally {
            restaurarSelector(fila, nuevaCantidad, stock);
        }
    }

    async function eliminarProducto(idVariante, fila) {
        const selectorEl = fila.querySelector('.selector-cantidad');
        const btnEliminar = fila.querySelector('button[onclick*="eliminarProducto"]');
        
        if (selectorEl) {
            selectorEl.style.opacity = '0.4';
            selectorEl.style.pointerEvents = 'none';
        }
        if (btnEliminar) {
            btnEliminar.style.pointerEvents = 'none';
        }

        try {
            const url = `{{ url('/carrito/eliminar') }}/${idVariante}`;

            await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            fila.style.transition = 'opacity 0.2s, transform 0.2s';
            fila.style.opacity = '0';
            fila.style.transform = 'translateX(-20px)';

            setTimeout(() => {
                fila.remove();
                recalcularTotalGlobal();

                const quedanItems = document.querySelectorAll('[data-item-id]');
                if (quedanItems.length === 0) {
                    window.location.reload();
                }
            }, 200);

        } catch (error) {
            fila.style.opacity = '1';
            if (selectorEl) {
                selectorEl.style.opacity = '1';
                selectorEl.style.pointerEvents = 'auto';
            }
            if (btnEliminar) {
                btnEliminar.style.pointerEvents = 'auto';
            }
        }
    }

    function restaurarSelector(fila, cantidad, stock) {
        const selectorEl = fila.querySelector('.selector-cantidad');
        const btnAumentar = fila.querySelector('.btn-aumentar');

        if (!selectorEl) return;

        selectorEl.style.opacity = '1';
        selectorEl.style.pointerEvents = 'auto';

        if (btnAumentar) {
            if (cantidad >= stock) {
                btnAumentar.classList.add('cursor-not-allowed');
                btnAumentar.classList.remove('hover:bg-gray-100');
                btnAumentar.disabled = true;
            } else {
                btnAumentar.classList.remove('cursor-not-allowed');
                btnAumentar.classList.add('hover:bg-gray-100');
                btnAumentar.disabled = false;
            }
        }
    }

    function recalcularTotalGlobal() {
        let total = 0;

        document.querySelectorAll('[data-item-id]').forEach(fila => {
            const precio = parseFloat(fila.dataset.precio) || 0;
            const cantidad = parseInt(
                fila.querySelector('.cantidad-display').textContent.trim()
            );
            total += precio * cantidad;
        });

        const totalEl = document.querySelector('.total-global');
        if (totalEl) {
            totalEl.textContent = 'S/ ' + total.toFixed(2);
        }
    }
</script>
@endpush