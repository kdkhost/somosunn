<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\SellerStore;
use App\Models\User;
use App\Services\Marketplace\SellerStoreService;
use App\Support\UploadStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(
        private readonly SellerStoreService $sellerStoreService
    ) {
    }

    public function paginatedForAdmin(int $perPage = 15): LengthAwarePaginator
    {
        if (!Company::tableAvailable()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return Company::query()
            ->withCount(['memberships', 'sponsors'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function assignableUsers(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'company']);
    }

    public function save(array $data, ?Company $company = null): Company
    {
        $company ??= new Company();

        $members = collect($data['members'] ?? [])->values();
        unset($data['members']);

        $data['slug'] = $this->resolveSlug(
            $data['slug'] ?? null,
            $data['name'] ?? '',
            $company->id
        );

        $data['verified'] = (bool) ($data['verified'] ?? false);
        $data['active'] = (bool) ($data['active'] ?? false);

        $removeLogo = (bool) ($data['remove_logo'] ?? false);
        $removeBanner = (bool) ($data['remove_banner'] ?? false);

        if ($removeLogo && $company->logo) {
            UploadStorage::delete($company->logo);
            $data['logo'] = null;
        }

        if ($removeBanner && $company->banner) {
            UploadStorage::delete($company->banner);
            $data['banner'] = null;
        }

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            if ($company->logo) {
                UploadStorage::delete($company->logo);
            }
            $data['logo'] = UploadStorage::storeUploadedFile($data['logo'], 'companies/logos');
        } elseif (!$removeLogo) {
            unset($data['logo']);
        }

        if (($data['banner'] ?? null) instanceof UploadedFile) {
            if ($company->banner) {
                UploadStorage::delete($company->banner);
            }
            $data['banner'] = UploadStorage::storeUploadedFile($data['banner'], 'companies/banners');
        } elseif (!$removeBanner) {
            unset($data['banner']);
        }

        unset($data['remove_logo'], $data['remove_banner']);

        $company->fill($data);
        $company->save();

        $this->syncMembers($company, $members);

        return $company->fresh(['memberships.user', 'activeSponsor.plan']);
    }

    public function publicProfileBySlug(string $slug): ?array
    {
        if (!Company::tableAvailable()) {
            return null;
        }

        $company = Company::query()
            ->with([
                'memberships.user',
                'activeSponsor.plan',
                'activeSponsor.banners',
            ])
            ->where('slug', $slug)
            ->where('active', true)
            ->first();

        if (!$company) {
            return null;
        }

        $ownerMembership = $company->memberships->firstWhere('role', CompanyUser::ROLE_OWNER)
            ?? $company->memberships->first();
        $owner = $ownerMembership?->user;

        $store = null;
        $storefront = [
            'products' => collect(),
            'courses' => collect(),
            'mentorships' => collect(),
            'events' => collect(),
        ];

        if ($owner && SellerStore::tableAvailable()) {
            $store = SellerStore::query()
                ->where('user_id', $owner->id)
                ->where('is_published', true)
                ->where('is_blocked', false)
                ->first();

            if ($store) {
                $storefront = $this->sellerStoreService->storefrontPayload($store);
            }
        }

        return compact('company', 'owner', 'store', 'storefront');
    }

    private function syncMembers(Company $company, Collection $members): void
    {
        if (!CompanyUser::tableAvailable()) {
            return;
        }

        $syncPayload = [];

        foreach ($members as $member) {
            $userId = (int) data_get($member, 'user_id');
            $role = (string) data_get($member, 'role', CompanyUser::ROLE_STAFF);

            if ($userId <= 0) {
                continue;
            }

            $syncPayload[$userId] = [
                'role' => in_array($role, [CompanyUser::ROLE_OWNER, CompanyUser::ROLE_MANAGER, CompanyUser::ROLE_STAFF], true)
                    ? $role
                    : CompanyUser::ROLE_STAFF,
            ];
        }

        $company->users()->sync($syncPayload);
    }

    private function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name) ?: 'empresa';
        $candidate = $base;
        $suffix = 2;

        while (
            Company::query()
                ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
