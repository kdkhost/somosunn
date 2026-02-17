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

<body class="bg-gray-50 dark:bg-slate-950 min-h-screen flex flex-col transition-colors duration-300">
    <header
        class="bg-white dark:bg-slate-900 shadow p-4 flex items-center justify-between border-b dark:border-slate-800 transition-colors">
        <div class="flex items-center gap-2">
            <img src="{{ asset('img/logo-unn.png') }}" alt="Logo UNN" class="h-8">
            <span class="font-bold text-lg text-blue-900 dark:text-white transition-colors">SOMOS UNN</span>
        </div>
        <!-- Menu Desktop -->
        <nav class="hidden md:flex gap-4">
            <a href="/painel" class="text-blue-700 dark:text-blue-400 hover:underline">Início</a>
            <a href="/painel/cursos" class="text-blue-700 dark:text-blue-400 hover:underline">Meus Cursos</a>
            <a href="/painel/eventos" class="text-blue-700 dark:text-blue-400 hover:underline">Eventos</a>
            <a href="/painel/marketplace" class="text-blue-700 dark:text-blue-400 hover:underline">Marketplace</a>
        </nav>
        <!-- Botão Mobile -->
        <button id="mobile-menu-toggle" class="md:hidden text-blue-700 focus:outline-none" aria-label="Abrir menu">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div class="hidden md:flex items-center">
            <span class="text-gray-700 dark:text-slate-300 mr-2">{{ Auth::user()->name }}</span>
            <a href="/logout" class="text-red-600 dark:text-red-400 hover:underline">Sair</a>
        </div>
    </header>
    <!-- Menu Mobile -->
    <div id="mobile-menu"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm hidden pointer-events-none transition-opacity duration-300">
        <div class="absolute top-0 right-0 w-3/4 max-w-xs h-full bg-white dark:bg-slate-900 shadow-lg flex flex-col p-6 transform translate-x-full transition-transform duration-300 border-l dark:border-slate-800"
            id="mobile-menu-panel" tabindex="-1" aria-modal="true" role="dialog">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('img/logo-unn.png') }}" alt="Logo UNN" class="h-8">
                    <span class="font-bold text-lg text-blue-900 dark:text-white">SOMOS UNN</span>
                </div>
                <button id="mobile-menu-close" class="text-gray-700 dark:text-slate-300 text-2xl focus:outline-none"
                    aria-label="Fechar menu">&times;</button>
            </div>
            <nav class="flex flex-col gap-4 mb-8">
                <a href="/painel" class="text-blue-700 dark:text-blue-400 hover:underline">Início</a>
                <a href="/painel/cursos" class="text-blue-700 dark:text-blue-400 hover:underline">Meus Cursos</a>
                <a href="/painel/eventos" class="text-blue-700 dark:text-blue-400 hover:underline">Eventos</a>
                <a href="/painel/marketplace" class="text-blue-700 dark:text-blue-400 hover:underline">Marketplace</a>
            </nav>
            <div class="mt-auto flex flex-col gap-2">
                <span class="text-gray-700 dark:text-slate-400">{{ Auth::user()->name }}</span>
                <a href="/logout" class="text-red-600 dark:text-red-400 hover:underline">Sair</a>
            </div>
        </div>
    </div>
    <main class="flex-1 p-6">
        @yield('content')
    </main>
    <footer
        class="bg-white dark:bg-slate-900 text-center text-gray-500 dark:text-slate-400 text-sm p-4 border-t dark:border-slate-800 transition-colors">
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

            if (!toggle || !menu || !panel || !close) {
                return;
            }

            function openMenu() {
                lastFocused = document.activeElement;

                // Remove pointer-events-none e adiciona pointer-events-auto
                menu.classList.remove('hidden', 'pointer-events-none');
                menu.classList.add('pointer-events-auto');
                menu.setAttribute('aria-hidden', 'false');

                // Anima o painel
                setTimeout(() => {
                    panel.classList.remove('translate-x-full');
                }, 10);

                // Foca no painel para acessibilidade
                panel.focus();

                // Previne scroll do body
                document.body.style.overflow = 'hidden';
            }

            function closeMenu() {
                // Anima o painel para fora
                panel.classList.add('translate-x-full');

                // Adiciona pointer-events-none e remove pointer-events-auto
                menu.classList.add('pointer-events-none');
                menu.classList.remove('pointer-events-auto');

                // Aguarda animação antes de esconder
                setTimeout(() => {
                    menu.classList.add('hidden');
                    menu.setAttribute('aria-hidden', 'true');

                    // Restaura scroll do body
                    document.body.style.overflow = '';

                    // Retorna foco ao botão toggle
                    if (lastFocused) {
                        lastFocused.focus();
                    }
                }, 300);
            }

            // Event listeners
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                openMenu();
            });

            close.addEventListener('click', function (e) {
                e.preventDefault();
                closeMenu();
            });

            // Fecha ao clicar no overlay
            menu.addEventListener('click', function (e) {
                if (e.target === menu) {
                    closeMenu();
                }
            });

            // Fecha com tecla ESC
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
                    closeMenu();
                }
            });

            // Trap focus dentro do menu quando aberto
            panel.addEventListener('keydown', function (e) {
                if (e.key === 'Tab') {
                    const focusableElements = panel.querySelectorAll(
                        'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (e.shiftKey && document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    } else if (!e.shiftKey && document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            });
        });
    </script>
</body>

</html>