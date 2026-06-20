<?php

namespace App\Services;

use App\Models\SponsorBanner;
use App\Support\UploadStorage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class SponsorBannerService
{
    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        if (!SponsorBanner::tableAvailable()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        }

        return SponsorBanner::query()
            ->with(['sponsor.company'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function save(array $data, ?SponsorBanner $banner = null): SponsorBanner
    {
        $banner ??= new SponsorBanner();

        $removeImage = (bool) ($data['remove_image'] ?? false);

        if ($removeImage && $banner->image) {
            UploadStorage::delete($banner->image);
            $data['image'] = null;
        }

        if (($data['image'] ?? null) instanceof UploadedFile) {
            if ($banner->image) {
                UploadStorage::delete($banner->image);
            }
            $data['image'] = UploadStorage::storeUploadedFile($data['image'], 'sponsors/banners');
        } elseif (!$removeImage) {
            unset($data['image']);
        }

        $banner->fill($data);
        $banner->active = (bool) ($data['active'] ?? false);
        $banner->save();

        return $banner->fresh(['sponsor.company']);
    }
}
