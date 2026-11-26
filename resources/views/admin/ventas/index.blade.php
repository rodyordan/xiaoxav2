
@extends('admin.layout')

@section('content')

<style>
    .color-btn-venta {
        box-sizing: border-box;
        border: 0;
        transition: box-shadow 0.15s ease;
    }

    .color-btn-venta.activo-oscuro {
        box-shadow: inset 0 0 0 2px var(--color), inset 0 0 0 3.5px #fff;
    }

    .color-btn-venta.activo-claro {
        box-shadow: inset 0 0 0 2px var(--color), inset 0 0 0 3.5px #aaa;
    }

    .color-btn-venta.claro-sin-activar {
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12);
    }

    .color-btn-venta.claro-sin-activar.activo-oscuro {
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12), inset 0 0 0 3px var(--color), inset 0 0 0 4.5px #fff;
    }

    .color-btn-venta.claro-sin-activar.activo-claro {
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12), inset 0 0 0 3px var(--color), inset 0 0 0 4.5px #aaa;
    }

    .talla-btn-venta {
        box-sizing: border-box;
        font-weight: 600;
        color: #000 !important;
        background-color: #fff;
        border: 1.5px solid #9ca3af;
        border-radius: 0.375rem;
        text-decoration: none !important;
        cursor: pointer;
        transition: none;
    }

    .talla-btn-venta.talla-activa {
        background-color: #000 !important;
        color: #fff !important;
        border-color: #000 !important;
    }

    .talla-btn-venta:disabled,
    .talla-btn-venta.talla-bloqueada {
        color: #9ca3af !important;
        text-decoration: none !important;
        cursor: default !important;
    }

    .talla-btn-venta:not(:disabled):not(.talla-bloqueada) {
        cursor: pointer;
    }
</style>

