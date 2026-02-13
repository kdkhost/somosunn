<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CMSController extends Controller
{
    private const ALLOWED_SLUGS = [
        'home',
        'about',
        'footer',
        'institucional_sobre',
        'institucional_manifesto',
        'institucional_quem_somos',
        'institucional_como_funciona',
        'institucional_valores',
        'institucional_contato',
    ];

    public function index(Request $request, ?string $slug = null)
    {
        $slug = $this->normalizeSlug($slug ?: (string) $request->query('slug', 'home'));
        $contents = SiteContent::query()->where('slug', $slug)->pluck('value', 'key')->toArray();

        return view('admin.cms.index', [
            'slug' => $slug,
            'contents' => $contents,
        ]);
    }

    public function update(Request $request, string $slug)
    {
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
                    if ($currentPath) {
                        Storage::disk('public')->delete($currentPath);
                    }
                    SiteContent::putValue($slug, $field, null, 'image');
                    continue;
                }

                if ($request->hasFile($field)) {
                    if ($currentPath) {
                        Storage::disk('public')->delete($currentPath);
                    }

                    $path = $request->file($field)->store('site-content/' . $slug, 'public');
                    SiteContent::putValue($slug, $field, $path, 'image');
                }

                continue;
            }

            SiteContent::putValue($slug, $field, (string) ($validated[$field] ?? ''), $type);
        }

        return redirect()
            ->route('admin.cms.index', ['slug' => $slug])
            ->with('success', 'Conteudo salvo com sucesso.');
    }

    public function uploadMedia(Request $request)
    {
        $request->validate([
            'slug' => ['nullable', 'string', 'max:120'],
            'file' => ['required', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp,gif,svg'],
        ]);

        $slug = $this->normalizeSlug((string) $request->input('slug', 'home'));
        $path = $request->file('file')->store('site-content/cms/' . $slug, 'public');

        return response()->json([
            'url' => asset('storage/' . ltrim($path, '/')),
            'path' => $path,
        ]);
    }

    private function normalizeSlug(string $slug): string
    {
        return in_array($slug, self::ALLOWED_SLUGS, true) ? $slug : 'home';
    }

    private function fieldsFor(string $slug): array
    {
        return match ($slug) {
            'institucional_sobre',
            'institucional_manifesto',
            'institucional_quem_somos',
            'institucional_como_funciona',
            'institucional_valores',
            'institucional_contato' => [
                'title' => 'text',
                'meta_title' => 'text',
                'meta_description' => 'text',
                'meta_keywords' => 'text',
                'canonical' => 'text',
                'meta_robots' => 'text',
                'og_type' => 'text',
                'twitter_card' => 'text',
                'meta_image' => 'image',
                'twitter_image' => 'image',
                'body' => 'html',
            ],
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
                'hero_text' => 'text',
                'hero_image' => 'image',
            ],
        };
    }
}
