@props(['modoCarrito' => false, 'sinAcciones' => false])

<nav class="border-b border-gray-200 bg-white"
    x-data="{
        searchOpen: false,
        catOpen: false,
        query: '{{ addslashes(request('buscar', '')) }}',
        submit() {
            const q = this.query.trim();
            if (q.length > 0) {
                window.location.href = '{{ route('home') }}?buscar=' + encodeURIComponent(q);
            }
        },
        clear() {
            this.query = '';
            window.location.href = '{{ route('home') }}';
        }
    }"
>
    <div class="bg-white">
        <div class="w-full px-4 sm:px-8">
            {{-- Izquierda y derecha se reparten el espacio; el logo ocupa solo lo que necesita --}}
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 h-16 sm:h-20">

                <div class="flex items-center justify-start min-w-0">
                    @if(!$sinAcciones)
                        <div class="hidden lg:flex items-center bg-[#f1f1f1] rounded-full px-3 py-1">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5 text-black mr-2" />
                            <input
                                x-ref="searchInput"
                                x-model="query"
                                @keydown.enter="submit()"
                                type="text"
                                placeholder="Buscar..."
                                class="w-48 bg-transparent focus:outline-none text-black placeholder-black text-sm py-1"
                            >
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-center">
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Xiaoxa" class="h-10 sm:h-14 w-auto">
                    </a>
                </div>

                <div class="flex items-center justify-end gap-0.5 sm:gap-1 min-w-0">
                    @if(!$sinAcciones)
                        <button @click="searchOpen = !searchOpen" class="lg:hidden p-1" aria-label="Buscar">
                            <x-heroicon-o-magnifying-glass x-show="!searchOpen" x-cloak class="h-6 w-6 sm:h-7 sm:w-7 text-black" />
                            <x-heroicon-o-x-mark x-show="searchOpen" x-cloak class="h-6 w-6 sm:h-7 sm:w-7 text-black" />
                        </button>

                        @if($modoCarrito)
                            <a href="{{ route('home') }}"
                               title="Seguir comprando"
                               aria-label="Seguir comprando"
                               class="flex items-center gap-2 p-1 text-black font-bold text-sm uppercase transition-opacity">
                                <x-heroicon-o-shopping-bag class="h-6 w-6 text-black shrink-0" />
                                {{-- En celular solo se ve el ícono para que no se desborde --}}
                                <span class="hidden sm:inline whitespace-nowrap">Seguir comprando</span>
                            </a>
                        @else
                            @auth
                                @if(auth()->user()->hasVerifiedEmail())
                                    <a href="{{ route('perfil.index') }}" class="p-1" title="Mi cuenta">
                                        <x-heroicon-o-user class="h-6 w-6 sm:h-7 sm:w-7 text-black" />
                                    </a>
                                @else
                                    <a href="{{ route('logout.redirect') }}" class="p-1" title="Iniciar sesión">
                                        <x-heroicon-o-user class="h-6 w-6 sm:h-7 sm:w-7 text-black" />
                                    </a>
                                @endif
                            @endauth
                            @guest
                                <a href="{{ route('login') }}" class="p-1" title="Iniciar sesión">
                                    <x-heroicon-o-user class="h-6 w-6 sm:h-7 sm:w-7 text-black" />
                                </a>
                            @endguest

                            @php
                                $totalItems = 0;
                                if (Auth::check() && Auth::user()->hasVerifiedEmail()) {
                                    $carrito = \App\Models\Carrito::where('id_usuario', Auth::id())->first();
                                    $totalItems = $carrito ? $carrito->detalles()->sum('cantidad') : 0;
                                }
                            @endphp

                            <a href="{{ route('carrito.index') }}" class="relative p-1" title="Carrito">
                                <x-heroicon-o-shopping-bag class="h-6 w-6 sm:h-7 sm:w-7 text-black" />

                                @if($totalItems > 0)
                                    <span class="absolute top-0 right-0 bg-red-500 text-white text-[9px] rounded-full h-4 w-4 flex items-center justify-center font-bold">
                                        {{ $totalItems }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @unless($modoCarrito)
        <div class="border-t border-gray-200"></div>

        <div class="bg-[#f1f1f1]">
            <div class="w-full px-5 sm:px-8">

                <div class="hidden md:flex items-center justify-center space-x-8 lg:space-x-10 py-3">

                    <a href="{{ route('home', ['nuevo' => 1]) }}"
                        class="text-base font-normal text-black">
                        Lo nuevo
                    </a>

                    @if(isset($categoriasMenu) && $categoriasMenu->count() > 0)
                    <div class="relative"
                         x-data="{ open: false }"
                         @mouseenter="open = true"
                         @mouseleave="open = false"
                         @click.outside="open = false"
                         @keydown.escape.window="open = false">

                        {{-- @click permite abrirlo también con el dedo en tablets --}}
                        <button @click="open = true" class="text-base font-normal text-black flex items-center gap-1">
                            Categorías
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0 -translate-y-2"
                            class="absolute top-full left-0 pt-2 z-50"
                        >
                            <div class="bg-white border border-gray-200 rounded-lg shadow-lg py-2 min-w-[200px]">
                                @foreach($categoriasMenu as $cat)
                                    <a href="{{ route('home', ['categoria' => $cat->nombre_categoria]) }}"
                                        class="block px-4 py-2.5 text-sm text-black hover:bg-[#f1f1f1] transition-colors">
                                        {{ $cat->nombre_categoria }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('home', ['categoria' => 'Accesorios']) }}"
                        class="text-base font-normal text-black">
                        Accesorios
                    </a>

                    <a href="{{ route('home', ['promocion' => 1]) }}"
                        class="text-base font-normal text-black">
                        Promociones
                    </a>
                </div>

                <div class="md:hidden overflow-x-auto no-scrollbar -mx-5 px-5">
                    <div class="flex items-center w-max mx-auto gap-6 py-2.5 whitespace-nowrap text-sm text-black">

                        <a href="{{ route('home', ['nuevo' => 1]) }}" class="text-black">Lo nuevo</a>

                        @if(isset($categoriasMenu) && $categoriasMenu->count() > 0)
                            <button @click="catOpen = !catOpen" class="flex items-center gap-1 text-black">
                                Categorías
                                <span class="inline-flex transition-transform duration-200"
                                      :class="catOpen ? 'rotate-180' : ''">
                                    <x-heroicon-o-chevron-down class="w-3.5 h-3.5" />
                                </span>
                            </button>
                        @endif

                        <a href="{{ route('home', ['categoria' => 'Accesorios']) }}" class="text-black">Accesorios</a>
                        <a href="{{ route('home', ['promocion' => 1]) }}" class="text-black">Promociones</a>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($categoriasMenu) && $categoriasMenu->count() > 0)
            <div
                x-show="catOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="md:hidden bg-white border-t border-gray-200 shadow-md"
            >
                <div class="max-h-64 overflow-y-auto">
                    @foreach($categoriasMenu as $cat)
                        <a href="{{ route('home', ['categoria' => $cat->nombre_categoria]) }}"
                           class="block px-5 py-3 text-sm text-black border-b border-gray-100 last:border-b-0 active:bg-[#f1f1f1]">
                            {{ $cat->nombre_categoria }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    @endunless

    @if(!$sinAcciones)
        <div
            x-show="searchOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="lg:hidden border-t border-gray-100 px-4 py-3 bg-white shadow-md"
        >
            <div class="relative max-w-md mx-auto">
                <div class="flex items-center bg-[#f1f1f1] rounded-full px-3 py-1">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-black mr-2" />
                    {{-- text-base evita que iPhone haga zoom automático al tocar el campo --}}
                    <input
                        x-model="query"
                        @keydown.enter="submit()"
                        type="text"
                        placeholder="Buscar productos..."
                        class="w-full bg-transparent focus:outline-none text-black placeholder-black text-base py-1"
                    >
                </div>
            </div>
        </div>
    @endif
</nav>