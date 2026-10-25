@extends('admin.layout')

@section('content')
<div x-data="{ createModal: {{ $errors->any() ? 'true' : 'false' }} }">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-6">
        <div>
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Banners</h1>
            <p class="text-gray-500 mt-2 text-lg font-medium">Gestiona los banners del carrusel principal.</p>
        </div>

        <button @click="createModal = true"
            class="inline-flex items-center gap-3 bg-indigo-600 text-white px-7 py-4 rounded-2xl font-bold transition-colors duration-200">
            <x-heroicon-o-plus class="w-6 h-6" />
            Nuevo Banner
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($banners as $banner)
            <div class="group relative bg-white border border-gray-200 overflow-hidden shadow-sm transition-all duration-300">

                <div class="relative w-full h-56 bg-gray-100 overflow-hidden">
                    <img src="{{ url('/api/imagen/' . $banner->imagen) }}"
                         alt="{{ $banner->titulo }}"
                         class="w-full h-full object-cover"
                         onerror="this.src='https://placehold.co/800x400/e5e7eb/9ca3af?text=Sin+imagen'">

                    <div class="absolute top-4 left-4 right-32">
                        <span class="inline-block bg-black/70 backdrop-blur-sm text-white text-sm font-bold px-3 py-1.5 rounded-full truncate max-w-full shadow-lg">
                            {{ $banner->titulo }}
                        </span>
                    </div>

                    <div class="absolute top-4 right-4">
                        <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-md
                            {{ $banner->estado
                                ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                                : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $banner->estado ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $banner->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-12">

                        <button @click="$dispatch('open-edit-banner', @js($banner))"
                            class="flex flex-col items-center gap-2 transition-all hover:scale-110"
                            title="Editar">
                            <span class="w-14 h-14 rounded-full bg-indigo-600 flex items-center justify-center shadow-lg">
                                <x-heroicon-o-pencil-square class="w-7 h-7 text-white" />
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wider text-white">Editar</span>
                        </button>

                        <button @click="$dispatch('open-delete-banner', @js($banner))"
                            class="flex flex-col items-center gap-2 transition-all hover:scale-110"
                            title="Eliminar">
                            <span class="w-14 h-14 rounded-full bg-rose-500 flex items-center justify-center shadow-lg">
                                <x-heroicon-o-trash class="w-7 h-7 text-white" />
                            </span>
                            <span class="text-xs font-bold uppercase tracking-wider text-white">Eliminar</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-white border-2 border-dashed border-gray-200">
                <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <x-heroicon-o-photo class="w-8 h-8 text-indigo-600" />
                </div>
                <p class="text-gray-500 font-bold text-lg">No hay banners registrados.</p>
                <p class="text-gray-400 font-medium mt-1">Crea tu primer banner para el carrusel.</p>
            </div>
        @endforelse
    </div>

    <template x-if="createModal">
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             x-data="{
                estadoCrear: '{{ old('estado', 1) }}',
                estadoTextoCrear: '{{ old('estado', 1) == 1 ? 'Activo' : 'Inactivo' }}',
                fileName: '',
                submitting: false,
                errores: {},
                async submitForm(e) {
                    if (this.submitting) return;
                    this.submitting = true;
                    this.errores = {};

                    const form = e.target;

                    const titulo = form.querySelector('input[name=titulo]').value.trim();
                    if (!titulo) this.errores.titulo = true;

                    const imagen = form.querySelector('input[name=imagen]').files.length;
                    if (imagen === 0) this.errores.imagen = true;

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
                                detail: { type: 'success', message: data.message || 'Banner creado correctamente.' }
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
            <div class="relative bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl max-h-[92vh] overflow-y-auto">

                <div class="flex justify-between items-start mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Nuevo Banner</h2>
                        <p class="text-gray-500 mt-1 text-sm font-medium">Configura el banner para el carrusel.</p>
                    </div>
                    <button @click="createModal = false" class="text-gray-500 transition -mt-1">
                        <x-heroicon-o-x-mark class="w-6 h-6" />
                    </button>
                </div>

                <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                      @submit.prevent="submitForm($event)">
                    @csrf

                    <div class="flex items-center gap-2">
                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Título:</label>
                        <input type="text" name="titulo" value="{{ old('titulo') }}" maxlength="100"
                            @input="delete errores.titulo"
                            class="w-full px-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-normal transition-colors"
                            :class="errores.titulo ? 'border-rose-500' : 'border-gray-200'">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-[14px] font-bold text-gray-800 shrink-0">URL de redireccionamiento:</label>
                        <input type="text" name="url_boton" value="{{ old('url_boton') }}" maxlength="500"
                            class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-normal transition-colors">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-[14px] font-bold text-gray-800 shrink-0">Orden de aparición:</label>
                            <input name="orden" type="text" inputmode="numeric" value="{{ old('orden', 0) }}"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] font-normal focus:outline-none focus:border-gray-400 transition-colors">
                        </div>

                        <div class="flex items-center gap-2" x-data="{ open: false }">
                            <label class="text-[14px] font-bold text-gray-800 shrink-0">Estado:</label>
                            <input type="hidden" name="estado" :value="estadoCrear">
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

                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2 ml-1">Imagen:</label>
                        <label
                            class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed rounded-2xl cursor-pointer transition-all"
                            :class="errores.imagen ? 'border-rose-500 bg-rose-50/30' : 'border-gray-300 bg-gray-50 hover:border-indigo-400'">

                            <div :class="errores.imagen ? 'text-rose-500' : 'text-gray-300'" class="transition-colors mb-2">
                                <x-heroicon-o-cloud-arrow-up class="w-8 h-8" />
                            </div>

                            <span class="text-sm font-bold px-4 text-center truncate w-full max-w-[250px]"
                                :class="errores.imagen ? 'text-rose-500' : 'text-gray-400'"
                                x-text="fileName || 'Haz clic para subir imagen'"></span>
                            <span class="text-xs mt-1"
                                :class="errores.imagen ? 'text-rose-400' : 'text-gray-300'">JPG, PNG, WEBP — Max 2MB</span>
                            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="hidden"
                                @change="fileName = $event.target.files[0]?.name || ''; delete errores.imagen">
                        </label>
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
            banner: {},
            estadoEdit: '1',
            estadoTextoEdit: 'Activo',
            fileName: '',
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

                const titulo = form.querySelector('input[name=titulo]').value.trim();
                if (!titulo) this.errores.titulo = true;

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
                            detail: { type: 'success', message: data.message || 'Banner actualizado correctamente.' }
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
        @open-edit-banner.window="
            banner = $event.detail;
            estadoEdit = String(banner.estado);
            estadoTextoEdit = (banner.estado == 1) ? 'Activo' : 'Inactivo';
            fileName = '';
            errores = {};
            editModal = true;
        ">
        <template x-if="editModal">
            <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div @click="editModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                <div class="relative bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl max-h-[92vh] overflow-y-auto">

                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Editar Banner</h2>
                            <p class="text-gray-500 mt-1 text-sm font-medium">Modifica los datos del banner.</p>
                        </div>
                        <button @click="editModal = false" class="text-gray-500 transition -mt-1">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <form :action="`/admin/banners/${banner.id_banner}`" method="POST" enctype="multipart/form-data" class="space-y-4"
                          @submit.prevent="submitEdit($event)">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="flex items-center gap-2">
                            <label class="text-[14px] font-bold text-gray-800 shrink-0">Título:</label>
                            <input type="text" name="titulo" :value="banner.titulo" maxlength="100"
                                @input="delete errores.titulo"
                                class="w-full px-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-normal transition-colors"
                                :class="errores.titulo ? 'border-rose-500' : 'border-gray-200'">
                        </div>

                        <div class="flex items-center gap-2">
                            <label class="text-[14px] font-bold text-gray-800 shrink-0">URL de redireccionamiento:</label>
                            <input type="text" name="url_boton" :value="banner.url_boton" maxlength="500"
                                class="w-full px-3 py-1.5 bg-gray-50 border border-gray-200 rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-normal transition-colors">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Orden de aparición:</label>
                                <input name="orden" type="text" inputmode="numeric" :value="banner.orden"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] font-normal focus:outline-none focus:border-gray-400 transition-colors">
                            </div>

                            <div class="flex items-center gap-2" x-data="{ open: false }">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Estado:</label>
                                <input type="hidden" name="estado" :value="estadoEdit">
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

                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2 ml-1">Imagen:</label>
                            <label class="relative block w-full h-40 rounded-2xl overflow-hidden bg-gray-100 border cursor-pointer group"
                                :class="errores.imagen ? 'border-rose-500' : 'border-gray-200'">
                                <img :src="`/api/imagen/${banner.imagen}`"
                                     :alt="banner.titulo"
                                     class="w-full h-full object-cover">

                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white">
                                    <x-heroicon-o-arrow-path class="w-8 h-8 mb-2" />
                                    <span class="text-sm font-bold truncate max-w-[200px]" x-text="fileName || 'Cambiar imagen'"></span>
                                </div>

                                <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" class="hidden"
                                    @change="fileName = $event.target.files[0]?.name || ''; delete errores.imagen">
                            </label>
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

    <div x-data="{ deleteModal: false, bannerDelete: {} }"
         @open-delete-banner.window="bannerDelete = $event.detail; deleteModal = true">
        <template x-if="deleteModal">
            <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div @click="deleteModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                <div class="relative bg-white rounded-[2.5rem] p-10 max-w-sm w-full shadow-2xl text-center">
                    <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-heroicon-o-exclamation-triangle class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">¿Estás seguro?</h3>
                    <p class="text-gray-500 font-medium mb-8">
                        Vas a eliminar <span class="font-bold text-gray-800" x-text="bannerDelete.titulo"></span>.
                    </p>
                    <form :action="`/admin/banners/${bannerDelete.id_banner}`" method="POST" class="flex gap-3">
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
@endsection