@extends('layouts.app')

@section('title', 'Iniciar sesión - Xiaoxa')

@section('content')
<div class="min-h-[calc(100vh-200px)] flex items-center justify-center py-16 px-4 bg-[#fbfaf8]">
    <div class="w-full max-w-md">

        <div class="p-8 sm:p-10"
             x-data="{
                correo: '{{ old('correo') }}',
                contrasena: '',
                errores: {},

                init() {
                    @if ($errors->has('correo'))
                        this.errores.correo = '{{ $errors->first('correo') }}';
                    @endif
                    @if ($errors->has('contrasena'))
                        this.errores.contrasena = '{{ $errors->first('contrasena') }}';
                    @endif
                },

                validar(e) {
                    this.errores = {};

                    if (!this.correo.trim()) {
                        this.errores.correo = 'El correo electrónico es obligatorio.';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.correo.trim())) {
                        this.errores.correo = 'Ingresa un correo electrónico válido.';
                    }

                    if (!this.contrasena.trim()) {
                        this.errores.contrasena = 'La contraseña es obligatoria.';
                    }

                    if (Object.keys(this.errores).length > 0) {
                        e.preventDefault();
                        return;
                    }

                    e.target.submit();
                }
             }">

            <h1 class="text-2xl font-bold text-center text-black mb-8">Iniciar sesión</h1>

            <form method="POST" action="{{ route('login') }}" @submit.prevent="validar($event)" novalidate>
                @csrf

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
                        class="w-full border rounded-lg px-4 py-2.5 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                        :class="errores.correo ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
                        autofocus
                    >
                    <p x-show="errores.correo" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.correo"></span>
                    </p>
                </div>

                <div class="mb-6" x-data="{ verPassword: false }">
                    <label for="contrasena" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <div class="relative">
                        <input
                            :type="verPassword ? 'text' : 'password'"
                            name="contrasena"
                            id="contrasena"
                            x-model="contrasena"
                            @input="if (errores.contrasena) delete errores.contrasena"
                            placeholder="Ingresar contraseña"
                            class="w-full border rounded-lg px-4 py-2.5 pr-10 text-sm text-black placeholder-gray-400 focus:outline-none transition"
                            :class="errores.contrasena ? 'border-red-500 focus:border-red-500' : 'border-gray-200 focus:border-gray-400'"
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
                    <p x-show="errores.contrasena" x-cloak
                       class="flex items-center gap-1.5 text-xs text-red-600 mt-1.5 font-medium">
                        <x-heroicon-s-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                        <span x-text="errores.contrasena"></span>
                    </p>
                </div>

                <button
                    type="submit"
                    class="w-full bg-black text-white text-sm font-bold uppercase tracking-wider py-3 rounded-lg hover:bg-gray-800 transition-colors duration-200"
                >
                    Iniciar sesión
                </button>
            </form>

            <p class="text-center text-sm text-black mt-6">
                ¿No tienes una cuenta?
                <a href="{{ route('register') }}" class="text-black font-semibold hover:underline">
                    Registrarse
                </a>
            </p>

        </div>
    </div>
</div>
@endsection