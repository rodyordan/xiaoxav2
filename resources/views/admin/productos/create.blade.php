@extends('admin.layout')

@section('content')
    <div x-data="productoForm(
        @js(old('variantes', [['talla' => '', 'color' => '', 'color_hex' => '#000000', 'stock' => '', 'sku' => '']])),
        @js(old('detalles', ['']))
    )">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Nuevo Producto</h1>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.productos.index') }}"
                   class="bg-white text-gray-700 border border-gray-300 rounded-2xl py-4 px-14 text-base font-bold text-center
                          transition-colors duration-200
                          hover:bg-gray-50">
                    Cancelar
                </a>

                <button type="submit" form="form-nuevo-producto"
                        class="bg-indigo-600 text-white rounded-2xl py-4 px-14 text-base font-bold
                               transition-colors duration-200">
                    Guardar
                </button>
            </div>
        </div>

        <form id="form-nuevo-producto" action="{{ route('admin.productos.store') }}" method="POST"
            enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-6"
            x-data="{
                categoriaCheck: '{{ old('id_categoria', '') }}',
                errores: {},
                erroresVariantes: {},
                submitting: false,

                async validateForm(e) {
                    if (this.submitting) return;
                    this.errores = {};
                    this.erroresVariantes = {};

                    const nombre = this.$refs.nombreProducto.value.trim();
                    if (!nombre) this.errores.nombre_producto = true;

                    if (!this.categoriaCheck) this.errores.id_categoria = true;

                    const precio = this.$refs.precio.value.trim();
                    if (!precio || isNaN(precio) || parseFloat(precio) < 0) this.errores.precio = true;

                    const precioOferta = this.$refs.precioOferta.value.trim();
                    if (precioOferta && (isNaN(precioOferta) || parseFloat(precioOferta) >= parseFloat(precio))) {
                        this.errores.precio_oferta = true;
                    }

                    const detallesConTexto = this.detalles.filter(d => d && d.trim() !== '');
                    if (detallesConTexto.length === 0) {
                        this.errores.detalles = true;
                    }

                    this.variantes.forEach((variante, index) => {
                        const vErr = {};

                        if (!variante.talla || !variante.talla.trim()) vErr.talla = true;
                        if (!variante.color || !variante.color.trim()) vErr.color = true;
                        if (!variante.sku || !variante.sku.trim()) vErr.sku = true;

                        if (variante.stock === '' || variante.stock === null || variante.stock === undefined) {
                            vErr.stock = true;
                        } else if (isNaN(variante.stock) || parseInt(variante.stock) < 0) {
                            vErr.stock = true;
                        }

                        if (Object.keys(vErr).length > 0) {
                            this.erroresVariantes[index] = vErr;
                        }
                    });

                    // Detectar combinaciones duplicadas
                    const combinaciones = this.variantes.map(v => 
                        (v.talla || '').toLowerCase().trim() + '-' + (v.color || '').toLowerCase().trim()
                    );
                    const hayDuplicados = combinaciones.some((c, i) => combinaciones.indexOf(c) !== i);

                    if (hayDuplicados) {
                        combinaciones.forEach((c, i) => {
                            if (combinaciones.indexOf(c) !== i || combinaciones.lastIndexOf(c) !== i) {
                                if (!this.erroresVariantes[i]) this.erroresVariantes[i] = {};
                                this.erroresVariantes[i].talla = true;
                                this.erroresVariantes[i].color = true;
                            }
                        });
                    }

                    const hayErrores = Object.keys(this.errores).length > 0 || Object.keys(this.erroresVariantes).length > 0;

                    if (hayErrores || hayDuplicados) {
                        if (Object.keys(this.errores).length > 0) {
                            const primerError = Object.keys(this.errores)[0];
                            const ref = this.$refs[primerError + 'Container'];
                            if (ref) ref.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else if (Object.keys(this.erroresVariantes).length > 0) {
                            const primerIndex = Object.keys(this.erroresVariantes)[0];
                            const ref = this.$refs['varianteContainer' + primerIndex];
                            if (ref) ref.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }

                        if (hayDuplicados) {
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: { type: 'error', message: ['Hay combinaciones de Talla y Color duplicadas.'] }
                            }));
                        }
                        return;
                    }

                    this.submitting = true;

                    const form = e.target;
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
                                detail: { type: 'success', message: data.message || 'Producto creado exitosamente.' }
                            }));
                            setTimeout(() => window.location.href = '{{ route('admin.productos.index') }}', 500);
                        } else {
                            if (data.errors) {
                                Object.keys(data.errors).forEach(key => {
                                    if (key.startsWith('variantes.')) {
                                        const m = key.match(/^variantes\.(\d+)\./);
                                        if (m) {
                                            const idx = parseInt(m[1]);
                                            const campo = key.split('.').pop();
                                            if (!this.erroresVariantes[idx]) this.erroresVariantes[idx] = {};
                                            this.erroresVariantes[idx][campo] = true;
                                        }
                                    } else {
                                        this.errores[key] = true;
                                    }
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
                            detail: { type: 'error', message: ['Error de conexión. Intenta de nuevo.'] }
                        }));
                        this.submitting = false;
                    }
                }
            }"
            @submit.prevent="validateForm($event)">
            @csrf

            <div class="space-y-6">

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <x-heroicon-o-document-text class="w-5 h-5" />
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Información General</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">

                        <div class="md:col-span-2" x-ref="nombre_productoContainer">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Nombre del producto:</label>
                                <input name="nombre_producto" x-ref="nombreProducto" value="{{ old('nombre_producto') }}"
                                    @input="delete errores.nombre_producto"
                                    class="w-full px-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors"
                                    :class="errores.nombre_producto ? 'border-rose-500' : 'border-gray-200'">
                            </div>
                        </div>

                        <div x-ref="marcaContainer">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Marca:</label>
                                <input name="marca" value="{{ old('marca') }}"
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors">
                            </div>
                        </div>

                        <div x-ref="id_categoriaContainer"
                             x-data="{ open: false, categoriaTexto: '{{ old('id_categoria') ? addslashes(\App\Models\Categoria::find(old('id_categoria'))?->nombre_categoria ?? '') : 'Seleccionar' }}' }"
                             x-init="
                                $watch('categoriaCheck', value => {
                                    if (!value) categoriaTexto = 'Seleccionar';
                                    delete errores.id_categoria;
                                });
                             ">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Categoría:</label>
                                <input type="hidden" name="id_categoria" :value="categoriaCheck" x-model="categoriaCheck">
                                <div class="relative w-full">
                                    <button type="button" @click="open = !open" x-ref="id_categoria"
                                        class="w-full flex items-center justify-between gap-2 pl-4 pr-3 py-1.5 border rounded-full bg-gray-50 text-[14px] cursor-pointer transition-colors focus:outline-none focus:border-gray-400"
                                        :class="errores.id_categoria ? 'border-rose-500 text-rose-600' : 'border-gray-200 text-gray-600'">
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
                                            @foreach($categorias as $c)
                                                <button type="button"
                                                    @click="categoriaCheck = '{{ $c->id_categoria }}'; categoriaTexto = '{{ addslashes($c->nombre_categoria) }}'; open = false"
                                                    class="w-full text-left px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-50">
                                                    {{ $c->nombre_categoria }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-ref="precioContainer">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Precio:</label>
                                <div class="relative w-full">
                                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[14px]">S/</span>
                                    <input name="precio" x-ref="precio" type="text" inputmode="decimal" value="{{ old('precio') }}"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                        @input="delete errores.precio"
                                        class="w-full pl-9 pr-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors"
                                        :class="errores.precio ? 'border-rose-500' : 'border-gray-200'">
                                </div>
                            </div>
                        </div>

                        <div x-ref="precio_ofertaContainer">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Precio de oferta:</label>
                                <div class="relative w-full">
                                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[14px]">S/</span>
                                    <input name="precio_oferta" x-ref="precioOferta" type="text" inputmode="decimal" value="{{ old('precio_oferta') }}"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                        @input="delete errores.precio_oferta"
                                        class="w-full pl-9 pr-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors"
                                        :class="errores.precio_oferta ? 'border-rose-500' : 'border-gray-200'">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg transition-colors"
                                 :class="errores.detalles ? 'bg-rose-50 text-rose-600' : 'bg-indigo-50 text-indigo-600'">
                                <x-heroicon-o-list-bullet class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Detalles</h2>
                        </div>

                        <button type="button" @click="addDetalle"
                                class="flex items-center gap-2 text-sm font-black text-indigo-600">
                            <x-heroicon-o-plus-circle class="w-5 h-5" />
                            Añadir Detalle
                        </button>
                    </div>

                    <div class="space-y-2 pt-1.5">
                        <template x-for="(detalle, index) in detalles" :key="index">
                            <div class="relative group flex items-start gap-3">
                                <span class="w-2 h-2 rounded-full bg-gray-900 flex-shrink-0 mt-[12px]"></span>

                                <div class="relative flex-1">
                                    <textarea
                                        :name="`detalles[${index}]`"
                                        x-model="detalles[index]"
                                        rows="1"
                                        @input="delete errores.detalles"
                                        oninput="this.style.height = 'auto'; this.style.height = this.scrollHeight + 'px';"
                                        x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'; })"
                                        class="w-full px-4 py-1.5 pr-10 border rounded-2xl bg-gray-50 text-[14px] leading-5 focus:outline-none focus:border-gray-400 resize-none overflow-hidden"
                                        :class="errores.detalles ? 'border-rose-500' : 'border-gray-200'"></textarea>

                                    <button type="button" @click="removeDetalle(index)"
                                            x-show="detalles.length > 1"
                                            class="absolute -top-2 -right-2 w-6 h-6 bg-black text-white rounded-full flex items-center justify-center text-xs font-bold transition z-10 opacity-0 group-hover:opacity-100"
                                            title="Eliminar detalle">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                <x-heroicon-o-swatch class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Variantes del Producto</h2>
                        </div>

                        <button type="button" @click="addVariante"
                                class="flex items-center gap-2 text-sm font-black text-indigo-600">
                            <x-heroicon-o-plus-circle class="w-5 h-5" />
                            Añadir Variante
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(variante, index) in variantes" :key="index">
                            <div class="relative group pt-1.5 pb-2 px-3 rounded-2xl bg-gray-50 space-y-2"
                                 :x-ref="'varianteContainer' + index">

                                <button type="button" @click="removeVariante(index)"
                                        x-show="variantes.length > 1"
                                        class="absolute -top-2 -right-2 w-6 h-6 bg-black text-white rounded-full flex items-center justify-center text-xs font-bold transition z-10 opacity-0 group-hover:opacity-100"
                                        title="Eliminar variante">
                                    ✕
                                </button>

                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">SKU:</label>
                                        <input type="text" :name="`variantes[${index}][sku]`" x-model="variante.sku"
                                            @input="if (erroresVariantes[index]) delete erroresVariantes[index].sku"
                                            class="w-full px-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors"
                                            :class="erroresVariantes[index]?.sku ? 'border-rose-500' : 'border-gray-200'">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Talla:</label>
                                        <input type="text" :name="`variantes[${index}][talla]`" x-model="variante.talla"
                                            @input="if (erroresVariantes[index]) delete erroresVariantes[index].talla"
                                            class="w-[70px] px-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors"
                                            :class="erroresVariantes[index]?.talla ? 'border-rose-500' : 'border-gray-200'">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Stock:</label>
                                        <input type="text" inputmode="numeric" :name="`variantes[${index}][stock]`" x-model="variante.stock"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                            @input="if (erroresVariantes[index]) delete erroresVariantes[index].stock"
                                            class="w-[50px] px-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors"
                                            :class="erroresVariantes[index]?.stock ? 'border-rose-500' : 'border-gray-200'">
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-2 flex-1">
                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Color:</label>
                                        <input type="text" :name="`variantes[${index}][color]`" x-model="variante.color"
                                            @input="if (erroresVariantes[index]) delete erroresVariantes[index].color"
                                            class="w-full px-3 py-1.5 border rounded-full bg-gray-50 text-[14px] focus:outline-none focus:border-gray-400 transition-colors"
                                            :class="erroresVariantes[index]?.color ? 'border-rose-500' : 'border-gray-200'">
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Color Hex:</label>
                                        <div class="flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 focus-within:border-gray-400 transition-colors">
                                            <input type="color" :name="`variantes[${index}][color_hex]`" x-model="variante.color_hex"
                                                   class="w-5 h-6 rounded-md border-0 cursor-pointer p-0 flex-shrink-0"
                                                   style="background: transparent;">
                                            <input type="text"
                                                   x-model="variante.color_hex"
                                                   @input="variante.color_hex = variante.color_hex.toUpperCase()"
                                                   maxlength="7"
                                                   placeholder="#000000"
                                                   class="w-[60px] bg-transparent border-none outline-none font-mono font-bold text-[14px] uppercase focus:ring-0">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 sm:grid-cols-5 gap-2 pt-3">
                                    <template x-for="(file, imgIndex) in variante.files" :key="imgIndex">
                                        <div class="relative group/img aspect-[3/4]">
                                            <img :src="variante.previewUrls[imgIndex]"
                                                 @click="pickColorFromImage($event, index, imgIndex)"
                                                 class="w-full h-full object-cover cursor-crosshair"
                                                 title="Seleccionar color">

                                            <button type="button"
                                                    @click="removeImagen(index, imgIndex)"
                                                    class="absolute -top-2 -right-2 w-6 h-6 bg-black text-white rounded-full flex items-center justify-center text-xs font-bold transition shadow-md opacity-0 group-hover/img:opacity-100">
                                                ✕
                                            </button>
                                        </div>
                                    </template>

                                    <label class="aspect-[3/4] flex flex-col items-center justify-center bg-white border-2 border-dashed border-gray-200 cursor-pointer transition-all hover:border-indigo-300">
                                        <x-heroicon-o-cloud-arrow-up class="w-5 h-5 text-gray-300" />
                                        <span class="text-[9px] font-bold text-gray-400 mt-1">Añadir imagen</span>
                                        <span class="text-[7px] font-medium text-gray-300 mt-0.5">JPG, PNG, WEBP</span>
                                        <span class="text-[7px] font-medium text-gray-300">Máx 2MB</span>

                                        <input type="file"
                                               :name="`variantes[${index}][imagenes][]`"
                                               accept="image/jpeg,image/png,image/webp"
                                               multiple
                                               class="hidden"
                                               @change="handleImagenes($event, index)">
                                    </label>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </form>

        <script>
            function productoForm(initialVariantes, initialDetalles) {
                return {
                    variantes: (initialVariantes || []).map(v => ({
                        talla: v.talla ?? '',
                        color: v.color ?? '',
                        color_hex: (v.color_hex ?? '#000000').toUpperCase(),
                        stock: v.stock ?? '',
                        sku: v.sku ?? '',
                        files: [],
                        previewUrls: []
                    })),
                    detalles: (initialDetalles && initialDetalles.length > 0) ? initialDetalles : [''],

                    addVariante() {
                        this.variantes.push({
                            talla: '', color: '', color_hex: '#000000', stock: '', sku: '',
                            files: [], previewUrls: []
                        })
                    },
                    removeVariante(index) {
                        (this.variantes[index].previewUrls || []).forEach(u => URL.revokeObjectURL(u))
                        this.variantes.splice(index, 1)
                    },
                    addDetalle() {
                        this.detalles.push('')
                    },
                    removeDetalle(index) {
                        if (this.detalles.length > 1) this.detalles.splice(index, 1)
                        else this.detalles[0] = ''
                    },

                    handleImagenes(event, index) {
                        const nuevos = Array.from(event.target.files)
                        if (nuevos.length === 0) return

                        const variante = this.variantes[index]

                        variante.files = [...variante.files, ...nuevos]

                        variante.previewUrls.forEach(u => URL.revokeObjectURL(u))
                        variante.previewUrls = variante.files.map(f => URL.createObjectURL(f))

                        this.syncInputFiles(index, event.target)
                    },

                    removeImagen(index, imgIndex) {
                        const variante = this.variantes[index]

                        URL.revokeObjectURL(variante.previewUrls[imgIndex])

                        variante.files.splice(imgIndex, 1)
                        variante.previewUrls.splice(imgIndex, 1)

                        const input = this.$el.querySelector(`input[name="variantes[${index}][imagenes][]"]`)
                        if (input) this.syncInputFiles(index, input)
                    },

                    syncInputFiles(index, input) {
                        const dt = new DataTransfer()
                        this.variantes[index].files.forEach(f => dt.items.add(f))
                        input.files = dt.files
                    },

                    pickColorFromImage(event, index, imgIndex) {
                        const img = event.target
                        const rect = img.getBoundingClientRect()

                        const x = Math.floor((event.clientX - rect.left) * (img.naturalWidth / rect.width))
                        const y = Math.floor((event.clientY - rect.top) * (img.naturalHeight / rect.height))

                        const canvas = document.createElement('canvas')
                        canvas.width = img.naturalWidth
                        canvas.height = img.naturalHeight
                        const ctx = canvas.getContext('2d')
                        ctx.drawImage(img, 0, 0)

                        try {
                            const pixel = ctx.getImageData(x, y, 1, 1).data
                            const hex = '#' + [pixel[0], pixel[1], pixel[2]]
                                .map(c => c.toString(16).padStart(2, '0')).join('')
                                .toUpperCase()

                            this.variantes[index].color_hex = hex
                        } catch (e) {
                            console.warn('No se pudo leer el píxel (posible CORS):', e)
                        }
                    }
                }
            }
        </script>
    </div>
@endsection