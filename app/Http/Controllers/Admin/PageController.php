<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\CmsPageCatalog;
use App\Support\UploadStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Campos escalares editáveis por slug.
     * Campos de array JSON são tratados separadamente via SLUG_JSON_FIELDS.
     */
    private const SLUG_SCALAR_FIELDS = [
        'home' => [
            'hero_title',
            'hero_subtitle',
            'body',
            'hero_cta_text',
            'hero_cta2_text',
            'stat_1_value',
            'stat_1_label',
            'stat_2_value',
            'stat_2_label',
            'stat_3_value',
            'stat_3_label',
            'stat_4_value',
            'stat_4_label',
            'about_title',
            'about_subtitle',
            'about_card_1_title',
            'about_card_1_text',
            'about_card_2_title',
            'about_card_2_text',
            'about_card_3_title',
            'about_card_3_text',
            'about_card_4_title',
            'about_card_4_text',
            'events_title',
            'events_subtitle',
            'mentorships_title',
            'mentorships_subtitle',
            'community_title',
            'community_beginner_title',
            'community_beginner_desc',
            'community_success_title',
            'community_success_desc',
            'ranking_title',
            'ranking_subtitle',
            'testimonials_title',
            'cta_section_title',
            'cta_section_subtitle',
            'cta_section_btn_primary',
            'cta_section_btn_secondary',
            'seo_title',
            'seo_description',
        ],
        'sobre' => [
            'seo_title',
            'seo_description',
            'hero_title',
            'vision',
            'cta_btn_primary',
            'cta_btn_secondary',
            'stat_1_value',
            'stat_1_label',
            'stat_2_value',
            'stat_2_label',
            'stat_3_value',
            'stat_3_label',
            'stat_4_value',
            'stat_4_label',
            'history_title',
            'history_lead',
            'history_p1',
            'history_p2',
            'diff_title',
            'diff_card_1_title',
            'diff_card_1_text',
            'diff_card_2_title',
            'diff_card_2_text',
            'diff_card_3_title',
            'diff_card_3_text',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'manifesto' => [
            'seo_title',
            'seo_description',
            'hero_title',
            'hero_title_highlight',
            'hero_subtitle',
            'quote_top',
            'section_1_title',
            'section_1_text',
            'section_2_title',
            'section_2_text',
            'section_3_title',
            'section_3_text',
            'section_4_title',
            'section_4_text',
            'section_5_title',
            'section_5_text',
            'quote_bottom',
            'quote_author',
            'pillars_title',
            'pillar_1_title',
            'pillar_2_title',
            'pillar_3_title',
            'pillar_4_title',
            'pillars_link_text',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'valores' => [
            'seo_title',
            'seo_description',
            'hero_subtitle',
            'blockquote_text',
            'blockquote_author',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'como-funciona' => [
            'seo_title',
            'seo_description',
            'hero_subtitle',
            'plans_title',
            'plans_subtitle',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'quem-somos' => [
            'seo_title',
            'seo_description',
            'hero_subtitle',
            'founders_title',
            'team_title',
            'stats_title',
            'stat_1_value',
            'stat_1_label',
            'stat_2_value',
            'stat_2_label',
            'stat_3_value',
            'stat_3_label',
            'stat_4_value',
            'stat_4_label',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'eventos' => [
            'seo_title',
            'seo_description',
            'hero_badge',
            'hero_title',
            'hero_subtitle',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'membros' => [
            'seo_title',
            'seo_description',
            'hero_title',
            'hero_subtitle',
        ],
        'vagas-abertas' => [
            'seo_title',
            'seo_description',
            'hero_badge',
            'hero_title',
            'hero_subtitle',
        ],
        'cursos' => [
            'seo_title',
            'seo_description',
            'hero_badge',
            'hero_title',
            'hero_subtitle',
        ],
        'portal' => [
            'seo_title',
            'seo_description',
            'hero_title',
            'hero_subtitle',
            'stat_1_value',
            'stat_1_label',
            'stat_2_value',
            'stat_2_label',
            'stat_3_value',
            'stat_3_label',
            'stat_4_value',
            'stat_4_label',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'premium' => [
            'seo_title',
            'seo_description',
            'hero_badge',
            'hero_title',
            'hero_subtitle',
            'hero_trust_1',
            'hero_trust_2',
            'plans_title',
            'plans_subtitle',
        ],
        'feed' => ['seo_title', 'seo_description'],
        'somos-unicas' => [
            'seo_title',
            'seo_description',
            'theme_color',
            'hero_title',
            'hero_subtitle',
            'courses_title',
            'courses_subtitle',
            'events_title',
            'events_subtitle',
            'mentorships_title',
            'mentorships_subtitle',
            'empty_title',
            'empty_description'
        ],
        'somos-unicas-sobre' => [
            'seo_title',
            'seo_description',
            'theme_color',
            'hero_title',
            'hero_subtitle',
            'content_title',
            'content_body'
        ],
    ];

    /**
     * Campos de imagem por slug - recebidos via file input no form.
     * O valor armazenado é o path relativo ao disco 'public'.
     */
    private const SLUG_IMAGE_FIELDS = [
        'home' => ['hero_image'],
        'sobre' => ['hero_image'],
        'quem-somos' => ['cover_image'],
        'eventos' => ['hero_image'],
        'cursos' => ['hero_image'],
        'portal' => ['hero_image'],
        'premium' => ['hero_image'],
        'somos-unicas' => ['hero_image', 'networking_image'],
        'somos-unicas-sobre' => ['hero_image', 'networking_image'],
    ];

    /** Campos JSON (arrays) por slug - enviados como textarea JSON no form. */
    private const SLUG_JSON_FIELDS = [
        'home' => ['testimonials'],
        'sobre' => [],
        'manifesto' => [],
        'valores' => ['values'],
        'como-funciona' => ['steps'],
        'quem-somos' => ['founders', 'team'],
        'eventos' => [],
        'membros' => [],
        'vagas-abertas' => [],
        'cursos' => [],
        'portal' => [],
        'premium' => [],
        'feed' => [],
        'somos-unicas' => [],
        'somos-unicas-sobre' => [],
    ];

    public function index(): View
    {
        Page::resetTableAvailabilityCache();

        if (!Page::tableAvailable()) {
            session()->flash('warning', 'A tabela de páginas CMS ainda não existe nesta base. Rode as migrations ou o seeder correspondente para habilitar o gerenciamento.');

            return view('admin.pages.index', [
                'pages' => collect(),
                'pageTableAvailable' => false,
            ]);
        }

        CmsPageCatalog::createMissing();

        $pages = Page::orderBy('slug')->get();

        return view('admin.pages.index', [
            'pages' => $pages,
            'pageTableAvailable' => true,
        ]);
    }

    public function edit(Page $page): View
    {
        $data = $page->data ?? [];

        return view('admin.pages.edit', compact('page', 'data'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $slug = $page->slug;
        $scalarFields = self::SLUG_SCALAR_FIELDS[$slug] ?? [];
        $jsonFields = self::SLUG_JSON_FIELDS[$slug] ?? [];
        $imageFields = self::SLUG_IMAGE_FIELDS[$slug] ?? [];

        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ];

        foreach ($imageFields as $field) {
            $rules[$field] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:6144'];
            $rules['remove_' . $field] = ['nullable', 'boolean'];
        }

        $request->validate($rules, [
            'seo_description.max' => 'A descrição SEO não pode ultrapassar 320 caracteres.',
            '*.image' => 'O arquivo deve ser uma imagem.',
            '*.max' => 'A imagem não pode ultrapassar 6 MB.',
        ]);

        $newData = $page->data ?? [];

        foreach ($scalarFields as $field) {
            if ($request->exists($field)) {
                $newData[$field] = $request->input($field, '');
            }
        }

        foreach ($imageFields as $field) {
            $removeKey = 'remove_' . $field;
            $currentPath = $newData[$field] ?? null;

            if ($request->boolean($removeKey)) {
                if ($currentPath) {
                    UploadStorage::delete($currentPath);
                }

                $newData[$field] = null;
                continue;
            }

            if ($request->hasFile($field)) {
                if ($currentPath) {
                    UploadStorage::delete($currentPath);
                }

                $newData[$field] = UploadStorage::storeUploadedFile($request->file($field), 'pages/' . $slug);
            }
        }

        foreach ($jsonFields as $field) {
            $raw = trim($request->input("{$field}_json", ''));

            if ($raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withInput()
                    ->withErrors(["{$field}_json" => "JSON inválido no campo \"{$field}\". Verifique a sintaxe."]);
            }

            $newData[$field] = $decoded;
        }

        $page->update([
            'title' => $request->input('title') ?: $page->title,
            'data' => $newData,
        ]);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Página "' . $slug . '" salva com sucesso.');
    }
}
