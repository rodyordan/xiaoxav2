@extends('layouts.app')

@section('title', 'Compra finalizada - Xiaoxa')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-8 pt-6 pb-2">
    <div class="flex items-center justify-center gap-1 sm:gap-2 flex-wrap">
        <a href="{{ route('carrito.index') }}" class="flex items-center gap-1">
            <span class="text-sm sm:text-[20px] font-normal text-gray-800">Mi bolsa</span>
        </a>
        <x-heroicon-o-chevron-right class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 shrink-0" />
        <span class="text-sm sm:text-[20px] font-normal text-gray-800">Datos de compra</span>
        <x-heroicon-o-chevron-right class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 shrink-0" />
        <span class="text-sm sm:text-[20px] font-bold text-gray-800">Compra finalizada</span>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 sm:py-12">

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-10 text-center max-w-lg mx-auto">

        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
            ¡Compra exitosa!
        </h1>

        <p class="text-gray-500 text-sm leading-relaxed mb-8">
            Su pedido fue registrado. Una vez sea enviado, podrá ver en
            <span class="font-semibold text-gray-700">"Mis pedidos"</span>
            la dirección donde deberá recoger su pedido.
        </p>

        <a href="{{ route('perfil.index') }}?seccion=pedidos"
           class="block w-full border border-gray-200 rounded-full py-3.5 text-sm font-semibold hover:bg-gray-50 transition-colors">
            Ver mis pedidos
        </a>

    </div>
</div>

@endsection