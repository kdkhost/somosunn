<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel do Membro')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome (opcional) -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <header class="bg-white shadow p-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <img src="{{ asset('img/logo-unn.png') }}" alt="Logo UNN" class="h-8">
            <span class="font-bold text-lg text-blue-900">SOMOS UNN</span>
        </div>
        <nav class="flex gap-4">
            <!-- Exemplo de links, ajuste conforme necessário -->
            <a href="/painel" class="text-blue-700 hover:underline">Início</a>
            <a href="/painel/cursos" class="text-blue-700 hover:underline">Meus Cursos</a>
            <a href="/painel/eventos" class="text-blue-700 hover:underline">Eventos</a>
            <a href="/painel/marketplace" class="text-blue-700 hover:underline">Marketplace</a>
        </nav>
        <div>
            <span class="text-gray-700 mr-2">{{ Auth::user()->name }}</span>
            <a href="/logout" class="text-red-600 hover:underline">Sair</a>
        </div>
    </header>
    <main class="flex-1 p-6">
        @yield('content')
    </main>
    <footer class="bg-white text-center text-gray-500 text-sm p-2 border-t">
        &copy; {{ date('Y') }} Grupo UNN. Todos os direitos reservados.
    </footer>
    @stack('scripts')
</body>
</html>
