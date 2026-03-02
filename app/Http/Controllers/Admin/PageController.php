<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
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
            'hero_title', 'hero_subtitle', 'body', 'hero_cta_text', 'hero_cta2_text',
            'stat_1_value', 'stat_1_label', 'stat_2_value', 'stat_2_label',
            'stat_3_value', 'stat_3_label', 'stat_4_value', 'stat_4_label',
            'about_title', 'about_subtitle',
            'about_card_1_title', 'about_card_1_text', 'about_card_2_title', 'about_card_2_text',
            'about_card_3_title', 'about_card_3_text', 'about_card_4_title', 'about_card_4_text',
            'events_title', 'events_subtitle', 'mentorships_title', 'mentorships_subtitle',
            'community_title', 'community_beginner_title', 'community_beginner_desc',
            'community_success_title', 'community_success_desc',
            'ranking_title', 'ranking_subtitle', 'testimonials_title',
            'cta_section_title', 'cta_section_subtitle', 'cta_section_btn_primary', 'cta_section_btn_secondary',
            'seo_title', 'seo_description',
        ],
        'sobre' => [
            'seo_title', 'seo_description', 'hero_title', 'vision',
            'cta_btn_primary', 'cta_btn_secondary',
            'stat_1_value', 'stat_1_label', 'stat_2_value', 'stat_2_label',
            'stat_3_value', 'stat_3_label', 'stat_4_value', 'stat_4_label',
            'history_title', 'history_lead', 'history_p1', 'history_p2',
            'diff_title',
            'diff_card_1_title', 'diff_card_1_text',
            'diff_card_2_title', 'diff_card_2_text',
            'diff_card_3_title', 'diff_card_3_text',
            'cta_title', 'cta_subtitle', 'cta_btn',
        ],
        'manifesto' => [
            'seo_title', 'seo_description',
            'hero_title', 'hero_title_highlight', 'hero_subtitle', 'quote_top',
            'section_1_title', 'section_1_text', 'section_2_title', 'section_2_text',
            'section_3_title', 'section_3_text', 'section_4_title', 'section_4_text',
            'section_5_title', 'section_5_text',
            'quote_bottom', 'quote_author',
            'pillars_title', 'pillar_1_title', 'pillar_2_title', 'pillar_3_title', 'pillar_4_title',
            'pillars_link_text', 'cta_title', 'cta_subtitle', 'cta_btn',
        ],
        'valores' => [
            'seo_title', 'seo_description', 'hero_subtitle',
            'blockquote_text', 'blockquote_author',
            'cta_title', 'cta_subtitle', 'cta_btn',
        ],
        'como-funciona' => [
            'seo_title', 'seo_description', 'hero_subtitle',
            'plans_title', 'plans_subtitle',
            'cta_title', 'cta_subtitle', 'cta_btn',
        ],
        'quem-somos' => [
            'seo_title', 'seo_description', 'hero_subtitle',
            'founders_title', 'team_title', 'stats_title',
            'stat_1_value', 'stat_1_label', 'stat_2_value', 'stat_2_label',
            'stat_3_value', 'stat_3_label', 'stat_4_value', 'stat_4_label',
            'cta_title', 'cta_subtitle', 'cta_btn',
        ],
    ];

    /** Campos JSON (arrays) por slug — enviados como textarea JSON no form. */
    private const SLUG_JSON_FIELDS = [
        'home'          => ['testimonials'],
        'sobre'         => [],
        'manifesto'     => [],
        'valores'       => ['values'],
        'como-funciona' => ['steps'],
        'quem-somos'    => ['founders', 'team'],
    ];

    public function index(): View
    {
        $pages = Page::orderBy('slug')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page): View
    {
        $data = $page->data ?? [];

        return view('admin.pages.edit', compact('page', 'data'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $slug         = $page->slug;
        $scalarFields = self::SLUG_SCALAR_FIELDS[$slug] ?? [];
        $jsonFields   = self::SLUG_JSON_FIELDS[$slug]   ?? [];

        $request->validate([
            'title'           => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ], [
            'seo_description.max' => 'A descrição SEO não pode ultrapassar 320 caracteres.',
        ]);

        $newData = $page->data ?? [];

        // Campos escalares: whitelist por slug
        foreach ($scalarFields as $field) {
            if ($request->exists($field)) {
                $newData[$field] = $request->input($field, '');
            }
        }

        // Campos JSON: textarea enviado como {campo}_json
        foreach ($jsonFields as $field) {
            $raw = trim($request->input("{$field}_json", ''));
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $newData[$field] = $decoded;
                } else {
                    return back()
                        ->withInput()
                        ->withErrors(["{$field}_json" => "JSON inválido no campo \"{$field}\". Verifique a sintaxe."]);
                }
            }
        }

        $page->update([
            'title' => $request->input('title') ?: $page->title,
            'data'  => $newData,
        ]);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Página "' . $slug . '" salva com sucesso.');
    }
}
