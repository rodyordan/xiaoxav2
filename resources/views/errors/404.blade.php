@extends('layouts.error')

@section('title', 'Página no encontrada - Xiaoxa')

@section('content')
    {{-- Cambiamos flex-1 por un min-h que ocupe el 100% de la pantalla menos el header (80px) --}}
    <div class="min-h-[calc(100vh-80px)] flex flex-col items-center justify-center p-6 text-center">
        
        {{-- Contenedor invisible para mantener el tamaño --}}
        <div class="max-w-md w-full bg-transparent border-transparent shadow-none p-8">
            
            {{-- Icono de Lupa (más pequeño) --}}
            <div class="w-20 h-20 bg-[#f1f1f1] rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>

            <h1 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Página no encontrada</h1>
            
            <p class="text-gray-500 font-medium mb-8">
                Lo sentimos, la página que buscas no existe o fue movida. Verifica la dirección o regresa al inicio para seguir comprando.
            </p>

            <a href="{{ url('/') }}" 
               class="inline-flex items-center justify-center gap-2 w-full bg-black text-white px-8 py-4 rounded-xl font-bold uppercase tracking-widest text-xs hover:bg-gray-800 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Volver al inicio
            </a>
            
        </div>

    </div>
@endsection