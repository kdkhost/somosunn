@php
    $user = auth()->user();
    if (!$user) {
        return;
    }

    $navItems = [
        [
            'label' => 'Inicio',
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

<div class="mobile-app-nav lg:hidden fixed inset-x-0 bottom-0 z-[100] animate-fade-in-up">
    <nav class="mobile-app-nav__bar" aria-label="Navegacao rapida">
        @foreach ($navItems as $item)
            <a href="{{ $item['route'] }}"
                class="mobile-app-nav__item {{ $item['active'] ? 'is-active' : '' }}"
                @if ($item['active']) aria-current="page" @endif>
                <span class="mobile-app-nav__icon">
                    <i class="{{ $item['icon'] }}"></i>
                </span>
                <span class="mobile-app-nav__label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</div>

<style>
    :root {
        --mobile-app-nav-height: 78px;
        --mobile-app-nav-safe-area: env(safe-area-inset-bottom, 0px);
    }

    body {
        padding-bottom: calc(var(--mobile-app-nav-height) + var(--mobile-app-nav-safe-area)) !important;
    }

    .mobile-app-nav__bar {
        position: relative;
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        align-items: stretch;
        gap: 0.35rem;
        width: 100%;
        min-height: var(--mobile-app-nav-height);
        padding: 0.65rem 0.5rem calc(0.65rem + var(--mobile-app-nav-safe-area));
        background: rgba(255, 255, 255, 0.98);
        border-top: 1px solid rgba(148, 163, 184, 0.26);
        box-shadow: 0 -14px 34px rgba(15, 23, 42, 0.12);
    }

    .dark .mobile-app-nav__bar {
        background: rgba(2, 6, 23, 0.98);
        border-top-color: rgba(71, 85, 105, 0.55);
        box-shadow: 0 -14px 34px rgba(0, 0, 0, 0.45);
    }

    .mobile-app-nav__bar::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 3px;
        background: linear-gradient(90deg, #1f5edb 0%, #177fd6 50%, #1d3fc4 100%);
        opacity: 0.92;
    }

    .mobile-app-nav__item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.28rem;
        min-height: 56px;
        padding: 0.45rem 0.25rem;
        border-radius: 1rem;
        color: #64748b;
        transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .dark .mobile-app-nav__item {
        color: #94a3b8;
    }

    .mobile-app-nav__item:hover {
        background: rgba(37, 99, 235, 0.07);
        color: #1d4ed8;
    }

    .dark .mobile-app-nav__item:hover {
        background: rgba(59, 130, 246, 0.12);
        color: #dbeafe;
    }

    .mobile-app-nav__item.is-active {
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.14) 0%, rgba(37, 99, 235, 0.08) 100%);
        color: #1d4ed8;
        transform: translateY(-1px);
    }

    .dark .mobile-app-nav__item.is-active {
        background: linear-gradient(180deg, rgba(59, 130, 246, 0.22) 0%, rgba(59, 130, 246, 0.12) 100%);
        color: #eff6ff;
    }

    .mobile-app-nav__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 999px;
        font-size: 1.05rem;
        line-height: 1;
    }

    .mobile-app-nav__item.is-active .mobile-app-nav__icon {
        background: rgba(255, 255, 255, 0.88);
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.16);
    }

    .dark .mobile-app-nav__item.is-active .mobile-app-nav__icon {
        background: rgba(15, 23, 42, 0.9);
        box-shadow: 0 10px 18px rgba(0, 0, 0, 0.28);
    }

    .mobile-app-nav__label {
        font-size: 0.63rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        line-height: 1;
        text-align: center;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .site-back-to-top {
        bottom: calc(var(--mobile-app-nav-height) + var(--mobile-app-nav-safe-area) + 0.9rem);
    }

    @media (min-width: 1024px) {
        body {
            padding-bottom: 0 !important;
        }

        .site-back-to-top {
            bottom: 6rem;
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
