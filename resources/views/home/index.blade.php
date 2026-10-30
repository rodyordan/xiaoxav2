@extends('layouts.app')

@section('title', 'Xiaoxa')

@section('content')

@if(!request('categoria') && !request('promocion') && !request('buscar') && !request('nuevo'))
    @if(isset($banners) && $banners->count() > 0)
    @php
        $slides = $banners->count() > 1 ? $banners->concat([$banners->first()]) : $banners;
    @endphp
    <section class="w-full bg-white">
        <div
            x-data="{
                pos: 0,
                total: {{ $banners->count() }},
                animar: true,
                timer: null,
                inicioX: 0,

                init() { this.iniciarAutoplay(); },
                actual() { return this.pos % this.total; },
                iniciarAutoplay() {
                    this.detenerAutoplay();
                    if (this.total < 2) return;
                    this.timer = setInterval(() => this.siguiente(), 5000);
                },
                detenerAutoplay() {
                    if (this.timer) { clearInterval(this.timer); this.timer = null; }
                },
                siguiente() {
                    if (this.total < 2 || this.pos >= this.total) return;
                    this.pos++;
                    if (this.pos === this.total) {
                        setTimeout(() => this.saltarAlInicio(), 750);
                    }
                },
                anterior() {
                    if (this.total < 2) return;
                    if (this.pos === 0) {
                        this.animar = false;
                        this.pos = this.total;
                        setTimeout(() => { this.animar = true; this.pos = this.total - 1; }, 50);
                    } else {
                        this.pos--;
                    }
                },
                saltarAlInicio() {
                    if (this.pos !== this.total) return;
                    this.animar = false;
                    this.pos = 0;
                    setTimeout(() => { this.animar = true; }, 50);
                },
                irA(i) {
                    if (this.pos === this.total && i === 0) {
                        this.saltarAlInicio();
                    } else {
                        this.pos = i;
                    }
                    this.iniciarAutoplay();
                },
                terminarToque(e) {
                    const dx = e.changedTouches[0].clientX - this.inicioX;
                    if (Math.abs(dx) > 50) {
                        if (dx < 0) { this.siguiente(); } else { this.anterior(); }
                    }
                    this.iniciarAutoplay();
                }
            }"
            class="relative w-full"
        >
            <div
                class="relative w-full h-[300px] md:h-[500px] lg:h-[750px]"
                style="overflow: hidden; touch-action: pan-y;"
                @touchstart.passive="inicioX = $event.touches[0].clientX; detenerAutoplay()"
                @touchend="terminarToque($event)"
            >
                <div
                    class="flex h-full"
                    :style="'transform: translateX(-' + (pos * 100) + '%); transition: ' + (animar ? 'transform 700ms ease-in-out' : 'none')"
                    @transitionend.self="saltarAlInicio()"
                >
                    @foreach($slides as $index => $banner)
                    <div
                        class="w-full h-full shrink-0 flex items-center justify-center"
                        @if($index >= $banners->count()) aria-hidden="true" @endif
                    >
                        @if($banner->url_boton)
                            <a href="{{ $banner->url_boton }}" class="block w-full h-full">
                                <img
                                    src="{{ url('/api/imagen/' . $banner->imagen) }}"
                                    alt="{{ $banner->titulo }}"
                                    class="w-full h-full object-cover object-center"
                                    loading="eager"
                                    draggable="false"
                                >
                            </a>
                        @else
                            <img
                                src="{{ url('/api/imagen/' . $banner->imagen) }}"
                                alt="{{ $banner->titulo }}"
                                class="w-full h-full object-cover object-center"
                                loading="eager"
                                draggable="false"
                            >
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            @if($banners->count() > 1)
            <div class="flex items-center justify-center gap-2 mt-3 pb-3">
                @foreach($banners as $index => $banner)
                <button
                    @click="irA({{ $index }})"
                    :class="actual() === {{ $index }} ? 'w-12 h-1.5 bg-gray-600' : 'w-12 h-1.5 bg-gray-300 hover:bg-gray-400'"
                    class="rounded-sm transition-all duration-300"
                    aria-label="Slide {{ $index + 1 }}"
                ></button>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @else
    <section class="w-full bg-white">
        <div class="relative w-full h-[300px] md:h-[500px] lg:h-[750px]" style="overflow: hidden;">
            <img src="{{ asset('images/banner-home.jpg') }}" alt="Banner" class="w-full h-full object-cover object-center">
        </div>
    </section>
    @endif
