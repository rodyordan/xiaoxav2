<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error - Xiaoxa')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#fbfaf8] text-gray-900 min-h-screen flex flex-col">

    {{-- HEADER LIMPIO: Solo el logo centrado --}}
    <header class="w-full bg-white border-b border-gray-200 py-4 flex justify-center items-center sticky top-0 z-50 h-20">
        <a href="{{ url('/') }}">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Xiaoxa" class="h-14 w-auto">
        </a>
    </header>

    {{-- CONTENIDO --}}
    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

</body>
</html>