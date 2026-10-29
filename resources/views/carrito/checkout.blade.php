@extends('layouts.app')

@section('title', 'Datos de compra - Xiaoxa')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-8 pt-6 pb-2">
    <div class="flex items-center justify-center gap-2 flex-wrap">
        <a href="{{ route('carrito.index') }}" class="flex items-center gap-1">
            <span class="text-[20px] font-normal text-gray-800">Mi bolsa</span>
        </a>
        <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400" />
        <span class="text-[20px] font-bold text-gray-800">Datos de compra</span>
        <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400" />
        <span class="text-[20px] font-normal text-gray-800">Compra finalizada</span>
    </div>
</div>

@php
    $total = 0;
    $resumenProductos = [];
    if ($carrito && $carrito->detalles->count()) {
        foreach ($carrito->detalles as $detalle) {
            $variante = $detalle->variante ?? null;
            $producto = $variante?->producto;
            if (!$producto) continue;
            $precio = $producto->precio_oferta ?? $producto->precio;
            $subtotalItem = $precio * $detalle->cantidad;
            $total += $subtotalItem;

            $resumenProductos[] = [
                'nombre'   => $producto->nombre_producto,
                'cantidad' => $detalle->cantidad,
                'subtotal' => $subtotalItem,
            ];
        }
    }

    $tipoDocumentoTextoInicial = 'Tipo de documento';
    $longitudDocInicial = '8';
    if (auth()->user()->id_tipo_documento) {
        $td = $tiposDocumento->firstWhere('id_tipo_documento', auth()->user()->id_tipo_documento);
        if ($td) {
            $tipoDocumentoTextoInicial = $td->nombre_tipo_documento;
            $nombreLower = strtolower($td->nombre_tipo_documento);
            if (str_contains($nombreLower, 'ruc')) {
                $longitudDocInicial = '11';
            } elseif (str_contains($nombreLower, 'carnet') || str_contains($nombreLower, 'extranjería') || str_contains($nombreLower, 'ce')) {
                $longitudDocInicial = '9-12';
            }
        }
    }
@endphp

