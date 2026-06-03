<?php

namespace App\Services;

use App\Models\AffiliateApiSandboxRequest;
use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\SiteContent;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AffiliateShareKitService
{
    public function ensureReferralCode(User $user): User
    {
        if (!empty($user->referral_code)) {
            return $user;
        }

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $code = 'UNN' . strtoupper(substr(md5($user->id . microtime()), 0, 7));
            if (!User::where('referral_code', $code)->exists()) {
                $user->forceFill(['referral_code' => $code])->save();

                return $user->refresh();
            }
        }

        throw new \RuntimeException('Nao foi possivel gerar um codigo de indicacao unico.');
    }

    public function buildForUser(User $user): array
    {
        $user = $this->ensureReferralCode($user);

        $referral = $this->buildReferralData($user);
        $branding = $this->buildBrandingData();
        $offers = $this->buildOffers($user);
        $socialProof = $this->buildSocialProof();
        $materials = $this->buildMaterials($user, $referral, $branding, $offers, $socialProof);
        $landingPage = $this->buildLandingPage($referral, $branding, $offers, $socialProof);
        $graphicAssets = $this->buildGraphicAssets($referral, $branding, $offers);
        $embedWidgets = $this->buildEmbedWidgets($referral, $graphicAssets);
        $sandbox = $this->buildSandboxStatus($user);
        $playground = $this->buildPlaygroundGuide($sandbox);

        return [
            'referral' => $referral,
            'branding' => $branding,
            'offers' => $offers,
            'materials' => $materials,
            'graphic_assets' => $graphicAssets,
            'embed_widgets' => $embedWidgets,
            'landing_page' => $landingPage,
            'sandbox' => $sandbox,
            'playground' => $playground,
            'api' => $this->buildApiGuide($playground),
        ];
    }

    private function buildReferralData(User $user): array
    {
        $registerUrl = $this->appendQuery(route('register'), [
            'ref' => $user->referral_code,
            'utm_source' => 'affiliate',
            'utm_medium' => 'share-kit',
            'utm_campaign' => 'member-referral',
        ]);

        $homeUrl = $this->appendQuery(url('/'), [
            'ref' => $user->referral_code,
            'utm_source' => 'affiliate',
            'utm_medium' => 'share-kit',
            'utm_campaign' => 'member-home',
        ]);

        return [
            'code' => $user->referral_code,
            'register_url' => $registerUrl,
            'home_url' => $homeUrl,
            'short_label' => sprintf('Código %s', $user->referral_code),
        ];
    }

    private function buildBrandingData(): array
    {
        $siteName = (string) (Setting::get('app_name') ?: Setting::get('company_name') ?: config('app.name', 'UNN'));
        $heroTitle = $this->cleanText($this->siteContent('home', 'hero_title', "Entre para {$siteName}"));
        $heroSubtitle = $this->cleanText($this->siteContent('home', 'hero_subtitle', 'Networking, conteúdo e oportunidades reais para crescer.'));
        $heroText = $this->cleanText($this->siteContent('home', 'hero_text', 'Use a comunidade para gerar conexões, aprender com especialistas e acelerar negócios.'));
        $manifesto = $this->cleanText($this->siteContent('about', 'manifesto', 'Uma comunidade orientada a negócios, relacionamento e crescimento consistente.'));

        return [
            'site_name' => $siteName,
            'hero_title' => $heroTitle,
            'hero_subtitle' => $heroSubtitle,
            'hero_text' => $heroText,
            'manifesto' => $manifesto,
            'logo_url' => Setting::getUrl('logo_front') ?: Setting::getUrl('logo_image') ?: Setting::getUrl('logo_admin') ?: asset('img/logo.svg'),
            'hero_image_url' => $this->resolveHeroImageUrl(),
            'favicon_url' => Setting::getUrl('favicon_image') ?: asset('favicon.ico'),
            'social_links' => [
                'instagram' => $this->siteContent('footer', 'instagram_url'),
                'linkedin' => $this->siteContent('footer', 'linkedin_url'),
                'youtube' => $this->siteContent('footer', 'youtube_url'),
                'facebook' => $this->siteContent('footer', 'facebook_url'),
            ],
        ];
    }

    private function buildOffers(User $user): array
    {
        return [
            'plans' => $this->buildPlanOffers($user),
            'courses' => $this->buildCourseOffers($user),
            'events' => $this->buildEventOffers($user),
            'mentorships' => $this->buildMentorshipOffers($user),
        ];
    }

    private function buildPlanOffers(User $user): array
    {
        if (!$this->hasTable('plans')) {
            return [];
        }

        return Plan::query()
            ->where('is_active', true)
            ->where('is_free', false)
            ->orderByDesc('highlight')
            ->orderByDesc('is_featured')
            ->orderBy('price')
            ->limit(3)
            ->get()
            ->map(function (Plan $plan) use ($user) {
                return [
                    'type' => 'plan',
                    'id' => $plan->id,
                    'title' => $plan->name,
                    'subtitle' => $this->cleanText($plan->description, 140),
                    'image_url' => $this->resolveMediaUrl($plan->image),
                    'price_label' => 'R$ ' . number_format((float) $plan->price, 2, ',', '.'),
                    'cta_label' => 'Assinar agora',
                    'public_url' => route('subscription.checkout', $plan),
                    'affiliate_url' => $this->buildPromotionalUrl(route('subscription.checkout', $plan), $user, 'plan-' . $plan->id),
                ];
            })
            ->all();
    }

    private function buildCourseOffers(User $user): array
    {
        if (!$this->hasTable('courses')) {
            return [];
        }

        return Course::query()
            ->where(function ($query) {
                $query->where('status', 'published')
                    ->orWhere('published', true);
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(function (Course $course) use ($user) {
                $routeParam = $course->slug ?: $course->id;

                return [
                    'type' => 'course',
                    'id' => $course->id,
                    'title' => $course->title,
                    'subtitle' => $this->cleanText($course->short_description ?: $course->full_description, 140),
                    'image_url' => $this->resolveMediaUrl($course->thumbnail),
                    'price_label' => 'R$ ' . number_format((float) $course->effective_price, 2, ',', '.'),
                    'cta_label' => ((float) $course->effective_price) > 0 ? 'Conhecer curso' : 'Começar agora',
                    'public_url' => route('courses.show', $routeParam),
                    'affiliate_url' => $this->buildPromotionalUrl(route('courses.show', $routeParam), $user, 'course-' . $course->id),
                ];
            })
            ->all();
    }

    private function buildEventOffers(User $user): array
    {
        if (!$this->hasTable('events')) {
            return [];
        }

        return Event::query()
            ->where('published', true)
            ->publicUpcoming()
            ->orderBy('start_at')
            ->limit(3)
            ->get()
            ->map(function (Event $event) use ($user) {
                return [
                    'type' => 'event',
                    'id' => $event->id,
                    'title' => $event->title,
                    'subtitle' => $this->cleanText($event->description, 140),
                    'image_url' => $this->resolveMediaUrl($event->image),
                    'price_label' => 'R$ ' . number_format((float) $event->effective_price, 2, ',', '.'),
                    'cta_label' => 'Reservar vaga',
                    'public_url' => route('events.show', $event),
                    'affiliate_url' => $this->buildPromotionalUrl(route('events.show', $event), $user, 'event-' . $event->id),
                ];
            })
            ->all();
    }

    private function buildMentorshipOffers(User $user): array
    {
        if (!$this->hasTable('mentorships')) {
            return [];
        }

        return Mentorship::query()
            ->orderByDesc('id')
            ->limit(24)
            ->get()
            ->filter(fn (Mentorship $mentorship) => $mentorship->hasPublicAction())
            ->take(3)
            ->map(function (Mentorship $mentorship) use ($user) {
                return [
                    'type' => 'mentorship',
                    'id' => $mentorship->id,
                    'title' => $mentorship->title,
                    'subtitle' => $this->cleanText($mentorship->description, 140),
                    'image_url' => $this->resolveMediaUrl($mentorship->image),
                    'price_label' => 'R$ ' . number_format((float) $mentorship->effective_price, 2, ',', '.'),
                    'cta_label' => 'Ver mentoria',
                    'public_url' => route('mentorships.show', $mentorship),
                    'affiliate_url' => $this->buildPromotionalUrl(route('mentorships.show', $mentorship), $user, 'mentorship-' . $mentorship->id),
                ];
            })
            ->all();
    }

    private function buildSocialProof(): array
    {
        if (!$this->hasTable('testimonials')) {
            return [];
        }

        return Testimonial::query()
            ->forSite()
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->map(fn (Testimonial $testimonial) => [
                'author_name' => $testimonial->display_name,
                'author_title' => $testimonial->author_title,
                'content' => $this->cleanText($testimonial->content, 180),
                'rating' => (int) $testimonial->rating,
            ])
            ->all();
    }

    private function buildMaterials(User $user, array $referral, array $branding, array $offers, array $socialProof): array
    {
        $bestPlan = $offers['plans'][0] ?? null;
        $bestCourse = $offers['courses'][0] ?? null;
        $bestEvent = $offers['events'][0] ?? null;
        $bestMentorship = $offers['mentorships'][0] ?? null;
        $mainOffer = $bestPlan ?? $bestCourse ?? $bestEvent ?? $bestMentorship;

        $benefits = $this->defaultBenefits($branding, $offers);
        $proof = $socialProof[0]['content'] ?? 'Comunidade ativa, networking qualificado e oportunidades reais de negócio.';

        return [
            [
                'key' => 'convite-rapido',
                'title' => 'Convite rápido',
                'channel' => 'copy',
                'description' => 'Mensagem curta para DM, direct ou bio.',
                'text' => sprintf(
                    "Quero te indicar para %s. %s Acesse por este link: %s",
                    $branding['site_name'],
                    $branding['hero_subtitle'],
                    $referral['register_url']
                ),
                'target_url' => $referral['register_url'],
            ],
            [
                'key' => 'whatsapp-oferta',
                'title' => 'WhatsApp com oferta',
                'channel' => 'whatsapp',
                'description' => 'Mensagem pronta com benefício e CTA.',
                'text' => sprintf(
                    "Estou divulgando a %s e acho que faz sentido para você. %s Benefícios: %s. Entre pelo meu link: %s",
                    $branding['site_name'],
                    $mainOffer['title'] ?? $branding['hero_text'],
                    implode(' | ', array_slice($benefits, 0, 3)),
                    $mainOffer['affiliate_url'] ?? $referral['register_url']
                ),
                'target_url' => $mainOffer['affiliate_url'] ?? $referral['register_url'],
            ],
            [
                'key' => 'linkedin-autoridade',
                'title' => 'LinkedIn com autoridade',
                'channel' => 'linkedin',
                'description' => 'Texto para post ou artigo curto.',
                'text' => sprintf(
                    "Se você quer acelerar conexões e aprendizado com foco em negócios, vale conhecer a %s. %s. Uma das ofertas que mais recomendo hoje é %s. Link oficial: %s",
                    $branding['site_name'],
                    $branding['manifesto'],
                    $mainOffer['title'] ?? 'a plataforma completa',
                    $mainOffer['affiliate_url'] ?? $referral['home_url']
                ),
                'target_url' => $mainOffer['affiliate_url'] ?? $referral['home_url'],
            ],
            [
                'key' => 'email-convite',
                'title' => 'E-mail de prospecção',
                'channel' => 'email',
                'description' => 'Assunto e corpo para convite formal.',
                'subject' => sprintf('Convite para conhecer %s', $branding['site_name']),
                'text' => sprintf(
                    "Olá,\n\nQuero te indicar uma oportunidade interessante: %s.\n\n%s\n\nDestaques:\n- %s\n- %s\n- %s\n\nSe fizer sentido, entre por este link: %s\n",
                    $branding['site_name'],
                    $branding['hero_text'],
                    $benefits[0] ?? 'Networking com foco em resultado',
                    $benefits[1] ?? 'Conteúdo e experiências premium',
                    $proof,
                    $mainOffer['affiliate_url'] ?? $referral['register_url']
                ),
                'target_url' => $mainOffer['affiliate_url'] ?? $referral['register_url'],
            ],
        ];
    }

    private function buildLandingPage(array $referral, array $branding, array $offers, array $socialProof): array
    {
        $benefits = $this->defaultBenefits($branding, $offers);
        $featuredOffers = array_values(array_slice(array_merge(
            $offers['plans'],
            $offers['courses'],
            $offers['events'],
            $offers['mentorships']
        ), 0, 6));

        return [
            'hero' => [
                'eyebrow' => 'Indicação oficial',
                'title' => $branding['hero_title'],
                'subtitle' => $branding['hero_subtitle'],
                'body' => $branding['hero_text'],
                'cta_label' => 'Quero entrar agora',
                'cta_url' => $referral['register_url'],
                'secondary_cta_label' => 'Ver página principal',
                'secondary_cta_url' => $referral['home_url'],
                'background_image_url' => $branding['hero_image_url'],
            ],
            'benefits' => $benefits,
            'social_proof' => $socialProof,
            'featured_offers' => $featuredOffers,
            'cta' => [
                'headline' => 'Use o link oficial e acompanhe as oportunidades da comunidade.',
                'button_label' => 'Acessar com meu convite',
                'button_url' => $referral['register_url'],
                'supporting_text' => $branding['manifesto'],
            ],
        ];
    }

    private function buildApiGuide(array $playground): array
    {
        $baseUrl = url('/api/v1');
        $sandboxBaseUrl = $playground['sandbox_base_url'] ?? ($baseUrl . '/sandbox/affiliate');

        return [
            'base_url' => $baseUrl,
            'sandbox_base_url' => $sandboxBaseUrl,
            'auth' => [
                'login_url' => $baseUrl . '/auth/login',
                'logout_url' => $baseUrl . '/auth/logout',
                'me_url' => $baseUrl . '/me',
            ],
            'endpoints' => [
                [
                    'name' => 'Visão geral',
                    'method' => 'GET',
                    'url' => $baseUrl . '/affiliate/overview',
                    'description' => 'Código do afiliado, link oficial, marca e resumo do programa.',
                ],
                [
                    'name' => 'Materiais de divulgação',
                    'method' => 'GET',
                    'url' => $baseUrl . '/affiliate/materials',
                    'description' => 'Copies prontas, CTA, branding e ativos para campanhas.',
                ],
                [
                    'name' => 'Landing page',
                    'method' => 'GET',
                    'url' => $baseUrl . '/affiliate/landing-page',
                    'description' => 'Blocos estruturados para montar página externa.',
                ],
                [
                    'name' => 'Ofertas',
                    'method' => 'GET',
                    'url' => $baseUrl . '/affiliate/offers',
                    'description' => 'Planos, cursos, eventos e mentorias prontos para divulgar.',
                ],
                [
                    'name' => 'Analytics',
                    'method' => 'GET',
                    'url' => $baseUrl . '/affiliate/analytics',
                    'description' => 'Resumo, canais, funil e eventos detalhados paginados.',
                ],
                [
                    'name' => 'Sandbox overview',
                    'method' => 'GET',
                    'url' => $sandboxBaseUrl . '/overview',
                    'description' => 'Versão de homologação da visão geral, liberada mediante ticket com IP e domínio aprovados.',
                ],
                [
                    'name' => 'Sandbox analytics',
                    'method' => 'GET',
                    'url' => $sandboxBaseUrl . '/analytics',
                    'description' => 'Teste controlado das métricas em ambiente de homologação.',
                ],
            ],
            'curl_examples' => [
                'login' => sprintf(
                    "curl -X POST %s -H \"Accept: application/json\" -d \"email=SEU_EMAIL\" -d \"password=SUA_SENHA\" -d \"device_name=site-afiliado\"",
                    $baseUrl . '/auth/login'
                ),
                'overview' => sprintf(
                    "curl %s -H \"Accept: application/json\" -H \"Authorization: Bearer SEU_TOKEN\"",
                    $baseUrl . '/affiliate/overview'
                ),
                'landing' => sprintf(
                    "curl %s -H \"Accept: application/json\" -H \"Authorization: Bearer SEU_TOKEN\"",
                    $baseUrl . '/affiliate/landing-page'
                ),
                'sandbox' => sprintf(
                    "curl %s -H \"Accept: application/json\" -H \"Authorization: Bearer SEU_TOKEN\"",
                    $sandboxBaseUrl . '/overview'
                ),
            ],
        ];
    }

    private function buildGraphicAssets(array $referral, array $branding, array $offers): array
    {
        $mainOffer = collect(array_merge(
            $offers['plans'] ?? [],
            $offers['courses'] ?? [],
            $offers['events'] ?? [],
            $offers['mentorships'] ?? [],
        ))->first();

        $title = $mainOffer['title'] ?? ($branding['hero_title'] ?? 'Entre para a comunidade UNN');
        $subtitle = $mainOffer['subtitle'] ?? ($branding['hero_subtitle'] ?? 'Networking, conteúdo e oportunidades reais.');
        $cta = $mainOffer['cta_label'] ?? 'Acessar convite oficial';

        $presets = [
            ['preset' => 'social-landscape', 'title' => 'Post horizontal 1200x628', 'width' => 1200, 'height' => 628],
            ['preset' => 'social-square', 'title' => 'Post quadrado 1080x1080', 'width' => 1080, 'height' => 1080],
            ['preset' => 'story', 'title' => 'Story 1080x1920', 'width' => 1080, 'height' => 1920],
            ['preset' => 'leaderboard', 'title' => 'Banner 728x90', 'width' => 728, 'height' => 90],
            ['preset' => 'medium-rectangle', 'title' => 'Retângulo 300x250', 'width' => 300, 'height' => 250],
        ];

        return array_map(function (array $preset) use ($referral, $title, $subtitle, $cta) {
            $assetUrl = route('affiliate.embed.graphic', [
                'referralCode' => $referral['code'] ?? 'UNN',
                'preset' => $preset['preset'],
            ]);

            return [
                'preset' => $preset['preset'],
                'title' => $preset['title'],
                'width' => $preset['width'],
                'height' => $preset['height'],
                'subtitle' => $subtitle,
                'cta_label' => $cta,
                'caption' => $referral['short_label'] ?? ($referral['code'] ?? ''),
                'image_url' => $assetUrl,
                'download_url' => $assetUrl,
                'html_snippet' => sprintf(
                    '<a href="%s" target="_blank" rel="noopener"><img src="%s" width="%d" height="%d" alt="%s" style="max-width:100%%;height:auto;border:0;display:block;"></a>',
                    e($referral['register_url'] ?? url('/')),
                    e($assetUrl),
                    $preset['width'],
                    $preset['height'],
                    e($title)
                ),
                'markdown_snippet' => sprintf('[![%s](%s)](%s)', $title, $assetUrl, $referral['register_url'] ?? url('/')),
                'title_text' => $title,
            ];
        }, $presets);
    }

    private function buildEmbedWidgets(array $referral, array $graphicAssets): array
    {
        $responsiveGraphic = collect($graphicAssets);
        $mobile = $responsiveGraphic->firstWhere('preset', 'medium-rectangle');
        $tablet = $responsiveGraphic->firstWhere('preset', 'leaderboard');
        $desktop = $responsiveGraphic->firstWhere('preset', 'social-landscape');

        $widgetVariants = [
            [
                'key' => 'embed-compact',
                'title' => 'Widget compacto',
                'description' => 'Bloco curto para sidebar, blog post ou landing externa.',
                'iframe_url' => route('affiliate.embed.widget', ['referralCode' => $referral['code'] ?? 'UNN', 'variant' => 'compact']),
                'width' => 420,
                'height' => 560,
            ],
            [
                'key' => 'embed-hero',
                'title' => 'Widget hero',
                'description' => 'Versão maior para página principal ou dobra inicial.',
                'iframe_url' => route('affiliate.embed.widget', ['referralCode' => $referral['code'] ?? 'UNN', 'variant' => 'hero']),
                'width' => 720,
                'height' => 640,
            ],
            [
                'key' => 'embed-offers',
                'title' => 'Widget com ofertas',
                'description' => 'Mostra múltiplas ofertas dentro de um iframe pronto.',
                'iframe_url' => route('affiliate.embed.widget', ['referralCode' => $referral['code'] ?? 'UNN', 'variant' => 'offers']),
                'width' => 720,
                'height' => 760,
            ],
        ];

        return array_map(function (array $widget) use ($mobile, $tablet, $desktop, $referral) {
            return $widget + [
                'iframe_snippet' => sprintf(
                    '<iframe src="%s" loading="lazy" style="width:100%%;max-width:%dpx;height:%dpx;border:0;border-radius:24px;overflow:hidden;" referrerpolicy="strict-origin-when-cross-origin"></iframe>',
                    e($widget['iframe_url']),
                    $widget['width'],
                    $widget['height']
                ),
                'responsive_html_snippet' => sprintf(
                    '<a href="%s" target="_blank" rel="noopener"><img src="%s" srcset="%s 300w, %s 728w, %s 1200w" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 728px, 1200px" alt="Banner oficial de afiliado" style="width:100%%;height:auto;border:0;display:block;"></a>',
                    e($referral['register_url'] ?? url('/')),
                    e($desktop['image_url'] ?? ($mobile['image_url'] ?? url('/'))),
                    e($mobile['image_url'] ?? url('/')),
                    e($tablet['image_url'] ?? url('/')),
                    e($desktop['image_url'] ?? url('/'))
                ),
            ];
        }, $widgetVariants);
    }

    private function buildSandboxStatus(User $user): array
    {
        if (!$this->hasTable('affiliate_api_sandbox_requests')) {
            return [
                'available' => false,
                'enabled' => false,
                'latest_request' => null,
                'approved_request' => null,
            ];
        }

        $latestRequest = AffiliateApiSandboxRequest::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $approvedRequest = AffiliateApiSandboxRequest::query()
            ->approved()
            ->where('user_id', $user->id)
            ->latest('reviewed_at')
            ->latest('id')
            ->first();

        return [
            'available' => true,
            'enabled' => $approvedRequest !== null,
            'latest_request' => $latestRequest ? $this->serializeSandboxRequest($latestRequest) : null,
            'approved_request' => $approvedRequest ? $this->serializeSandboxRequest($approvedRequest) : null,
        ];
    }

    private function buildPlaygroundGuide(array $sandbox): array
    {
        $sandboxBaseUrl = url('/api/v1/sandbox/affiliate');

        return [
            'sandbox_base_url' => $sandboxBaseUrl,
            'requires_approval' => true,
            'enabled' => (bool) ($sandbox['enabled'] ?? false),
            'request_requirements' => [
                'Explique o motivo do acesso e o tipo de integração que será testado.',
                'Informe o IP público de origem das chamadas.',
                'Informe o domínio ou subdomínio onde o sandbox será consumido.',
            ],
            'endpoints' => [
                ['key' => 'overview', 'label' => 'Overview', 'path' => '/overview', 'method' => 'GET'],
                ['key' => 'materials', 'label' => 'Materials', 'path' => '/materials', 'method' => 'GET'],
                ['key' => 'offers', 'label' => 'Offers', 'path' => '/offers', 'method' => 'GET'],
                ['key' => 'landing-page', 'label' => 'Landing Page', 'path' => '/landing-page', 'method' => 'GET'],
                ['key' => 'analytics', 'label' => 'Analytics', 'path' => '/analytics?per_page=10&visit_limit=5', 'method' => 'GET'],
            ],
        ];
    }

    private function serializeSandboxRequest(AffiliateApiSandboxRequest $request): array
    {
        return [
            'id' => $request->id,
            'status' => $request->status,
            'reason' => $request->reason,
            'requested_domain' => $request->requested_domain,
            'requested_ip' => $request->requested_ip,
            'admin_notes' => $request->admin_notes,
            'reviewed_at' => optional($request->reviewed_at)?->toIso8601String(),
            'reviewed_at_label' => optional($request->reviewed_at)?->format('d/m/Y H:i'),
        ];
    }

    private function defaultBenefits(array $branding, array $offers): array
    {
        $planBenefit = $offers['plans'][0]['title'] ?? 'Planos premium com foco em crescimento';
        $courseBenefit = $offers['courses'][0]['title'] ?? 'Cursos e conteúdos estratégicos';
        $eventBenefit = $offers['events'][0]['title'] ?? 'Eventos e networking qualificado';

        return [
            $planBenefit,
            $courseBenefit,
            $eventBenefit,
            $branding['manifesto'],
        ];
    }

    private function siteContent(string $slug, string $key, string $default = ''): string
    {
        if (!$this->hasTable('site_contents')) {
            return $default;
        }

        return (string) (SiteContent::getValue($slug, $key, $default) ?: $default);
    }

    private function resolveHeroImageUrl(): ?string
    {
        $settingHero = Setting::getUrl('hero_image');
        if ($settingHero) {
            return $settingHero;
        }

        $cmsHero = $this->siteContent('home', 'hero_image');
        if ($cmsHero !== '') {
            return asset('storage/' . ltrim($cmsHero, '/'));
        }

        return null;
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($normalized, 'storage/') || str_starts_with($normalized, 'uploads/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }

    private function buildPromotionalUrl(string $url, User $user, string $campaign): string
    {
        return $this->appendQuery($url, [
            'ref' => $user->referral_code,
            'utm_source' => 'affiliate',
            'utm_medium' => 'external-landing',
            'utm_campaign' => $campaign,
        ]);
    }

    private function appendQuery(string $url, array $query): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query(array_filter($query, static fn ($value) => $value !== null && $value !== ''));
    }

    private function cleanText(?string $text, int $limit = 180): string
    {
        return (string) Str::of(strip_tags((string) $text))
            ->squish()
            ->limit($limit, '…');
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }
}
