@php
    $referral = $kit['referral'] ?? [];
    $branding = $kit['branding'] ?? [];
    $landingPage = $kit['landing_page'] ?? [];
    $offers = collect(array_merge(
        $kit['offers']['plans'] ?? [],
        $kit['offers']['courses'] ?? [],
        $kit['offers']['events'] ?? [],
        $kit['offers']['mentorships'] ?? [],
    ))->take($variant === 'offers' ? 4 : 1);
    $ctaUrl = $landingPage['hero']['cta_url'] ?? ($referral['register_url'] ?? url('/'));
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget de afiliado</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            background: transparent;
            color: #0f172a;
        }
        .widget {
            min-height: 100vh;
            background: linear-gradient(145deg, #081634 0%, #0f2f7c 58%, #1f5edb 100%);
            border-radius: 28px;
            overflow: hidden;
            padding: 28px;
            color: #fff;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(255,255,255,.12);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .title { margin: 18px 0 8px; font-size: {{ $variant === 'hero' ? '34px' : '28px' }}; line-height: 1.05; font-weight: 900; }
        .subtitle { margin: 0; color: rgba(255,255,255,.82); font-size: 15px; line-height: 1.6; }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 24px;
        }
        .brand img { height: 48px; max-width: 160px; object-fit: contain; border-radius: 14px; background: rgba(255,255,255,.92); padding: 8px; }
        .cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 24px;
            padding: 16px 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, #1f5edb 0%, #177fd6 100%);
            color: #fff;
            text-decoration: none;
            font-weight: 800;
            box-shadow: 0 16px 30px -12px rgba(23,127,214,.5);
        }
        .offers { display: grid; gap: 12px; margin-top: 24px; }
        .offer {
            border-radius: 18px;
            background: rgba(255,255,255,.1);
            padding: 16px;
            border: 1px solid rgba(255,255,255,.12);
        }
        .offer-title { font-size: 15px; font-weight: 800; margin: 0 0 6px; }
        .offer-subtitle { font-size: 13px; line-height: 1.5; color: rgba(255,255,255,.78); margin: 0 0 10px; }
        .offer-price { font-size: 13px; font-weight: 800; color: #bfdbfe; }
        .footer { margin-top: 20px; font-size: 12px; color: rgba(255,255,255,.68); }
    </style>
</head>
<body>
    <div class="widget">
        <span class="eyebrow">Indicação oficial</span>
        <h1 class="title">{{ $landingPage['hero']['title'] ?? ($branding['hero_title'] ?? 'Conheça a UNN') }}</h1>
        <p class="subtitle">{{ $landingPage['hero']['subtitle'] ?? ($branding['hero_subtitle'] ?? 'Use o convite oficial para acessar a comunidade.') }}</p>

        <div class="brand">
            @if(!empty($branding['logo_url']))
                <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['site_name'] ?? 'Marca' }}">
            @endif
            <div>
                <div style="font-size:12px; text-transform:uppercase; letter-spacing:.18em; color:rgba(255,255,255,.64); font-weight:800;">Afiliado</div>
                <div style="font-size:18px; font-weight:900;">{{ $affiliate->name }}</div>
            </div>
        </div>

        @if($offers->isNotEmpty())
            <div class="offers">
                @foreach($offers as $offer)
                    <div class="offer">
                        <p class="offer-title">{{ $offer['title'] }}</p>
                        <p class="offer-subtitle">{{ $offer['subtitle'] }}</p>
                        <div class="offer-price">{{ $offer['price_label'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        <a href="{{ $ctaUrl }}" target="_blank" rel="noopener noreferrer" class="cta">
            {{ $landingPage['hero']['cta_label'] ?? 'Quero acessar agora' }}
        </a>

        <div class="footer">{{ $referral['short_label'] ?? ($affiliate->referral_code ?? '') }}</div>
    </div>
</body>
</html>
