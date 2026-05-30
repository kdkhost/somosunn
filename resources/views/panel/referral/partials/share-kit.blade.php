@php
    $affiliateShareKit = $affiliateShareKit ?? [];
    $referral = $affiliateShareKit['referral'] ?? [];
    $branding = $affiliateShareKit['branding'] ?? [];
    $materials = $affiliateShareKit['materials'] ?? [];
    $offers = $affiliateShareKit['offers'] ?? [];
    $landingPage = $affiliateShareKit['landing_page'] ?? [];
    $apiGuide = $affiliateShareKit['api'] ?? [];
    $graphicAssets = $affiliateShareKit['graphic_assets'] ?? [];
    $embedWidgets = $affiliateShareKit['embed_widgets'] ?? [];
    $sandbox = $affiliateShareKit['sandbox'] ?? [];
    $playground = $affiliateShareKit['playground'] ?? [];
    $featuredOffers = $landingPage['featured_offers'] ?? [];
    $sandboxRequests = $sandboxRequests ?? collect();
    $sandboxLatestRequest = $sandboxLatestRequest ?? null;
    $sandboxApprovedRequest = $sandboxApprovedRequest ?? null;
    $sandboxAvailable = $sandboxAvailable ?? (bool) ($sandbox['available'] ?? false);
    $sandboxEnabled = (bool) ($sandbox['enabled'] ?? false);
    $sandboxBaseUrl = $playground['sandbox_base_url'] ?? ($apiGuide['sandbox_base_url'] ?? url('/api/v1/sandbox/affiliate'));
@endphp