<div x-data="ventaData()" x-cloak>

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-8">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Ventas</h1>
            <p class="text-gray-500 mt-2 text-lg font-medium">Registra las ventas de tu tienda física.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pb-10">

        {{-- IZQUIERDA: PRODUCTOS --}}
        <div class="lg:col-span-7 min-w-0">

            {{-- BUSCADOR Y FILTROS --}}
            <div class="mb-6 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">

                <div class="flex items-center gap-4 w-full mb-4">
                    <span class="text-sm font-bold text-gray-700 shrink-0">Buscar:</span>

                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-4 flex items-center text-gray-800">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                        </span>

                        <input type="text"
                               x-model="busqueda"
                               placeholder="Buscar por nombre de producto..."
                               class="w-full pl-12 pr-4 py-1.5 bg-[#f1f1f1] border border-gray-200 rounded-full text-gray-800 placeholder-gray-800 focus:outline-none focus:border-gray-400 transition-colors text-sm">
                    </div>
                </div>

                <div class="flex items-center gap-4 w-full flex-wrap">
                    <span class="text-sm font-bold text-gray-700 shrink-0">Filtros:</span>

                    {{-- CATEGORÍA --}}
                    <div x-data="{ open: false }" class="relative w-full max-w-[180px]">
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-2 pl-4 pr-4 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-sm text-gray-600 cursor-pointer focus:outline-none focus:border-gray-400 transition-colors">
                            <span x-text="categoriaTexto"></span>
                            <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0" />
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden">

                            <div class="max-h-64 overflow-y-auto py-1">
                                @foreach($categoriasFiltro ?? [] as $cat)
                                    <button type="button"
                                        @click="categoriaSel = '{{ addslashes($cat->nombre_categoria) }}'; categoriaTexto = '{{ addslashes($cat->nombre_categoria) }}'; open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                        {{ $cat->nombre_categoria }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- MARCA --}}
                    <div x-data="{ open: false }" class="relative w-full max-w-[180px]">
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-2 pl-4 pr-4 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-sm text-gray-600 cursor-pointer focus:outline-none focus:border-gray-400 transition-colors">
                            <span x-text="marcaTexto"></span>
                            <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0" />
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden">

                            <div class="max-h-64 overflow-y-auto py-1">
                                @foreach($marcasFiltro ?? [] as $marca)
                                    <button type="button"
                                        @click="marcaSel = '{{ addslashes($marca) }}'; marcaTexto = '{{ addslashes($marca) }}'; open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                        {{ $marca }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex-1"></div>

                    <button type="button"
                            @click="aplicarFiltros()"
                            class="shrink-0 px-6 py-1.5 bg-indigo-600 text-white rounded-full font-bold text-sm transition-colors duration-200">
                        Filtrar
                    </button>
                </div>
            </div>

            {{-- CONTENEDOR DE PRODUCTOS --}}
            <div class="bg-white rounded-[2.5rem] border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-8 items-start">

                        <template x-for="prod in productosFiltrados" :key="prod.id_producto">
                            <div x-init="inicializarProducto(prod)" class="flex flex-col">

                                {{-- IMAGEN --}}
                                <div class="relative aspect-[3/4] bg-[#f5f5f5] overflow-hidden rounded-xl shrink-0">

                                    <template x-if="prod.precio_oferta">
                                        <span class="absolute top-2.5 right-2.5 bg-red-500 text-white text-[15px] font-semibold leading-none min-w-[46px] text-center px-1 py-1.5 z-10 rounded-md shadow-sm">
                                            -<span x-text="prod.descuento"></span>%
                                        </span>
                                    </template>

                                    <template x-if="prod.imagen">
                                        <img :src="prod.imagen" :alt="prod.nombre"
                                             class="w-full h-full object-cover" loading="lazy">
                                    </template>

                                    <template x-if="!prod.imagen">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <x-heroicon-o-photo class="w-10 h-10 text-gray-300" />
                                        </div>
                                    </template>
                                </div>

                                {{-- INFORMACIÓN --}}
                                <div class="pt-3 space-y-1 shrink-0">
                                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest truncate h-[14px]"
                                       x-text="prod.marca"></p>

                                    <h3 class="text-[15px] font-normal text-gray-800 leading-snug line-clamp-2 h-auto"
                                        x-text="prod.nombre"></h3>

                                    <div class="flex items-center gap-2 flex-wrap pt-1 h-[28px]">
                                        <template x-if="prod.precio_oferta">
                                            <span class="text-xl font-normal text-red-500">
                                                S/ <span x-text="prod.precio_oferta.toFixed(2)"></span>
                                            </span>
                                        </template>

                                        <template x-if="prod.precio_oferta">
                                            <span class="text-sm text-gray-500 line-through">
                                                S/ <span x-text="prod.precio_normal.toFixed(2)"></span>
                                            </span>
                                        </template>

                                        <template x-if="!prod.precio_oferta">
                                            <span class="text-xl font-normal text-gray-900">
                                                S/ <span x-text="prod.precio.toFixed(2)"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                {{-- CONTROLES --}}
                                <div class="pt-3 space-y-3">

                                    {{-- COLOR --}}
                                    <div class="flex items-center gap-2 h-6">
                                        <p class="text-xs font-bold text-gray-900 shrink-0">Color:</p>

                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="(c, idx) in prod.colores" :key="idx">
                                                <button type="button"
                                                        @click="seleccionarColor(prod.id_producto, c.nombre)"
                                                        class="color-btn-venta w-6 h-6 rounded-full"
                                                        :class="{
                                                            'activo-oscuro': esColorSeleccionado(prod.id_producto, c.nombre) && !esClaro(c.hex),
                                                            'activo-claro': esColorSeleccionado(prod.id_producto, c.nombre) && esClaro(c.hex),
                                                            'claro-sin-activar': esClaro(c.hex)
                                                        }"
                                                        :style="`--color:${c.hex}; background-color:${c.hex};`"
                                                        :title="c.nombre">
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- TALLAS --}}
                                    <div class="flex items-start gap-2 min-h-[30px]">
                                        <p class="text-xs font-bold text-gray-900 shrink-0 mt-1">Talla:</p>

                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="v in tallasDeColor(prod, colorDe(prod.id_producto))" :key="v.talla">
                                                <button type="button"
                                                        @click="seleccionarTalla(prod.id_producto, v)"
                                                        :disabled="!v.id_variante || v.stock <= 0"
                                                        class="talla-btn-venta w-10 h-7 text-[11px] flex items-center justify-center"
                                                        :class="{
                                                            'talla-activa': esVarianteSeleccionada(prod.id_producto, v.id_variante),
                                                            'talla-bloqueada': !v.id_variante || v.stock <= 0
                                                        }">
                                                    <span x-text="v.talla"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- CANTIDAD + AÑADIR --}}
                                    <div class="flex items-center gap-3 pt-3 h-10">

                                        <div class="flex items-center border border-gray-400 rounded-lg overflow-hidden">

                                            <button type="button"
                                                    @click="restarCantidadProducto(prod.id_producto)"
                                                    :disabled="(cantidades[prod.id_producto] || 1) <= 1"
                                                    class="w-9 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition disabled:text-gray-300 disabled:hover:bg-transparent disabled:cursor-default">
                                                <x-heroicon-o-minus class="w-4 h-4" />
                                            </button>

                                            <span class="w-10 text-center text-sm font-semibold"
                                                  x-text="cantidades[prod.id_producto] || 1"></span>

                                            <button type="button"
                                                    @click="sumarCantidadProducto(prod.id_producto)"
                                                    :disabled="!seleccion[prod.id_producto]?.variante || (cantidades[prod.id_producto] || 1) >= (seleccion[prod.id_producto]?.variante?.stock || 0)"
                                                    class="w-9 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition disabled:text-gray-300 disabled:hover:bg-transparent disabled:cursor-default">
                                                <x-heroicon-o-plus class="w-4 h-4" />
                                            </button>
                                        </div>

                                        <button type="button"
                                                @click="agregarAlResumen(prod)"
                                                :disabled="!seleccion[prod.id_producto] || !seleccion[prod.id_producto].variante"
                                                class="flex-1 bg-indigo-600 text-white rounded-lg h-10 text-xs font-bold uppercase tracking-wider transition-all disabled:opacity-90 disabled:cursor-default">
                                            Añadir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="productosFiltrados.length === 0">
                            <div class="col-span-full p-12 text-center text-gray-400 text-sm">
                                No se encontraron productos.
                            </div>
                        </template>

                    </div>
                </div>
            </div>
        </div>

        {{-- DERECHA: RESUMEN DE VENTA --}}
        <div class="lg:col-span-5">
            <div class="bg-[#f1f1f1] rounded-[2.5rem] p-7 lg:sticky lg:top-6 min-h-[calc(100vh-20rem)] flex flex-col">

                <h2 class="font-black text-base text-gray-800 mb-5">Resumen de venta</h2>

                <div class="bg-white rounded-2xl overflow-hidden mb-5 flex-1 flex flex-col min-h-0">

                    <div class="grid grid-cols-12 gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100 text-[13px] font-bold text-gray-800">
                        <div class="col-span-7">Producto</div>
                        <div class="col-span-2 text-center">Cantidad</div>
                        <div class="col-span-3 text-right">Precio</div>
                    </div>

                    <div class="flex-1 overflow-y-auto divide-y divide-gray-100 flex flex-col min-h-0">

                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-2 px-4 py-3 items-center hover:bg-gray-50/60 transition-colors">

                                <div class="col-span-7 min-w-0">
                                    <p class="text-xs font-medium text-gray-800 leading-snug" x-text="item.nombre"></p>

                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        <span x-text="'Color: ' + item.color"></span>
                                        <span class="ml-2" x-text="'Talla: ' + item.talla"></span>
                                    </p>
                                </div>

                                <div class="col-span-2 flex flex-col items-center gap-1">
                                    <div class="flex items-center gap-0.5">

                                        <button @click="restarCantidadItem(index)"
                                                class="w-5 h-5 flex items-center justify-center border border-gray-400 rounded text-[11px] text-gray-500 hover:bg-gray-100 transition">
                                            −
                                        </button>

                                        <span class="w-6 text-center text-xs font-medium" x-text="item.cantidad"></span>

                                        <button @click="sumarCantidadItem(index)"
                                                :disabled="item.cantidad >= item.stock"
                                                class="w-5 h-5 flex items-center justify-center border border-gray-400 rounded text-[11px] text-gray-500 hover:bg-gray-100 transition disabled:text-gray-300 disabled:hover:bg-transparent disabled:cursor-default">
                                            +
                                        </button>

                                    </div>
                                </div>

                                <div class="col-span-3 flex items-center justify-end">
                                    <span class="text-xs font-medium text-gray-800 whitespace-nowrap">
                                        S/ <span x-text="(item.precio * item.cantidad).toFixed(2)"></span>
                                    </span>
                                </div>
                            </div>
                        </template>

                        <template x-if="items.length === 0">
                            <div class="flex-1 flex items-center justify-center px-4 py-8 text-center text-xs text-gray-400">
                                Sin productos agregados.
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-12 gap-2 px-4 py-4 border-t border-gray-100 text-[13px] font-bold text-gray-800">
                        <div class="col-span-7"></div>
                        <div class="col-span-2 text-center">Total</div>
                        <div class="col-span-3 text-right whitespace-nowrap">
                            S/ <span x-text="items.reduce((s, i) => s + (i.precio * i.cantidad), 0).toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                <template x-if="mensaje">
                    <div class="mb-4 text-xs rounded-2xl px-4 py-3 font-medium"
                         :class="mensaje.tipo === 'error' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100'">
                        <span x-text="mensaje.texto"></span>
                    </div>
                </template>

                <div class="flex gap-3">
                    <button type="button"
                            @click="cancelModal = true"
                            :disabled="items.length === 0 || enviando"
                            class="flex-1 bg-white text-gray-700 border border-gray-300 rounded-2xl py-4 text-base font-bold transition-colors duration-200 disabled:cursor-not-allowed hover:bg-gray-50">
                        Cancelar
                    </button>

                    <button type="button"
                        @click="registerModal = true"
                        :disabled="items.length === 0 || enviando"
                        class="flex-1 bg-indigo-600 text-white rounded-2xl py-4 text-base font-bold transition-colors duration-200 disabled:opacity-90 disabled:cursor-default">
                        <span x-show="!enviando">Registrar</span>
                        <span x-show="enviando">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CANCELAR VENTA --}}
    <template x-if="cancelModal">
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-4">
            <div @click="cancelModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>

            <div class="relative bg-white rounded-[2.5rem] px-8 py-7 max-w-sm w-full shadow-2xl text-center">
                <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-exclamation-triangle class="w-7 h-7" />
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">¿Cancelar venta?</h3>

                <p class="text-gray-500 text-sm font-medium mb-6 leading-relaxed">
                    Se perderán todos los productos agregados al resumen.
                </p>

                <div class="flex gap-3">
                    <button @click="cancelModal = false"
                        class="flex-1 py-3 bg-gray-100 text-gray-700 border border-gray-200 text-sm font-bold rounded-full transition">
                        Volver
                    </button>

                    <button @click="confirmarCancelarVenta()"
                        class="flex-1 py-3 bg-black text-white text-sm font-bold rounded-full transition">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL REGISTRAR VENTA --}}
    <template x-if="registerModal">
        <div class="fixed inset-0 z-[120] flex items-center justify-center p-4">
            <div @click="registerModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>

            <div class="relative bg-white rounded-[2.5rem] px-8 py-7 max-w-sm w-full shadow-2xl text-center">
                <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-exclamation-triangle class="w-7 h-7" />
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">¿Registrar venta?</h3>

                <p class="text-gray-500 text-sm font-medium mb-6 leading-relaxed">
                    Se guardará la venta con los productos del resumen.
                </p>

                <div class="flex gap-3">
                    <button @click="registerModal = false"
                        class="flex-1 py-3 bg-gray-100 text-gray-700 border border-gray-200 text-sm font-bold rounded-full transition">
                        Volver
                    </button>

                    <button @click="confirmarRegistrarVenta()"
                        class="flex-1 py-3 bg-black text-white text-sm font-bold rounded-full transition">
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function ventaData() {
    return {
        productos: @json($productosJson),
        busqueda: '',
        items: [],
        enviando: false,
        mensaje: null,

        cancelModal: false,
        registerModal: false,

        categoriaSel: '',
        categoriaTexto: 'Categoría',
        marcaSel: '',
        marcaTexto: 'Marca',

        seleccion: {},
        cantidades: {},

        get productosFiltrados() {
            const q = this.busqueda.trim().toLowerCase();

            return this.productos.filter(p => {
                if (q && !p.nombre.toLowerCase().includes(q)) return false;
                if (this.categoriaSel && p.categoria !== this.categoriaSel) return false;
                if (this.marcaSel && p.marca !== this.marcaSel) return false;

                return true;
            });
        },

        aplicarFiltros() {
            this.busqueda = this.busqueda.trim();
        },

        // Calcular el stock que queda después de añadir unidades al resumen.
        stockDisponible(idVariante) {
            const reservado = this.items
                .filter(item => item.id_variante === idVariante)
                .reduce((total, item) => total + item.cantidad, 0);

            const variante = this.productos
                .flatMap(p => Object.values(p.variantesAgrupadas || {}).flat())
                .find(v => v.id_variante === idVariante);

            if (!variante) return 0;

            return Math.max(0, variante.stock - reservado);
        },

        inicializarProducto(prod) {
            if (this.seleccion[prod.id_producto]) return;
            if (!prod.colores || prod.colores.length === 0) return;

            const primerColor = prod.colores[0].nombre;

            this.seleccion[prod.id_producto] = {
                color: primerColor,
                variante: null
            };

            this.cantidades[prod.id_producto] = 1;

            this.actualizarImagenColor(prod, primerColor);
        },

        colorDe(idProducto) {
            return this.seleccion[idProducto]?.color || null;
        },

        tallasDeColor(prod, color) {
            if (!prod.variantesAgrupadas || !color) return [];

            const grupo = prod.variantesAgrupadas[color] || [];

            // Obtener todas las tallas existentes en cualquiera de los colores.
            const todasLasTallas = [
                ...new Set(
                    Object.values(prod.variantesAgrupadas)
                        .flat()
                        .map(v => v.talla)
                )
            ];

            // Mostrar todas las tallas en cada color,
            // calculando el stock que queda de cada variante.
            return todasLasTallas.map(talla => {
                const variante = grupo.find(v => v.talla === talla);

                if (!variante) {
                    return {
                        id_variante: null,
                        talla: talla,
                        stock: 0
                    };
                }

                return {
                    ...variante,
                    stockOriginal: variante.stock,
                    stock: this.stockDisponible(variante.id_variante)
                };
            });
        },

        actualizarImagenColor(prod, color) {
            if (!prod.imagenesPorColor || !prod.imagenesPorColor[color]) return;

            const imgs = prod.imagenesPorColor[color];

            if (Array.isArray(imgs) && imgs.length > 0) {
                prod.imagen = imgs[0];
            }
        },

        esClaro(hex) {
            if (!hex) return false;

            hex = hex.replace('#', '');

            if (hex.length !== 6) return false;

            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);

            return (0.299 * r + 0.587 * g + 0.114 * b) > 230;
        },

        seleccionarColor(idProducto, color) {
            const actual = this.seleccion[idProducto];

            if (actual && actual.color === color) return;

            this.seleccion[idProducto] = {
                color: color,
                variante: null
            };

            this.cantidades[idProducto] = 1;

            const prod = this.productos.find(p => p.id_producto === idProducto);

            if (!prod) return;

            this.actualizarImagenColor(prod, color);
        },

        seleccionarTalla(idProducto, variante) {
            if (!this.seleccion[idProducto]) return;
            if (!variante.id_variante || variante.stock <= 0) return;

            this.seleccion[idProducto].variante = variante;
            this.cantidades[idProducto] = 1;
        },

        esColorSeleccionado(idProducto, color) {
            return this.seleccion[idProducto] &&
                   this.seleccion[idProducto].color === color;
        },

        esVarianteSeleccionada(idProducto, idVariante) {
            const sel = this.seleccion[idProducto];

            return sel && sel.variante &&
                   sel.variante.id_variante === idVariante;
        },

        sumarCantidadProducto(idProducto) {
            const sel = this.seleccion[idProducto];

            if (!sel || !sel.variante) return;

            const max = sel.variante.stock;
            const actual = this.cantidades[idProducto] || 1;

            if (actual < max) {
                this.cantidades[idProducto] = actual + 1;
            }
        },

        restarCantidadProducto(idProducto) {
            const actual = this.cantidades[idProducto] || 1;

            if (actual > 1) {
                this.cantidades[idProducto] = actual - 1;
            }
        },

        agregarAlResumen(prod) {
            const sel = this.seleccion[prod.id_producto];

            if (!sel || !sel.variante || !sel.variante.id_variante) return;

            const cant = this.cantidades[prod.id_producto] || 1;

            const existente = this.items.find(
                i => i.id_variante === sel.variante.id_variante
            );

            if (existente) {
                existente.cantidad = Math.min(
                    existente.cantidad + cant,
                    existente.stock
                );
            } else {
                this.items.push({
                    id_variante: sel.variante.id_variante,
                    nombre: prod.nombre,
                    color: sel.color,
                    talla: sel.variante.talla,
                    precio: prod.precio,
                    cantidad: cant,
                    stock: sel.variante.stockOriginal
                });
            }

            // Mantener el color, pero exigir que se seleccione
            // nuevamente una talla.
            this.seleccion[prod.id_producto] = {
                color: sel.color,
                variante: null
            };

            this.cantidades[prod.id_producto] = 1;
        },

        sumarCantidadItem(index) {
            const item = this.items[index];

            if (item.cantidad < item.stock) {
                item.cantidad++;
            }
        },

        restarCantidadItem(index) {
            const item = this.items[index];

            if (item.cantidad > 1) {
                item.cantidad--;
            } else {
                this.items.splice(index, 1);
            }
        },

        confirmarCancelarVenta() {
            this.items = [];
            this.mensaje = null;
            this.cancelModal = false;
        },

        confirmarRegistrarVenta() {
            this.registerModal = false;
            this.procesarRegistroVenta();
        },

        procesarRegistroVenta() {
            if (this.items.length === 0) return;

            this.enviando = true;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.ventas.store') }}';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);

            this.items.forEach((item, idx) => {
                const v = document.createElement('input');
                v.type = 'hidden';
                v.name = `items[${idx}][id_variante]`;
                v.value = item.id_variante;

                form.appendChild(v);

                const c = document.createElement('input');
                c.type = 'hidden';
                c.name = `items[${idx}][cantidad]`;
                c.value = item.cantidad;

                form.appendChild(c);
            });

            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

@endsection
