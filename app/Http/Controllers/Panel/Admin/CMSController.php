<?php

namespace App\Http\Controllers\Panel\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CMSController extends Controller
{
    private const ALLOWED_SLUGS = ['home', 'about', 'footer'];

    public function index(Request $request, ?string $slug = null)
    {
        $this->ensurePermission('cms.view');

        $slug = $this->normalizeSlug($slug ?: (string) $request->query('slug', 'home'));
        $contents = SiteContent::query()->where('slug', $slug)->pluck('value', 'key')->toArray();

        return view('panel.admin.cms.index', [
            'slug' => $slug,
            'contents' => $contents,
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $this->ensurePermission('cms.edit');

        $slug = $this->normalizeSlug($slug);
        $fields = $this->fieldsFor($slug);
        $rules = [];

        foreach ($fields as $field => $type) {
            if ($type === 'image') {
                $rules[$field] = ['nullable', 'image', 'max:6144'];
                $rules['remove_' . $field] = ['nullable', 'boolean'];
            } else {
                $rules[$field] = ['nullable', 'string'];
            }
        }

        $validated = $request->validate($rules);

        foreach ($fields as $field => $type) {
            if ($type === 'image') {
                $removeKey = 'remove_' . $field;
                $currentPath = SiteContent::getValue($slug, $field);

                if ($request->boolean($removeKey)) {
                    if ($currentPath)
                        Storage::disk('public')->delete($currentPath);
                    SiteContent::putValue($slug, $field, null, 'image');
                    continue;
                }

                if ($request->hasFile($field)) {
                    if ($currentPath)
                        Storage::disk('public')->delete($currentPath);
                    $path = $request->file($field)->store('site-content/' . $slug, 'public');
                    SiteContent::putValue($slug, $field, $path, 'image');
                }
                continue;
            }

            SiteContent::putValue($slug, $field, (string) ($validated[$field] ?? ''), 'text');
        }

        return redirect()->route('panel.admin.cms.index', ['slug' => $slug])->with('success', 'Conteúdo atualizado com sucesso.');
    }

    private function normalizeSlug(string $slug): string
    {
        return in_array($slug, self::ALLOWED_SLUGS, true) ? $slug : 'home';
    }

    private function fieldsFor(string $slug): array
    {
        return match ($slug) {
            'about' => [
                'manifesto' => 'text',
                'vision' => 'text',
                'values' => 'text',
            ],
            'footer' => [
                'instagram_url' => 'text',
                'linkedin_url' => 'text',
                'youtube_url' => 'text',
                'facebook_url' => 'text',
            ],
            default => [
                'hero_title' => 'text',
                'hero_subtitle' => 'text',
                'hero_image' => 'image',
            ],
        };
    }

    private function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm)) {
            abort(403);
        }
    }
}
