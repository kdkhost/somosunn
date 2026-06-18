{{--
* Sistema UNN - Rodape
* Autor: George Marcelo (KDKHOST SOLUCOES)
* Telefone: +55 (21) 98132-5441
* Telegram: https://t.me/MARCELO_BRAD
* Copyright (c) 2026 Kdkhost Solucoes. Todos os direitos reservados.
* AVISO LEGAL:
* Este software e seu codigo-fonte sao propriedade intelectual de KDKHOST Solucoes.
* E proibida a reproducao, distribuicao, modificacao, engenharia reversa ou uso nao autorizado,
* total ou parcial, sem autorizacao previa e por escrito.
* Contato: contato@kdkhost.com.br
* Licenciamento: Uso restrito conforme contrato/termos aplicaveis.
--}}
@php
    $siteName = trim((string) (\App\Models\Setting::get('app_name') ?: \App\Models\Setting::get('company_name') ?: config('app.name', 'UNN')));
    if ($siteName === '') {
        $siteName = 'UNN';
    }

    $logoSrc = \App\Models\Setting::getUrl('logo_front') ?: \App\Models\Setting::getUrl('logo_image') ?: asset('img/logo.svg');

    $footerText = trim((string) \App\Models\Setting::get('footer_text'));
    $legacyDefaultPattern = '/^(?:©|&copy;)?\s*\d{4}\s+(?:UNN|SOMOS\s+UNN)\.?$/iu';
    if ($footerText === '' || preg_match($legacyDefaultPattern, $footerText)) {
        $footerText = '© ' . date('Y') . ' ' . $siteName . '. Todos os direitos reservados.';
    }

    $supportEmail = trim((string) (\App\Models\Setting::get('company_email') ?: \App\Models\Setting::get('smtp_from_email') ?: config('mail.from.address')));
    if ($supportEmail === '' || $supportEmail === 'hello@example.com') {
        $supportEmail = null;
    }

    $companyPhone = trim((string) \App\Models\Setting::get('company_phone'));
    $companyPhoneHref = preg_replace('/[^\d+]+/', '', $companyPhone);
    if ($companyPhoneHref === '') {
        $companyPhoneHref = null;
    } else {
        $companyPhoneHref = 'tel:' . $companyPhoneHref;
    }

    $normalizeSocialUrl = function ($value, string $network): ?string {
        $value = trim((string) $value);
        if ($value === '' || $value === '#') {
            return null;
        }

        if (preg_match('/^\s*javascript\s*:/i', $value)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '//')) {
            return 'https:' . $value;
        }

        if ($network === 'instagram' && str_starts_with($value, '@')) {
            return 'https://instagram.com/' . ltrim($value, '@');
        }

        if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}/i', $value)) {
            return 'https://' . $value;
        }

        return $value;
    };

    $footerInstagram = \App\Models\SiteContent::getValue('footer', 'instagram_url');
    $footerFacebook = \App\Models\SiteContent::getValue('footer', 'facebook_url');
    $footerYoutube = \App\Models\SiteContent::getValue('footer', 'youtube_url');
    $footerLinkedin = \App\Models\SiteContent::getValue('footer', 'linkedin_url');

    $socialInstagram = $normalizeSocialUrl($footerInstagram ?: \App\Models\Setting::get('social_instagram'), 'instagram');
    $socialFacebook = $normalizeSocialUrl($footerFacebook ?: \App\Models\Setting::get('social_facebook'), 'facebook');
    $socialYoutube = $normalizeSocialUrl($footerYoutube ?: \App\Models\Setting::get('social_youtube'), 'youtube');
    $socialLinkedin = $normalizeSocialUrl($footerLinkedin ?: \App\Models\Setting::get('social_linkedin'), 'linkedin');

    $socialLinks = array_values(array_filter([
        ['url' => $socialInstagram, 'icon' => 'fab fa-instagram', 'title' => 'Instagram'],
        ['url' => $socialLinkedin, 'icon' => 'fab fa-linkedin-in', 'title' => 'LinkedIn'],
        ['url' => $socialYoutube, 'icon' => 'fab fa-youtube', 'title' => 'YouTube'],
        ['url' => $socialFacebook, 'icon' => 'fab fa-facebook-f', 'title' => 'Facebook'],
    ], fn ($item) => !empty($item['url'])));

    $quickLinks = [
        ['label' => 'Vagas Abertas', 'url' => route('jobs.public.index')],
        ['label' => 'Contato', 'url' => route('contato')],
        ['label' => 'Termos', 'url' => route('site.termos')],
        ['label' => 'Privacidade', 'url' => route('site.privacidade')],
        ['label' => 'LGPD', 'url' => route('site.lgpd')],
    ];
