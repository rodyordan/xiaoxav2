@extends('admin.layout')

@section('content')

    <style>
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }

        .paso-clickable { cursor: pointer; }
        .paso-clickable:disabled { cursor: not-allowed; }
    </style>

    <div x-data="pedidoEstado({{ Js::from($pedido->estado_pedido) }})">

        {{-- Header con título y botones a la derecha --}}
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Detalles del Pedido</h1>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.pedidos.index') }}"
                   class="bg-white text-gray-700 border border-gray-300 rounded-2xl py-4 px-14 text-base font-bold text-center
                          transition-colors duration-200
                          hover:bg-gray-50">
                    Cancelar
                </a>

                <button type="button"
                        @click="enviarFormulario('guardar')"
                        class="bg-indigo-600 text-white rounded-2xl py-4 px-14 text-base font-bold
                               transition-colors duration-200">
                    Guardar
                </button>
            </div>
        </div>

        <form id="form-actualizar-pedido"
              action="{{ route('admin.pedidos.update', $pedido->id_pedido) }}"
              method="POST"
              enctype="multipart/form-data">
            @csrf @method('PUT')

            <input type="hidden" name="estado_pedido" :value="estadoActual">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- COLUMNA IZQUIERDA: PEDIDO --}}
                <div class="lg:col-span-2 space-y-6">

                    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">

                        {{-- HEADER: Pedido + código + Fecha --}}
                        <div class="flex items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                    <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Pedido</h2>
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-900 rounded-lg text-sm font-bold">{{ $pedido->numero_pedido }}</span>
                            </div>

                            <p class="text-sm font-medium italic text-gray-500">
                                {{ \Carbon\Carbon::parse($pedido->created_at)->translatedFormat('d \d\e F \d\e\l Y') }}
                            </p>
                        </div>

                        {{-- Fila 1: Cliente | Tipo de entrega --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Cliente:</label>
                                <input type="text" readonly
                                    value="{{ $pedido->usuario->nombres }} {{ $pedido->usuario->apellidos }}"
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                            </div>

                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Tipo de entrega:</label>
                                <input type="text" readonly
                                    value="{{ $pedido->tipoEntrega?->nombre_tipo_entrega ?? '—' }}"
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                            </div>
                        </div>

                        {{-- Fila 2: Tipo de documento | Número de documento --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Tipo de documento:</label>
                                <input type="text" readonly
                                    value="{{ $pedido->usuario->tipoDocumento->nombre_tipo_documento ?? 'N/A' }}"
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                            </div>

                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Número de documento:</label>
                                <input type="text" readonly
                                    value="{{ $pedido->usuario->numero_documento ?? 'N/A' }}"
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                            </div>
                        </div>

                        {{-- Fila 3: Teléfono --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Teléfono:</label>
                                <input type="text" readonly
                                    value="{{ $pedido->usuario->telefono ?? 'N/A' }}"
                                    class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                            </div>
                        </div>

                        @if($pedido->id_tipo_entrega == 2)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="flex items-center gap-2">
                                    <label class="text-[14px] font-bold text-gray-800 shrink-0">Departamento:</label>
                                    <input type="text" readonly value="{{ $pedido->departamento->nombre_departamento ?? '—' }}"
                                        class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="text-[14px] font-bold text-gray-800 shrink-0">Provincia:</label>
                                    <input type="text" readonly value="{{ $pedido->provincia->nombre_provincia ?? '—' }}"
                                        class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="text-[14px] font-bold text-gray-800 shrink-0">Distrito:</label>
                                    <input type="text" readonly value="{{ $pedido->distrito->nombre_distrito ?? '—' }}"
                                        class="w-full px-3 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none">
                                </div>
                            </div>
                        @else
                            <div class="mb-2"></div>
                        @endif

                        {{-- TABLA DE PRODUCTOS --}}
                        <div class="overflow-hidden rounded-2xl border border-gray-100">
                            <table class="w-full text-left border-collapse table-fixed">
                                <thead>
                                    <tr class="bg-[#f1f1f1]">
                                        <th class="w-[45%] px-5 py-4 text-base font-black text-gray-800 text-left border-b border-gray-200">Producto</th>
                                        <th class="w-[18%] px-2 py-4 text-base font-black text-gray-800 text-center border-b border-gray-200">Precio Unit.</th>
                                        <th class="w-[17%] px-2 py-4 text-base font-black text-gray-800 text-center border-b border-gray-200">Cantidad</th>
                                        <th class="w-[20%] px-5 py-4 text-base font-black text-gray-800 text-right border-b border-gray-200">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pedido->detalles as $detalle)
                                        @php
                                            $variante = $detalle->variante;
                                            $producto = $variante?->producto;
                                        @endphp
                                        <tr class="border-b border-gray-200">
                                            <td class="px-5 py-4">
                                                <p class="font-normal text-gray-800 text-base leading-tight">
                                                    {{ $producto->nombre_producto ?? 'Producto eliminado' }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <span>Color: {{ $variante?->color ?? '—' }}</span>
                                                    <span class="ml-2">Talla: {{ $variante?->talla ?? '—' }}</span>
                                                </p>
                                            </td>
                                            <td class="px-2 py-4 text-center">
                                                <span class="font-normal text-gray-800 text-base whitespace-nowrap">
                                                    S/ {{ number_format($detalle->precio_unitario, 2) }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-4 text-center">
                                                <span class="font-normal text-gray-800 text-base">{{ $detalle->cantidad }}</span>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <span class="font-normal text-gray-800 text-base whitespace-nowrap">
                                                    S/ {{ number_format($detalle->subtotal, 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    @if($pedido->descuento > 0)
                                        <tr class="border-t border-gray-200">
                                            <td class="px-5 py-4"></td>
                                            <td class="px-2 py-4"></td>
                                            <td class="px-2 py-4 text-center text-base font-normal text-gray-800">
                                                Descuento
                                            </td>
                                            <td class="px-5 py-4 text-right text-base font-normal text-gray-800 whitespace-nowrap">
                                                - S/ {{ number_format($pedido->descuento, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr class="border-t border-gray-200">
                                        <td class="px-5 py-4"></td>
                                        <td class="px-2 py-4"></td>
                                        <td class="px-2 py-4 text-center text-base font-bold text-gray-800">Total</td>
                                        <td class="px-5 py-4 text-right text-base font-bold text-gray-800 whitespace-nowrap">
                                            S/ {{ number_format($pedido->total_pedido, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>

                </div>

                {{-- COLUMNA DERECHA --}}
                <div class="space-y-6">

                    {{-- ESTADO --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">

                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                <x-heroicon-o-arrow-path class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Estado</h2>
                        </div>

                        @php
                            if ($pedido->id_tipo_entrega == 2) {
                                $pasos = [
                                    ['nombre' => 'Pendiente',          'icono' => 'clock'],
                                    ['nombre' => 'En camino',          'icono' => 'truck'],
                                    ['nombre' => 'Listo para recoger', 'icono' => 'building-storefront'],
                                    ['nombre' => 'Entregado',          'icono' => 'check-badge'],
                                ];
                            } else {
                                $pasos = [
                                    ['nombre' => 'Pendiente',          'icono' => 'clock'],
                                    ['nombre' => 'Listo para recoger', 'icono' => 'building-storefront'],
                                    ['nombre' => 'Entregado',          'icono' => 'check-badge'],
                                ];
                            }
                            $esRetiroTienda = $pedido->id_tipo_entrega != 2;
                        @endphp

                        <div x-init="init({{ Js::from($pasos) }}, {{ $esRetiroTienda ? 'true' : 'false' }})"></div>

                        {{-- PASOS (colores negros) --}}
                        <div class="flex items-start w-full">
                            <template x-for="(paso, i) in pasos" :key="i">
                                <div class="contents">
                                    {{-- Contenedor del ícono --}}
                                    <div class="flex flex-col items-center relative z-10" style="width: 56px;">
                                        <button type="button"
                                            @click="avanzarA(i)"
                                            :disabled="i <= indiceActual || i > indiceActual + 1"
                                            class="paso-clickable w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 transition-transform disabled:cursor-not-allowed"
                                            :class="i <= indiceActual ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-400'">
                                            <template x-if="paso.icono === 'clock'">
                                                <x-heroicon-s-clock class="w-4 h-4" />
                                            </template>
                                            <template x-if="paso.icono === 'truck'">
                                                <x-heroicon-s-truck class="w-4 h-4" />
                                            </template>
                                            <template x-if="paso.icono === 'check-badge'">
                                                <x-heroicon-s-check-badge class="w-4 h-4" />
                                            </template>
                                            <template x-if="paso.icono === 'building-storefront'">
                                                <x-heroicon-s-building-storefront class="w-4 h-4" />
                                            </template>
                                        </button>

                                        <span class="text-[10px] mt-1.5 text-center leading-tight whitespace-nowrap"
                                            :class="i <= indiceActual ? 'font-bold text-gray-900' : 'font-normal text-gray-400'">
                                            <template x-if="paso.nombre === 'Listo para recoger'">
                                                <span>Listo para<br>recoger</span>
                                            </template>
                                            <template x-if="paso.nombre !== 'Listo para recoger'">
                                                <span x-text="paso.nombre"></span>
                                            </template>
                                        </span>
                                    </div>

                                    {{-- Línea conectora --}}
                                    <template x-if="i < pasos.length - 1">
                                        <div class="flex-1 mt-[16.5px] h-[3px] -mx-[10px] w-[calc(100%+20px)] relative z-0"
                                            :class="i < indiceActual ? 'bg-gray-900' : 'bg-gray-100'"></div>
                                    </template>
                                </div>
                            </template>
                        </div>

                    </div>

                    {{-- DATOS DE ENTREGA --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">

                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                <x-heroicon-o-map-pin class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Entrega</h2>
                        </div>

                        <div class="space-y-5">

                            {{-- Fecha --}}
                            <div class="flex items-center gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Fecha:</label>
                                <input type="text" name="tiempo_entrega"
                                    value="{{ $pedido->tiempo_entrega ?? '' }}"
                                    class="flex-1 px-4 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-[14px] text-gray-600 focus:outline-none focus:border-gray-400 transition-colors">
                            </div>

                            {{-- Dirección --}}
                            <div class="flex items-start gap-2">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0 mt-3">Dirección:</label>
                                <textarea name="direccion_entrega" rows="3"
                                    class="flex-1 px-4 py-3 border border-gray-200 rounded-2xl bg-gray-50 text-[14px] text-gray-600 focus:outline-none focus:border-gray-400 transition-colors resize-none">{{ $pedido->direccion_entrega ?? '' }}</textarea>
                            </div>

                            {{-- Ticket (archivo) --}}
                            <div class="flex flex-col gap-2" x-data="archivoGuia()">
                                <label class="text-[14px] font-bold text-gray-800">Ticket de envío:</label>
                                <div class="flex-1 min-w-0">
                                    <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-2 py-1.5 max-w-full">
                                        <span class="text-[13px] text-gray-500 truncate max-w-[200px]" x-text="nombreArchivo || 'Ningún archivo seleccionado'"></span>

                                        <button type="button"
                                            @click="$refs.archivoInput.click()"
                                            class="shrink-0 bg-[#dbe3ff] text-[#3b3bb3] text-[13px] font-medium px-4 py-1.5 rounded-full hover:bg-[#cfd8ff] transition">
                                            <span x-text="tieneArchivo ? 'Cambiar archivo' : 'Seleccionar archivo'"></span>
                                        </button>

                                        <input type="file" x-ref="archivoInput"
                                               name="archivo_adjunto"
                                               accept="image/*,application/pdf"
                                               class="hidden"
                                               @change="actualizarNombre($event)">
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        function pedidoEstado(estadoInicial) {
            return {
                estadoActual: estadoInicial,
                pasos: [],
                esRetiroTienda: false,
                indiceActual: 0,

                init(pasos, esRetiro) {
                    this.pasos = pasos;
                    this.esRetiroTienda = esRetiro;
                    this.actualizarIndice();
                },

                actualizarIndice() {
                    const nombres = this.pasos.map(p => p.nombre);
                    const idx = nombres.indexOf(this.estadoActual);
                    this.indiceActual = idx === -1 ? -1 : idx;
                },

                avanzarA(i) {
                    if (i !== this.indiceActual + 1) return;
                    if (i >= this.pasos.length) return;

                    this.estadoActual = this.pasos[i].nombre;
                    this.actualizarIndice();
                }
            };
        }

        function archivoGuia() {
            return {
                nombreArchivo: '',
                tieneArchivo: false,

                actualizarNombre(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.nombreArchivo = file.name;
                        this.tieneArchivo = true;
                    }
                }
            };
        }

        function enviarFormulario(accion) {
            const form = document.getElementById('form-actualizar-pedido');
            const formData = new FormData(form);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                           || form.querySelector('input[name="_token"]').value;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarToast('success', data.message);
                } else {
                    mostrarToast('error', data.message || 'Error al actualizar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast('error', 'Error de conexión');
            });
        }

        function mostrarToast(tipo, mensaje) {
            document.querySelectorAll('.toast-dinamico').forEach(t => t.remove());

            const color = tipo === 'success' ? 'emerald' : 'rose';
            const iconPath = tipo === 'success'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';

            const toast = document.createElement('div');
            toast.className = `toast-dinamico pointer-events-auto flex items-center gap-4 p-5 bg-white shadow-2xl rounded-[2rem] border border-${color}-100 min-w-[340px] max-w-md fixed top-6 right-6 z-[999]`;
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            toast.style.transition = 'all 0.3s ease';

            toast.innerHTML = `
                <div class="w-12 h-12 bg-${color}-50 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-${color}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        ${iconPath}
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-black text-gray-900 text-sm leading-none">
                        ${tipo === 'success' ? '¡Éxito!' : 'Error'}
                    </p>
                    <p class="text-gray-500 text-xs font-medium mt-1.5 leading-relaxed">${mensaje}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-gray-300 hover:text-gray-600 transition flex-shrink-0 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;

            document.body.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            });

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4500);
        }
    </script>

@endsection