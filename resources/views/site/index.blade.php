@extends('layouts.app')

@section('title', $homePage->get('seo_title', 'UNN - Conectando Empreendedores'))

@section('content')
    @php
        // Helper: $homePage->get() tem prioridade; cai para SiteContent e Settings como legado
        $p = fn(string $key, string $fallback = '') => (string) ($homePage->get($key) ?: $fallback);

        $heroTitle = $p('hero_title',
            (string) (\App\Models\SiteContent::getValue('home', 'hero_title')
                ?: \App\Models\Setting::get('hero_title',
                    \App\Models\Setting::get('home_hero_title', 'Conectando empreendedores.')))
        );
        $heroSubtitle = $p('hero_subtitle',
            (string) (\App\Models\SiteContent::getValue('home', 'hero_subtitle')
                ?: \App\Models\Setting::get('hero_subtitle',
                    \App\Models\Setting::get('home_hero_subtitle', 'Criando oportunidades reais.')))
        );
        $heroText = $p('body',
            (string) (\App\Models\SiteContent::getValue('home', 'hero_text')
                ?: \App\Models\Setting::get('home_hero_text',
                    'A UNN é uma comunidade de networking estratégico onde empreendedores compartilham experiências, constroem conexões e crescem juntos.'))
        );

        $heroImagePath = $homePage->get('hero_image')
            ?: \App\Models\SiteContent::getValue('home', 'hero_image');
        $heroImageUrl = '';
        if ($heroImagePath) {
            $heroImageUrl = asset('storage/' . ltrim($heroImagePath, '/'));
        }
        if ($heroImageUrl === '') {
            $heroImageUrl = \App\Models\Setting::getUrl('hero_image');
        }
        if ($heroImageUrl === '') {
            $heroImageUrl = 'https://images.unsplash.com/photo-1552664730-d307ca884978?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=800';
        }

        $journeyCards = collect([
            [
                'icon' => 'fas fa-handshake',
                'title' => $p('journey_card_1_title', 'Parcerias que viram negócio'),
                'result' => $p('journey_card_1_result', 'Novos contratos e clientes recorrentes'),
                'text' => $p('journey_card_1_text', 'Conexões bem feitas encurtam o caminho entre uma conversa e uma oportunidade real de faturamento.'),
            ],
            [
                'icon' => 'fas fa-microphone-lines',
                'title' => $p('journey_card_2_title', 'Autoridade e visibilidade'),
                'result' => $p('journey_card_2_result', 'Convites para palestrar, ensinar e liderar'),
                'text' => $p('journey_card_2_text', 'O networking certo posiciona sua marca em novos palcos e acelera o reconhecimento do seu trabalho.'),
            ],
            [
                'icon' => 'fas fa-chart-line',
                'title' => $p('journey_card_3_title', 'Expansão com direção'),
                'result' => $p('journey_card_3_result', 'Crescimento em novos mercados e frentes'),
                'text' => $p('journey_card_3_text', 'Relacionamentos estratégicos ajudam a abrir portas, validar decisões e crescer com menos tentativa e erro.'),
            ],
        ]);
    @endphp

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
        <!-- Hero Section -->
        @if($homePage->get('hero_enabled', true))
        <section class="pt-10 md:pt-24 pb-12 md:pb-16 w-full overflow-x-hidden">
            <div class="unn-container">
                <div class="grid lg:grid-cols-2 gap-8 md:gap-12 lg:gap-16 items-center">
                    <div>
                        <h1 class="unn-title-gradient unn-title-hero mb-6" style="word-break: keep-all; hyphens: none; max-width: 650px;">
                            {!! nl2br(e(str_replace(',', ",\n", $heroTitle))) !!} {{ $heroSubtitle }}
                        </h1>
                        <p class="text-base sm:text-lg text-gray-600 mb-6 md:mb-8 leading-relaxed max-w-xl">
                            {{ $heroText }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                            <a href="{{ route('register') }}"
                                class="btn-primary text-white px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold text-base md:text-lg inline-flex items-center justify-center gap-2 shadow-lg hover:shadow-xl transition">
                                {{ $homePage->get('hero_cta_text', 'Quero fazer parte') }} <i class="fas fa-arrow-right"></i>
                            </a>
                            <a href="{{ route('sobre') }}"
                                class="bg-white text-gray-700 px-6 py-3 md:px-10 md:py-4 rounded-xl font-bold border-2 border-gray-200 hover:border-blue-500 transition inline-flex items-center justify-center gap-2 text-base md:text-lg">
                                <i class="fas fa-play-circle"></i> {{ $homePage->get('hero_cta2_text', 'Conhecer a UNN') }}
                            </a>
                        </div>
                    </div>

                    <div class="hidden lg:block">
                        <div class="relative">
                            <div class="absolute inset-0 btn-primary rounded-3xl opacity-20 blur-3xl"></div>
                            <img src="{{ $heroImageUrl }}"
                                alt="Networking" class="relative w-full rounded-3xl shadow-2xl">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Stats Bar -->
        <!-- Stats Bar -->
        @if($homePage->get('stats_enabled', true))
        <section class="py-6 md:py-8 px-4 md:px-8 lg:px-12 w-full overflow-x-hidden">
            <div class="unn-container">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">
                            {{ $homePage->get('stat_1_value', '5.000+') }}</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ $homePage->get('stat_1_label', 'Empreendedores') }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">
                            {{ $homePage->get('stat_2_value', 'R$ 50M+') }}</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ $homePage->get('stat_2_label', 'Em negócios gerados') }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">
                            {{ $homePage->get('stat_3_value', '200+') }}</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ $homePage->get('stat_3_label', 'Eventos realizados') }}</p>
                    </div>
                    <div class="bg-white rounded-2xl p-4 md:p-6 text-center shadow-lg">
                        <p class="text-2xl sm:text-3xl md:text-4xl font-black truncate" style="color: var(--unn-azul-1)">
                            {{ $homePage->get('stat_4_value', '27') }}</p>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">{{ $homePage->get('stat_4_label', 'Estados') }}</p>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- O que é a UNN -->
        @if($homePage->get('about_enabled', true))
        <section class="py-16 px-4 md:px-8 lg:px-12 bg-white w-full overflow-x-hidden">
            <div class="unn-container">
                <div class="text-center mb-12">
                    <h2 class="text-4xl font-black unn-title-gradient mb-4">{{ $homePage->get('about_title', 'O que é a UNN') }}</h2>
                    <p class="text-gray-600 text-lg max-w-2xl mx-auto">{{ $homePage->get('about_subtitle', 'A UNN nasceu para unir empreendedores que acreditam no crescimento colaborativo.') }}</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-handshake text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">{{ $homePage->get('about_card_1_title', 'Conexões reais') }}</h3>
                        <p class="text-sm text-gray-600">{{ $homePage->get('about_card_1_text', 'Networking genuíno com empreendedores que compartilham seus valores') }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-line text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">{{ $homePage->get('about_card_2_title', 'Crescimento coletivo') }}</h3>
                        <p class="text-sm text-gray-600">{{ $homePage->get('about_card_2_text', 'Juntos somos mais fortes e alcançamos resultados maiores') }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-lightbulb text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">{{ $homePage->get('about_card_3_title', 'Troca de experiências') }}</h3>
                        <p class="text-sm text-gray-600">{{ $homePage->get('about_card_3_text', 'Aprenda com quem já passou pelos desafios que você enfrenta') }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-3xl p-8 text-center">
                        <div class="w-16 h-16 btn-primary rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-briefcase text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold unn-title-gradient mb-2">{{ $homePage->get('about_card_4_title', 'Oportunidades') }}</h3>
                        <p class="text-sm text-gray-600">{{ $homePage->get('about_card_4_text', 'Parcerias estratégicas que geram resultados concretos') }}</p>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Onde o network me levou -->
        @if($homePage->get('journey_enabled', true))
        <section class="py-16 w-full overflow-x-hidden">
            <div class="unn-container">
                <!-- Lead Banner (Blue) -->
                <div class="home-journey-lead rounded-[2rem] md:rounded-[3rem] px-6 py-10 md:p-12 lg:p-16 text-white overflow-hidden relative mb-12">
                    <div class="relative z-10 grid lg:grid-cols-[1fr_auto] gap-8 md:gap-10 items-center">
                        <div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/20 px-4 py-2 text-[10px] font-black tracking-[0.2em] uppercase text-white/90 backdrop-blur-md mb-6">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                                Resultados reais
                            </span>
                            <h2 class="text-2xl md:text-5xl lg:text-6xl font-black leading-[1.1] tracking-tight mb-4 md:mb-6">
                                {{ $homePage->get('journey_title', 'Onde o network me levou') }}
                            </h2>
                            <p class="text-lg md:text-xl text-white/70 leading-relaxed font-medium max-w-2xl">
                                {{ $homePage->get('journey_subtitle', 'Conexões que começaram em uma conversa e terminaram em contratos, convites, expansão e crescimento com direção.') }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-4 md:gap-6 min-w-0 md:min-w-[300px]">
                            <div class="rounded-2xl md:rounded-[2rem] bg-white/5 border border-white/10 p-5 md:p-6 backdrop-blur-xl">
                                <p class="text-[10px] uppercase tracking-[0.25em] text-white/40 font-black mb-3">
                                    {{ $homePage->get('journey_highlight_label', 'Aceleração de Resultados') }}
                                </p>
                                <p class="text-base md:text-xl font-black text-white leading-tight">
                                    {{ $homePage->get('journey_highlight_value', 'Mais visibilidade, novos negócios e acesso exclusivo') }}
                                </p>
                            </div>
                            <a href="{{ route('gallery.index') }}"
                                class="inline-flex items-center justify-center gap-3 rounded-full bg-white px-8 py-4 md:py-5 text-sm md:text-base font-black transition-all duration-300 hover:scale-105 hover:shadow-[0_20px_40px_-10px_rgba(255,255,255,0.3)] group"
                                style="color: var(--unn-azul-1);">
                                {{ $homePage->get('journey_cta_text', 'Ver fotos da comunidade') }}
                                <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Cards Row (White) -->
                <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($journeyCards as $card)
                        <article class="home-journey-card flex flex-col justify-between rounded-[2.5rem] border border-slate-100 bg-white p-6 md:p-8 lg:p-10 h-full group relative overflow-hidden transition-all duration-500">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-bl-[5rem] -mr-16 -mt-16 transition-all duration-500 group-hover:scale-150 group-hover:bg-blue-100/50"></div>
                            
                            <div class="relative z-10">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 mb-8 transition-all duration-500 group-hover:bg-blue-600 group-hover:text-white group-hover:rotate-6 group-hover:shadow-lg group-hover:shadow-blue-200">
                                    <i class="{{ $card['icon'] }} text-xl"></i>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400 mb-3">
                                    {{ $card['title'] }}
                                </p>
                                <h3 class="text-xl lg:text-2xl font-black leading-tight text-slate-900 group-hover:text-blue-700 transition-colors duration-300">
                                    {{ $card['result'] }}
                                </h3>
                            </div>

                            <div class="mt-6 relative z-10">
                                <p class="text-sm lg:text-base text-slate-500 leading-relaxed font-medium">
                                    {{ $card['text'] }}
                                </p>
                                <div class="mt-6 flex items-center gap-2 text-blue-600 font-bold text-xs opacity-0 -translate-x-2 transition-all duration-500 group-hover:opacity-100 group-hover:translate-x-0">
                                    Saiba mais <i class="fas fa-arrow-right text-[10px]"></i>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- Palestras Gratuitas -->
        @if($homePage->get('events_enabled', true))
        <section class="py-16 w-full overflow-x-hidden">
            <div class="unn-container">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900">{{ $homePage->get('events_title', 'Palestras gratuitas') }}</h2>
                        <p class="text-gray-500">{{ $homePage->get('events_subtitle', 'Eventos que chegam em breve') }}</p>
                    </div>
                    @if(isset($isDemo) && $isDemo)
                        <span class="text-sm text-yellow-600 bg-yellow-50 px-3 py-1 rounded-full font-semibold">
                            <i class="fas fa-info-circle mr-1"></i> Dados Demo
                        </span>
                    @endif
                </div>

                <div class="grid auto-rows-fr md:grid-cols-3 gap-8">
                    @foreach($freeEvents as $event)
                        @php
                            $isEventClosed = !($event->is_demo ?? false)
                                && method_exists($event, 'isClosedForPublic')
                                && $event->isClosedForPublic();
                        @endphp
                        <article
                            class="home-selling-card bg-white rounded-3xl p-8 shadow-lg border border-blue-50 hover:shadow-xl transition flex h-full flex-col {{ ($event->is_demo ?? false) ? 'ring-2 ring-yellow-400' : '' }}">
                            <div class="home-selling-body">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-4"
                                    style="background: var(--unn-azul-1); color: white">
                                    GRATUITA
                                </span>
                                <h3 class="home-selling-title text-xl font-bold unn-title-gradient mb-3 line-clamp-2">{{ $event->title }}</h3>
                                <p class="home-selling-copy text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit(strip_tags((string) ($event->description ?? '')), 100) }}</p>
                                <div class="home-selling-meta flex items-center gap-4 text-sm text-gray-500 mb-3">
                                    <span><i class="fas fa-calendar mr-1"></i>
                                        {{ \Carbon\Carbon::parse($event->start_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="home-selling-meta flex items-center gap-2 text-sm text-gray-500 mb-6">
                                    <i class="fas fa-map-marker-alt"></i> {{ $event->location }}
                                </div>
                            </div>
                            <div class="home-selling-footer mt-auto pt-4 border-t border-blue-50">
                                @if($event->is_demo ?? false)
                                    <button onclick="Swal.fire({
                                        title: 'Evento Demo',
                                        text: 'Este é um evento de demonstração.',
                                        icon: 'info',
                                        confirmButtonColor: '#1F5EDB'
                                    })" class="home-selling-action w-full btn-primary text-white py-3 rounded-xl font-semibold opacity-75">
                                    Quero participar
                                </button>
                                @elseif($isEventClosed)
                                    <span
                                        class="home-selling-action block w-full bg-slate-100 text-slate-400 py-3 rounded-xl font-semibold text-center cursor-not-allowed border border-slate-200">
                                        Evento encerrado
                                    </span>
                                @else
                                    <a href="{{ route('events.show', $event->id) }}"
                                        class="home-selling-action block w-full btn-primary text-white py-3 rounded-xl font-semibold text-center">
                                    Quero participar
                                </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="text-center mt-8">
                    <a href="{{ route('events.index') }}"
                        class="inline-flex items-center gap-2 font-semibold hover:underline"
                        style="color: var(--unn-azul-1)">
                        Ver todos os eventos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>
        @endif

        <!-- Mentorias Premium -->
        @if($homePage->get('mentorships_enabled', true))
        <section class="py-16 bg-white w-full overflow-x-hidden">
            <div class="unn-container">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900">{{ $homePage->get('mentorships_title', 'Mentorias premium') }}</h2>
                        <p class="text-gray-500">{{ $homePage->get('mentorships_subtitle', 'Conteúdo gravado + acompanhamento de mentores') }}</p>
                    </div>
                    <a href="{{ route('mentorships.index') }}"
                        class="hidden md:inline-flex items-center gap-2 font-semibold" style="color: var(--unn-azul-1)">
                        Ver todas as mentorias <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="grid auto-rows-fr md:grid-cols-3 gap-8">
                    @foreach($paidMentorings as $mentorship)
                        @php
                            $isMentorshipClosed = !($mentorship->is_demo ?? false)
                                && method_exists($mentorship, 'isClosedForPublic')
                                && $mentorship->isClosedForPublic();
                        @endphp
                        <article
                            class="home-selling-card bg-slate-50 rounded-3xl p-8 border border-blue-100 shadow-sm h-full flex flex-col transition-all duration-300 hover:shadow-lg hover:-translate-y-1 {{ ($mentorship->is_demo ?? false) ? 'ring-2 ring-yellow-400' : '' }}">
                            <div class="home-selling-header flex items-center justify-between gap-4 mb-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-11 h-11 rounded-full bg-white border border-blue-100 flex items-center justify-center text-blue-600 font-bold overflow-hidden shrink-0">
                                        @if(optional($mentorship->mentor)->profile_photo_url)
                                            <img src="{{ $mentorship->mentor->profile_photo_url }}"
                                                alt="{{ optional($mentorship->mentor)->name ?? 'Mentor UNN' }}"
                                                class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr(optional($mentorship->mentor)->name ?? 'M', 0, 1)) }}
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs uppercase tracking-wide text-gray-500 font-semibold line-clamp-2">{{ optional($mentorship->mentor)->name ?? 'Mentor UNN' }}</span>
                                </div>
                                <span class="shrink-0 text-lg font-black px-4 py-2 rounded-2xl bg-white shadow-sm"
                                    style="color: var(--unn-azul-1)">R$
                                    {{ number_format($mentorship->price, 2, ',', '.') }}</span>
                            </div>
                            <h3 class="home-selling-title text-xl font-black text-gray-900 mb-3 line-clamp-3">{{ $mentorship->title }}</h3>
                            <p class="home-selling-copy text-gray-600 text-sm mb-5 line-clamp-3">{{ Str::limit(strip_tags((string) ($mentorship->description ?? '')), 110) }}</p>
                            <div class="home-selling-footer mt-auto pt-4 border-t border-blue-100">
                                <p class="home-selling-meta text-sm text-gray-500 mb-4"><i class="fas fa-users mr-1 text-blue-400"></i> Vagas:
                                    <strong>{{ $mentorship->slots }}</strong></p>
                            @if(!($mentorship->is_demo ?? false) && isset($mentorship->id) && !$isMentorshipClosed)
                                <a href="{{ route('mentorships.show', $mentorship->id) }}"
                                    class="home-selling-action w-full btn-primary text-white py-3 rounded-xl font-semibold inline-flex items-center justify-center">
                                    Garantir vaga
                                </a>
                            @elseif($isMentorshipClosed)
                                <span
                                    class="home-selling-action w-full bg-slate-200 text-slate-500 py-3 rounded-xl font-semibold inline-flex items-center justify-center cursor-not-allowed border border-slate-300">
                                    Mentoria encerrada
                                </span>
                            @else
                                <button onclick="Swal.fire({
                                    title: 'Mentoria Demo',
                                    text: 'Esta é uma mentoria de demonstração.',
                                        icon: 'info',
                                        confirmButtonColor: '#1F5EDB'
                                })" class="home-selling-action w-full btn-primary text-white py-3 rounded-xl font-semibold opacity-75">
                                    Garantir vaga (Demo)
                                </button>
                            @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- Comunidade por níveis -->
        @if($homePage->get('community_enabled', true))
        <section class="py-16 w-full overflow-x-hidden">
            <div class="unn-container">
                <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">{{ $homePage->get('community_title', 'Comunidade por níveis') }}</h2>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                            style="background: #3B82F620">
                            <i class="fas fa-seedling text-2xl" style="color: #3B82F6"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-500 uppercase mb-2">{{ $homePage->get('community_beginner_title', 'Empreendedores iniciantes') }}</p>
                        <p class="text-5xl font-black" style="color: var(--unn-azul-1)">
                            {{ number_format($levelSummary['iniciante'] ?? 0, 0, '', '.') }}</p>
                        <p class="text-gray-500 mt-3">{{ $homePage->get('community_beginner_desc', 'Conectados entre si e acolhidos por quem já percorreu a jornada.') }}</p>
                    </div>
                    <div class="bg-white rounded-3xl p-8 shadow-lg text-center">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                            style="background: #8B5CF620">
                            <i class="fas fa-crown text-2xl" style="color: #8B5CF6"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-500 uppercase mb-2">{{ $homePage->get('community_success_title', 'Empresários de sucesso') }}</p>
                        <p class="text-5xl font-black" style="color: #8B5CF6">
                            {{ number_format($levelSummary['sucesso'] ?? 0, 0, '', '.') }}</p>
                        <p class="text-gray-500 mt-3">{{ $homePage->get('community_success_desc', 'Mentores ativos, parceiros e investidores prontos para novas oportunidades.') }}</p>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Ranking -->
        @if($homePage->get('ranking_enabled', true))
        <section class="py-16 bg-white w-full overflow-x-hidden">
            <div class="unn-container">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900">{{ $homePage->get('ranking_title', 'Ranking do networking') }}</h2>
                        <p class="text-gray-500">{{ $homePage->get('ranking_subtitle', 'Baseado nas avaliações após cada conexão') }}</p>
                    </div>
                    @if($topRankings->isNotEmpty())
                        <span class="text-sm uppercase tracking-wider text-gray-500">Top {{ $topRankings->count() }}</span>
                    @endif
                </div>

                @if($topRankings->isEmpty())
                    <div class="text-center py-16 text-gray-400">
                        <i class="fas fa-trophy text-5xl mb-4 opacity-30"></i>
                        <p class="text-lg">Nenhum ranking disponível ainda.</p>
                        <p class="text-sm mt-2">Participe de conexões e avaliações para aparecer aqui!</p>
                    </div>
                @else
                    @php
                        $medals = [
                            0 => ['bg' => 'from-yellow-400 to-amber-500',  'ring' => 'ring-yellow-400',  'label' => '🥇 1º lugar',  'icon' => 'fas fa-crown',   'iconColor' => 'text-yellow-400'],
                            1 => ['bg' => 'from-slate-300 to-slate-400',   'ring' => 'ring-slate-300',   'label' => '🥈 2º lugar',  'icon' => 'fas fa-medal',   'iconColor' => 'text-slate-400'],
                            2 => ['bg' => 'from-orange-400 to-amber-600',  'ring' => 'ring-orange-400',  'label' => '🥉 3º lugar',  'icon' => 'fas fa-medal',   'iconColor' => 'text-orange-400'],
                        ];
                    @endphp

                    <div class="grid lg:grid-cols-3 gap-6 items-end">
                        @foreach($topRankings as $rank)
                            @php
                                $pos = $loop->index;
                                $med = $medals[$pos] ?? $medals[2];
                                $userName = optional($rank->user)->name ?? 'Empreendedor';
                                $userAvatar = optional($rank->user)->profile_photo_url ?? null;
                                $isFirst = $pos === 0;
                            @endphp

                            {{-- 1º lugar: card destacado com ouro --}}
                            @if($isFirst)
                                <article class="lg:col-start-2 order-first lg:order-none bg-gradient-to-b from-amber-50 to-white rounded-3xl p-8 shadow-xl ring-2 ring-yellow-400 relative overflow-hidden flex flex-col items-center text-center">
                                    {{-- Brilho decorativo --}}
                                    <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full opacity-20" style="background: radial-gradient(circle, #fbbf24, transparent)"></div>

                                    {{-- Corona --}}
                                    <div class="mb-3">
                                        <i class="fas fa-crown text-3xl text-yellow-400" style="filter: drop-shadow(0 2px 4px rgba(251,191,36,.5))"></i>
                                    </div>

                                    {{-- Avatar --}}
                                    @if($userAvatar)
                                        <img src="{{ $userAvatar }}" alt="{{ $userName }}"
                                            class="w-20 h-20 rounded-full object-cover ring-4 ring-yellow-300 shadow-lg mb-4">
                                    @else
                                        <div class="w-20 h-20 rounded-full flex items-center justify-center text-white font-black text-3xl ring-4 ring-yellow-300 shadow-lg mb-4"
                                            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                            {{ strtoupper(substr($userName, 0, 1)) }}
                                        </div>
                                    @endif

                                    <h3 class="font-black text-xl text-gray-900">{{ $userName }}</h3>
                                    <p class="text-sm text-amber-600 font-semibold mt-1">{{ ucfirst($rank->level) }}</p>

                                    <div class="mt-4 pt-4 border-t border-yellow-200 w-full">
                                        <p class="text-3xl font-black" style="color: var(--unn-azul-1)">
                                            {{ number_format($rank->score, 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs uppercase tracking-widest text-gray-500 mt-1">pontos</p>
                                    </div>

                                    @if($rank->interactions_count > 0)
                                        <p class="text-xs text-gray-400 mt-3">
                                            {{ $rank->interactions_count }} conexões
                                            @if($rank->average_rating)
                                                · {{ number_format($rank->average_rating, 1, ',', '.') }}
                                                <i class="fas fa-star text-yellow-400"></i>
                                            @endif
                                        </p>
                                    @endif

                                    <span class="absolute top-3 left-3 bg-yellow-400 text-white text-xs font-bold px-2 py-1 rounded-full">1º</span>
                                </article>

                            {{-- 2º e 3º lugar: cards menores --}}
                            @else
                                @php $orderClass = $pos === 1 ? 'lg:order-first' : 'lg:order-last'; @endphp
                                <article class="{{ $orderClass }} bg-slate-50 rounded-3xl p-6 hover:shadow-md transition relative overflow-hidden flex flex-col items-center text-center">

                                    {{-- Número da posição --}}
                                    <span class="absolute top-3 left-3 bg-{{ $pos === 1 ? 'slate-400' : 'orange-400' }} text-white text-xs font-bold px-2 py-1 rounded-full">{{ $pos + 1 }}º</span>

                                    {{-- Ícone medalha --}}
                                    <div class="mb-3 mt-2">
                                        <i class="{{ $med['icon'] }} text-xl {{ $med['iconColor'] }}"></i>
                                    </div>

                                    {{-- Avatar --}}
                                    @if($userAvatar)
                                        <img src="{{ $userAvatar }}" alt="{{ $userName }}"
                                            class="w-14 h-14 rounded-full object-cover ring-2 ring-{{ $pos === 1 ? 'slate-300' : 'orange-300' }} mb-3">
                                    @else
                                        <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-xl ring-2 ring-{{ $pos === 1 ? 'slate-300' : 'orange-300' }} mb-3"
                                            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
                                            {{ strtoupper(substr($userName, 0, 1)) }}
                                        </div>
                                    @endif

                                    <h3 class="font-bold text-gray-900">{{ $userName }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ ucfirst($rank->level) }}</p>

                                    <div class="mt-3 pt-3 border-t border-slate-200 w-full">
                                        <p class="text-2xl font-black" style="color: var(--unn-azul-1)">
                                            {{ number_format($rank->score, 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-gray-400">pontos</p>
                                    </div>

                                    @if($rank->interactions_count > 0)
                                        <p class="text-xs text-gray-400 mt-2">
                                            {{ $rank->interactions_count }} conexões
                                            @if($rank->average_rating)
                                                · {{ number_format($rank->average_rating, 1, ',', '.') }}
                                                <i class="fas fa-star text-yellow-400"></i>
                                            @endif
                                        </p>
                                    @endif
                                </article>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
        @endif

        <!-- Depoimentos -->
        @if($homePage->get('testimonials_enabled', true))
        <section class="py-16 w-full overflow-x-hidden">
            <div class="unn-container">
                <h2 class="text-3xl font-black text-gray-900 mb-8 text-center">{{ $homePage->get('testimonials_title', 'O que dizem nossos membros') }}</h2>

                @php
                    // Usa depoimentos do banco; cai para fallback estático somente se vazio
                    $siteTestimonials = ($dbTestimonials ?? collect())->isNotEmpty()
                        ? ($dbTestimonials ?? collect())
                        : collect([
                            (object)['display_name'=>'Carlos Eduardo','author_title'=>'CEO, Tech Solutions','content'=>'A UNN transformou minha forma de fazer negócios. Em 6 meses, fechei parcerias que mudaram minha empresa.','rating'=>5,'resolved_avatar'=>null],
                            (object)['display_name'=>'Ana Paula Lima','author_title'=>'Fundadora, EcoModa','content'=>'O networking aqui é diferente. São conexões genuínas com pessoas que realmente querem ajudar.','rating'=>5,'resolved_avatar'=>null],
                            (object)['display_name'=>'Roberto Silva','author_title'=>'Investidor Anjo','content'=>'Encontrei projetos incríveis para investir e empreendedores talentosos. A comunidade é de altíssimo nível.','rating'=>5,'resolved_avatar'=>null],
                        ]);
                @endphp

                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($siteTestimonials as $testimonial)
                        @php
                            $tName   = method_exists($testimonial,'offsetGet') ? ($testimonial['name'] ?? $testimonial->display_name ?? 'Anônimo') : ($testimonial->display_name ?? 'Anônimo');
                            $tRole   = method_exists($testimonial,'offsetGet') ? ($testimonial['role'] ?? '') : ($testimonial->author_title ?? '');
                            $tText   = method_exists($testimonial,'offsetGet') ? ($testimonial['text'] ?? '') : ($testimonial->content ?? '');
                            $tRating = method_exists($testimonial,'offsetGet') ? ($testimonial['rating'] ?? 5) : ($testimonial->rating ?? 5);
                            $tAvatar = is_object($testimonial) && isset($testimonial->resolved_avatar) ? $testimonial->resolved_avatar : null;
                        @endphp
                        <div class="bg-white rounded-3xl p-8 shadow-lg">
                            <div class="flex gap-1 mb-4">
                                @for($i = 0; $i < $tRating; $i++)
                                    <i class="fas fa-star text-yellow-500"></i>
                                @endfor
                            </div>
                            <p class="text-gray-600 mb-6 italic">"{{ $tText }}"</p>
                            <div class="flex items-center gap-4">
                                @if($tAvatar)
                                    <img src="{{ $tAvatar }}" alt="{{ $tName }}"
                                        class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                                @else
                                    <div class="w-12 h-12 btn-primary rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                        {{ strtoupper(substr($tName, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-bold text-gray-900">{{ $tName }}</p>
                                    <p class="text-sm text-gray-500">{{ $tRole }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- CTA Final -->
        @if($homePage->get('cta_enabled', true))
        <section class="py-16 w-full overflow-x-hidden"
            style="background: linear-gradient(135deg, var(--unn-azul-1), var(--unn-azul-3))">
            <div class="unn-container text-center text-white">
                <h2 class="text-3xl lg:text-4xl font-black mb-4">{{ $homePage->get('cta_section_title', 'Pronto para transformar sua rede?') }}</h2>
                <p class="text-lg opacity-90 mb-8">{{ $homePage->get('cta_section_subtitle', 'Junte-se a milhares de empreendedores que já estão crescendo juntos.') }}</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white px-6 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-blue-50 transition"
                        style="color: var(--unn-azul-1)">
                        <i class="fas fa-rocket"></i>
                        {{ $homePage->get('cta_section_btn_primary', 'Começar agora - É grátis') }}
                    </a>
                    <a href="{{ route('planos') }}"
                        class="inline-flex items-center justify-center gap-2 border-2 border-white text-white px-6 py-3 sm:px-8 sm:py-4 rounded-full font-bold hover:bg-white/10 transition">
                        {{ $homePage->get('cta_section_btn_secondary', 'Ver planos Premium') }}
                    </a>
                </div>
            </div>
        </section>
        @endif
    </div>

    <style>
        .text-gradient {
            background: linear-gradient(135deg, var(--unn-azul-1) 0%, var(--unn-azul-3) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .unn-title-gradient {
            background: linear-gradient(90deg, #2E3192 0%, #0071BC 60%, #29ABE2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }
        .unn-title-hero {
            max-width: 650px;
            font-size: 2.1rem;
            line-height: 1.12;
            font-weight: 900;
            letter-spacing: -0.02em;
            margin-left: 0;
            margin-right: 0;
            overflow-wrap: normal;
            word-break: keep-all;
            hyphens: none;
        }
        @media (min-width: 640px) {
            .unn-title-hero {
                font-size: 2.4rem;
            }
        }
        @media (min-width: 1024px) {
            .unn-title-hero {
                font-size: 2.8rem;
                max-width: 700px;
            }
        }
        @media (max-width: 480px) {
            .unn-title-hero {
                font-size: 1.5rem;
                max-width: 98vw;
            }
        }

        .home-selling-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
        }

        .home-selling-header {
            min-height: 4rem;
            align-items: center;
        }

        .home-selling-title {
            min-height: 5.6rem;
        }

        .home-selling-copy {
            min-height: 5.4rem;
        }

        .home-selling-meta {
            min-height: 1.5rem;
        }

        .home-selling-footer {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .home-selling-action {
            min-height: 3.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .home-journey-lead {
            background: 
                radial-gradient(circle at 100% 0%, rgba(255, 255, 255, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 0% 100%, rgba(0, 0, 0, 0.1) 0%, transparent 40%),
                linear-gradient(135deg, #1A237E 0%, #1F5EDB 50%, #00B0FF 100%);
            box-shadow: 0 40px 80px -20px rgba(31, 94, 219, 0.4);
            position: relative;
        }

        .home-journey-lead::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4v-4H4v4H0v2h4v4h2v-4h4v-2H6zm30 0v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.5;
            pointer-events: none;
        }

        .home-journey-card {
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
            border-color: #f1f5f9;
        }

        .home-journey-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -15px rgba(31, 94, 219, 0.15);
            border-color: #e2e8f0;
            background: #ffffff;
        }

        @media (max-width: 767.98px) {
            .home-selling-header,
            .home-selling-title,
            .home-selling-copy,
            .home-journey-card h3 {
                min-height: auto;
            }
        }
    </style>
@endsection
