<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | Panel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-[#fbfaf8] min-h-screen text-gray-900 overflow-x-hidden">

    <div class="flex min-h-screen relative">

        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col z-40">

            <div class="pt-14 pb-8 flex justify-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-block">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Xiaoxa" class="h-12 w-auto">
                </a>
            </div>

            <nav class="flex-1 py-6 overflow-y-auto">
                @php
                    $links = [
                        ['route' => 'admin.dashboard',          'pattern' => 'admin.dashboard',       'icon' => 'o-chart-bar',              'label' => 'Dashboard'],
                        ['route' => 'admin.ventas.index',       'pattern' => 'admin.ventas.*',        'icon' => 'o-building-storefront',    'label' => 'Ventas'],
                        ['route' => 'admin.productos.index',    'pattern' => 'admin.productos.*',     'icon' => 'o-cube',                   'label' => 'Productos'],
                        ['route' => 'admin.categorias.index',   'pattern' => 'admin.categorias.*',    'icon' => 'o-tag',                    'label' => 'Categorías'],
                        ['route' => 'admin.pedidos.index',      'pattern' => 'admin.pedidos.*',       'icon' => 'o-clipboard-document-list','label' => 'Pedidos'],
                        ['route' => 'admin.banners.index',      'pattern' => 'admin.banners.*',       'icon' => 'o-photo',                  'label' => 'Banners'],
                        ['route' => 'admin.cupones.index',      'pattern' => 'admin.cupones.*',       'icon' => 'o-ticket',                 'label' => 'Cupones'],
                    ];
                @endphp

                @foreach($links as $link)
                @php $activo = request()->routeIs($link['pattern']); @endphp
                <a href="{{ route($link['route']) }}"
                   class="flex items-center gap-3 py-3.5 pl-10 pr-5 mr-6 font-medium transition-all
                   {{ $activo
                        ? 'bg-pink-600 text-white rounded-r-full'
                        : 'text-gray-700 rounded-full' }}">
                    <x-dynamic-component :component="'heroicon-' . $link['icon']" class="w-5 h-5 flex-shrink-0" />
                    <span class="text-[15px]">{{ $link['label'] }}</span>
                </a>
                @endforeach
            </nav>

            <div class="pt-4 pb-6 mr-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 py-3.5 pl-10 pr-5 rounded-full font-semibold text-gray-700 transition-all text-left">
                        <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5 flex-shrink-0" />
                        <span class="text-[15px]">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        @php
            $flashSuccess = session()->pull('success');
            $flashError = session()->pull('error');
        @endphp

        @if($flashSuccess)
            <script>window.__flashSuccess = @json($flashSuccess);</script>
        @endif
        @if($flashError)
            <script>window.__flashError = @json($flashError);</script>
        @endif

        <div x-data="{
                toasts: [],
                add(type, messages) {
                    // Normaliza: acepta string o array
                    const list = Array.isArray(messages) ? messages : [messages];
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, type, messages: list });
                    setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 6000);
                },
                init() {
                    if (window.__flashSuccess && !window.__flashShown) {
                        this.add('success', window.__flashSuccess);
                        window.__flashShown = true;
                    }
                    if (window.__flashError && !window.__flashErrorShown) {
                        this.add('error', window.__flashError);
                        window.__flashErrorShown = true;
                    }

                    if (!window.__toastListenerAdded) {
                        window.__toastListenerAdded = true;
                        window.addEventListener('toast', (e) => {
                            this.add(e.detail.type, e.detail.message);
                        });
                    }
                }
            }"
            x-init="init()"
            class="fixed top-6 right-6 z-[999] flex flex-col gap-3 pointer-events-none">

            <template x-for="t in toasts" :key="t.id">
                <div x-show="true"
                    x-transition:enter="transform ease-out duration-500 transition"
                    x-transition:enter-start="translate-y-4 opacity-0 scale-95 sm:translate-y-0 sm:translate-x-10"
                    x-transition:enter-end="translate-y-0 opacity-100 scale-100 sm:translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="pointer-events-auto flex items-start gap-4 p-5 bg-white shadow-2xl rounded-[2rem] min-w-[340px] max-w-md"
                    :class="t.type === 'success' ? 'border border-emerald-100' : 'border border-rose-100'">

                    <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                        :class="t.type === 'success'
                            ? 'bg-emerald-50'
                            : 'bg-gradient-to-br from-rose-50 to-rose-100 ring-4 ring-rose-50/50'">

                        <template x-if="t.type === 'success'">
                            <x-heroicon-o-check class="w-6 h-6 text-emerald-500" style="stroke-width:2" />
                        </template>

                        <template x-if="t.type === 'error'">
                            <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-rose-500" style="stroke-width:2" />
                        </template>
                    </div>

                    <div class="flex-1">
                        <p class="font-black text-gray-900 text-sm leading-none" x-text="t.type === 'success' ? '¡Éxito!' : 'Error'"></p>

                        <!-- Mensajes uno debajo del otro con viñeta alineada verticalmente -->
                        <template x-if="t.type === 'error'">
                            <ul class="mt-2 space-y-1">
                                <template x-for="(msg, i) in t.messages" :key="i">
                                    <li class="relative pl-3.5 text-gray-500 text-xs font-medium leading-relaxed">
                                        <span class="absolute left-0 top-[0.6em] w-1 h-1 rounded-full bg-rose-400"></span>
                                        <span x-text="msg"></span>
                                    </li>
                                </template>
                            </ul>
                        </template>

                        <template x-if="t.type === 'success'">
                            <p class="text-gray-500 text-xs font-medium mt-1.5 leading-relaxed" x-text="t.messages[0]"></p>
                        </template>
                    </div>

                    <button @click="toasts = toasts.filter(x => x.id !== t.id)" class="text-gray-300 hover:text-gray-600 transition flex-shrink-0 p-1">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>
                </div>
            </template>
        </div>

        <main class="flex-1 overflow-y-auto max-h-screen">
            <div class="px-10 py-8">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>