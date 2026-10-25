@extends('admin.layout')

@section('content')

    <div x-data="{ deleteModal: false, activeId: null, errorStockModal: false }">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-8">
            <div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Productos</h1>
                <p class="text-gray-500 mt-2 text-lg font-medium">Gestiona tu inventario, precios y disponibilidad.</p>
            </div>

            <a href="{{ route('admin.productos.create') }}"
                class="inline-flex items-center gap-3 bg-indigo-600 text-white px-7 py-4 rounded-2xl font-bold transition-colors duration-200">
                <x-heroicon-o-plus class="w-6 h-6" />
                Nuevo Producto
            </a>
        </div>

        <form action="{{ route('admin.productos.index') }}" method="GET"
            x-data="{
                categoria: '',
                categoriaTexto: 'Categoría',
                marca: '',
                marcaTexto: 'Marca',
                stock: '',
                stockTexto: 'Stock',

                applyFilter() {
                    const form = $el;
                    const params = new URLSearchParams();

                    const buscar = form.querySelector('input[name=buscar]').value.trim();
                    if (buscar) params.append('buscar', buscar);
                    if (this.categoria) params.append('categoria', this.categoria);
                    if (this.marca) params.append('marca', this.marca);
                    if (this.stock) params.append('stock', this.stock);

                    form.querySelector('input[name=buscar]').value = '';
                    this.categoria = '';
                    this.categoriaTexto = 'Categoría';
                    this.marca = '';
                    this.marcaTexto = 'Marca';
                    this.stock = '';
                    this.stockTexto = 'Stock';

                    window.location.href = form.action + '?' + params.toString();
                }
            }"
            x-on:submit.prevent="applyFilter()"
            class="mb-6 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4 w-full flex-wrap">

                <span class="text-sm font-bold text-gray-700 shrink-0">Buscar:</span>
                <div class="relative flex-1 min-w-[295px] max-w-[400px]">
                    <span class="absolute inset-y-0 left-4 flex items-center text-gray-800">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    </span>
                    <input type="text" name="buscar" value="" placeholder="Buscar por nombre..."
                        class="w-full pl-12 pr-4 py-1.5 bg-[#f1f1f1] border border-gray-200 rounded-full text-gray-800 placeholder-gray-800 focus:outline-none focus:border-gray-400 transition-colors text-sm">
                </div>

                <span class="text-sm font-bold text-gray-700 shrink-0">Filtros:</span>

                <div x-data="{ open: false }" class="relative w-full max-w-[180px]">
                    <input type="hidden" name="categoria" :value="categoria">

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
                                <button type="button" @click="categoria = '{{ $cat->id_categoria }}'; categoriaTexto = '{{ addslashes($cat->nombre_categoria) }}'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                    {{ $cat->nombre_categoria }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative w-full max-w-[180px]">
                    <input type="hidden" name="marca" :value="marca">

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
                                <button type="button" @click="marca = '{{ addslashes($marca) }}'; marcaTexto = '{{ addslashes($marca) }}'; open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                    {{ $marca }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div x-data="{ open: false }" class="relative w-full max-w-[180px]">
                    <input type="hidden" name="stock" :value="stock">

                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between gap-2 pl-4 pr-4 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-sm text-gray-600 cursor-pointer focus:outline-none focus:border-gray-400 transition-colors">
                        <span x-text="stockTexto"></span>
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
                            <button type="button" @click="stock = 'agotado'; stockTexto = 'Agotado'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                Agotado
                            </button>
                            <button type="button" @click="stock = 'bajo'; stockTexto = 'Bajo stock (1-10)'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                Bajo stock (1-10)
                            </button>
                            <button type="button" @click="stock = 'disponible'; stockTexto = 'Stock disponible'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                Stock disponible
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex-1"></div>

                <button type="submit"
                    class="shrink-0 px-6 py-1.5 bg-indigo-600 text-white rounded-full font-bold text-sm transition-colors duration-200">
                    Filtrar
                </button>

            </div>
        </form>

        <div class="bg-white rounded-[2.5rem] border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#f1f1f1]">
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-left border-b border-gray-200">Producto</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Precio</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Precio oferta</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Stock</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Estado</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-right border-b border-gray-200">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($productos as $producto)
                            <tr class="odd:bg-white even:bg-[#f1f1f1]/40">
                                <td class="px-8 py-5">
                                    <p class="font-normal text-gray-800 text-base leading-tight">
                                        {{ $producto->nombre_producto }}
                                    </p>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    <span class="font-normal text-gray-800 text-base">
                                        S/ {{ number_format($producto->precio, 2) }}
                                    </span>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($producto->precio_oferta && $producto->precio_oferta > 0)
                                        <span class="font-normal text-gray-800 text-base">
                                            S/ {{ number_format($producto->precio_oferta, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-800 text-base font-normal">NA</span>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($producto->stock <= 0)
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-4 py-1.5 rounded-full whitespace-nowrap">
                                            Agotado
                                        </span>
                                    @else
                                        <span class="text-gray-800 font-normal text-base">{{ $producto->stock }}</span>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($producto->estado_producto)
                                        <span class="inline-flex items-center gap-1.5 py-1.5 px-4 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-100">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 py-1.5 px-4 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500 border border-gray-200">
                                            Inactivo
                                        </span>
                                    @endif
                                </td>

                                <td class="px-8 py-5">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.productos.edit', $producto->id_producto) }}"
                                            class="p-2 text-indigo-600 transition-all"
                                            title="Editar">
                                            <x-heroicon-o-pencil-square class="w-5 h-5" />
                                        </a>

                                        <button
                                            @click="if({{ $producto->stock }} > 0) { errorStockModal = true } else { deleteModal = true; activeId = {{ $producto->id_producto }} }"
                                            class="p-2 text-rose-600 transition-all"
                                            title="Eliminar">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-gray-400 text-sm">
                                    No hay productos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($productos->hasPages())
                <div class="px-8 py-6 bg-[#f1f1f1]/50 border-t border-gray-200">
                    {{ $productos->links() }}
                </div>
                @endif
            </div>
        </div>

        <template x-if="deleteModal">
            <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div @click="deleteModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                <div class="relative bg-white rounded-[3rem] p-10 max-w-sm w-full shadow-2xl text-center">
                    <div class="mx-auto w-20 h-20 flex items-center justify-center rounded-full bg-rose-50 text-rose-500 mb-6 font-bold">
                        <x-heroicon-o-trash class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">¿Eliminar producto?</h3>
                    <p class="text-gray-500 mb-10 font-medium">Esta acción no se puede deshacer.</p>
                    <div class="flex flex-col gap-3">
                        <form :action="'{{ route('admin.productos.index') }}/' + activeId" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-full py-4 bg-black text-white font-bold rounded-full transition">Aceptar</button>
                        </form>
                        <button @click="deleteModal = false"
                            class="w-full py-4 bg-gray-100 text-gray-700 border border-gray-200 font-bold rounded-full transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="errorStockModal">
            <div class="fixed inset-0 z-[120] flex items-center justify-center p-4">
                <div @click="errorStockModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                <div class="relative bg-white rounded-[3rem] p-10 max-w-sm w-full shadow-2xl text-center">
                    <div class="mx-auto w-20 h-20 flex items-center justify-center rounded-full bg-amber-50 text-amber-500 mb-6">
                        <x-heroicon-o-exclamation-circle class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Acción denegada</h3>
                    <p class="text-gray-500 mb-10 font-medium leading-relaxed">
                        No puedes eliminar un producto que aún tiene <span class="text-amber-600 font-bold">stock disponible</span>. Debes agotar el inventario antes de retirarlo.
                    </p>
                    <button @click="errorStockModal = false"
                        class="w-full py-4 bg-black text-white font-bold rounded-full transition">
                        Entendido
                    </button>
                </div>
            </div>
        </template>

    </div>

@endsection