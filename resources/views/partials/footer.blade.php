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

<footer class="mt-auto border-t border-slate-200/70 bg-[radial-gradient(circle_at_top,#eff6ff_0%,#ffffff_45%,#f8fafc_100%)]">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-8 md:py-10">
        <div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/90 shadow-[0_20px_70px_-28px_rgba(15,23,42,0.22)] backdrop-blur">
            <div class="h-1.5 bg-gradient-to-r from-[#1F5EDB] via-[#177FD6] to-[#29ABE2]"></div>

            <div class="grid gap-8 px-6 py-7 md:px-8 md:py-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
                <div class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-50 ring-1 ring-slate-200/80 shadow-sm overflow-hidden">
                            <img src="{{ $logoSrc }}" alt="{{ $siteName }}" class="max-h-9 w-auto object-contain">
                        </div>

                        <div class="min-w-0">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.24em] text-blue-700">
                                Plataforma {{ $siteName }}
                            </span>
                            <h2 class="mt-2 text-lg md:text-xl font-black text-slate-900">{{ $siteName }}</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">
                                Networking, cursos, mentorias, eventos e oportunidades no mesmo ecossistema.
                            </p>
                        </div>
                    </div>

                    <p class="max-w-2xl text-sm leading-7 text-slate-600">
                        Conecte-se com a comunidade, acompanhe seus conteúdos e acesse os canais oficiais da plataforma
                        com uma navegação simples e consistente.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        @if($supportEmail)
                            <a href="mailto:{{ $supportEmail }}"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                <i class="fas fa-envelope text-xs"></i>
                                {{ $supportEmail }}
                            </a>
                        @endif

                        @if($companyPhone !== '' && $companyPhoneHref)
                            <a href="{{ $companyPhoneHref }}"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                <i class="fas fa-phone-alt text-xs"></i>
                                {{ $companyPhone }}
                            </a>
                        @endif
                    </div>

                    @if(!empty($socialLinks))
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            @foreach($socialLinks as $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:text-blue-700"
                                    aria-label="{{ $social['title'] }}" title="{{ $social['title'] }}">
                                    <i class="{{ $social['icon'] }}"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="space-y-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-slate-400">Acesso rapido</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($quickLinks as $link)
                                <a href="{{ $link['url'] }}"
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200/80 bg-slate-50/85 p-5 shadow-inner shadow-white/40">
                        <p class="text-sm font-bold text-slate-900">Documentos e suporte</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Consulte as informacoes legais da plataforma e fale com a equipe pelos canais oficiais sem sair do site.
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('site.privacidade') }}"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:text-blue-700 hover:ring-blue-200">
                                <i class="fas fa-shield-alt text-xs"></i>
                                Privacidade
                            </a>
                            <a href="{{ route('contato') }}"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:text-blue-700 hover:ring-blue-200">
                                <i class="fas fa-paper-plane text-xs"></i>
                                Fale conosco
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200/80 px-6 py-4 text-xs text-slate-500 md:px-8 sm:flex-row sm:items-center sm:justify-between">
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
