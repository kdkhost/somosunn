<?php

namespace App\Services\Marketplace;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\SellerProduct;
use App\Models\SellerStore;
use App\Models\User;
use App\Support\ContentVisibility;
use Illuminate\Support\Collection;

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

        return SellerStore::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'brand_name' => trim((string) ($user->company ?: $user->name ?: 'Loja UNN')),
                'support_email' => $user->email,
                'support_phone' => $user->phone,
                'primary_color' => '#1F5EDB',
                'accent_color' => '#0F172A',
            ]
        );
    }

    public function isReservedSlug(?string $slug): bool
    {
        $slug = trim((string) $slug);

        return $slug !== '' && in_array($slug, self::RESERVED_SLUGS, true);
    }

    public function isEligible(?User $user): bool
    {
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
            && $this->isEligible($store->user);
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
}
