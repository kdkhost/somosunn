<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\CmsPageCatalog;
use App\Support\UploadStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PageController extends Controller
{
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    private const MAX_IMAGE_BYTES = 6144 * 1024;

    private const SHARED_SCALAR_FIELDS = [
        'seo_title',
        'seo_description',
        'seo_keywords',
        'meta_robots',
        'canonical_url',
        'og_type',
        'twitter_card',
        'h1_title',
    ];

    /**
     * Campos escalares editáveis por slug.
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
        ],
        'sobre' => [
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
            'hero_subtitle',
            'blockquote_text',
            'blockquote_author',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'como-funciona' => [
            'hero_subtitle',
            'plans_title',
            'plans_subtitle',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'quem-somos' => [
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
            'hero_badge',
            'hero_title',
            'hero_subtitle',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'membros' => [
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
        'vagas-abertas' => [
            'hero_badge',
            'hero_title',
            'hero_subtitle',
        ],
        'cursos' => [
            'hero_badge',
            'hero_title',
            'hero_subtitle',
        ],
        'portal' => [
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
            'community_title',
            'community_level_1_name',
            'community_level_1_count',
            'community_level_1_icon',
            'community_level_1_color',
            'community_level_1_desc',
            'community_level_2_name',
            'community_level_2_count',
            'community_level_2_icon',
            'community_level_2_color',
            'community_level_2_desc',
            'community_level_3_name',
            'community_level_3_count',
            'community_level_3_icon',
            'community_level_3_color',
            'community_level_3_desc',
            'community_level_4_name',
            'community_level_4_count',
            'community_level_4_icon',
            'community_level_4_color',
            'community_level_4_desc',
            'ranking_title',
            'ranking_subtitle',
            'cta_title',
            'cta_subtitle',
            'cta_btn',
        ],
        'premium' => [
            'hero_badge',
            'hero_title',
            'hero_subtitle',
            'hero_trust_1',
            'hero_trust_2',
            'plans_title',
            'plans_subtitle',
        ],
        'feed' => [],
        'somos-unicas' => [
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
            'theme_color',
            'hero_title',
            'hero_subtitle',
            'content_title',
            'content_body'
        ],
        'consentimento-lgpd' => ['hero_title', 'hero_subtitle', 'body_content'],
        'politica-de-privacidade' => ['hero_title', 'hero_subtitle', 'body_content'],
        'termos-de-uso' => ['hero_title', 'hero_subtitle', 'body_content'],
    ];

    /**
     * Campos de imagem por slug - recebidos via file input no form.
     * O valor armazenado é o path relativo ao disco 'public'.
     */
    private const SHARED_IMAGE_FIELDS = ['seo_image', 'seo_og_image', 'seo_twitter_image'];

    private const SLUG_IMAGE_FIELDS = [
        'home' => ['hero_image'],
        'sobre' => ['hero_image'],
        'manifesto' => [],
        'valores' => [],
        'como-funciona' => [],
        'quem-somos' => ['cover_image'],
        'eventos' => ['hero_image'],
        'membros' => [],
        'vagas-abertas' => ['hero_image'],
        'cursos' => ['hero_image'],
        'portal' => ['hero_image'],
        'premium' => ['hero_image'],
        'feed' => [],
        'somos-unicas' => ['hero_image'],
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

    private const LEGAL_PAGE_SLUGS = [
        'consentimento-lgpd',
        'politica-de-privacidade',
        'termos-de-uso',
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

        CmsPageCatalog::upsertDefaults();

        $pages = Page::orderBy('slug')->get();

        return view('admin.pages.index', [
            'pages' => $pages,
            'pageTableAvailable' => true,
        ]);
    }

    public function edit(Page $page): View
    {
        if (Page::tableAvailable()) {
            CmsPageCatalog::upsertDefaults();
            $page->refresh();
        }

        $data = $page->data ?? [];

        return view('admin.pages.edit', compact('page', 'data'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $slug = $page->slug;
        $scalarFields = array_merge(
            self::SHARED_SCALAR_FIELDS,
            self::SLUG_SCALAR_FIELDS[$slug] ?? []
        );
        $jsonFields = self::SLUG_JSON_FIELDS[$slug] ?? [];
        $imageFields = array_values(array_unique(array_merge(
            self::SHARED_IMAGE_FIELDS,
            self::SLUG_IMAGE_FIELDS[$slug] ?? []
        )));

        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ];

        foreach ($imageFields as $field) {
            $rules[$field] = ['nullable'];
            $rules['remove_' . $field] = ['nullable', 'boolean'];
        }

        $request->validate($rules, [
            'seo_description.max' => 'A descrição SEO não pode ultrapassar 320 caracteres.',
            '*.image' => 'O arquivo deve ser uma imagem.',
            '*.max' => 'A imagem não pode ultrapassar 6 MB.',
        ]);

        $newData = $page->data ?? [];

        if (in_array($slug, self::LEGAL_PAGE_SLUGS, true) && !$request->exists('body_content') && $request->exists('content')) {
            $newData['body_content'] = $request->input('content', '');
        }

        foreach ($scalarFields as $field) {
            if ($request->exists($field)) {
                $newData[$field] = $request->input($field, '');
            }
        }

        foreach ($imageFields as $field) {
            $removeKey = 'remove_' . $field;
            $currentPath = $newData[$field] ?? null;
            $uploadedPath = $this->resolveChunkedImagePath($request, $field);

            try {
                if ($request->boolean($removeKey)) {
                    if ($currentPath) {
                        UploadStorage::delete($currentPath);
                    }

                    $newData[$field] = null;
                    continue;
                }

                if ($request->hasFile($field)) {
                    $this->validateUploadedImageFile($request, $field);

                    if ($currentPath) {
                        UploadStorage::delete($currentPath);
                    }

                    $newData[$field] = UploadStorage::storeUploadedFile($request->file($field), 'pages/' . $slug);
                    continue;
                }

                if ($uploadedPath !== null) {
                    $this->validateStoredImagePath($field, $uploadedPath);

                    if ($currentPath && UploadStorage::normalizePath($currentPath) !== $uploadedPath) {
                        UploadStorage::delete($currentPath);
                    }

                    $newData[$field] = $uploadedPath;
                }
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                Log::error('Falha ao salvar imagem da pagina CMS.', [
                    'page_id' => $page->id,
                    'slug' => $slug,
                    'field' => $field,
                    'message' => $exception->getMessage(),
                ]);

                return back()
                    ->withInput()
                    ->withErrors([
                        $field => 'Nao foi possivel salvar a imagem selecionada. Verifique o storage do servidor e tente novamente.',
                    ]);
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

    private function validateUploadedImageFile(Request $request, string $field): void
    {
        Validator::make(
            [$field => $request->file($field)],
            [$field => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:6144']],
            [
                "{$field}.image" => 'O arquivo deve ser uma imagem.',
                "{$field}.mimes" => 'Formato de imagem nao permitido.',
                "{$field}.max" => 'A imagem nao pode ultrapassar 6 MB.',
            ]
        )->validate();
    }

    private function resolveChunkedImagePath(Request $request, string $field): ?string
    {
        if ($request->hasFile($field)) {
            return null;
        }

        $value = $request->input($field);
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return UploadStorage::normalizePath($value);
    }

    private function validateStoredImagePath(string $field, string $path): void
    {
        $normalizedPath = UploadStorage::normalizePath($path);
        $extension = strtolower(pathinfo((string) $normalizedPath, PATHINFO_EXTENSION));

        if ($normalizedPath === null || !in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                $field => 'Formato de imagem nao permitido.',
            ]);
        }

        if (!UploadStorage::exists($normalizedPath)) {
            throw ValidationException::withMessages([
                $field => 'A imagem enviada nao foi encontrada no servidor.',
            ]);
        }

        $size = UploadStorage::size($normalizedPath);
        if ($size !== null && $size > self::MAX_IMAGE_BYTES) {
            throw ValidationException::withMessages([
                $field => 'A imagem nao pode ultrapassar 6 MB.',
            ]);
        }
    }

    public function toggleSection(Request $request, Page $page)
    {
        $section = $request->input('section');
        $status = $request->boolean('status');

        $data = $page->data ?: [];
        $data["{$section}_enabled"] = $status;

        $page->data = $data;
        $page->save();

        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('view:clear');
        } catch (\Throwable $e) {
            \Log::error('Erro ao limpar cache na Admin/PageController@toggleSection: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Visibilidade da seção atualizada com sucesso!'
        ]);
    }
}
