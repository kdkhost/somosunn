<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SponsorBannerRequest;
use App\Models\Sponsor;
use App\Models\SponsorBanner;
use App\Services\SponsorBannerService;

class SponsorBannerController extends Controller
{
    public function __construct(
        private readonly SponsorBannerService $bannerService
    ) {
    }

    public function index()
    {
        $banners = $this->bannerService->paginated();

        return view('admin.sponsors.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.sponsors.banners.form', [
            'banner' => new SponsorBanner(),
            'sponsors' => Sponsor::query()->with('company')->orderByDesc('id')->get(),
        ]);
    }

    public function store(SponsorBannerRequest $request)
    {
        $banner = $this->bannerService->save($request->validated());

        return redirect()
            ->route('admin.sponsor-banners.edit', $banner)
            ->with('success', 'Banner de patrocinio criado com sucesso.');
    }

    public function edit(SponsorBanner $sponsorBanner)
    {
        return view('admin.sponsors.banners.form', [
            'banner' => $sponsorBanner,
            'sponsors' => Sponsor::query()->with('company')->orderByDesc('id')->get(),
        ]);
    }

    public function update(SponsorBannerRequest $request, SponsorBanner $sponsorBanner)
    {
        $banner = $this->bannerService->save($request->validated(), $sponsorBanner);

        return redirect()
            ->route('admin.sponsor-banners.edit', $banner)
            ->with('success', 'Banner de patrocinio atualizado com sucesso.');
    }

    public function destroy(SponsorBanner $sponsorBanner)
    {
        if ($sponsorBanner->image) {
            \App\Support\UploadStorage::delete($sponsorBanner->image);
        }

        $sponsorBanner->delete();

        return redirect()
            ->route('admin.sponsor-banners.index')
            ->with('success', 'Banner removido com sucesso.');
    }
}
