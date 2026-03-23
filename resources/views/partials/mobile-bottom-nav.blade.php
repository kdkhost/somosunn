@php
    $user = auth()->user();
    if (!$user) return;

    $navItems = [
        [
            'label' => 'Início',
            'icon' => 'fas fa-th-large',
            'route' => route('panel.dashboard'),
            'active' => request()->routeIs('panel.dashboard'),
        ],
        [
            'label' => 'Social',
            'icon' => 'fas fa-users',
            'route' => route('social.feed'),
            'active' => request()->routeIs('social.feed') || request()->is('social*'),
        ],
        [
            'label' => 'Chat',
            'icon' => 'fas fa-comments',
            'route' => route('chat.index'),
            'active' => request()->routeIs('chat.*'),
        ],
        [
            'label' => 'Loja',
            'icon' => 'fas fa-store',
            'route' => route('marketplace.index'),
            'active' => request()->routeIs('marketplace.*'),
        ],
        [
            'label' => 'Perfil',
            'icon' => 'fas fa-user-circle',
            'route' => route('panel.profile.edit'),
            'active' => request()->routeIs('panel.profile.*'),
        ],
    ];
@endphp

<div class="lg:hidden fixed bottom-0 left-0 right-0 z-[100] px-4 pb-4 animate-fade-in-up">
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-2xl border border-slate-200/50 dark:border-slate-700/50 rounded-[2.5rem] shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.1)] dark:shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.4)] h-20 flex items-center justify-around relative overflow-hidden">
        
        <!-- Abstract background glows -->
        <div class="absolute -left-10 -bottom-10 w-32 h-32 bg-blue-500/10 dark:bg-blue-400/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-indigo-500/10 dark:bg-indigo-400/5 rounded-full blur-3xl pointer-events-none"></div>

        @foreach($navItems as $item)
            <a href="{{ $item['route'] }}" class="flex flex-col items-center justify-center gap-1.5 relative group min-w-[64px] transition-all duration-300">
                @if($item['active'])
                    <!-- Active Indicator Sphere -->
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 w-8 h-1 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full shadow-[0_4px_12px_rgba(59,130,246,0.5)]"></div>
                    <div class="absolute inset-0 bg-blue-500/5 dark:bg-blue-400/10 rounded-2xl scale-125 blur-sm -z-10 animate-pulse"></div>
                @endif

                <div class="relative">
                    <i class="{{ $item['icon'] }} text-xl transition-all duration-300 {{ $item['active'] ? 'text-blue-600 dark:text-blue-400 scale-110' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}"></i>
                    
                    @if($item['active'])
                        <div class="absolute -right-1 -top-1 w-2 h-2 bg-blue-500 rounded-full border-2 border-white dark:border-slate-900"></div>
                    @endif
                </div>

                <span class="text-[10px] font-black uppercase tracking-widest transition-all duration-300 {{ $item['active'] ? 'text-blue-700 dark:text-blue-300' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300' }}">
                    {{ $item['label'] }}
                </span>
            </a>
        @endforeach
    </div>
</div>

<style>
    /* Prevent content from being hidden behind the bottom nav */
    body {
        padding-bottom: 5rem !important;
    }
    
    @media (min-width: 1024px) {
        body {
            padding-bottom: 0 !important;
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
