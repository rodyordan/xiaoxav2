@extends('admin.layout')

@section('content')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        [x-cloak] { display: none !important; }

        .flatpickr-calendar {
            width: 252px !important;
            padding: 0 !important;
            font-size: 11px !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
        }
        .flatpickr-calendar::before,
        .flatpickr-calendar::after,
        .flatpickr-calendar.arrowTop::before,
        .flatpickr-calendar.arrowTop::after,
        .flatpickr-calendar.arrowBottom::before,
        .flatpickr-calendar.arrowBottom::after,
        .flatpickr-calendar.arrowLeft::before,
        .flatpickr-calendar.arrowLeft::after,
        .flatpickr-calendar.arrowRight::before,
        .flatpickr-calendar.arrowRight::after {
            display: none !important;
            border: none !important;
        }
        .flatpickr-calendar .flatpickr-months { padding: 6px 0 !important; }
        .flatpickr-calendar .flatpickr-month { height: 28px !important; }
        .flatpickr-calendar .flatpickr-current-month {
            font-size: 12px !important;
            padding: 0 !important;
            height: 28px !important;
            line-height: 28px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            color: #374151 !important;
            font-weight: 600 !important;
            pointer-events: none !important;
            cursor: default !important;
        }
        .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-calendar .flatpickr-current-month input.cur-year,
        .flatpickr-calendar .flatpickr-current-month .numInputWrapper {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #374151 !important;
            padding: 0 !important;
            margin: 0 !important;
            pointer-events: none !important;
            cursor: default !important;
            appearance: none !important;
        }
        .flatpickr-calendar .flatpickr-current-month .numInputWrapper span.arrowUp,
        .flatpickr-calendar .flatpickr-current-month .numInputWrapper span.arrowDown {
            display: none !important;
        }
        .flatpickr-calendar .flatpickr-prev-month,
        .flatpickr-calendar .flatpickr-next-month {
            padding: 6px !important;
            height: 28px !important;
            line-height: 28px !important;
            top: 6px !important;
            color: #6366f1 !important;
        }
        .flatpickr-calendar .flatpickr-prev-month svg,
        .flatpickr-calendar .flatpickr-next-month svg {
            width: 12px !important;
            height: 12px !important;
            fill: #6366f1 !important;
        }
        .flatpickr-calendar .flatpickr-weekdays {
            height: 24px !important;
            background: #f9fafb !important;
        }
        .flatpickr-calendar .flatpickr-weekday {
            font-size: 10px !important;
            font-weight: 600 !important;
            line-height: 24px !important;
            color: #6b7280 !important;
        }
        .flatpickr-calendar .flatpickr-days {
            width: 252px !important;
            height: 180px !important;
            overflow: hidden !important;
        }
        .flatpickr-calendar .dayContainer {
            width: 252px !important;
            min-width: 252px !important;
            max-width: 252px !important;
            height: 180px !important;
            overflow: hidden !important;
        }
        .flatpickr-calendar .flatpickr-day {
            width: 28px !important;
            height: 28px !important;
            max-width: 28px !important;
            flex-basis: 28px !important;
            line-height: 28px !important;
            font-size: 11px !important;
            margin: 1px 4px !important;
            border-radius: 50% !important;
            color: #374151 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .flatpickr-calendar .flatpickr-day.prevMonthDay,
        .flatpickr-calendar .flatpickr-day.nextMonthDay { color: #d1d5db !important; }
        .flatpickr-calendar .flatpickr-day.today {
            border-color: #6366f1 !important;
            color: #6366f1 !important;
            font-weight: 700 !important;
        }
        .flatpickr-calendar .flatpickr-day.selected {
            background: #6366f1 !important;
            border-color: #6366f1 !important;
            color: white !important;
            font-weight: 700 !important;
            border-radius: 50% !important;
        }
        .flatpickr-calendar .flatpickr-day:hover {
            background: #eef2ff !important;
            border-radius: 50% !important;
        }
    </style>

    <div x-data="{ deleteModal: false, activeId: null, createModal: false }">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-6">
            <div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Cupones</h1>
                <p class="text-gray-500 mt-2 text-lg font-medium">Administra los cupones de descuento.</p>
            </div>

            <button @click="createModal = true"
                class="inline-flex items-center gap-3 bg-indigo-600 text-white px-7 py-4 rounded-2xl font-bold transition-colors duration-200">
                <x-heroicon-o-plus class="w-6 h-6" />
                Nuevo Cupón
            </button>
        </div>

        <form action="{{ route('admin.cupones.index') }}" method="GET"
            x-data="{
                estado: '',
                estadoTexto: 'Estado',

                applyFilter() {
                    const form = $el;
                    const params = new URLSearchParams();

                    const buscar = form.querySelector('input[name=buscar]').value.trim();
                    if (buscar) params.append('buscar', buscar);
                    if (this.estado) params.append('estado', this.estado);

                    const fecha = form.querySelector('input[name=fecha]').value;
                    if (fecha) params.append('fecha', fecha);

                    form.querySelector('input[name=buscar]').value = '';
                    this.estado = '';
                    this.estadoTexto = 'Estado';
                    form.querySelector('input[name=fecha]').value = '';
                    document.getElementById('fechaTexto').textContent = 'Fecha de Vencimiento';

                    window.location.href = form.action + '?' + params.toString();
                }
            }"
            x-on:submit.prevent="applyFilter()"
            class="mb-6 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-4 w-full flex-wrap">

                <span class="text-sm font-bold text-gray-700 shrink-0">Buscar:</span>
                <div class="relative flex-1 min-w-[295px]">
                    <span class="absolute inset-y-0 left-4 flex items-center text-gray-800">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    </span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por código..."
                        class="w-full pl-12 pr-4 py-1.5 bg-[#f1f1f1] border border-gray-200 rounded-full text-gray-800 placeholder-gray-800 focus:outline-none focus:border-gray-400 transition-colors text-sm">
                </div>

                <span class="text-sm font-bold text-gray-700 shrink-0">Filtros:</span>

                <div x-data="{ open: false }" class="relative w-full max-w-[180px]">
                    <input type="hidden" name="estado" :value="estado">

                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between gap-2 pl-4 pr-4 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-sm text-gray-600 cursor-pointer focus:outline-none focus:border-gray-400 transition-colors">
                        <span x-text="estadoTexto"></span>
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
                            <button type="button" @click="estado = '1'; estadoTexto = 'Activo'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                Activo
                            </button>
                            <button type="button" @click="estado = '0'; estadoTexto = 'Inactivo'; open = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-600 transition">
                                Inactivo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="relative w-full max-w-[220px]">
                    <input type="text" name="fecha" id="fechaFiltro" value="" class="sr-only" readonly>

                    <button type="button" id="fechaBtn"
                        class="w-full flex items-center justify-between gap-2 pl-4 pr-4 py-1.5 border border-gray-200 rounded-full bg-gray-50 text-sm text-gray-600 cursor-pointer focus:outline-none focus:border-gray-400 transition-colors">
                        <span id="fechaTexto">Fecha de Vencimiento</span>
                        <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0" />
                    </button>
                </div>

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
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-left border-b border-gray-200">Código</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Descuento</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Compra Mínima</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Fecha de Vencimiento</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-center border-b border-gray-200">Estado</th>
                            <th class="px-8 py-5 text-base font-black text-gray-800 text-right border-b border-gray-200">Acciones</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($cupones as $cupon)
                            @php
                                $vencido = $cupon->fecha_vencimiento < now()->toDateString();
                            @endphp
                            <tr x-data="{ editModal: false }" class="odd:bg-white even:bg-[#f1f1f1]/40">

                                <td class="px-8 py-5">
                                    <span class="font-black text-gray-900 tracking-widest text-sm font-mono bg-gray-100 px-3 py-1.5 rounded-lg">
                                        {{ $cupon->codigo_cupon }}
                                    </span>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    <span class="font-normal text-gray-800 text-base">
                                        S/ {{ number_format($cupon->monto_cupon, 2) }}
                                    </span>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    <span class="font-normal text-gray-800 text-base">
                                        S/ {{ number_format($cupon->monto_compra_minima, 2) }}
                                    </span>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($vencido)
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-4 py-1.5 rounded-full whitespace-nowrap">
                                            Vencido · {{ \Carbon\Carbon::parse($cupon->fecha_vencimiento)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="font-normal text-gray-800 text-base">
                                            {{ \Carbon\Carbon::parse($cupon->fecha_vencimiento)->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($cupon->estado_cupon)
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
                                        <button @click="editModal = true"
                                            class="p-2 text-indigo-600 transition-all"
                                            title="Editar">
                                            <x-heroicon-o-pencil-square class="w-5 h-5" />
                                        </button>

                                        <button @click="deleteModal = true; activeId = {{ $cupon->id_cupon }}"
                                            class="p-2 text-rose-600 transition-all"
                                            title="Eliminar">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>

                                <template x-if="editModal">
                                    <div class="fixed inset-0 z-[110] flex items-center justify-center p-4"
                                         x-data="{
                                             estadoEdit: '{{ $cupon->estado_cupon }}',
                                             estadoTextoEdit: '{{ $cupon->estado_cupon ? 'Activo' : 'Inactivo' }}',
                                             fechaEdit: '{{ $cupon->fecha_vencimiento }}',
                                             submitting: false,
                                             errores: {},
                                             initFecha() {
                                                 const input = this.$refs.fechaEditInput;
                                                 const btn = this.$refs.fechaEditBtn;
                                                 const texto = this.$refs.fechaEditTexto;

                                                 const picker = flatpickr(input, {
                                                     dateFormat: 'Y-m-d',
                                                     defaultDate: input.value || null,
                                                     positionElement: btn,
                                                     static: false,
                                                     appendTo: document.body,
                                                     locale: 'es',
                                                     firstDayOfWeek: 0,
                                                     monthSelectorType: 'static',
                                                     yearSelectorType: 'input',
                                                     disableMobile: true,
                                                     onChange: (dates, dateStr) => {
                                                         this.fechaEdit = dateStr;
                                                         if (dateStr) {
                                                             const [y, m, d] = dateStr.split('-');
                                                             texto.textContent = `${d}/${m}/${y}`;
                                                         }
                                                         delete this.errores.fecha_vencimiento;
                                                     },
                                                     onOpen: (selectedDates, dateStr, instance) => {
                                                         setTimeout(() => instance._positionCalendar(), 10);
                                                     }
                                                 });

                                                 btn.addEventListener('click', (e) => {
                                                     e.preventDefault();
                                                     picker.open();
                                                 });
                                             },
                                             async submitEdit(e) {
                                                 if (this.submitting) return;
                                                 this.submitting = true;
                                                 this.errores = {};

                                                 const form = e.target;

                                                 const codigo = form.querySelector('[name=codigo_cupon]').value.trim();
                                                 const monto = form.querySelector('[name=monto_cupon]').value.trim();
                                                 const minimo = form.querySelector('[name=monto_compra_minima]').value.trim();
                                                 const fecha = form.querySelector('[name=fecha_vencimiento]').value;

                                                 if (!codigo) this.errores.codigo_cupon = true;
                                                 if (!monto) this.errores.monto_cupon = true;
                                                 if (!minimo) this.errores.monto_compra_minima = true;
                                                 if (!fecha) this.errores.fecha_vencimiento = true;

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
                                                             detail: { type: 'success', message: data.message || 'Cupón actualizado correctamente.' }
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
                                         x-init="initFecha()">
                                        <div @click="editModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                                        <div class="relative bg-white rounded-[2.5rem] p-10 max-w-[610px] w-full shadow-2xl max-h-[92vh] overflow-y-auto">

                                            <div class="flex justify-between items-start mb-8">
                                                <div>
                                                    <h2 class="text-2xl font-bold text-gray-900">Editar Cupón</h2>
                                                </div>
                                                <button @click="editModal = false" class="text-gray-500 transition -mt-1">
                                                    <x-heroicon-o-x-mark class="w-6 h-6" />
                                                </button>
                                            </div>

                                            <form @submit.prevent="submitEdit($event)" action="{{ route('admin.cupones.update', $cupon->id_cupon) }}" method="POST" class="space-y-4">
                                                @csrf @method('PUT')

                                                <div class="flex items-center gap-2">
                                                    <label class="text-[14px] font-bold text-gray-800 shrink-0">Código del cupón:</label>
                                                    <input type="text" name="codigo_cupon" value="{{ $cupon->codigo_cupon }}"
                                                        @input="delete errores.codigo_cupon"
                                                        class="w-full px-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-medium transition-colors"
                                                        :class="errores.codigo_cupon ? 'border-rose-500' : 'border-gray-200'">
                                                </div>

                                                <div class="flex items-center gap-4">
                                                    <div class="flex items-center gap-2 w-[225px]">
                                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Valor del cupón:</label>
                                                        <div class="relative w-full">
                                                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[14px]">S/</span>
                                                            <input type="text" inputmode="decimal" name="monto_cupon" value="{{ $cupon->monto_cupon }}"
                                                                @input="delete errores.monto_cupon"
                                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                                                class="w-full pl-9 pr-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-medium transition-colors"
                                                                :class="errores.monto_cupon ? 'border-rose-500' : 'border-gray-200'">
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2 flex-1">
                                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Monto mínimo de compra:</label>
                                                        <div class="relative w-full">
                                                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[14px]">S/</span>
                                                            <input type="text" inputmode="decimal" name="monto_compra_minima" value="{{ $cupon->monto_compra_minima }}"
                                                                @input="delete errores.monto_compra_minima"
                                                                oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                                                class="w-full pl-9 pr-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-medium transition-colors"
                                                                :class="errores.monto_compra_minima ? 'border-rose-500' : 'border-gray-200'">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-4">
                                                    <!-- Fecha con flex-[1.5] para tener más peso que el estado pero adaptarse -->
                                                    <div class="flex items-center gap-2 flex-[1.5] min-w-[230px]">
                                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Fecha de vencimiento:</label>

                                                        <input type="text" name="fecha_vencimiento"
                                                               x-ref="fechaEditInput"
                                                               x-model="fechaEdit"
                                                               class="sr-only" readonly>

                                                        <button type="button" x-ref="fechaEditBtn"
                                                            class="w-full flex items-center justify-between gap-2 pl-4 pr-3 py-1.5 border rounded-full bg-gray-50 text-[14px] cursor-pointer focus:outline-none focus:border-gray-400 transition-colors"
                                                            :class="errores.fecha_vencimiento ? 'border-rose-500' : 'border-gray-200 text-gray-600'">
                                                            <span x-ref="fechaEditTexto">
                                                                {{ \Carbon\Carbon::parse($cupon->fecha_vencimiento)->format('d/m/Y') }}
                                                            </span>
                                                            <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0" />
                                                        </button>
                                                    </div>

                                                    <!-- Estado con flex-1 -->
                                                    <div class="flex items-center gap-2 flex-1 min-w-[140px]" x-data="{ open: false }">
                                                        <label class="text-[14px] font-bold text-gray-800 shrink-0">Estado:</label>
                                                        <input type="hidden" name="estado_cupon" :value="estadoEdit">
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-12 text-center text-gray-400 text-sm">
                                    No hay cupones registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($cupones->hasPages())
                    <div class="px-8 py-6 bg-[#f1f1f1]/50 border-t border-gray-200">
                        {{ $cupones->links() }}
                    </div>
                @endif
            </div>
        </div>

        <template x-if="createModal">
            <div class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                 x-data="{
                     estadoCrear: '1',
                     estadoTextoCrear: 'Activo',
                     fechaCrear: '',
                     submitting: false,
                     errores: {},
                     initFechaCrear() {
                         const input = this.$refs.fechaCrearInput;
                         const btn = this.$refs.fechaCrearBtn;
                         const texto = this.$refs.fechaCrearTexto;

                         const picker = flatpickr(input, {
                             dateFormat: 'Y-m-d',
                             defaultDate: null,
                             positionElement: btn,
                             static: false,
                             appendTo: document.body,
                             locale: 'es',
                             firstDayOfWeek: 0,
                             monthSelectorType: 'static',
                             yearSelectorType: 'input',
                             disableMobile: true,
                             onChange: (dates, dateStr) => {
                                 this.fechaCrear = dateStr;
                                 if (dateStr) {
                                     const [y, m, d] = dateStr.split('-');
                                     texto.textContent = `${d}/${m}/${y}`;
                                 } else {
                                     texto.textContent = 'Seleccionar';
                                 }
                                 delete this.errores.fecha_vencimiento;
                             },
                             onOpen: (selectedDates, dateStr, instance) => {
                                 setTimeout(() => instance._positionCalendar(), 10);
                             }
                         });

                         btn.addEventListener('click', (e) => {
                             e.preventDefault();
                             picker.open();
                         });
                     },
                     async submitCreate(e) {
                         if (this.submitting) return;
                         this.submitting = true;
                         this.errores = {};

                         const form = e.target;

                         const codigo = form.querySelector('[name=codigo_cupon]').value.trim();
                         const monto = form.querySelector('[name=monto_cupon]').value.trim();
                         const minimo = form.querySelector('[name=monto_compra_minima]').value.trim();
                         const fecha = form.querySelector('[name=fecha_vencimiento]').value;

                         if (!codigo) this.errores.codigo_cupon = true;
                         if (!monto) this.errores.monto_cupon = true;
                         if (!minimo) this.errores.monto_compra_minima = true;
                         if (!fecha) this.errores.fecha_vencimiento = true;

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
                                     detail: { type: 'success', message: data.message || 'Cupón creado correctamente.' }
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
                 x-init="initFechaCrear()">
                <div @click="createModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                <div class="relative bg-white rounded-[2.5rem] p-10 max-w-[610px] w-full shadow-2xl max-h-[92vh] overflow-y-auto">

                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Nuevo Cupón</h2>
                        </div>
                        <button @click="createModal = false" class="text-gray-500 transition -mt-1">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate($event)" action="{{ route('admin.cupones.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="flex items-center gap-2">
                            <label class="text-[14px] font-bold text-gray-800 shrink-0">Código del cupón:</label>
                            <input type="text" name="codigo_cupon" value="{{ old('codigo_cupon') }}" autofocus
                                @input="delete errores.codigo_cupon"
                                class="w-full px-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-medium transition-colors"
                                :class="errores.codigo_cupon ? 'border-rose-500' : 'border-gray-200'">
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2 w-[225px]">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Valor del cupón:</label>
                                <div class="relative w-full">
                                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[14px]">S/</span>
                                    <input type="text" inputmode="decimal" name="monto_cupon"
                                        value="{{ old('monto_cupon') }}"
                                        @input="delete errores.monto_cupon"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                        class="w-full pl-9 pr-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-medium transition-colors"
                                        :class="errores.monto_cupon ? 'border-rose-500' : 'border-gray-200'">
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-1">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Monto mínimo de compra:</label>
                                <div class="relative w-full">
                                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-[14px]">S/</span>
                                    <input type="text" inputmode="decimal" name="monto_compra_minima"
                                        value="{{ old('monto_compra_minima') }}"
                                        @input="delete errores.monto_compra_minima"
                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '')"
                                        class="w-full pl-9 pr-3 py-1.5 bg-gray-50 border rounded-full focus:outline-none focus:border-gray-400 text-[14px] font-medium transition-colors"
                                        :class="errores.monto_compra_minima ? 'border-rose-500' : 'border-gray-200'">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2 flex-[1.5] min-w-[230px]">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Fecha de vencimiento:</label>

                                <input type="text" name="fecha_vencimiento"
                                       x-ref="fechaCrearInput"
                                       x-model="fechaCrear"
                                       class="sr-only" readonly>

                                <button type="button" x-ref="fechaCrearBtn"
                                    class="w-full flex items-center justify-between gap-2 pl-4 pr-3 py-1.5 border rounded-full bg-gray-50 text-[14px] cursor-pointer focus:outline-none focus:border-gray-400 transition-colors"
                                    :class="errores.fecha_vencimiento ? 'border-rose-500' : 'border-gray-200 text-gray-600'">
                                    <span x-ref="fechaCrearTexto">Seleccionar</span>
                                    <x-heroicon-o-chevron-down class="w-4 h-4 shrink-0" />
                                </button>
                            </div>

                            <div class="flex items-center gap-2 flex-1 min-w-[140px]" x-data="{ open: false }">
                                <label class="text-[14px] font-bold text-gray-800 shrink-0">Estado:</label>
                                <input type="hidden" name="estado_cupon" :value="estadoCrear">
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

        <template x-if="deleteModal">
            <div class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div @click="deleteModal = false" class="absolute inset-0 bg-gray-900/40 backdrop-blur-md"></div>
                <div class="relative bg-white rounded-[2.5rem] p-10 max-w-sm w-full shadow-2xl text-center">
                    <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-heroicon-o-exclamation-triangle class="w-10 h-10" />
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">¿Estás seguro?</h3>
                    <p class="text-gray-500 font-medium mb-8">
                        Vas a eliminar este cupón.
                    </p>
                    <form :action="'{{ route('admin.cupones.index') }}/' + activeId" method="POST" class="flex gap-3">
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

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr.localize(flatpickr.l10ns.es);
            flatpickr.l10ns.es.firstDayOfWeek = 0;

            const inputFechaFiltro = document.getElementById('fechaFiltro');
            const botonFechaFiltro = document.getElementById('fechaBtn');
            const textoFechaFiltro = document.getElementById('fechaTexto');

            const pickerFiltro = flatpickr(inputFechaFiltro, {
                dateFormat: 'Y-m-d',
                defaultDate: inputFechaFiltro.value || null,
                positionElement: botonFechaFiltro,
                static: false,
                appendTo: document.body,
                locale: 'es',
                firstDayOfWeek: 0,
                monthSelectorType: 'static',
                yearSelectorType: 'input',
                disableMobile: true,
                onChange: function (selectedDates, dateStr) {
                    if (dateStr) {
                        const [y, m, d] = dateStr.split('-');
                        textoFechaFiltro.textContent = `${d}/${m}/${y}`;
                    } else {
                        textoFechaFiltro.textContent = 'Fecha de Vencimiento';
                    }
                },
                onOpen: function (selectedDates, dateStr, instance) {
                    setTimeout(() => instance._positionCalendar(), 10);
                    botonFechaFiltro.classList.add('border-indigo-400', 'ring-2', 'ring-indigo-100');
                },
                onClose: function () {
                    botonFechaFiltro.classList.remove('border-indigo-400', 'ring-2', 'ring-indigo-100');
                }
            });

            botonFechaFiltro.addEventListener('click', function (e) {
                e.preventDefault();
                pickerFiltro.open();
            });
        });
    </script>

@endsection