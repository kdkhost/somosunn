@props([
    'context' => 'global', // 'global' | 'feed'
    'class' => '',
])

@php
    $allSettings = \App\Models\Setting::allValues();
    $adsEnabled = (string) ($allSettings['ads_enabled'] ?? '0') === '1';
    $ownNetworkEnabled = (string) ($allSettings['ads_own_network_enabled'] ?? '1') !== '0';
    $globalBannerEnabled = (string) ($allSettings['ads_global_banner_enabled'] ?? '1') !== '0';
    $interFeedEnabled = (string) ($allSettings['ads_inter_feed_enabled'] ?? '0') === '1';

    $publisherId = trim((string) ($allSettings['adsense_publisher_id'] ?? ''));
    $slotId = trim((string) ($allSettings['adsense_slot_id'] ?? ''));
    $format = trim((string) ($allSettings['adsense_format'] ?? 'auto')) ?: 'auto';
    $globalHtml = trim((string) ($allSettings['ads_code_html'] ?? ''));
    $feedHtml = trim((string) ($allSettings['ads_inter_feed_code'] ?? ''));

    // Nunca exibe anúncios em páginas de checkout, login/registro ou áreas administrativas
    $isBlockedRoute = request()->routeIs('login', 'register', 'password.*', 'checkout.*', 'cart.*', 'admin.*', 'panel.admin.*')
        || request()->is('admin', 'admin/*', 'painel/admin', 'painel/admin/*', 'checkout/*', 'carrinho*');

    $ownBanner = null;
    $canRender = false;
    $customCode = '';
    $useAdsense = false;

    if ($adsEnabled && !$isBlockedRoute) {
        $contextAllowed = $context === 'feed' ? $interFeedEnabled : $globalBannerEnabled;

        // 1. Prioridade: Anúncio Próprio Pago Ativo (SponsorBanner)
        if ($contextAllowed && $ownNetworkEnabled && \App\Models\SponsorBanner::tableAvailable()) {
            try {
                $ownBanner = \App\Models\SponsorBanner::query()
                    ->liveForContext($context)
                    ->whereNotNull('image')
                    ->inRandomOrder()
                    ->first();

                if ($ownBanner) {
                    $canRender = true;
                    if (\Illuminate\Support\Facades\Schema::hasColumn('sponsor_banners', 'impressions_count')) {
                        $ownBanner->increment('impressions_count');
                    }
                }
            } catch (\Throwable $e) {
                $ownBanner = null;
            }
        }

        // 2. Fallback: AdSense ou HTML personalizado
        if (!$canRender && $contextAllowed) {
            if ($context === 'feed') {
                if ($feedHtml !== '') {
                    $customCode = $feedHtml;
                    $canRender = true;
                } elseif ($publisherId !== '' && $slotId !== '') {
                    $useAdsense = true;
                    $canRender = true;
                } elseif ($globalHtml !== '') {
                    $customCode = $globalHtml;
                    $canRender = true;
                }
            } else {
                if ($publisherId !== '' && $slotId !== '') {
                    $useAdsense = true;
                    $canRender = true;
                } elseif ($globalHtml !== '') {
                    $customCode = $globalHtml;
                    $canRender = true;
                }
            }
        }
    }
@endphp

@if($canRender)
    <div class="unn-ad-slot {{ $context === 'feed' ? 'bg-white rounded-2xl shadow-sm border border-slate-100 p-4 my-4' : 'max-w-7xl mx-auto px-4 md:px-6 my-6' }} {{ $class }}"
         data-ad-context="{{ $context }}">
        <div class="flex items-center justify-between mb-2">
            <span class="inline-block text-[10px] font-bold uppercase tracking-widest text-slate-400">Publicidade</span>
            @if($ownBanner && $ownBanner->title)
                <span class="text-[11px] font-semibold text-slate-400 truncate max-w-[260px]">{{ $ownBanner->title }}</span>
            @endif
        </div>

        <div class="w-full overflow-hidden flex justify-center items-center min-h-[90px]">
            @if($ownBanner)
                @php($bannerClickUrl = route('ads.banner.click', $ownBanner))
                <a href="{{ $bannerClickUrl }}" target="_blank" rel="noopener noreferrer sponsored"
                   class="block w-full rounded-xl overflow-hidden group transition hover:opacity-95">
                    <img src="{{ $ownBanner->image_url }}"
                         alt="{{ $ownBanner->title }}"
                         class="w-full h-auto max-h-[260px] object-cover mx-auto rounded-xl"
                         loading="lazy" decoding="async">
                </a>
            @elseif($customCode !== '')
                {!! $customCode !!}
            @elseif($useAdsense)
                <ins class="adsbygoogle"
                     style="display:block; width:100%;"
                     data-ad-client="{{ $publisherId }}"
                     data-ad-slot="{{ $slotId }}"
                     data-ad-format="{{ $context === 'feed' && $format === 'auto' ? 'fluid' : $format }}"
                     data-full-width-responsive="true"></ins>
                <script>
                    (window.adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            @endif
        </div>
    </div>
@endif