<section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
                <i class="fas fa-satellite-dish text-[10px]"></i>
                Publicação externa
            </div>
            <h2 class="mt-3 text-lg font-black text-slate-900 dark:text-white">Central de materiais, embeds e sandbox</h2>
            <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                Aqui ficam separados os textos prontos, criativos em tamanhos específicos, widgets para copiar via HTML/iframe e o ambiente de testes da API.
            </p>
        </div>
        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
            <a href="#affiliateCopySection" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                <i class="fas fa-bullhorn"></i>
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
            <a href="#affiliateSandboxSection" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                <i class="fas fa-flask"></i>
                Sandbox / playground
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
                            <img src="{{ $branding['hero_image_url'] }}" alt="Hero de divulgação" class="h-44 w-full object-cover">
                        @endif
                        <div class="space-y-4 p-5">
                            <div class="flex items-center gap-3">
                                @if(!empty($branding['logo_url']))
                                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] ?? 'Marca' }}" class="h-12 w-auto rounded-xl bg-white p-2">
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
                                    <img src="{{ $asset['image_url'] }}" alt="{{ $asset['title'] }}" class="h-36 w-full object-cover">
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

    <div id="affiliateSandboxSection" class="mt-6 grid gap-6 xl:grid-cols-[1.02fr,0.98fr]">
        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">API REST e acesso controlado ao sandbox</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Gere seu token na seção acima e use o playground abaixo para testar em ambiente de homologação depois da aprovação do ticket.
                    </p>
                </div>
                <a href="#affiliateApiTokensSection" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                    <i class="fas fa-key"></i>
                    Ir para tokens
                </a>
            </div>

            <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:bg-slate-950 dark:text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Endpoint</th>
                            <th class="px-4 py-3 text-left">Uso</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($apiGuide['endpoints'] ?? [] as $endpoint)
                            <tr>
                                <td class="px-4 py-4 align-top">
                                    <p class="font-black text-slate-900 dark:text-white">{{ $endpoint['method'] }} {{ $endpoint['url'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $endpoint['name'] }}</p>
                                </td>
                                <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $endpoint['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach($apiGuide['curl_examples'] ?? [] as $label => $command)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ $label }}</p>
                            <button type="button"
                                onclick="copyReferralMaterial(this)"
                                data-copy-text="{{ e($command) }}"
                                data-track-channel="api-snippet"
                                data-target-url="{{ $apiGuide['base_url'] ?? '' }}"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                <i class="fas fa-copy"></i>
                                Copiar
                            </button>
                        </div>
                        <pre class="mt-3 overflow-x-auto whitespace-pre-wrap rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">{{ $command }}</pre>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Solicitação de acesso ao sandbox</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            O ambiente de testes exige motivo, IP público e domínio que vão consumir a API.
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] {{ $sandboxEnabled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                        {{ $sandboxEnabled ? 'Liberado' : 'Aguardando ticket' }}
                    </span>
                </div>

                @if(!$sandboxAvailable)
                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-300">
                        O sandbox da API ainda não está disponível neste ambiente. Rode as migrations para liberar esta área.
                    </div>
                @else
                    @if($sandboxApprovedRequest)
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                            <p class="font-bold">Acesso liberado</p>
                            <p class="mt-1">Domínio aprovado: <strong>{{ $sandboxApprovedRequest->requested_domain ?: 'Não informado' }}</strong> · IP aprovado: <strong>{{ $sandboxApprovedRequest->requested_ip ?: 'Não informado' }}</strong></p>
                            @if($sandboxApprovedRequest->admin_notes)
                                <p class="mt-2"><strong>Observação do time:</strong> {{ $sandboxApprovedRequest->admin_notes }}</p>
                            @endif
                        </div>
                    @elseif($sandboxLatestRequest)
                        <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800 dark:bg-slate-900">
                            <p class="font-bold text-slate-900 dark:text-white">Último ticket: {{ strtoupper($sandboxLatestRequest->status) }}</p>
                            <p class="mt-1 text-slate-500 dark:text-slate-400">Domínio: {{ $sandboxLatestRequest->requested_domain ?: 'Não informado' }} · IP: {{ $sandboxLatestRequest->requested_ip ?: 'Não informado' }}</p>
                            @if($sandboxLatestRequest->admin_notes)
                                <p class="mt-2 text-slate-600 dark:text-slate-300"><strong>Retorno do admin:</strong> {{ $sandboxLatestRequest->admin_notes }}</p>
                            @endif
                        </div>
                    @endif

                    @if($errors->has('sandbox'))
                        <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-300">
                            {{ $errors->first('sandbox') }}
                        </div>
                    @endif

                    <form action="{{ route('panel.referral.sandbox.store') }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="sandbox_reason" class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Motivo da solicitação</label>
                            <textarea
                                id="sandbox_reason"
                                name="reason"
                                rows="4"
                                placeholder="Explique como vai usar a API, qual sistema vai consumir e o objetivo do teste."
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                            >{{ old('reason', $sandboxLatestRequest?->status === 'pending' ? $sandboxLatestRequest->reason : '') }}</textarea>
                            @error('reason')
                                <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="sandbox_domain" class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Domínio / subdomínio</label>
                                <input
                                    id="sandbox_domain"
                                    name="requested_domain"
                                    type="text"
                                    value="{{ old('requested_domain', $sandboxLatestRequest?->status === 'pending' ? $sandboxLatestRequest->requested_domain : '') }}"
                                    placeholder="ex.: afiliado.suaempresa.com"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                >
                                @error('requested_domain')
                                    <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="sandbox_ip" class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">IP público</label>
                                <input
                                    id="sandbox_ip"
                                    name="requested_ip"
                                    type="text"
                                    value="{{ old('requested_ip', $sandboxLatestRequest?->status === 'pending' ? $sandboxLatestRequest->requested_ip : '') }}"
                                    placeholder="203.0.113.10"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                >
                                @error('requested_ip')
                                    <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-700 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
                            <i class="fas fa-ticket-alt"></i>
                            Enviar ticket de acesso
                        </button>
                    </form>

                    @if($sandboxRequests->isNotEmpty())
                        <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Histórico recente</p>
                            <div class="mt-3 space-y-3">
                                @foreach($sandboxRequests as $requestItem)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white">{{ strtoupper($requestItem->status) }}</span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ optional($requestItem->created_at)->format('d/m/Y H:i') ?: '—' }}</span>
                                        </div>
                                        <p class="mt-2 text-slate-600 dark:text-slate-300">{{ $requestItem->reason }}</p>
                                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Domínio: {{ $requestItem->requested_domain ?: 'Não informado' }} · IP: {{ $requestItem->requested_ip ?: 'Não informado' }}</p>
                                        @if($requestItem->admin_notes)
                                            <p class="mt-2 text-xs text-slate-600 dark:text-slate-300"><strong>Admin:</strong> {{ $requestItem->admin_notes }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <div id="affiliatePlaygroundSection" class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Playground da API</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Teste os endpoints do sandbox sem sair do painel e veja o payload real retornado.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] {{ $sandboxEnabled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ $sandboxEnabled ? 'Pronto para testar' : 'Aguardando aprovação' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-[0.9fr,1.1fr]">
                    <div class="space-y-4">
                        <form id="affiliateSandboxPlaygroundForm" class="space-y-4">
                            <div>
                                <label for="affiliateSandboxEndpoint" class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Endpoint</label>
                                <select id="affiliateSandboxEndpoint" name="endpoint" onchange="updateAffiliateSandboxPreview()"
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                    {{ $sandboxEnabled ? '' : 'disabled' }}>
                                    @foreach($playground['endpoints'] ?? [] as $endpoint)
                                        <option value="{{ $endpoint['key'] }}">{{ $endpoint['label'] }} · {{ $endpoint['method'] }} {{ $endpoint['path'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="affiliateSandboxPerPage" class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Per page</label>
                                    <input id="affiliateSandboxPerPage" type="number" min="1" max="100" value="10" onchange="updateAffiliateSandboxPreview()"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                        {{ $sandboxEnabled ? '' : 'disabled' }}>
                                </div>
                                <div>
                                    <label for="affiliateSandboxVisitLimit" class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Visit limit</label>
                                    <input id="affiliateSandboxVisitLimit" type="number" min="1" max="50" value="5" onchange="updateAffiliateSandboxPreview()"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                        {{ $sandboxEnabled ? '' : 'disabled' }}>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">URL simulada</p>
                                <pre id="affiliateSandboxRequestUrl" class="mt-3 overflow-x-auto whitespace-pre-wrap break-all rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">{{ $sandboxBaseUrl }}/overview</pre>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">cURL pronto</p>
                                    <button type="button" onclick="copyAffiliateSandboxCurl(this)"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300"
                                        {{ $sandboxEnabled ? '' : 'disabled' }}>
                                        <i class="fas fa-copy"></i>
                                        Copiar cURL
                                    </button>
                                </div>
                                <pre id="affiliateSandboxCurlSnippet" class="mt-3 overflow-x-auto whitespace-pre-wrap break-all rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">curl {{ $sandboxBaseUrl }}/overview -H "Accept: application/json" -H "Authorization: Bearer SEU_TOKEN"</pre>
                            </div>

                            <button type="button" id="affiliateSandboxRunButton" onclick="runAffiliateSandboxPlayground(this)"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 dark:disabled:bg-slate-800 dark:disabled:text-slate-500"
                                {{ $sandboxEnabled ? '' : 'disabled' }}>
                                <i class="fas fa-play"></i>
                                Executar no sandbox
                            </button>
                        </form>

                        @if(!$sandboxEnabled)
                            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                                O playground fica liberado depois que o time aprovar seu ticket com motivo, IP e domínio.
                            </div>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Resposta do playground</p>
                                <span id="affiliateSandboxResponseMeta" class="text-xs font-semibold text-slate-500 dark:text-slate-400">Aguardando execução</span>
                            </div>
                            <pre id="affiliateSandboxResponsePayload" class="mt-3 min-h-[24rem] overflow-auto whitespace-pre-wrap break-words rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">{
  "ok": false,
  "message": "Execute um endpoint para ver o retorno JSON aqui."
}</pre>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Checklist para liberar o acesso</p>
                            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                @foreach($playground['request_requirements'] ?? [] as $requirement)
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle mt-1 text-emerald-500"></i>
                                        <span>{{ $requirement }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