@endif

@if(isset($categorias) && $categorias->count() > 0)

    @foreach($categorias as $categoria)
    <div class="max-w-7xl mx-auto px-4 sm:px-8 py-10">

        <div class="flex items-center justify-between mb-7">
            <div class="w-20"></div>
            <h2 class="text-3xl font-normal text-black text-center flex-1">
                {{ $categoria->nombre_categoria }}
            </h2>
            <div class="w-20 flex justify-end">
                <a href="{{ route('home', ['categoria' => $categoria->nombre_categoria]) }}"
                    class="text-xs font-medium hover:text-black transition-colors flex items-center gap-1 mt-4">
                    Ver todo
                    <x-heroicon-o-chevron-right class="w-3 h-3" />
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($categoria->productos->take(4) as $item)
                <a href="{{ url('/producto/' . $item->id_producto) }}"
                    class="group cursor-pointer block transition-all duration-300">

                    <div class="relative aspect-[3/4] bg-[#f5f5f5] overflow-hidden rounded-xl">
                        @if($item->precio_oferta)
                            <span class="absolute top-2.5 right-2.5 bg-red-500 text-white text-[15px] font-semibold leading-none min-w-[46px] text-center px-1 py-1.5 z-10 rounded-md shadow-sm">
                                -{{ abs($item->descuento) }}%
                            </span>
                        @endif
                        <img
                            src="{{ $item->imagen_principal ? url('/api/imagen/' . $item->imagen_principal) : 'https://placehold.co/600x800/f5f5f5/cccccc?text=Sin+Imagen' }}"
                            alt="{{ $item->nombre_producto }}"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                            loading="lazy"
                        >
                    </div>

                    <div class="pt-3 space-y-1">
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">
                            {{ $item->marca }}
                        </p>
                        <h3 class="text-[15px] font-normal text-gray-800 leading-snug line-clamp-2">
                            {{ ucwords(strtolower($item->nombre_producto)) }}
                        </h3>
                        <div class="flex items-center gap-2 flex-wrap pt-1">
                            @if($item->precio_oferta)
                                <span class="text-xl font-normal text-red-500">
                                    S/ {{ number_format($item->precio_oferta, 2) }}
                                </span>
                                <span class="text-sm text-gray-500 line-through">
                                    S/ {{ number_format($item->precio, 2) }}
                                </span>
                            @else
                                <span class="text-xl font-normal text-gray-900">
                                    S/ {{ number_format($item->precio, 2) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
    @endforeach

@endif

@if(isset($productos) && $productos->count() > 0)

@if(request('buscar'))
    <div class="w-full px-6 sm:px-16 pt-6 pb-2">
        <h1 class="text-xl font-normal text-black" style="text-align: left !important; margin: 0;">
            Resultados para <span class="font-semibold">{{ request('buscar') }}</span>
        </h1>
    </div>
@else
    <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-6 pb-2">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="flex items-center gap-1" title="Volver al inicio">
                <span class="text-base font-normal text-black">Inicio</span>
                <x-heroicon-o-chevron-right class="w-3 h-3 text-black" />
            </a>

            @if(request('categoria'))
                <span class="text-base font-normal text-black truncate">
                    {{ ucwords(strtolower(request('categoria'))) }}
                </span>
            @elseif(request('promocion'))
                <span class="text-base font-normal text-black truncate">
                    Promociones
                </span>
            @elseif(request('nuevo'))
                <span class="text-base font-normal text-black truncate">
                    Lo nuevo
                </span>
            @else
                <span class="text-base font-normal text-black truncate">
                    Todos los Productos
                </span>
            @endif
        </div>
    </div>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8">

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        @foreach($productos as $item)
            <a href="{{ url('/producto/' . $item->id_producto) }}"
                class="group cursor-pointer block transition-all duration-300">

                <div class="relative aspect-[3/4] bg-[#f5f5f5] overflow-hidden rounded-xl">
                    @if($item->precio_oferta)
                        <span class="absolute top-2.5 right-2.5 bg-red-500 text-white text-[15px] font-semibold leading-none min-w-[46px] text-center px-1 py-1.5 z-10 rounded-md shadow-sm">
                            -{{ abs($item->descuento) }}%
                        </span>
                    @endif
                    <img
                        src="{{ $item->imagen_principal ? url('/api/imagen/' . $item->imagen_principal) : 'https://placehold.co/600x800/f5f5f5/cccccc?text=Sin+Imagen' }}"
                        alt="{{ $item->nombre_producto }}"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        loading="lazy"
                    >
                </div>

                <div class="pt-3 space-y-1">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">
                        {{ $item->marca }}
                    </p>
                    <h3 class="text-[15px] font-normal text-gray-800 leading-snug line-clamp-2">
                        {{ ucwords(strtolower($item->nombre_producto)) }}
                    </h3>
                    <div class="flex items-center gap-2 flex-wrap pt-1">
                        @if($item->precio_oferta)
                            <span class="text-xl font-normal text-red-500">
                                S/ {{ number_format($item->precio_oferta, 2) }}
                            </span>
                            <span class="text-sm text-gray-500 line-through">
                                S/ {{ number_format($item->precio, 2) }}
                            </span>
                        @else
                            <span class="text-xl font-normal text-gray-900">
                                S/ {{ number_format($item->precio, 2) }}
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

</div>

@elseif(isset($productos) && $productos->isEmpty() && (request('buscar') || request('categoria') || request('promocion') || request('nuevo')))

@if(request('buscar'))
    <div class="w-full px-6 sm:px-16 pt-6 pb-2">
        <h1 class="text-xl font-normal text-black" style="text-align: left !important; margin: 0;">
            Resultados para <span class="font-semibold">{{ request('buscar') }}</span>
        </h1>
    </div>
@else
    <div class="max-w-7xl mx-auto px-4 sm:px-8 pt-6 pb-2">
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="flex items-center gap-1" title="Volver al inicio">
                <span class="text-base font-normal text-black">Inicio</span>
                <x-heroicon-o-chevron-right class="w-3 h-3 text-black" />
            </a>

            @if(request('categoria'))
                <span class="text-base font-normal text-black truncate">
                    {{ ucwords(strtolower(request('categoria'))) }}
                </span>
            @elseif(request('promocion'))
                <span class="text-base font-normal text-black truncate">
                    Promociones
                </span>
            @elseif(request('nuevo'))
                <span class="text-base font-normal text-black truncate">
                    Lo nuevo
                </span>
            @endif
        </div>
    </div>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-8 py-24 text-center">
    <x-heroicon-o-magnifying-glass class="w-16 h-16 mx-auto mb-4 text-gray-200" />

    @if(request('buscar'))
        <h3 class="text-lg font-bold text-gray-800 mb-2">
            No encontramos resultados para "{{ request('buscar') }}"
        </h3>
        <p class="text-sm text-gray-500">
            Intenta con otras palabras o revisa la ortografía.
        </p>
    @elseif(request('categoria'))
        <h3 class="text-lg font-bold text-gray-800 mb-2">
            No hay productos en esta categoría
        </h3>
        <p class="text-sm text-gray-500">
            Vuelve pronto para ver novedades.
        </p>
    @elseif(request('promocion'))
        <h3 class="text-lg font-bold text-gray-800 mb-2">
            No hay promociones activas
        </h3>
        <p class="text-sm text-gray-500">
            Vuelve pronto para aprovechar nuestras ofertas.
        </p>
    @elseif(request('nuevo'))
        <h3 class="text-lg font-bold text-gray-800 mb-2">
            No hay productos nuevos
        </h3>
        <p class="text-sm text-gray-500">
            Vuelve pronto para ver las novedades.
        </p>
    @endif
</div>
@endif

@endsection