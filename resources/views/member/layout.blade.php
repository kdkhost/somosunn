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
        <!-- Menu Desktop -->
        <nav class="hidden md:flex gap-4">
            <a href="/painel" class="text-blue-700 hover:underline">Início</a>
            <a href="/painel/cursos" class="text-blue-700 hover:underline">Meus Cursos</a>
            <a href="/painel/eventos" class="text-blue-700 hover:underline">Eventos</a>
            <a href="/painel/marketplace" class="text-blue-700 hover:underline">Marketplace</a>
        </nav>
        <!-- Botão Mobile -->
        <button id="mobile-menu-toggle" class="md:hidden text-blue-700 focus:outline-none" aria-label="Abrir menu">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div class="hidden md:flex items-center">
            <span class="text-gray-700 mr-2">{{ Auth::user()->name }}</span>
            <a href="/logout" class="text-red-600 hover:underline">Sair</a>
        </div>
    </header>
    <!-- Menu Mobile -->
    <div id="mobile-menu" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden transition-opacity duration-300">
        <div class="absolute top-0 right-0 w-3/4 max-w-xs h-full bg-white shadow-lg flex flex-col p-6 transform translate-x-full transition-transform duration-300" id="mobile-menu-panel" tabindex="-1" aria-modal="true" role="dialog">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('img/logo-unn.png') }}" alt="Logo UNN" class="h-8">
                    <span class="font-bold text-lg text-blue-900">SOMOS UNN</span>
                </div>
                <button id="mobile-menu-close" class="text-gray-700 text-2xl focus:outline-none" aria-label="Fechar menu">&times;</button>
            </div>
            <nav class="flex flex-col gap-4 mb-8">
                <a href="/painel" class="text-blue-700 hover:underline">Início</a>
                <a href="/painel/cursos" class="text-blue-700 hover:underline">Meus Cursos</a>
                <a href="/painel/eventos" class="text-blue-700 hover:underline">Eventos</a>
                <a href="/painel/marketplace" class="text-blue-700 hover:underline">Marketplace</a>
            </nav>
            <div class="mt-auto flex flex-col gap-2">
                <span class="text-gray-700">{{ Auth::user()->name }}</span>
                <a href="/logout" class="text-red-600 hover:underline">Sair</a>
            </div>
        </div>
    </div>
    <main class="flex-1 p-6">
        @yield('content')
    </main>
    <footer class="bg-white text-center text-gray-500 text-sm p-2 border-t">
        &copy; {{ date('Y') }} Grupo UNN. Todos os direitos reservados.
    </footer>
    @stack('scripts')
    <script>
        // Menu mobile responsivo aprimorado
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('mobile-menu-toggle');
            const menu = document.getElementById('mobile-menu');
            const panel = document.getElementById('mobile-menu-panel');
            const close = document.getElementById('mobile-menu-close');
            let lastFocused = null;
            function openMenu() {
                menu.classList.remove('hidden');
                setTimeout(() => {
                    menu.classList.add('opacity-100');
                    panel.classList.remove('translate-x-full');
                    panel.focus();
                }, 10);
                document.body.style.overflow = 'hidden';
                lastFocused = document.activeElement;
            }
            function closeMenu() {
                menu.classList.remove('opacity-100');
                panel.classList.add('translate-x-full');
                setTimeout(() => {
                    menu.classList.add('hidden');
                    if (lastFocused) lastFocused.focus();
                }, 300);
                document.body.style.overflow = '';
            }
            if (toggle && menu && close && panel) {
                toggle.addEventListener('click', openMenu);
                close.addEventListener('click', closeMenu);
                menu.addEventListener('click', function (e) {
                    if (e.target === menu) closeMenu();
                });
                // Fecha ao navegar
                menu.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', closeMenu);
                });
                // Acessibilidade: ESC fecha menu
                document.addEventListener('keydown', function (e) {
                    if (!menu.classList.contains('hidden') && e.key === 'Escape') closeMenu();
                });
            }
        });
    </script>
</body>
</html>
