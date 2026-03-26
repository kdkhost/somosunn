<?php

namespace App\Services\Marketplace;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Models\Setting;
use App\Models\User;
use App\Support\ContentVisibility;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SellerStoreService
{
    private const RESERVED_SLUGS = [
        'carrinho',
        'checkout',
        'admin',
        'painel',
        'marketplace',
        'produto',
        'produtos',
        'p',
    ];

    public function ensureForUser(User $user): SellerStore
    {
        abort_unless(SellerStore::tableAvailable(), 503, 'O modulo da loja virtual ainda nao foi instalado. Rode as migrations do sistema.');

        $defaults = $this->defaultStoreAttributesFor($user);

        $store = SellerStore::query()->firstOrCreate(
            ['user_id' => $user->id],
            $defaults
        );

        if ($user->isSuperAdmin()) {
            $dirty = false;

            if (!$store->isPlatformStore()) {
                $store->is_platform_store = true;
                $dirty = true;
            }

            if (blank($store->slug) && !$store->isSlugLocked()) {
                $store->slug = $this->generateDefaultSlugFor($user, $store);
                $dirty = true;
            }

            foreach (['brand_name', 'support_email', 'support_phone', 'primary_color', 'accent_color'] as $field) {
                if (blank($store->{$field}) && filled($defaults[$field] ?? null)) {
                    $store->{$field} = $defaults[$field];
                    $dirty = true;
                }
            }

            if ($dirty) {
                $store->save();
            }
        }

        return $store;
    }

    public function isReservedSlug(?string $slug): bool
    {
        $slug = trim((string) $slug);

        return $slug !== '' && in_array($slug, self::RESERVED_SLUGS, true);
    }

    public function isEligible(?User $user, ?SellerStore $store = null): bool
    {
        if ($this->isPlatformStoreForSuperAdmin($store, $user)) {
            return true;
        }

        return $user instanceof User
            && $user->canSellOnMarketplace()
            && (bool) $user->activePlan();
    }

    public function isPubliclyAvailable(?SellerStore $store): bool
    {
        if (!SellerStore::tableAvailable()) {
            return false;
        }

        return $store instanceof SellerStore
            && $store->is_published
            && !$store->is_blocked
            && $store->slug !== null
            && $this->isEligible($store->user, $store);
    }

    public function publishedStoresByUserIds(iterable $userIds): Collection
    {
        if (!SellerStore::tableAvailable()) {
            return collect();
        }

        $ids = collect($userIds)
            ->map(fn($id) => (int) $id)
            ->filter(fn(int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return SellerStore::query()
            ->with('user')
            ->whereIn('user_id', $ids->all())
            ->where('is_published', true)
            ->where('is_blocked', false)
            ->whereNotNull('slug')
            ->get()
            ->filter(fn(SellerStore $store) => $this->isPubliclyAvailable($store))
            ->keyBy('user_id');
    }

    public function storefrontPayload(SellerStore $store): array
    {
        if (!SellerStore::tableAvailable() || !SellerProduct::tableAvailable()) {
            return [
                'products' => collect(),
                'courses' => collect(),
                'mentorships' => collect(),
                'events' => collect(),
            ];
        }

        $store->loadMissing('user', 'products.media', 'products.redeemableItem');

        $products = $store->products()
            ->with(['media', 'redeemableItem'])
            ->published()
            ->get()
            ->filter(fn(SellerProduct $product) => !$product->store->is_blocked)
            ->values();

        $courses = ContentVisibility::applyPublicFilter(
            $store->user->courses()
                ->with('creator')
                ->whereIn('status', ['published', 'paused']),
            'courses'
        )->latest('id')->get();

        $mentorships = ContentVisibility::applyPublicFilter(
            Mentorship::query()
                ->with('mentor')
                ->where('mentor_id', $store->user_id),
            'mentorships'
        )->latest('id')->get()->filter(fn(Mentorship $mentorship) => $mentorship->hasPublicAction())->values();

        $events = ContentVisibility::applyPublicFilter(
            Event::query()
                ->with('user')
                ->where('user_id', $store->user_id)
                ->where('published', true),
            'events'
        )->publicUpcoming()->orderBy('start_at')->get();

        return compact('products', 'courses', 'mentorships', 'events');
    }

    private function defaultStoreAttributesFor(User $user): array
    {
        $isPlatformStore = $user->isSuperAdmin();

        return [
            'is_platform_store' => $isPlatformStore,
            'slug' => $isPlatformStore ? $this->generateDefaultSlugFor($user) : null,
            'brand_name' => $this->defaultBrandNameFor($user, $isPlatformStore),
            'tagline' => $isPlatformStore ? $this->defaultPlatformTagline() : null,
            'support_email' => $this->defaultSupportEmailFor($user, $isPlatformStore),
            'support_phone' => $this->defaultSupportPhoneFor($user, $isPlatformStore),
            'primary_color' => $this->defaultPrimaryColorFor($isPlatformStore),
            'accent_color' => '#0F172A',
        ];
    }

    private function defaultBrandNameFor(User $user, bool $isPlatformStore): string
    {
        if ($isPlatformStore) {
            return trim((string) (Setting::get('app_name') ?: config('app.name', 'Loja Oficial UNN')));
        }

        return trim((string) ($user->company ?: $user->name ?: 'Loja UNN'));
    }

    private function defaultSupportEmailFor(User $user, bool $isPlatformStore): ?string
    {
        if ($isPlatformStore) {
            return trim((string) (Setting::get('site_contact_email') ?: config('mail.from.address') ?: $user->email)) ?: null;
        }

        return $user->email;
    }

    private function defaultSupportPhoneFor(User $user, bool $isPlatformStore): ?string
    {
        if ($isPlatformStore) {
            return trim((string) (Setting::get('site_contact_phone') ?: $user->phone)) ?: null;
        }

        return $user->phone;
    }

    private function defaultPrimaryColorFor(bool $isPlatformStore): string
    {
        if ($isPlatformStore) {
            return trim((string) (Setting::get('site_color_primary') ?: '#1F5EDB')) ?: '#1F5EDB';
        }

        return '#1F5EDB';
    }

    private function defaultPlatformTagline(): string
    {
        return trim((string) (Setting::get('site_tagline') ?: 'Loja oficial da plataforma.')) ?: 'Loja oficial da plataforma.';
    }

    private function generateDefaultSlugFor(User $user, ?SellerStore $ignoreStore = null): string
    {
        $appName = trim((string) (Setting::get('app_name') ?: config('app.name', 'unn')));
        $base = $user->isSuperAdmin()
            ? (Str::slug($appName ?: 'loja-oficial') ?: 'loja-oficial')
            : (Str::slug((string) ($user->company ?: $user->name ?: 'loja-unn')) ?: 'loja-unn');

        $candidates = array_values(array_unique([
            $base,
            'loja-' . $base,
            $base . '-oficial',
            'loja-oficial',
        ]));

        foreach ($candidates as $candidate) {
            if (!$this->slugExists($candidate, $ignoreStore)) {
                return $candidate;
            }
        }

        $suffix = 2;
        do {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        } while ($this->slugExists($candidate, $ignoreStore));

        return $candidate;
    }

    private function slugExists(string $slug, ?SellerStore $ignoreStore = null): bool
    {
        return SellerStore::query()
            ->where('slug', $slug)
            ->when($ignoreStore?->exists, fn ($query) => $query->where('id', '!=', $ignoreStore->id))
            ->exists();
    }

    private function isPlatformStoreForSuperAdmin(?SellerStore $store, ?User $user = null): bool
    {
        if (!$store instanceof SellerStore || !$store->isPlatformStore()) {
            return false;
        }

        $owner = $user instanceof User ? $user : $store->user;

        return $owner instanceof User && $owner->isSuperAdmin();
    }
}
