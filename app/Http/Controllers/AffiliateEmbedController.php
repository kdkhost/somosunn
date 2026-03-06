<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AffiliateShareKitService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AffiliateEmbedController extends Controller
{
    public function __construct(
        private readonly AffiliateShareKitService $shareKit,
    ) {
    }

    public function widget(Request $request, string $referralCode)
    {
        $affiliate = User::query()->where('referral_code', $referralCode)->firstOrFail();
        $kit = $this->shareKit->buildForUser($affiliate);
        $variant = (string) $request->query('variant', 'compact');

        return response()
            ->view('affiliate.embed.widget', [
                'affiliate' => $affiliate,
                'kit' => $kit,
                'variant' => in_array($variant, ['compact', 'hero', 'offers'], true) ? $variant : 'compact',
            ])
            ->header('X-Frame-Options', 'ALLOWALL');
    }

    public function graphic(Request $request, string $referralCode, string $preset): Response
    {
        $affiliate = User::query()->where('referral_code', $referralCode)->firstOrFail();
        $kit = $this->shareKit->buildForUser($affiliate);
        $graphic = collect($kit['graphic_assets'] ?? [])->firstWhere('preset', $preset);

        abort_if(!$graphic, 404);

        $svg = $this->buildSvg(
            $graphic['width'],
            $graphic['height'],
            $kit['branding']['site_name'] ?? 'UNN',
            $graphic['title'] ?? 'Indicação oficial',
            $graphic['subtitle'] ?? '',
            $graphic['cta_label'] ?? 'Acesse agora',
            $graphic['caption'] ?? ($kit['referral']['short_label'] ?? $affiliate->referral_code)
        );

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=600',
        ]);
    }

    private function buildSvg(int $width, int $height, string $brand, string $title, string $subtitle, string $cta, string $caption): string
    {
        $title = e($title);
        $subtitle = e($subtitle);
        $cta = e($cta);
        $brand = e($brand);
        $caption = e($caption);
        $titleFontSize = $width >= 1000 ? 62 : ($width >= 700 ? 40 : 28);
        $subtitleFontSize = $width >= 1000 ? 26 : ($width >= 700 ? 20 : 16);
        $badgeFontSize = $width >= 700 ? 18 : 14;
        $titleY = $titleFontSize + 90;
        $subtitleY = $titleFontSize + 140;
        $ctaY = $height - 152;
        $ctaTextY = $height - 116;
        $captionY = $height - 52;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}" role="img" aria-label="Criativo de afiliado {$brand}">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#081634"/>
      <stop offset="55%" stop-color="#0F2F7C"/>
      <stop offset="100%" stop-color="#1F5EDB"/>
    </linearGradient>
    <linearGradient id="cta" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#1F5EDB"/>
      <stop offset="100%" stop-color="#177FD6"/>
    </linearGradient>
  </defs>
  <rect width="{$width}" height="{$height}" rx="32" fill="url(#bg)"/>
  <circle cx="{$width}" cy="0" r="{$height}" fill="rgba(255,255,255,0.08)"/>
  <circle cx="0" cy="{$height}" r="{$height}" fill="rgba(255,255,255,0.05)"/>
  <text x="72" y="88" fill="#93C5FD" font-family="Arial, Helvetica, sans-serif" font-size="{$badgeFontSize}" font-weight="700" letter-spacing="2">INDICAÇÃO OFICIAL</text>
  <text x="72" y="{$titleY}" fill="#FFFFFF" font-family="Arial, Helvetica, sans-serif" font-size="{$titleFontSize}" font-weight="800">{$title}</text>
  <text x="72" y="{$subtitleY}" fill="#DBEAFE" font-family="Arial, Helvetica, sans-serif" font-size="{$subtitleFontSize}" font-weight="500">{$subtitle}</text>
  <rect x="72" y="{$ctaY}" width="280" height="58" rx="29" fill="url(#cta)"/>
  <text x="212" y="{$ctaTextY}" text-anchor="middle" fill="#FFFFFF" font-family="Arial, Helvetica, sans-serif" font-size="22" font-weight="700">{$cta}</text>
  <text x="72" y="{$captionY}" fill="#BFDBFE" font-family="Arial, Helvetica, sans-serif" font-size="18" font-weight="600">{$brand} · {$caption}</text>
</svg>
SVG;
    }
}