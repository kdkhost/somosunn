<?php

namespace App\Services;

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

        do {
            $code = 'UNN' . strtoupper(substr(md5($user->id . microtime()), 0, 7));
        } while (User::where('referral_code', $code)->exists());

        $user->forceFill(['referral_code' => $code])->save();

        return $user->refresh();
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

        return [
            'referral' => $referral,
            'branding' => $branding,
            'offers' => $offers,
            'materials' => $materials,
            'landing_page' => $landingPage,
            'api' => $this->buildApiGuide(),
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
            ->where('start_at', '>=', now()->subDay())
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
            ->limit(3)
            ->get()
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

    private function buildApiGuide(): array
    {
        $baseUrl = url('/api/v1');

        return [
            'base_url' => $baseUrl,
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
            ],
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
