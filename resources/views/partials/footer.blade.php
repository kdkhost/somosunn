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

<footer class="mt-auto border-t border-blue-100/80 bg-[radial-gradient(circle_at_top_left,rgba(59,130,246,0.16),transparent_32%),radial-gradient(circle_at_top_right,rgba(14,165,233,0.12),transparent_28%),linear-gradient(180deg,#eef5ff_0%,#f7fbff_55%,#edf4ff_100%)]">
    <div class="h-px bg-gradient-to-r from-transparent via-blue-400/50 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 md:px-6 py-10 md:py-12">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)] lg:gap-14">
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/70 bg-white/80 shadow-[0_10px_30px_-18px_rgba(37,99,235,0.55)] overflow-hidden">
                        <img src="{{ $logoSrc }}" alt="{{ $siteName }}" class="max-h-9 w-auto object-contain">
                    </div>

                    <div class="min-w-0">
                        <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.22em] text-blue-700 ring-1 ring-blue-100/80">
                            {{ $siteName }}
                        </span>
                        <h2 class="mt-2 text-xl md:text-2xl font-black text-slate-900">{{ $siteName }}</h2>
                        <p class="mt-2 max-w-2xl text-sm md:text-base leading-7 text-slate-600">
                            Networking, cursos, mentorias, eventos e oportunidades em um ambiente unico e organizado.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    @if($supportEmail)
                        <a href="mailto:{{ $supportEmail }}"
                            class="inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/80 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                            <i class="fas fa-envelope text-xs"></i>
                            {{ $supportEmail }}
                        </a>
                    @endif

                    @if($companyPhone !== '' && $companyPhoneHref)
                        <a href="{{ $companyPhoneHref }}"
                            class="inline-flex items-center gap-2 rounded-full border border-white/80 bg-white/80 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                            <i class="fas fa-phone-alt text-xs"></i>
                            {{ $companyPhone }}
                        </a>
                    @endif
                </div>

                @if(!empty($socialLinks))
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($socialLinks as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/80 bg-white/80 text-slate-500 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-700"
                                aria-label="{{ $social['title'] }}" title="{{ $social['title'] }}">
                                <i class="{{ $social['icon'] }}"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-500">Acesso rapido</p>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    @foreach($quickLinks as $link)
                        <a href="{{ $link['url'] }}"
                            class="group flex items-center justify-between rounded-2xl border border-white/80 bg-white/80 px-4 py-3 text-sm font-semibold text-slate-700 shadow-[0_10px_30px_-22px_rgba(37,99,235,0.45)] transition hover:border-blue-200 hover:text-blue-700">
                            <span>{{ $link['label'] }}</span>
                            <i class="fas fa-arrow-right text-[11px] text-slate-400 transition group-hover:text-blue-600"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col gap-3 border-t border-blue-100/80 pt-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
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
</footer>