<div x-data="checkoutData({{ $total }})" x-cloak
    class="max-w-7xl mx-auto px-4 sm:px-8 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

    <div class="lg:col-span-8 space-y-8">

        <div>
            <h2 class="text-base font-normal text-gray-700 mb-5">Datos personales</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm font-normal text-gray-700 mb-2">Tipo de documento</label>

                    <div x-data="{ open: false }" class="relative">
                        <input type="hidden" name="id_tipo_documento" :value="idTipoDocumento">

                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-2 pl-4 pr-4 py-2.5 border rounded-full bg-white text-sm text-gray-700 cursor-pointer focus:outline-none transition-colors"
                            :class="errores.idTipoDocumento ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'">
                            <span x-text="idTipoDocumentoTexto"></span>
                            <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0" />
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute z-50 mt-2 w-full bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden">
                            <div class="max-h-64 overflow-y-auto py-1">
                                @foreach($tiposDocumento as $td)
                                    @php
                                        $nombreLower = strtolower($td->nombre_tipo_documento);
                                        $longitud = '8';
                                        if (str_contains($nombreLower, 'ruc')) {
                                            $longitud = '11';
                                        } elseif (str_contains($nombreLower, 'carnet') || str_contains($nombreLower, 'extranjería') || str_contains($nombreLower, 'ce')) {
                                            $longitud = '9-12';
                                        }
                                    @endphp
                                    <button type="button"
                                        @click="idTipoDocumento = '{{ $td->id_tipo_documento }}'; idTipoDocumentoTexto = '{{ addslashes($td->nombre_tipo_documento) }}'; longDocRequerida = '{{ $longitud }}'; open = false; if(errores.idTipoDocumento) delete errores.idTipoDocumento"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition">
                                        {{ $td->nombre_tipo_documento }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <p x-show="errores.idTipoDocumento" x-cloak class="text-xs text-red-600 mt-1.5 font-medium">
                        <span x-text="errores.idTipoDocumento"></span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-normal text-gray-700 mb-2">Número de documento</label>
                    <input type="text" name="numero_documento"
                        x-model="numeroDocumento"
                        @input="if(errores.numeroDocumento) delete errores.numeroDocumento"
                        placeholder="Ingresar documento"
                        :disabled="!idTipoDocumento"
                        class="w-full border rounded-full px-4 py-2.5 text-sm placeholder-gray-400 focus:outline-none transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-400"
                        :class="errores.numeroDocumento ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'">
                    <p x-show="errores.numeroDocumento" x-cloak class="text-xs text-red-600 mt-1.5 font-medium">
                        <span x-text="errores.numeroDocumento"></span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-normal text-gray-700 mb-2">Teléfono</label>
                    <input type="text" name="telefono"
                        x-model="telefono"
                        @input="if(errores.telefono) delete errores.telefono"
                        placeholder="Ingresar teléfono"
                        class="w-full border rounded-full px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 bg-white focus:outline-none transition-colors"
                        :class="errores.telefono ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'">
                    <p x-show="errores.telefono" x-cloak class="text-xs text-red-600 mt-1.5 font-medium">
                        <span x-text="errores.telefono"></span>
                    </p>
                </div>

            </div>
        </div>

        <div>
            <h2 class="text-base font-normal text-gray-700 mb-5">Datos de entrega</h2>

            <div class="flex items-center gap-8 mb-6 text-sm text-gray-700">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="tipo_entrega" value="2"
                        x-model.number="tipoEntrega"
                        class="w-4 h-4 cursor-pointer"
                        style="accent-color: #111827;">
                    <span>Envío a provincia</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="tipo_entrega" value="1"
                        x-model.number="tipoEntrega"
                        class="w-4 h-4 cursor-pointer"
                        style="accent-color: #111827;">
                    <span>Retiro en tienda</span>
                </label>
            </div>

            <div x-show="tipoEntrega == 2" class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm font-normal text-gray-700 mb-2">Departamento</label>
                    <input type="text"
                        x-model="departamento"
                        @input="if(errores.departamento) delete errores.departamento"
                        placeholder="Ingresar departamento"
                        class="w-full border rounded-full px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 bg-white focus:outline-none transition-colors"
                        :class="errores.departamento ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'">
                    <p x-show="errores.departamento" x-cloak class="text-xs text-red-600 mt-1.5 font-medium">
                        <span x-text="errores.departamento"></span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-normal text-gray-700 mb-2">Provincia</label>
                    <input type="text"
                        x-model="provincia"
                        @input="if(errores.provincia) delete errores.provincia"
                        placeholder="Ingresar provincia"
                        class="w-full border rounded-full px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 bg-white focus:outline-none transition-colors"
                        :class="errores.provincia ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'">
                    <p x-show="errores.provincia" x-cloak class="text-xs text-red-600 mt-1.5 font-medium">
                        <span x-text="errores.provincia"></span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-normal text-gray-700 mb-2">Distrito</label>
                    <input type="text"
                        x-model="distrito"
                        @input="if(errores.distrito) delete errores.distrito"
                        placeholder="Ingresar distrito"
                        class="w-full border rounded-full px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 bg-white focus:outline-none transition-colors"
                        :class="errores.distrito ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'">
                    <p x-show="errores.distrito" x-cloak class="text-xs text-red-600 mt-1.5 font-medium">
                        <span x-text="errores.distrito"></span>
                    </p>
                </div>

            </div>

            <div class="mt-6">

                <div x-show="tipoEntrega == 2" class="space-y-4">

                    <p class="text-sm text-gray-800">
                        Tu pedido llegará en un plazo de 3 a 5 días hábiles.
                    </p>

                    <div class="relative bg-gray-900 rounded-2xl p-4 overflow-hidden">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center shrink-0">
                                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-white" />
                            </div>
                            <p class="text-gray-200 text-sm leading-relaxed pt-1">
                                Cuando su pedido haya sido revisado, podrá ver la fecha y dirección donde deberá recoger su pedido en la lista de sus pedidos, ubicada en el apartado de su perfil.
                            </p>
                        </div>
                    </div>

                </div>

                <div x-show="tipoEntrega != 2" class="space-y-4">

                    <div class="relative bg-gray-900 rounded-2xl p-4 overflow-hidden">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center shrink-0">
                                <x-heroicon-o-exclamation-circle class="w-5 h-5 text-white" />
                            </div>
                            <p class="text-gray-200 text-sm leading-relaxed pt-1">
                                Cuando su pedido haya sido revisado, podrá ver la fecha en que deberá recogerlo en la lista de sus pedidos, ubicada en el apartado de su perfil.
                            </p>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="lg:hidden">
            <button @click="intentarPagar"
                class="w-full bg-gray-900 text-white rounded-full py-4 text-sm font-semibold tracking-wide transition-all">
                <span>Ir a pagar</span>
            </button>
        </div>

    </div>

    <div class="lg:col-span-4">
        <div class="bg-[#f1f1f1] rounded-2xl p-7 lg:sticky lg:top-24">

            <h2 class="font-bold text-lg mb-6 text-gray-900">Resumen del pedido</h2>

            <div class="space-y-3 mb-5">
                @foreach($resumenProductos as $item)
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-800 leading-tight">
                                {{ ucwords(strtolower($item['nombre'])) }} x {{ $item['cantidad'] }}
                            </p>
                        </div>
                        <span class="text-sm text-gray-800 shrink-0">
                            S/ {{ number_format($item['subtotal'], 2) }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-800 text-sm">Total</span>
                <span class="font-semibold text-gray-800 text-sm">S/ {{ number_format($total, 2) }}</span>
            </div>

            <button @click="intentarPagar"
                class="hidden lg:flex mt-7 items-center justify-center w-full bg-gray-900 text-white rounded-full py-4 text-[14px] font-semibold transition-all uppercase">
                <span>Ir a pagar</span>
            </button>

        </div>
    </div>

    <form id="form-pedido" method="POST" action="{{ route('checkout.confirmar') }}">
        @csrf
        <input type="hidden" name="id_tipo_entrega"    x-bind:value="tipoEntrega">
        <input type="hidden" name="departamento"       x-bind:value="departamento">
        <input type="hidden" name="provincia"          x-bind:value="provincia">
        <input type="hidden" name="distrito"           x-bind:value="distrito">
        <input type="hidden" name="id_tipo_documento"  x-bind:value="idTipoDocumento">
        <input type="hidden" name="numero_documento"   x-bind:value="numeroDocumento">
        <input type="hidden" name="telefono"           x-bind:value="telefono">
    </form>

</div>

@endsection

@push('scripts')
<script>
    function checkoutData(totalProductos) {
        return {
            tipoEntrega:         {{ $tiposEntrega->first()->id_tipo_entrega ?? 1 }},
            departamento:        '',
            provincia:           '',
            distrito:            '',
            totalProductos:      totalProductos,
            
            errores: {},

            idTipoDocumento:      '{{ auth()->user()->id_tipo_documento ?? '' }}',
            idTipoDocumentoTexto: '{{ addslashes($tipoDocumentoTextoInicial) }}',
            numeroDocumento:      '{{ auth()->user()->numero_documento ?? '' }}',
            telefono:             '{{ auth()->user()->telefono ?? '' }}',
            longDocRequerida:     '{{ $longitudDocInicial }}',

            intentarPagar() {
                this.errores = {};

                if (!this.idTipoDocumento) {
                    this.errores.idTipoDocumento = 'Selecciona un tipo de documento.';
                }
                
                const doc = this.numeroDocumento.trim();
                if (!doc) {
                    this.errores.numeroDocumento = 'El número de documento es obligatorio.';
                } else if (!/^\d+$/.test(doc)) {
                    this.errores.numeroDocumento = 'El documento debe contener solo números.';
                } else if (this.longDocRequerida === '9-12') {
                    if (doc.length < 9 || doc.length > 12) {
                        this.errores.numeroDocumento = 'El documento debe tener entre 9 y 12 dígitos.';
                    }
                } else if (doc.length !== parseInt(this.longDocRequerida)) {
                    this.errores.numeroDocumento = `El documento debe tener ${this.longDocRequerida} dígitos.`;
                }
                
                const tel = this.telefono.trim();
                if (!tel) {
                    this.errores.telefono = 'El teléfono es obligatorio.';
                } else if (!/^\d+$/.test(tel)) {
                    this.errores.telefono = 'El teléfono debe contener solo números.';
                } else if (tel.length !== 9) {
                    this.errores.telefono = 'El teléfono debe tener 9 dígitos.';
                }
                
                if (this.tipoEntrega == 2) {
                    if (!this.departamento.trim()) {
                        this.errores.departamento = 'El departamento es obligatorio.';
                    }
                    if (!this.provincia.trim()) {
                        this.errores.provincia = 'La provincia es obligatoria.';
                    }
                    if (!this.distrito.trim()) {
                        this.errores.distrito = 'El distrito es obligatorio.';
                    }
                }

                if (Object.keys(this.errores).length > 0) {
                    return;
                }

                document.getElementById('form-pedido').submit();
            },
        }
    }
</script>
@endpush