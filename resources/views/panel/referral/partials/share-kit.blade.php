@php
    $affiliateShareKit = $affiliateShareKit ?? [];
    $referral = $affiliateShareKit['referral'] ?? [];
    $branding = $affiliateShareKit['branding'] ?? [];
    $materials = $affiliateShareKit['materials'] ?? [];
    $offers = $affiliateShareKit['offers'] ?? [];
    $landingPage = $affiliateShareKit['landing_page'] ?? [];
    $graphicAssets = $affiliateShareKit['graphic_assets'] ?? [];
    $embedWidgets = $affiliateShareKit['embed_widgets'] ?? [];
    $featuredOffers = $landingPage['featured_offers'] ?? [];
@endphp

<section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                <i class="fas fa-bullhorn text-[10px]"></i>
                Kit de Divulgação
            </div>
            <h2 class="mt-3 text-lg font-black text-slate-900 dark:text-white">Central de Materiais e Criativos</h2>
            <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                Textos prontos para redes sociais, criativos visuais em formatos padrão e widgets em HTML/iframe para alavancar suas conversões.
            </p>
        </div>
        <div class="grid gap-2 sm:grid-cols-3">
            <a href="#affiliateCopySection" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                <i class="fas fa-comment-alt"></i>
                Textos
            </a>
            <a href="#affiliateGraphicAssetsSection" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                <i class="fas fa-image"></i>
                Criativos
            </a>
            <a href="#affiliateEmbedWidgetsSection" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                <i class="fas fa-code"></i>
                Embed / iframe
            </a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.12fr,0.88fr]">
        <div id="affiliateCopySection" class="space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Textos e abordagens prontas</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Copie o texto ideal para WhatsApp, LinkedIn, e-mail ou distribuição manual. O link já vai com seu código de afiliado.
                        </p>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-300">
                        <i class="fas fa-copy text-[10px]"></i>
                        Uso rápido
                    </div>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach($materials as $material)
                        @php
                            $channelLabel = match($material['channel'] ?? 'copy') {
                                'whatsapp' => 'WhatsApp',
                                'linkedin' => 'LinkedIn',
                                'email' => 'E-mail',
                                default => 'Cópia rápida',
                            };
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ $channelLabel }}</p>
                                    <h4 class="mt-1 font-black text-slate-900 dark:text-white">{{ $material['title'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $material['description'] }}</p>
                                </div>
                                <button type="button"
                                    onclick="copyReferralMaterial(this)"
                                    data-copy-text="{{ e(($material['subject'] ?? '') !== '' ? ($material['subject'] . "\n\n" . $material['text']) : $material['text']) }}"
                                    data-track-channel="{{ $material['channel'] ?? 'copy' }}"
                                    data-target-url="{{ $material['target_url'] ?? ($referral['register_url'] ?? '') }}"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                                    <i class="fas fa-copy"></i>
                                    Copiar texto
                                </button>
                            </div>

                            @if(!empty($material['subject']))
                                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Assunto</p>
                                    <p class="mt-2 font-semibold text-slate-800 dark:text-slate-100">{{ $material['subject'] }}</p>
                                </div>
                            @endif

                            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
                                <p class="whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $material['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Ofertas e landing prontas</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Use em páginas próprias, anúncios, blog posts e páginas de captura.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        {{ count($featuredOffers) }} itens
                    </span>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-[0.92fr,1.08fr]">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                        @if(!empty($branding['hero_image_url']))
                            <img src="{{ $branding['hero_image_url'] }}" alt="Hero de divulgação" class="h-44 w-full object-cover" loading="lazy" decoding="async">
                        @endif
                        <div class="space-y-4 p-5">
                            <div class="flex items-center gap-3">
                                @if(!empty($branding['logo_url']))
                                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] ?? 'Marca' }}" class="h-12 w-auto rounded-xl bg-white p-2" loading="lazy" decoding="async">
                                @endif
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Landing oficial</p>
                                    <h4 class="text-base font-black text-slate-900 dark:text-white">{{ $landingPage['hero']['title'] ?? ($branding['hero_title'] ?? 'Página oficial de afiliado') }}</h4>
                                </div>
                            </div>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $landingPage['hero']['subtitle'] ?? ($branding['hero_subtitle'] ?? '') }}</p>
                            <ul class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                                @foreach(array_slice($landingPage['benefits'] ?? [], 0, 4) as $benefit)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle mt-1 text-emerald-500"></i>
                                        <span>{{ $benefit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <a href="{{ $landingPage['hero']['cta_url'] ?? ($referral['register_url'] ?? '#') }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                                {{ $landingPage['hero']['cta_label'] ?? 'Quero entrar agora' }}
                                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                            </a>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse($featuredOffers as $offer)
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ strtoupper($offer['type'] ?? 'offer') }}</p>
                                        <h4 class="mt-1 font-black text-slate-900 dark:text-white">{{ $offer['title'] }}</h4>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $offer['subtitle'] }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                                        {{ $offer['price_label'] }}
                                    </span>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ $offer['affiliate_url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-blue-700">
                                        <i class="fas fa-link"></i>
                                        Abrir link
                                    </a>
                                    <button type="button"
                                        onclick="copyReferralMaterial(this)"
                                        data-copy-text="{{ e($offer['affiliate_url']) }}"
                                        data-track-channel="offer-link"
                                        data-target-url="{{ $offer['affiliate_url'] }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                        <i class="fas fa-copy"></i>
                                        Copiar URL
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                                Ainda não há ofertas públicas suficientes para montar materiais externos.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div id="affiliateGraphicAssetsSection" class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Criativos prontos para publicar</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tamanhos específicos e responsivos para blog, banner, anúncio ou outro sistema.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                        {{ count($graphicAssets) }} formatos
                    </span>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach($graphicAssets as $asset)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="grid gap-4 xl:grid-cols-[220px,1fr]">
                                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-950">
                                    <img src="{{ $asset['image_url'] }}" alt="{{ $asset['title'] }}" class="h-36 w-full object-cover" loading="lazy" decoding="async">
                                </div>
                                <div class="space-y-3">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <h4 class="font-black text-slate-900 dark:text-white">{{ $asset['title'] }}</h4>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $asset['width'] }} × {{ $asset['height'] }} px · Pronto para publicar onde quiser.</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ $asset['download_url'] }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-blue-700">
                                                <i class="fas fa-download"></i>
                                                Abrir imagem
                                            </a>
                                            <button type="button"
                                                onclick="copyReferralMaterial(this)"
                                                data-copy-text="{{ e($asset['html_snippet']) }}"
                                                data-track-channel="graphic-html"
                                                data-target-url="{{ $asset['image_url'] }}"
                                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                                <i class="fas fa-code"></i>
                                                Copiar HTML
                                            </button>
                                            <button type="button"
                                                onclick="copyReferralMaterial(this)"
                                                data-copy-text="{{ e($asset['markdown_snippet']) }}"
                                                data-track-channel="graphic-markdown"
                                                data-target-url="{{ $asset['image_url'] }}"
                                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                                <i class="fab fa-markdown"></i>
                                                Copiar Markdown
                                            </button>
                                        </div>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">HTML pronto</p>
                                        <pre class="mt-3 overflow-x-auto whitespace-pre-wrap rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">{{ $asset['html_snippet'] }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="affiliateEmbedWidgetsSection" class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Embeds e widgets para outros sistemas</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Copie o iframe ou o HTML responsivo e publique em qualquer site externo.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-cyan-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">
                        {{ count($embedWidgets) }} widgets
                    </span>
                </div>

                <div class="mt-4 space-y-4">
                    @foreach($embedWidgets as $widget)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h4 class="font-black text-slate-900 dark:text-white">{{ $widget['title'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $widget['description'] }}</p>
                                    <p class="mt-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ $widget['width'] }} × {{ $widget['height'] }} px sugeridos</p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ $widget['iframe_url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-blue-700">
                                        <i class="fas fa-external-link-alt"></i>
                                        Pré-visualizar
                                    </a>
                                    <button type="button"
                                        onclick="copyReferralMaterial(this)"
                                        data-copy-text="{{ e($widget['iframe_snippet']) }}"
                                        data-track-channel="embed-iframe"
                                        data-target-url="{{ $widget['iframe_url'] }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                        <i class="fas fa-window-maximize"></i>
                                        Copiar iframe
                                    </button>
                                    <button type="button"
                                        onclick="copyReferralMaterial(this)"
                                        data-copy-text="{{ e($widget['responsive_html_snippet']) }}"
                                        data-track-channel="embed-responsive"
                                        data-target-url="{{ $widget['iframe_url'] }}"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                        <i class="fas fa-mobile-alt"></i>
                                        Copiar HTML responsivo
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 grid-cols-1">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Iframe</p>
                                    <pre class="mt-3 overflow-x-auto whitespace-pre-wrap rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">{{ $widget['iframe_snippet'] }}</pre>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">HTML responsivo</p>
                                    <pre class="mt-3 overflow-x-auto whitespace-pre-wrap rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">{{ $widget['responsive_html_snippet'] }}</pre>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
