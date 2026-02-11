<?php

namespace App\Services\Mail;

use App\Models\Setting;

class SystemMailLayoutData
{
    /**
     * Returns the default layout variables used across system emails.
     *
     * @param array<string,mixed> $overrides
     * @return array{siteName:string,logoUrl:string,primaryColor:string,secondaryColor:string}
     */
    public function make(array $overrides = []): array
    {
        $siteName = (string) (Setting::get('app_name') ?: Setting::get('company_name') ?: config('app.name', 'UNN'));

        $logo = Setting::get('logo_admin') ?: Setting::get('logo_front') ?: Setting::get('logo_image');
        $logoUrl = $logo ? asset(ltrim((string) $logo, '/')) : asset('img/logo.svg');

        $primaryColor = (string) (Setting::get('site_color_primary') ?: '#1F5EDB');
        $secondaryColor = (string) (Setting::get('site_color_secondary') ?: '#177FD6');

        return array_merge([
            'siteName' => $siteName,
            'logoUrl' => $logoUrl,
            'primaryColor' => $primaryColor,
            'secondaryColor' => $secondaryColor,
        ], $overrides);
    }
}

