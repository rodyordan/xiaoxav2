@extends('layouts.app')

@section('title', 'Registrarse - Xiaoxa')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-8 px-4 bg-[#fbfaf8]">
    <div class="w-full max-w-md">

        <div class="p-8 sm:p-10"
             x-data="{
                nombres: '{{ old('nombres') }}',
                apellidos: '{{ old('apellidos') }}',
                correo: '{{ old('correo') }}',
                telefono: '{{ old('telefono') }}',
                password: '',
                password_confirmation: '',
                errores: {},

                init() {
                    @if ($errors->has('nombres'))
                        this.errores.nombres = '{{ $errors->first('nombres') }}';
                    @endif
                    @if ($errors->has('apellidos'))
                        this.errores.apellidos = '{{ $errors->first('apellidos') }}';
                    @endif
                    @if ($errors->has('correo'))
                        this.errores.correo = '{{ $errors->first('correo') }}';
                    @endif
                    @if ($errors->has('telefono'))
                        this.errores.telefono = '{{ $errors->first('telefono') }}';
                    @endif
                    @if ($errors->has('password'))
                        this.errores.password = '{{ $errors->first('password') }}';
                    @endif
                },

                validar(e) {
                    this.errores = {};

                    if (!this.nombres.trim()) {
                        this.errores.nombres = 'Los nombres son obligatorios.';
                    }

                    if (!this.apellidos.trim()) {
                        this.errores.apellidos = 'Los apellidos son obligatorios.';
                    }

                    const correoTrim = this.correo.trim();
                    if (!correoTrim) {
                        this.errores.correo = 'El correo electrónico es obligatorio.';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(correoTrim)) {
                        this.errores.correo = 'Ingresa un correo electrónico válido.';
                    } else if (correoTrim.length < 10) {
                        this.errores.correo = 'El correo electrónico es demasiado corto.';
                    }

                    const telTrim = this.telefono.trim();
                    if (!telTrim) {
                        this.errores.telefono = 'El teléfono es obligatorio.';
                    } else if (!/^9\d{8}$/.test(telTrim)) {
                        this.errores.telefono = 'El teléfono debe comenzar con 9 y tener 9 dígitos.';
                    }

                    if (!this.password.trim()) {
                        this.errores.password = 'La contraseña es obligatoria.';
                    } else if (this.password.length < 8) {
                        this.errores.password = 'La contraseña debe tener al menos 8 caracteres.';
                    }

                    if (!this.password_confirmation.trim()) {
                        this.errores.password_confirmation = 'Debes confirmar la contraseña.';
                    } else if (this.password !== this.password_confirmation) {
                        this.errores.password_confirmation = 'Las contraseñas no coinciden.';
                    }

                    if (Object.keys(this.errores).length > 0) {
                        e.preventDefault();
                        return;
                    }

                    e.target.submit();
                }
             }">

            <h1 class="text-2xl font-bold text-center text-black mb-8">Registrarse</h1>

            <form method="POST" action="{{ route('register') }}" @submit.prevent="validar($event)" novalidate>
                @csrf

                <div class="mb-5">
                    <label for="nombres" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombres
                    </label>
                    <input
                        type="text"
                        name="nombres"
                        id="nombres"
                        x-model="nombres"
                        @input="if (errores.nombres) delete errores.nombres; nombres = nombres.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                        value="{{ old('nombres') }}"
                        placeholder="Ingresar nombres"
                        maxlength="50"
                        class="w-full border rounded-lg px-4 py-2.5 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                        :class="errores.nombres ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
                        autofocus
                    >
                    <p x-show="errores.nombres" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.nombres"></span>
                    </p>
                </div>

                <div class="mb-5">
                    <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-2">
                        Apellidos
                    </label>
                    <input
                        type="text"
                        name="apellidos"
                        id="apellidos"
                        x-model="apellidos"
                        @input="if (errores.apellidos) delete errores.apellidos; apellidos = apellidos.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '')"
                        value="{{ old('apellidos') }}"
                        placeholder="Ingresar apellidos"
                        maxlength="50"
                        class="w-full border rounded-lg px-4 py-2.5 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                        :class="errores.apellidos ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
                    >
                    <p x-show="errores.apellidos" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.apellidos"></span>
                    </p>
                </div>

                <div class="mb-5">
                    <label for="correo" class="block text-sm font-medium text-gray-700 mb-2">
                        Correo electrónico
                    </label>
                    <input
                        type="email"
                        name="correo"
                        id="correo"
                        x-model="correo"
                        @input="if (errores.correo) delete errores.correo"
                        value="{{ old('correo') }}"
                        placeholder="Ingresar correo electrónico"
                        maxlength="100"
                        class="w-full border rounded-lg px-4 py-2.5 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                        :class="errores.correo ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
                    >
                    <p x-show="errores.correo" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.correo"></span>
                    </p>
                </div>

                <div class="mb-5">
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono
                    </label>
                    <input
                        type="text"
                        name="telefono"
                        id="telefono"
                        x-model="telefono"
                        @input="if (errores.telefono) delete errores.telefono; telefono = telefono.replace(/[^0-9]/g, '')"
                        value="{{ old('telefono') }}"
                        placeholder="Ingresar teléfono"
                        maxlength="9"
                        class="w-full border rounded-lg px-4 py-2.5 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                        :class="errores.telefono ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
                    >
                    <p x-show="errores.telefono" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.telefono"></span>
                    </p>
                </div>

                <div class="mb-5" x-data="{ verPassword: false }">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <div class="relative">
                        <input
                            :type="verPassword ? 'text' : 'password'"
                            name="password"
                            id="password"
                            x-model="password"
                            @input="if (errores.password) delete errores.password"
                            placeholder="Ingresar contraseña"
                            maxlength="72"
                            class="w-full border rounded-lg px-4 py-2.5 pr-10 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                            :class="errores.password ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
                        >
                        <button
                            type="button"
                            @click="verPassword = !verPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                            aria-label="Mostrar contraseña"
                        >
                            <x-heroicon-o-eye x-show="!verPassword" class="w-5 h-5" />
                            <x-heroicon-o-eye-slash x-show="verPassword" x-cloak class="w-5 h-5" />
                        </button>
                    </div>
                    <p x-show="errores.password" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.password"></span>
                    </p>
                </div>

                <div class="mb-6" x-data="{ verConfirmar: false }">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmar contraseña
                    </label>
                    <div class="relative">
                        <input
                            :type="verConfirmar ? 'text' : 'password'"
                            name="password_confirmation"
                            id="password_confirmation"
                            x-model="password_confirmation"
                            @input="if (errores.password_confirmation) delete errores.password_confirmation"
                            placeholder="Ingresar contraseña nuevamente"
                            maxlength="72"
                            class="w-full border rounded-lg px-4 py-2.5 pr-10 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                            :class="errores.password_confirmation ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
                        >
                        <button
                            type="button"
                            @click="verConfirmar = !verConfirmar"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"
                            aria-label="Mostrar contraseña"
                        >
                            <x-heroicon-o-eye x-show="!verConfirmar" class="w-5 h-5" />
                            <x-heroicon-o-eye-slash x-show="verConfirmar" x-cloak class="w-5 h-5" />
                        </button>
                    </div>
                    <p x-show="errores.password_confirmation" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.password_confirmation"></span>
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full bg-black text-white text-sm font-bold uppercase tracking-wider py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200"
                >
                    Crear cuenta
                </button>
            </form>

            <p class="text-center text-sm text-black mt-6">
                ¿Ya tienes una cuenta?
                <a href="{{ route('login') }}" class="text-black font-semibold hover:underline">
                    Iniciar sesión
                </a>
            </p>

        </div>
    </div>
</div>
@endsection