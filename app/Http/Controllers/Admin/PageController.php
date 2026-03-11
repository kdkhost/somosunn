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
    private const SHARED_IMAGE_FIELDS = ['seo_image'];

    private const SLUG_IMAGE_FIELDS = [
        'home' => ['hero_image', 'seo_image'],
        'sobre' => ['hero_image', 'seo_image'],
        'manifesto' => ['seo_image'],
        'valores' => ['seo_image'],
        'como-funciona' => ['seo_image'],
        'quem-somos' => ['cover_image', 'seo_image'],
        'eventos' => ['hero_image', 'seo_image'],
        'membros' => ['seo_image'],
        'vagas-abertas' => ['seo_image'],
        'cursos' => ['hero_image', 'seo_image'],
        'portal' => ['hero_image', 'seo_image'],
        'premium' => ['hero_image', 'seo_image'],
        'feed' => ['seo_image'],
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

        return response()->json([
            'success' => true,
            'message' => 'Visibilidade da seção atualizada com sucesso!'
        ]);
    }
}
