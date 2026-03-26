<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerStore;
use App\Services\Marketplace\SellerStoreService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MarketplaceStoreController extends Controller
{
    public function edit(SellerStoreService $storeService)
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('admin.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $store = $storeService->ensureForUser(auth()->user());

        return view('admin.marketplace.store', [
            'store' => $store,
            'storeUrl' => $store->slug ? route('seller-stores.show', $store->slug) : null,
        ]);
    }

    public function update(Request $request, SellerStoreService $storeService)
    {
        if (!SellerStore::tableAvailable()) {
            return redirect()
                ->route('admin.marketplace.index')
                ->with('error', 'O modulo da loja virtual ainda nao foi instalado. Rode php artisan migrate.');
        }

        $user = auth()->user();
        $store = $storeService->ensureForUser($user);

        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'slug' => [
                'nullable',
                'string',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('seller_stores', 'slug')->ignore($store->id),
            ],
            'bio' => ['nullable', 'string', 'max:5000'],
            'support_email' => ['nullable', 'email', 'max:120'],
            'support_phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:7', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'string', 'max:7', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'is_published' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'banner' => ['nullable', 'image', 'max:6144'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],
        ], [
            'slug.regex' => 'Use apenas letras minusculas, numeros e hifens no slug da loja.',
            'primary_color.regex' => 'Informe uma cor hexadecimal valida no formato #RRGGBB.',
            'accent_color.regex' => 'Informe uma cor hexadecimal valida no formato #RRGGBB.',
        ]);

        $slug = Str::slug((string) ($data['slug'] ?? ''));
        if ($slug !== '' && $storeService->isReservedSlug($slug)) {
            return back()->withErrors(['slug' => 'Esse slug da loja e reservado pela plataforma.'])->withInput();
        }

        if ($store->isSlugLocked() && $slug !== '' && $slug !== $store->slug) {
            return back()->withErrors(['slug' => 'O slug da loja nao pode ser alterado apos a primeira publicacao.'])->withInput();
        }

        $publishRequested = $request->boolean('is_published');
        if ($publishRequested && $slug === '' && blank($store->slug)) {
            return back()->withErrors(['slug' => 'Informe e confirme um slug unico antes de publicar a loja.'])->withInput();
        }

        if ($request->boolean('remove_logo') && $store->logo_path) {
            UploadStorage::disk()->delete(UploadStorage::normalizePath($store->logo_path));
            $store->logo_path = null;
        }

        if ($request->boolean('remove_banner') && $store->banner_path) {
            UploadStorage::disk()->delete(UploadStorage::normalizePath($store->banner_path));
            $store->banner_path = null;
        }

        if ($request->hasFile('logo')) {
            $data['logo_path'] = UploadStorage::storeUploadedFile($request->file('logo'), 'uploads/imagens/marketplace/stores/logos');
        }

        if ($request->hasFile('banner')) {
            $data['banner_path'] = UploadStorage::storeUploadedFile($request->file('banner'), 'uploads/imagens/marketplace/stores/banners');
        }

        $store->fill([
            'brand_name' => $data['brand_name'],
            'tagline' => $data['tagline'] ?? null,
            'bio' => $data['bio'] ?? null,
            'support_email' => $data['support_email'] ?? $user->email,
            'support_phone' => $data['support_phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'instagram_url' => $data['instagram_url'] ?? null,
            'facebook_url' => $data['facebook_url'] ?? null,
            'youtube_url' => $data['youtube_url'] ?? null,
            'primary_color' => $this->normalizeHexColor($data['primary_color'] ?? null, '#1F5EDB'),
            'accent_color' => $this->normalizeHexColor($data['accent_color'] ?? null, '#0F172A'),
            'is_published' => $publishRequested,
        ]);

        if ($slug !== '' && blank($store->slug)) {
            $store->slug = $slug;
        }

        if ($publishRequested && !$store->published_at) {
            $store->published_at = now();
        }

        if ($publishRequested && !$store->slug_locked_at) {
            $store->slug_locked_at = now();
        }

        if (!$publishRequested) {
            $store->published_at = null;
        }

        if (isset($data['logo_path'])) {
            $store->logo_path = $data['logo_path'];
        }

        if (isset($data['banner_path'])) {
            $store->banner_path = $data['banner_path'];
        }

        $store->save();

        return redirect()
            ->route('admin.marketplace.store.edit')
            ->with('success', 'Configuracoes da loja atualizadas com sucesso.');
    }

    private function normalizeHexColor(?string $value, string $fallback): string
    {
        $cleaned = strtoupper(ltrim(trim((string) $value), '#'));

        if (!preg_match('/^[0-9A-F]{6}$/', $cleaned)) {
            return $fallback;
        }

        return '#' . $cleaned;
    }
}
