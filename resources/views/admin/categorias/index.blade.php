@extends('admin.layout')

@section('content')
    <div x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }} }">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-6">
            <div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Categorías</h1>
                <p class="text-gray-500 mt-2 text-lg font-medium">Administra las categorías de tus productos.</p>
            </div>

            <a @click="createModal = true"
                class="inline-flex items-center gap-3 bg-indigo-600 text-white px-7 py-4 rounded-2xl font-bold transition-colors duration-200 cursor-pointer">
                <x-heroicon-o-plus class="w-6 h-6" />
                Nueva Categoría
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($categorias as $categoria)
                <div class="group relative bg-white border border-gray-200 rounded-[2rem] p-6 shadow-sm transition-all duration-300">

                    <div class="absolute top-6 right-6">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $categoria->estado_categoria
                                ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                                : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $categoria->estado_categoria ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $categoria->estado_categoria ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div class="mb-6">
                        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-4">
                            <x-heroicon-o-folder class="w-7 h-7" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 leading-tight pr-20">
                            {{ $categoria->nombre_categoria }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 font-medium">
                            {{ $categoria->productos->count() }} {{ $categoria->productos->count() === 1 ? 'producto' : 'productos' }}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                        <button @click="$dispatch('open-edit-categoria', @js($categoria))"
                            class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 transition-all"
                            title="Editar">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                            Editar
                        </button>

                        <button @click="$dispatch('open-delete-categoria', @js($categoria))"
                            class="inline-flex items-center gap-2 text-sm font-bold text-rose-600 transition-all"
                            title="Eliminar">
                            <x-heroicon-o-trash class="w-4 h-4" />
                            Eliminar
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center bg-white rounded-[2.5rem] border-2 border-dashed border-gray-200">
                    <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-o-folder class="w-8 h-8 text-indigo-600" />
                    </div>
                    <p class="text-gray-500 font-bold text-lg">No hay categorías registradas.</p>
                    <p class="text-gray-400 font-medium mt-1">Crea tu primera categoría para empezar.</p>
                </div>
            @endforelse
        </div>

        <template x-if="createModal">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                 x-data="{
                    estadoCrear: '1',
                    estadoTextoCrear: 'Activo',
                    submitting: false,
                    errores: {},
                    async submitForm(e) {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errores = {};

                        const form = e.target;

                        const nombre = form.querySelector('input[name=nombre_categoria]').value.trim();
                        if (!nombre) this.errores.nombre_categoria = true;

                        if (Object.keys(this.errores).length > 0) {
                            this.submitting = false;
                            return;
                        }

                        const formData = new FormData(form);

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                            });

                            const data = await response.json();

                            if (response.ok) {
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: { type: 'success', message: data.message || 'Categoría creada correctamente.' }
                                }));
                                setTimeout(() => window.location.reload(), 500);
                            } else {
                                if (data.errors) {
                                    Object.keys(data.errors).forEach(key => {
                                        this.errores[key] = true;
                                    });
                                    const todos = Object.values(data.errors).flat();
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: { type: 'error', message: todos }
                                    }));
                                } else if (data.message) {
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: { type: 'error', message: data.message }
                                    }));
                                }
                                this.submitting = false;
                            }
                        } catch (error) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: 'Error de conexión. Intenta de nuevo.' }
                            }));
                            this.submitting = false;
                        }
                    }
                 }">
                <div @click="createModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                <div class="relative bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Nueva Categoría</h2>
                        </div>
                        <button @click="createModal = false" class="text-gray-500 transition -mt-1">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <form action="{{ route('admin.categorias.store') }}" method="POST" class="space-y-4"
                          @submit.prevent="submitForm($event)">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Nombre:</label>
                                <input type="text" name="nombre_categoria" autofocus
                                    @input="delete errores.nombre_categoria"
                                    class="w-full px-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-normal transition-colors"
                                    :class="errores.nombre_categoria ? 'border-rose-500' : 'border-gray-200'">
                            </div>

                            <div class="flex items-center gap-2" x-data="{ open: false }">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Estado:</label>
                                <input type="hidden" name="estado_categoria" :value="estadoCrear">
                                <div class="relative w-full">
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between gap-2 pl-4 pr-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 cursor-pointer focus:outline-none focus:border-gray-400 transition-colors">
                                        <span x-text="estadoTextoCrear"></span>
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
                                            <button type="button" @click="estadoCrear = '1'; estadoTextoCrear = 'Activo'; open = false"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                                Activo
                                            </button>
                                            <button type="button" @click="estadoCrear = '0'; estadoTextoCrear = 'Inactivo'; open = false"
                                                class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                                Inactivo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="createModal = false"
                                class="flex-1 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-full text-sm transition">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="submitting"
                                class="flex-1 py-3.5 bg-black text-white font-bold rounded-full text-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!submitting">Aceptar</span>
                                <span x-show="submitting" x-cloak>Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <div x-data="{
                editModal: false,
                categoria: {},
                estadoEdit: '1',
                estadoTextoEdit: 'Activo',
                submitting: false,
                errores: {},
                init() {
                    @if($errors->any() && old('_method') === 'PUT')
                        this.editModal = true;
                    @endif
                },
                async submitEdit(e) {
                    if (this.submitting) return;
                    this.submitting = true;
                    this.errores = {};

                    const form = e.target;

                    const nombre = form.querySelector('input[name=nombre_categoria]').value.trim();
                    if (!nombre) this.errores.nombre_categoria = true;

                    if (Object.keys(this.errores).length > 0) {
                        this.submitting = false;
                        return;
                    }

                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (response.ok) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'success', message: data.message || 'Categoría actualizada correctamente.' }
                            }));
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            if (data.errors) {
                                Object.keys(data.errors).forEach(key => {
                                    this.errores[key] = true;
                                });
                                const todos = Object.values(data.errors).flat();
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: { type: 'error', message: todos }
                                }));
                            } else if (data.message) {
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: { type: 'error', message: data.message }
                                }));
                            }
                            this.submitting = false;
                        }
                    } catch (error) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', message: 'Error de conexión. Intenta de nuevo.' }
                        }));
                        this.submitting = false;
                    }
                }
            }"
            @open-edit-categoria.window="
                categoria = $event.detail;
                estadoEdit = String(categoria.estado_categoria ?? 0);
                estadoTextoEdit = (estadoEdit === '1') ? 'Activo' : 'Inactivo';
                errores = {};
                editModal = true;
            ">
            <template x-if="editModal">
                <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                    <div @click="editModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                    <div class="relative bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl">

                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Editar Categoría</h2>
                                <p class="text-gray-500 mt-1 text-sm font-medium">Modifica los datos de la categoría.</p>
                            </div>
                            <button @click="editModal = false" class="text-gray-500 transition -mt-1">
                                <x-heroicon-o-x-mark class="w-6 h-6" />
                            </button>
                        </div>

                        <form :action="`/admin/categorias/${categoria.id_categoria}`" method="POST" class="space-y-4"
                              @submit.prevent="submitEdit($event)">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center gap-2">
                                    <label class="text-[14px] font-bold text-gray-800 shrink-0">Nombre:</label>
                                    <input type="text" name="nombre_categoria" :value="categoria.nombre_categoria"
                                        @input="delete errores.nombre_categoria"
                                        class="w-full px-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 font-normal transition-colors"
                                        :class="errores.nombre_categoria ? 'border-rose-500' : 'border-gray-200'">
                                </div>

                                <div class="flex items-center gap-2" x-data="{ open: false }">
                                    <label class="text-[14px] font-bold text-gray-800 shrink-0">Estado:</label>
                                    <input type="hidden" name="estado_categoria" :value="estadoEdit">
                                    <div class="relative w-full">
                                        <button type="button" @click="open = !open"
                                            class="w-full flex items-center justify-between gap-2 pl-4 pr-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 cursor-pointer focus:outline-none focus:border-gray-400 transition-colors">
                                            <span x-text="estadoTextoEdit"></span>
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
                                                <button type="button" @click="estadoEdit = '1'; estadoTextoEdit = 'Activo'; open = false"
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                                    Activo
                                                </button>
                                                <button type="button" @click="estadoEdit = '0'; estadoTextoEdit = 'Inactivo'; open = false"
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                                    Inactivo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="editModal = false"
                                    class="flex-1 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-full text-sm transition">
                                    Cancelar
                                </button>
                                <button type="submit" :disabled="submitting"
                                    class="flex-1 py-3.5 bg-black text-white font-bold rounded-full text-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span x-show="!submitting">Aceptar</span>
                                    <span x-show="submitting" x-cloak>Guardando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        <div x-data="{ deleteModal: false, categoriaDelete: {} }"
             @open-delete-categoria.window="categoriaDelete = $event.detail; deleteModal = true">
            <template x-if="deleteModal">
                <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                    <div @click="deleteModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                    <div class="relative bg-white rounded-[2.5rem] p-10 max-w-sm w-full shadow-2xl text-center">
                        <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
                            <x-heroicon-o-exclamation-triangle class="w-10 h-10" />
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">¿Estás seguro?</h3>
                        <p class="text-gray-500 font-medium mb-8">
                            Vas a eliminar <span class="font-bold text-gray-800" x-text="categoriaDelete.nombre_categoria"></span>.
                            Se eliminarán también todos sus productos asociados. Esta acción no se puede deshacer.
                        </p>
                        <form :action="`/admin/categorias/${categoriaDelete.id_categoria}`" method="POST" class="flex gap-3">
                            @csrf @method('DELETE')
                            <button type="button" @click="deleteModal = false"
                                class="flex-1 py-3.5 bg-gray-100 text-gray-700 font-bold rounded-full text-sm transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="flex-1 py-3.5 bg-black text-white font-bold rounded-full text-sm transition">
                                Aceptar
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>

    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
@endsection