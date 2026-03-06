@php
    $affiliateShareKit = $affiliateShareKit ?? [];
    $referral = $affiliateShareKit['referral'] ?? [];
    $branding = $affiliateShareKit['branding'] ?? [];
    $materials = $affiliateShareKit['materials'] ?? [];
    $offers = $affiliateShareKit['offers'] ?? [];
    $landingPage = $affiliateShareKit['landing_page'] ?? [];
    $apiGuide = $affiliateShareKit['api'] ?? [];
    $featuredOffers = $landingPage['featured_offers'] ?? [];
@endphp

<section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-lg font-black text-slate-900 dark:text-white">Kit de divulgação e API REST</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Materiais prontos para compartilhar e endpoints para montar site, landing page ou painel próprio com seu link de afiliado.
            </p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-300">
            <i class="fas fa-code text-[10px]"></i>
            Consumo externo
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
        <div class="space-y-4">
            @foreach($materials as $material)
                @php
                    $channelLabel = match($material['channel'] ?? 'copy') {
                        'whatsapp' => 'WhatsApp',
                        'linkedin' => 'LinkedIn',
                        'email' => 'E-mail',
                        default => 'Cópia rápida',
                    };
                @endphp
                <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-1">
                            <div class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-600 shadow-sm dark:bg-slate-900 dark:text-slate-300">
                                <i class="fas fa-bullhorn text-[10px]"></i>
                                {{ $channelLabel }}
                            </div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">{{ $material['title'] }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $material['description'] }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            <button type="button"
                                onclick="copyReferralMaterial(this)"
                                data-copy-text="{{ e(($material['subject'] ?? '') !== '' ? ($material['subject'] . "\n\n" . $material['text']) : $material['text']) }}"
                                data-track-channel="{{ $material['channel'] ?? 'copy' }}"
                                data-target-url="{{ $material['target_url'] ?? ($referral['register_url'] ?? '') }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700">
                                <i class="fas fa-copy"></i>
                                Copiar texto
                            </button>
                        </div>
                    </div>

                    @if(!empty($material['subject']))
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Assunto</p>
                            <p class="mt-2 font-semibold text-slate-800 dark:text-slate-100">{{ $material['subject'] }}</p>
                        </div>
                    @endif

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p class="whitespace-pre-line text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $material['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="space-y-6">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/40">
                @if(!empty($branding['hero_image_url']))
                    <img src="{{ $branding['hero_image_url'] }}" alt="Hero de divulgação" class="h-44 w-full object-cover">
                @endif
                <div class="space-y-4 p-5">
                    <div class="flex items-center gap-3">
                        @if(!empty($branding['logo_url']))
                            <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] ?? 'Marca' }}" class="h-12 w-auto rounded-xl bg-white p-2">
                        @endif
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Landing pronta</p>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $landingPage['hero']['title'] ?? ($branding['hero_title'] ?? 'Página pronta para captação') }}</h3>
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
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">CTA oficial</p>
                        <a href="{{ $landingPage['hero']['cta_url'] ?? ($referral['register_url'] ?? '#') }}"
                            target="_blank" rel="noopener noreferrer"
                            class="mt-2 inline-flex items-center gap-2 font-bold text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
                            {{ $landingPage['hero']['cta_label'] ?? 'Quero entrar agora' }}
                            <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Ofertas recomendadas</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Links prontos para usar em páginas e campanhas.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                        {{ count($featuredOffers) }} itens
                    </span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse($featuredOffers as $offer)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ strtoupper($offer['type'] ?? 'offer') }}</p>
                                    <h4 class="mt-1 font-black text-slate-900 dark:text-white">{{ $offer['title'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $offer['subtitle'] }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                                    {{ $offer['price_label'] }}
                                </span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ $offer['affiliate_url'] }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white transition-all hover:bg-blue-700">
                                    <i class="fas fa-link"></i>
                                    Abrir link do afiliado
                                </a>
                                <button type="button"
                                    onclick="copyReferralMaterial(this)"
                                    data-copy-text="{{ e($offer['affiliate_url']) }}"
                                    data-track-channel="offer-link"
                                    data-target-url="{{ $offer['affiliate_url'] }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                                    <i class="fas fa-copy"></i>
                                    Copiar URL
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400">
                            Ainda não há ofertas públicas suficientes para sugerir no kit.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
            <h3 class="text-base font-black text-slate-900 dark:text-white">API REST para sites e painéis externos</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Use o token gerado na seção de acesso API pessoal para consumir materiais, analytics e blocos de landing page em site próprio, microsite ou painel externo.
            </p>
            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
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
                                <td class="px-4 py-4">
                                    <p class="font-black text-slate-900 dark:text-white">{{ $endpoint['method'] }} {{ $endpoint['url'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $endpoint['name'] }}</p>
                                </td>
                                <td class="px-4 py-4 text-slate-600 dark:text-slate-300">{{ $endpoint['description'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50/70 p-5 dark:border-slate-800 dark:bg-slate-950/40">
            <div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Exemplos rápidos</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Autentique uma vez e depois consuma os endpoints com Bearer Token.</p>
            </div>

            @foreach($apiGuide['curl_examples'] ?? [] as $label => $command)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">{{ $label }}</p>
                        <button type="button"
                            onclick="copyReferralMaterial(this)"
                            data-copy-text="{{ e($command) }}"
                            data-track-channel="api-snippet"
                            data-target-url="{{ $apiGuide['base_url'] ?? '' }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition-all hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-blue-900 dark:hover:bg-blue-950/30 dark:hover:text-blue-300">
                            <i class="fas fa-copy"></i>
                            Copiar
                        </button>
                    </div>
                    <pre class="mt-3 overflow-x-auto whitespace-pre-wrap rounded-2xl bg-slate-950 px-4 py-3 text-xs leading-6 text-slate-200">{{ $command }}</pre>
                </div>
            @endforeach
        </div>
    </div>
</section>
