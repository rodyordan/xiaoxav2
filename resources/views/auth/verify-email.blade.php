@extends('layouts.app')

@section('ocultar-footer', true)
@section('title', 'Verificar cuenta - Xiaoxa')

@section('content')
<div class="flex flex-col items-center justify-center px-4 py-8" style="min-height: calc(100vh - 160px);">
    <div class="w-full max-w-xl">

        <div class="p-8 sm:p-10">

            @if (request()->has('verificado'))
                <h1 class="text-2xl font-bold text-center text-black mb-8">¡Cuenta verificada!</h1>

                <div class="text-center space-y-3">
                    <p class="text-gray-700 text-sm leading-relaxed">
                        Tu correo electrónico ha sido verificado correctamente.
                    </p>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        Ya puedes iniciar sesión con tu correo y contraseña.
                    </p>
                </div>
            @else
                <h1 class="text-2xl font-bold text-center text-black mb-8">Verifica tu cuenta</h1>

                <div class="text-center space-y-3">
                    <p class="text-gray-700 text-sm leading-relaxed">
                        Tu cuenta ha sido creada. Sin embargo, es necesario activarla.
                    </p>
                    <p class="text-gray-700 text-sm leading-relaxed">
                        Hemos enviado un enlace de verificación a tu correo electrónico. Por favor, revisa tu bandeja de entrada o la carpeta de spam para activar tu cuenta.
                    </p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection