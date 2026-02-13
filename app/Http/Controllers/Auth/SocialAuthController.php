<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    protected $providers = ['google','facebook','linkedin'];

    public function redirect($provider)
    {
        if (!in_array($provider, $this->providers, true)) {
            abort(404);
        }
        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, $provider)
    {
        if (!in_array($provider, $this->providers, true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            Log::warning('Social login callback failed: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Não foi possível autenticar com o provedor informado. Tente novamente.');
        }

        $email = $socialUser->getEmail();
        if (!$email) {
            return redirect()->route('login')->with('error', 'Seu provedor não informou um e-mail válido. Use outro método de login.');
        }

        $providerIdField = $provider . '_id';
        $providerId = (string) $socialUser->getId();

        $user = User::where($providerIdField, $providerId)->first();

        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        if (!$user) {
            $user = new User();
            $user->name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário';
            $user->email = $email;
            $user->email_verified_at = now();
            $user->password = bcrypt(bin2hex(random_bytes(16)));
        }

        if (!$user->{$providerIdField}) {
            $user->{$providerIdField} = $providerId;
        }

        // Preenche dados automaticamente (sem sobrescrever customizações do usuário)
        if (!$user->name) {
            $user->name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuário';
        }

        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
        }

        $user->save();

        // Vincula pacote inicial (se ainda não tiver plano ativo)
        if (!$user->activePlan()) {
            $defaultPlan = $this->resolveDefaultPlan();
            if ($defaultPlan) {
                $user->plan_id = (int) $defaultPlan->id;
                $user->plan_expires_at = null;
                $user->save();
            }
        }

        // Avatar do provedor (salva apenas se ainda não tiver foto definida)
        $this->trySaveSocialAvatar($user, $socialUser->getAvatar());

        Auth::login($user, true);

        $defaultRedirect = $user->isAdmin() ? route('admin.dashboard') : route('panel.dashboard');
        return redirect()->intended($defaultRedirect);
    }

    private function resolveDefaultPlan(): ?Plan
    {
        try {
            $plan = Plan::query()->where('slug', 'cliente')->first();
            if ($plan) {
                return $plan;
            }

            return Plan::query()->orderBy('price')->orderBy('id')->first();
        } catch (\Throwable $e) {
            return null;
        }
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
            // best-effort: não quebra login se avatar falhar
            return;
        }
    }
}
