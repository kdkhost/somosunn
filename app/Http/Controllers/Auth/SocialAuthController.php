<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\SanitizesIntendedRedirect;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;
use App\Services\AffiliateTrackingService;
use App\Services\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthController extends Controller
{
    use SanitizesIntendedRedirect;

    private array $providers = ['google', 'facebook', 'linkedin'];

    public function redirect(Request $request, string $provider, AffiliateTrackingService $tracking)
    {
        if (!$this->isSupportedProvider($provider)) {
            abort(404);
        }

        if (!$this->isProviderEnabled($provider)) {
            return $this->socialUnavailable($provider, 'Login social desativado para este provedor.');
        }

        // Preserva código de indicação para usar após o callback
        $refCode = trim((string) $request->query('ref', $tracking->currentReferralCode($request) ?: session('social_ref', '')));
        if ($refCode !== '') {
            session(['social_ref' => $refCode]);
        }

        $this->applyProviderRuntimeConfig($provider);

        if (!$this->hasProviderCredentials($provider)) {
            Log::warning('Social provider missing credentials on redirect.', ['provider' => $provider]);

            return $this->socialUnavailable($provider, 'Login social indisponivel no momento. Tente novamente em instantes.');
        }

        try {
            $driver = Socialite::driver($provider);
            if ($provider === 'facebook') {
                $driver = $driver->scopes(['email']);
            }

            return $driver->redirect();
        } catch (\Throwable $e) {
            Log::warning('Social redirect failed.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return $this->socialUnavailable($provider, 'Nao foi possivel iniciar o login social agora.');
        }
    }

    public function callback(Request $request, string $provider, AffiliateTrackingService $tracking)
    {
        if (!$this->isSupportedProvider($provider)) {
            abort(404);
        }

        if (!$this->isProviderEnabled($provider)) {
            return $this->socialUnavailable($provider, 'Login social desativado para este provedor.');
        }

        $this->applyProviderRuntimeConfig($provider);

        if (!$this->hasProviderCredentials($provider)) {
            Log::warning('Social provider missing credentials on callback.', ['provider' => $provider]);

            return $this->socialUnavailable($provider, 'Login social indisponivel no momento. Tente novamente em instantes.');
        }

        if ($request->filled('error')) {
            Log::warning('Social provider returned an OAuth error.', [
                'provider' => $provider,
                'error' => (string) $request->query('error'),
                'error_description' => (string) $request->query('error_description', ''),
            ]);

            return redirect()->route('login')->with('warning', 'Nao foi possivel concluir o login social. Tente novamente.');
        }

        try {
            $driver = Socialite::driver($provider);
            if ($provider === 'facebook') {
                $driver = $driver->scopes(['email']);
            }

            try {
                $socialUser = $driver->user();
            } catch (InvalidStateException $e) {
                Log::info('Social callback state mismatch. Retrying stateless mode.', ['provider' => $provider]);

                $fallbackDriver = Socialite::driver($provider);
                if ($provider === 'facebook') {
                    $fallbackDriver = $fallbackDriver->scopes(['email']);
                }
                $socialUser = $fallbackDriver->stateless()->user();
            }
        } catch (\Throwable $e) {
            Log::warning('Social login callback failed.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('login')->with('warning', 'Nao foi possivel autenticar com o provedor informado. Tente novamente.');
        }

        $email = trim((string) $socialUser->getEmail());
        if ($email === '') {
            return redirect()->route('login')->with('warning', 'Seu provedor nao informou um e-mail valido. Use outro metodo de login.');
        }

        $providerIdField = $provider . '_id';
        $providerId = (string) $socialUser->getId();

        $user = User::where($providerIdField, $providerId)->first();

        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        $isNewUser = ($user === null);

        if ($isNewUser) {
            $user = new User();
            $user->name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuario';
            $user->email = $email;
            $user->email_verified_at = now();
            $user->password = bcrypt(bin2hex(random_bytes(16)));
        }

        if (!$user->{$providerIdField}) {
            $user->{$providerIdField} = $providerId;
        }

        if (!$user->name) {
            $user->name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuario';
        }

        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        // Resolve código de indicação (guardado na sessão durante o redirect)
        $referrer = null;
        if ($isNewUser) {
            $refCode = trim((string) session('social_ref', $tracking->currentReferralCode($request) ?: ''));
            session()->forget('social_ref');
            if ($refCode !== '') {
                $referrer = $tracking->resolveReferrerByCode($refCode);
                if ($referrer && (int) $referrer->id !== (int) $user->id) {
                    $user->referred_by = $referrer->id;
                }
            }
        }

        try {
            $user->save();
        } catch (\Illuminate\Database\QueryException $e) {
            // Violação de unicidade de e-mail: pode ser que outro usuário
            // já tem este e-mail — faz login no usuário existente ao invés de criar um novo
            if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate')) {
                $existing = User::where('email', $email)->first();
                if ($existing) {
                    if (!$existing->{$providerIdField}) {
                        $existing->{$providerIdField} = $providerId;
                        $existing->save();
                    }
                    $this->trySaveSocialAvatar($existing, $socialUser->getAvatar());
                    Auth::login($existing, true);
                    return $this->redirectToSafeIntended(
                        $request,
                        route($existing->isAdmin() ? 'panel.admin.dashboard' : 'panel.dashboard')
                    );
                }
            }
            Log::error('Social login user save failed.', ['provider' => $provider, 'email' => $email, 'error' => $e->getMessage()]);
            return redirect()->route('login')->with('warning', 'Nao foi possivel concluir o cadastro via login social. Tente com e-mail e senha.');
        }

        if ($isNewUser) {
            $tracking->attachRegisteredUser($request, $user, $referrer?->referral_code ?? null);
        }

        if (!$user->activePlan()) {
            $defaultPlan = $this->resolveDefaultPlan();
            if ($defaultPlan) {
                $user->plan_id = (int) $defaultPlan->id;
                $user->plan_expires_at = null;
                $user->save();
            }
        }

        // Gamificação: pontos de cadastro
        // Nota: pontos de referral para o indicador são dados SOMENTE após pagamento de plano
        //       (ver PaymentWebhookController::activatePlanForOrder)
        if ($isNewUser) {
            try {
                (new PointsService())->award($user, 'signup');
            } catch (\Throwable $e) {
                Log::warning('Social signup points award failed.', ['error' => $e->getMessage()]);
            }
        }

        $this->trySaveSocialAvatar($user, $socialUser->getAvatar());

        Auth::login($user, true);

        // Novo usuário → encaminhar para escolha de plano
        if ($isNewUser) {
            return redirect()->route('premium')->with('success', 'Conta criada com sucesso! Escolha um plano para aproveitar ao máximo a plataforma.');
        }

        $redirectRoute = $user->isAdmin() ? 'panel.admin.dashboard' : 'panel.dashboard';

        return $this->redirectToSafeIntended($request, route($redirectRoute));
    }

    private function resolveDefaultPlan(): ?Plan
    {
        return Plan::getFreePlan();
    }

    private function trySaveSocialAvatar(User $user, ?string $avatarUrl): void
    {
        try {
            if (!$avatarUrl || $user->photo) {
                return;
            }

            $response = Http::timeout(10)->get($avatarUrl);
            if (!$response->ok()) {
                return;
            }

            $body = $response->body();
            if ($body === '' || strlen($body) > (5 * 1024 * 1024)) {
                return;
            }

            $contentType = strtolower((string) $response->header('Content-Type'));
            $ext = 'jpg';
            if (str_contains($contentType, 'png')) {
                $ext = 'png';
            } elseif (str_contains($contentType, 'webp')) {
                $ext = 'webp';
            } elseif (str_contains($contentType, 'gif')) {
                $ext = 'gif';
            } elseif (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) {
                $ext = 'jpg';
            }

            $directory = public_path('uploads/imagens/avatars');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $filename = 'avatar_social_' . $user->id . '_' . time() . '.' . $ext;
            $relativePath = 'uploads/imagens/avatars/' . $filename;
            $fullPath = $directory . DIRECTORY_SEPARATOR . $filename;

            file_put_contents($fullPath, $body);

            if (file_exists($fullPath)) {
                $user->photo = $relativePath;
                $user->save();
            }
        } catch (\Throwable $e) {
            return;
        }
    }

    private function isSupportedProvider(string $provider): bool
    {
        return in_array($provider, $this->providers, true);
    }

    private function isProviderEnabled(string $provider): bool
    {
        $socialLoginEnabled = $this->isEnabled(Setting::get('social_login_enabled', '1'));
        if (!$socialLoginEnabled) {
            return false;
        }

        return match ($provider) {
            'google' => $this->isEnabled(Setting::get('social_google_enabled', Setting::get('social_google_active', '0'))),
            'facebook' => $this->isEnabled(Setting::get('social_facebook_enabled', Setting::get('social_facebook_active', '0'))),
            'linkedin' => $this->isEnabled(Setting::get('social_linkedin_enabled', Setting::get('social_linkedin_active', '0'))),
            default => false,
        };
    }

    private function isEnabled(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function applyProviderRuntimeConfig(string $provider): void
    {
        $prefix = 'social_' . $provider . '_';
        $redirect = trim((string) Setting::get($prefix . 'redirect', ''));

        $clientId = trim((string) Setting::get($prefix . 'client_id', ''));
        $clientSecret = trim((string) Setting::get($prefix . 'client_secret', ''));

        if ($provider === 'facebook') {
            if ($clientId === '') {
                $clientId = trim((string) Setting::get('social_facebook_app_id', ''));
            }
            if ($clientSecret === '') {
                $clientSecret = trim((string) Setting::get('social_facebook_app_secret', ''));
            }
        }

        if ($clientId !== '') {
            Config::set("services.{$provider}.client_id", $clientId);
        }
        if ($clientSecret !== '') {
            Config::set("services.{$provider}.client_secret", $clientSecret);
        }

        if ($redirect !== '') {
            Config::set("services.{$provider}.redirect", $redirect);
        }
    }

    private function hasProviderCredentials(string $provider): bool
    {
        $serviceConfig = (array) Config::get("services.{$provider}", []);

        $clientId = trim((string) Arr::get($serviceConfig, 'client_id', ''));
        $clientSecret = trim((string) Arr::get($serviceConfig, 'client_secret', ''));
        $redirect = trim((string) Arr::get($serviceConfig, 'redirect', ''));

        return $clientId !== '' && $clientSecret !== '' && $redirect !== '';
    }

    private function socialUnavailable(string $provider, string $message)
    {
        $providerLabel = match ($provider) {
            'google' => 'Google',
            'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn',
            default => ucfirst($provider),
        };

        return redirect()
            ->route('login')
            ->with('warning', "{$providerLabel}: {$message}");
    }
}
