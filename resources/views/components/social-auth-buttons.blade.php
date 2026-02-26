@props([
    'withDivider' => true,
])

@php
    $socialLoginEnabled = (string) \App\Models\Setting::get('social_login_enabled', '1') === '1';

    $googleEnabled = (string) \App\Models\Setting::get('social_google_enabled', \App\Models\Setting::get('social_google_active', '0')) === '1';
    $facebookEnabled = (string) \App\Models\Setting::get('social_facebook_enabled', \App\Models\Setting::get('social_facebook_active', '0')) === '1';
    $linkedinEnabled = (string) \App\Models\Setting::get('social_linkedin_enabled', \App\Models\Setting::get('social_linkedin_active', '0')) === '1';

    $providerReady = function (string $provider): bool {
        $prefix = 'social_' . $provider . '_';

        $clientId = trim((string) \App\Models\Setting::get($prefix . 'client_id', ''));
        $clientSecret = trim((string) \App\Models\Setting::get($prefix . 'client_secret', ''));

        if ($provider === 'facebook') {
            if ($clientId === '') {
                $clientId = trim((string) \App\Models\Setting::get('social_facebook_app_id', ''));
            }
            if ($clientSecret === '') {
                $clientSecret = trim((string) \App\Models\Setting::get('social_facebook_app_secret', ''));
            }
        }

        if ($clientId === '') {
            $clientId = trim((string) config('services.' . $provider . '.client_id', ''));
        }
        if ($clientSecret === '') {
            $clientSecret = trim((string) config('services.' . $provider . '.client_secret', ''));
        }

        return $clientId !== '' && $clientSecret !== '';
    };

    $providers = [];

    if ($socialLoginEnabled && $googleEnabled && $providerReady('google')) {
        $providers[] = [
            'key' => 'google',
            'label' => 'Google',
            'icon' => 'fab fa-google',
            'icon_class' => 'text-rose-600',
        ];
    }

    if ($socialLoginEnabled && $facebookEnabled && $providerReady('facebook')) {
        $providers[] = [
            'key' => 'facebook',
            'label' => 'Facebook',
            'icon' => 'fab fa-facebook-f',
            'icon_class' => 'text-blue-600',
        ];
    }

    if ($socialLoginEnabled && $linkedinEnabled && $providerReady('linkedin')) {
        $providers[] = [
            'key' => 'linkedin',
            'label' => 'LinkedIn',
            'icon' => 'fab fa-linkedin-in',
            'icon_class' => 'text-sky-700',
        ];
    }
@endphp

@if(count($providers) > 0)
    <div {{ $attributes->merge(['class' => 'space-y-4']) }}>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($providers as $provider)
                <a href="{{ route('social.redirect', $provider['key']) }}"
                    class="inline-flex items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <i class="{{ $provider['icon'] }} {{ $provider['icon_class'] }}"></i>
                    <span>Continuar com {{ $provider['label'] }}</span>
                </a>
            @endforeach
        </div>

        @if($withDivider)
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-slate-400 font-semibold">ou</span>
                </div>
            </div>
        @endif
    </div>
@endif
