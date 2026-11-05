<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Xiaoxa')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @stack('styles')
</head>

<body class="bg-[#fbfaf8] text-gray-900 min-h-screen flex flex-col">
    {{-- NAVBAR STICKY --}}
    <div class="sticky top-0 z-50">
        <x-navbar :modoCarrito="request()->routeIs('carrito.*') || request()->routeIs('checkout.*') || request()->routeIs('pago.*')" />
    </div>

    <main class="flex-1 w-full" style="min-height: calc(100vh - 160px);">
        @yield('content')
    </main>

    @hasSection('ocultar-footer')
    @else
        <x-footer />
    @endif

    @stack('scripts')
</body>
</html>