@endphp

<footer class="mt-auto border-t border-sky-100 bg-[linear-gradient(180deg,#eef6ff_0%,#f7fbff_52%,#edf5ff_100%)]">
    <div class="h-px bg-gradient-to-r from-transparent via-sky-400/40 to-transparent"></div>

    <div class="w-full">
        <div class="max-w-7xl mx-auto px-4 md:px-6 py-8 md:py-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-4">
                        <div class="inline-flex h-10 sm:h-12 md:h-16 w-auto items-center justify-center overflow-hidden shrink-0">
                            <img src="{{ $logoSrc }}" alt="" class="h-full w-auto object-contain"
                                onerror="this.style.display='none';">
                        </div>

                        <div class="min-w-0">
                            <h2 class="text-lg md:text-xl font-black text-slate-900">{{ $siteName }}</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Networking, cursos, mentorias, eventos e oportunidades em um unico ecossistema.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-3 text-sm text-slate-600">
                        @if($supportEmail)
                            <a href="mailto:{{ $supportEmail }}"
                                class="inline-flex items-center gap-2 font-medium transition hover:text-blue-700">
                                <i class="fas fa-envelope text-xs text-sky-600"></i>
                                {{ $supportEmail }}
                            </a>
                        @endif

                        @if($companyPhone !== '' && $companyPhoneHref)
                            <a href="{{ $companyPhoneHref }}"
                                class="inline-flex items-center gap-2 font-medium transition hover:text-blue-700">
                                <i class="fas fa-phone-alt text-xs text-sky-600"></i>
                                {{ $companyPhone }}
                            </a>
                        @endif

                        @if(!empty($socialLinks))
                            <div class="flex items-center gap-2">
                                @foreach($socialLinks as $social)
                                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/80 bg-white/80 text-slate-500 shadow-sm transition hover:border-sky-200 hover:text-blue-700"
                                        aria-label="{{ $social['title'] }}" title="{{ $social['title'] }}">
                                        <i class="{{ $social['icon'] }}"></i>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="lg:max-w-[520px]">
                    <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">Acesso rapido</p>
                    <nav class="flex flex-wrap items-center gap-x-5 gap-y-3 text-sm font-semibold text-slate-700">
                        @foreach($quickLinks as $index => $link)
                            <a href="{{ $link['url'] }}" class="transition hover:text-blue-700">
                                {{ $link['label'] }}
                            </a>
                            @if($index < count($quickLinks) - 1)
                                <span class="text-slate-300" aria-hidden="true">•</span>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 border-t border-sky-100 pt-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <div>{{ $footerText }}</div>

                <div class="flex flex-wrap items-center gap-1">
                    <span>Desenvolvido por</span>
                    <a href="https://kdkhost.com.br" target="_blank" rel="noopener"
                        class="font-semibold transition hover:underline"
                        style="color: var(--unn-azul-1)">
                        Marcelo Brad RJ
                    </a>
                    <span class="text-slate-300">•</span>
                    <span>kdkhost.com.br</span>
                </div>
            </div>
        </div>
    </div>
</footer>
