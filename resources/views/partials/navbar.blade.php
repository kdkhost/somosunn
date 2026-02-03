{{-- Navbar UNN com submenus e sidebar mobile --}}
@php
    $logoFront = \App\Models\Setting::get('logo_front') ?: \App\Models\Setting::get('logo_image');
    $logoSrc = $logoFront ? asset(ltrim($logoFront, '/')) : asset('img/logo.svg');
    $menuItems = [
        [
            'label' => 'Institucional',
            'href' => route('sobre'),
            'children' => [
                ['label' => 'Sobre a UNN', 'href' => route('sobre')],
                ['label' => 'Manifesto', 'href' => route('manifesto')],
                ['label' => 'Quem somos', 'href' => route('quem-somos')],
                ['label' => 'Como funciona', 'href' => route('como-funciona')],
                ['label' => 'Valores', 'href' => route('valores')],
                ['label' => 'Contato', 'href' => route('contato')],
            ],
        ],
        [
            'label' => 'Comunidade',
            'href' => route('portal'),
            'setting_key' => 'feature_community',
            'children' => [
                ['label' => 'Portal', 'href' => route('portal')],
                ['label' => 'Feed Social', 'href' => route('social.feed'), 'setting_key' => 'feature_social'],
                ['label' => 'Cursos', 'href' => route('courses.index'), 'setting_key' => 'feature_courses'],
                ['label' => 'Eventos', 'href' => route('events.index'), 'setting_key' => 'feature_events'],
                ['label' => 'Membros', 'href' => route('membros')],
            ],
        ],
        ['label' => 'Premium', 'href' => route('premium'), 'setting_key' => 'feature_premium'],
    ];
    $cta = ['label' => 'Fazer parte', 'href' => route('register'), 'class' => 'bg-gradient-to-br from-[#1F5EDB] via-[#177FD6] to-[#1D3FC4] text-white shadow-[0_15px_30px_-10px_rgba(29,63,196,0.45)]'];
@endphp

<nav class="fixed inset-x-0 top-0 z-30 bg-white shadow-xl border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 md:px-10 lg:px-16 py-4 flex items-center justify-between gap-4">
        <!-- Logo -->
            <div class="inline-flex h-12 md:h-16 w-auto items-center justify-center overflow-hidden">
                <img src="{{ $logoSrc }}" alt="UNN" class="h-full w-auto object-contain" onerror="this.style.display='none';">
            </div>

        <!-- Menu + Actions (aligned right) -->
        <div class="flex items-center gap-6">
            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-4 text-sm font-semibold text-gray-800">
                @foreach($menuItems as $item)
                    @php
                        // Check if this menu item has a setting key and if it's enabled
                        $settingKey = $item['setting_key'] ?? null;
                        $isEnabled = $settingKey ? \App\Models\Setting::get($settingKey, '1') === '1' : true;
                    @endphp
                    @if($isEnabled)
                        @if(isset($item['children']))
                            <div class="relative group">
                                <a href="{{ $item['href'] }}" class="inline-flex items-center gap-1 hover:text-[#1F5EDB] transition-colors py-2">
                                    {{ $item['label'] }}
                                    <i class="fas fa-chevron-down text-xs ml-0.5 opacity-70"></i>
                                </a>
                                <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-40">
                                    <div class="rounded-2xl bg-white shadow-xl border border-slate-100 min-w-[220px] py-2 overflow-hidden">
                                        @foreach($item['children'] as $child)
                                            @php
                                                $childSettingKey = $child['setting_key'] ?? null;
                                                $childEnabled = $childSettingKey ? \App\Models\Setting::get($childSettingKey, '1') === '1' : true;
                                            @endphp
                                            @if($childEnabled)
                                                <a href="{{ $child['href'] }}" class="block px-5 py-3 text-sm text-gray-700 hover:bg-slate-50 hover:text-[#1F5EDB] transition bg-white">
                                                    {{ $child['label'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ $item['href'] }}" class="hover:text-[#1F5EDB] transition-colors">{{ $item['label'] }}</a>
                        @endif
                    @endif
                @endforeach
            </div>

            <!-- Mobile Toggle -->
            <button id="mobile-menu-toggle" class="lg:hidden inline-flex items-center justify-center rounded-full border border-[#1F5EDB] px-3 py-2 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10">
                <span class="sr-only">Abrir menu</span>
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Action Buttons -->
            <div class="hidden lg:flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full border border-[#1F5EDB] px-6 py-2 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10">
                        Entrar
                    </a>
                @else
                    <a href="#" class="inline-flex items-center gap-2 rounded-full border border-[#1F5EDB] px-6 py-2 text-sm font-bold text-[#1F5EDB] hover:bg-[#1F5EDB]/10">
                        Meu perfil
                    </a>
                @endguest
                <a href="{{ $cta['href'] }}" class="inline-flex items-center gap-2 rounded-full {{ $cta['class'] }} px-7 py-3 text-sm font-bold">
                    {{ $cta['label'] }}
                </a>
            </div>
        </div>
    </div>
</nav>

<div id="mobile-menu" class="fixed inset-0 z-40 hidden" aria-hidden="true">
    <div id="mobile-menu-overlay" class="absolute inset-0 bg-black/40 opacity-0 pointer-events-none transition-opacity duration-300"></div>
    <div id="mobile-menu-panel" class="relative z-10 w-4/5 max-w-sm h-full bg-white border-r border-white/80 shadow-2xl transform -translate-x-full transition-transform duration-400 ease-out overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="inline-flex h-12 w-auto items-center justify-center overflow-hidden">
                    <img src="{{ $logoSrc }}" alt="UNN" class="h-full w-auto object-contain" onerror="this.style.display='none';">
                </div>
            </div>
            <button id="mobile-menu-close" class="text-gray-500 hover:text-gray-900 text-3xl leading-none">&times;</button>
        </div>
        <nav class="px-6 py-4 flex flex-col gap-1 text-gray-700">
            @foreach($menuItems as $item)
                <div>
                    <a href="{{ $item['href'] }}" class="block rounded-2xl px-4 py-2 font-semibold hover:bg-slate-100 transition-colors">
                        {{ $item['label'] }}
                    </a>
                    @if(isset($item['children']))
                        <div class="pl-4 space-y-1">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['href'] }}" class="block rounded-xl px-3 py-1.5 text-sm text-gray-600 hover:bg-slate-100 transition-colors">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>
        <div class="px-6 mt-2 mb-6">
            <a href="{{ $cta['href'] }}" class="inline-flex w-full items-center justify-center rounded-full border border-[#1F5EDB] px-6 py-3 text-sm font-semibold text-[#1F5EDB] hover:bg-[#1F5EDB]/10 transition">
                {{ $cta['label'] }}
            </a>
        </div>
    </div>
</div>