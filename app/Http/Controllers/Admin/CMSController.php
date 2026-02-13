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
        $schema = $this->schemaFor($slug);
        $contents = SiteContent::query()->where('slug', $slug)->pluck('value', 'key')->toArray();
        $repeaters = $this->decodeRepeaters($schema, $contents);

        return view('admin.cms.index', [
            'slug' => $slug,
            'schema' => $schema,
            'contents' => $contents,
            'repeaters' => $repeaters,
        ]);
    }

    public function update(Request $request, string $slug)
    {
        $slug = $this->normalizeSlug($slug);
        $schema = $this->schemaFor($slug);
        $fields = $this->flattenFields($schema);
        $rules = [];

        foreach ($fields as $field => $def) {
            $type = (string) ($def['type'] ?? 'text');

            if ($type === 'image') {
                $rules[$field] = ['nullable', 'image', 'max:6144'];
                $rules['remove_' . $field] = ['nullable', 'boolean'];
                continue;
            }

            if ($type === 'repeater') {
                $rules[$field] = ['nullable', 'array'];
                $itemFields = (array) ($def['fields'] ?? []);
                foreach ($itemFields as $itemKey => $itemDef) {
                    $itemType = (string) ($itemDef['type'] ?? 'text');
                    if ($itemType === 'boolean') {
                        $rules[$field . '.*.' . $itemKey] = ['nullable'];
                    } else {
                        $rules[$field . '.*.' . $itemKey] = ['nullable', 'string'];
                    }
                }
                continue;
            }

            if ($type === 'boolean') {
                $rules[$field] = ['nullable'];
                continue;
            }

            $rules[$field] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        foreach ($fields as $field => $def) {
            $type = (string) ($def['type'] ?? 'text');

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

            if ($type === 'repeater') {
                $items = $validated[$field] ?? [];
                if (!is_array($items)) {
                    $items = [];
                }

                $itemFields = (array) ($def['fields'] ?? []);
                if (!empty($itemFields)) {
                    foreach ($items as $idx => $item) {
                        if (!is_array($item)) {
                            $items[$idx] = [];
                            continue;
                        }
                        foreach ($itemFields as $itemKey => $itemDef) {
                            if (($itemDef['type'] ?? '') === 'boolean') {
                                $items[$idx][$itemKey] = !empty($item[$itemKey]) ? 1 : 0;
                            }
                        }
                    }
                }

                SiteContent::putValue($slug, $field, json_encode(array_values($items), JSON_UNESCAPED_UNICODE), 'json');
                continue;
            }

            if ($type === 'boolean') {
                SiteContent::putValue($slug, $field, $request->boolean($field) ? '1' : '0', 'boolean');
                continue;
            }

            $storeType = $type === 'html' ? 'html' : 'text';
            SiteContent::putValue($slug, $field, (string) ($validated[$field] ?? ''), $storeType);
        }

        return redirect()
            ->route('admin.cms.index', ['slug' => $slug])
            ->with('success', 'Conteúdo salvo com sucesso.');
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

    private function schemaFor(string $slug): array
    {
        $seoFields = $this->seoFields();

        return match ($slug) {
            'institucional_sobre' => [
                'label' => 'Institucional: Sobre',
                'sections' => [
                    'hero' => [
                        'label' => 'Hero',
                        'fields' => [
                            'hero_title' => ['label' => 'Título (parte 1)', 'type' => 'text'],
                            'hero_title_highlight' => ['label' => 'Título (destaque)', 'type' => 'text'],
                            'hero_subtitle' => ['label' => 'Subtítulo', 'type' => 'textarea', 'rows' => 4],
                            'hero_primary_button_text' => ['label' => 'Botão principal (texto)', 'type' => 'text'],
                            'hero_primary_button_url' => ['label' => 'Botão principal (link)', 'type' => 'text'],
                            'hero_secondary_button_text' => ['label' => 'Botão secundário (texto)', 'type' => 'text'],
                            'hero_secondary_button_url' => ['label' => 'Botão secundário (link)', 'type' => 'text'],
                            'hero_stats' => [
                                'label' => 'Números (cards)',
                                'type' => 'repeater',
                                'fields' => [
                                    'value' => ['label' => 'Valor', 'type' => 'text'],
                                    'label' => ['label' => 'Legenda', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'historia' => [
                        'label' => 'Nossa História',
                        'fields' => [
                            'history_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'history_body' => ['label' => 'Texto da seção', 'type' => 'html', 'height' => 360],
                        ],
                    ],
                    'diferenciais' => [
                        'label' => 'Diferenciais',
                        'fields' => [
                            'diff_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'diff_items' => [
                                'label' => 'Cards',
                                'type' => 'repeater',
                                'fields' => [
                                    'icon' => ['label' => 'Ícone (FontAwesome)', 'type' => 'text'],
                                    'title' => ['label' => 'Título', 'type' => 'text'],
                                    'text' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 3],
                                ],
                            ],
                        ],
                    ],
                    'cta' => [
                        'label' => 'CTA',
                        'fields' => [
                            'cta_title' => ['label' => 'Título', 'type' => 'text'],
                            'cta_subtitle' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 2],
                            'cta_button_text' => ['label' => 'Botão (texto)', 'type' => 'text'],
                            'cta_button_url' => ['label' => 'Botão (link)', 'type' => 'text'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'SEO',
                        'fields' => $seoFields,
                    ],
                ],
            ],
            'institucional_manifesto' => [
                'label' => 'Institucional: Manifesto',
                'sections' => [
                    'hero' => [
                        'label' => 'Hero',
                        'fields' => [
                            'hero_title' => ['label' => 'Título (parte 1)', 'type' => 'text'],
                            'hero_title_highlight' => ['label' => 'Título (destaque)', 'type' => 'text'],
                            'hero_subtitle' => ['label' => 'Subtítulo', 'type' => 'textarea', 'rows' => 3],
                        ],
                    ],
                    'manifesto' => [
                        'label' => 'Manifesto',
                        'fields' => [
                            'manifesto_quote' => ['label' => 'Frase de destaque', 'type' => 'text'],
                            'manifesto_body' => ['label' => 'Texto (com títulos e parágrafos)', 'type' => 'html', 'height' => 520],
                            'highlight_quote' => ['label' => 'Bloco final (frase)', 'type' => 'text'],
                            'highlight_author' => ['label' => 'Bloco final (autor)', 'type' => 'text'],
                        ],
                    ],
                    'pilares' => [
                        'label' => 'Pilares',
                        'fields' => [
                            'pillars_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'pillars_items' => [
                                'label' => 'Itens',
                                'type' => 'repeater',
                                'fields' => [
                                    'icon' => ['label' => 'Ícone (FontAwesome)', 'type' => 'text'],
                                    'title' => ['label' => 'Título', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'cta' => [
                        'label' => 'CTA',
                        'fields' => [
                            'cta_title' => ['label' => 'Título', 'type' => 'text'],
                            'cta_subtitle' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 2],
                            'cta_button_text' => ['label' => 'Botão (texto)', 'type' => 'text'],
                            'cta_button_url' => ['label' => 'Botão (link)', 'type' => 'text'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'SEO',
                        'fields' => $seoFields,
                    ],
                ],
            ],
            'institucional_quem_somos' => [
                'label' => 'Institucional: Quem Somos',
                'sections' => [
                    'hero' => [
                        'label' => 'Hero',
                        'fields' => [
                            'hero_title_highlight' => ['label' => 'Título (destaque)', 'type' => 'text'],
                            'hero_title' => ['label' => 'Título (parte 2)', 'type' => 'text'],
                            'hero_subtitle' => ['label' => 'Subtítulo', 'type' => 'textarea', 'rows' => 3],
                        ],
                    ],
                    'fundadores' => [
                        'label' => 'Fundadores',
                        'fields' => [
                            'founders_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'founders_items' => [
                                'label' => 'Lista de fundadores',
                                'type' => 'repeater',
                                'fields' => [
                                    'name' => ['label' => 'Nome', 'type' => 'text'],
                                    'role' => ['label' => 'Cargo', 'type' => 'text'],
                                    'bio' => ['label' => 'Bio', 'type' => 'textarea', 'rows' => 3],
                                    'initials' => ['label' => 'Iniciais', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'equipe' => [
                        'label' => 'Equipe',
                        'fields' => [
                            'team_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'team_items' => [
                                'label' => 'Lista da equipe',
                                'type' => 'repeater',
                                'fields' => [
                                    'name' => ['label' => 'Nome', 'type' => 'text'],
                                    'role' => ['label' => 'Cargo', 'type' => 'text'],
                                    'initials' => ['label' => 'Iniciais', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'numeros' => [
                        'label' => 'Números',
                        'fields' => [
                            'numbers_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'numbers_items' => [
                                'label' => 'Cards',
                                'type' => 'repeater',
                                'fields' => [
                                    'value' => ['label' => 'Valor', 'type' => 'text'],
                                    'label' => ['label' => 'Legenda', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'cta' => [
                        'label' => 'CTA',
                        'fields' => [
                            'cta_title' => ['label' => 'Título', 'type' => 'text'],
                            'cta_subtitle' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 2],
                            'cta_button_text' => ['label' => 'Botão (texto)', 'type' => 'text'],
                            'cta_button_url' => ['label' => 'Botão (link)', 'type' => 'text'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'SEO',
                        'fields' => $seoFields,
                    ],
                ],
            ],
            'institucional_como_funciona' => [
                'label' => 'Institucional: Como Funciona',
                'sections' => [
                    'hero' => [
                        'label' => 'Hero',
                        'fields' => [
                            'hero_title_highlight' => ['label' => 'Título (destaque)', 'type' => 'text'],
                            'hero_title' => ['label' => 'Título (parte 2)', 'type' => 'text'],
                            'hero_subtitle' => ['label' => 'Subtítulo', 'type' => 'textarea', 'rows' => 3],
                        ],
                    ],
                    'passos' => [
                        'label' => 'Passos',
                        'fields' => [
                            'steps_items' => [
                                'label' => 'Lista de passos',
                                'type' => 'repeater',
                                'fields' => [
                                    'title' => ['label' => 'Título', 'type' => 'text'],
                                    'text' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 3],
                                    'bullet_1' => ['label' => 'Bullet 1', 'type' => 'text'],
                                    'bullet_2' => ['label' => 'Bullet 2', 'type' => 'text'],
                                    'bullet_3' => ['label' => 'Bullet 3', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'planos' => [
                        'label' => 'Planos (vitrine)',
                        'fields' => [
                            'plans_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'plans_subtitle' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 2],
                            'plans_items' => [
                                'label' => 'Cards de planos',
                                'type' => 'repeater',
                                'fields' => [
                                    'title' => ['label' => 'Nome', 'type' => 'text'],
                                    'price' => ['label' => 'Preço', 'type' => 'text'],
                                    'period' => ['label' => 'Período', 'type' => 'text'],
                                    'tagline' => ['label' => 'Descrição curta', 'type' => 'text'],
                                    'feature_1' => ['label' => 'Feature 1', 'type' => 'text'],
                                    'feature_2' => ['label' => 'Feature 2', 'type' => 'text'],
                                    'feature_3' => ['label' => 'Feature 3', 'type' => 'text'],
                                    'feature_4' => ['label' => 'Feature 4', 'type' => 'text'],
                                    'button_text' => ['label' => 'Botão (texto)', 'type' => 'text'],
                                    'button_url' => ['label' => 'Botão (link)', 'type' => 'text'],
                                    'featured' => ['label' => 'Destacar', 'type' => 'boolean'],
                                    'badge' => ['label' => 'Selo', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'cta' => [
                        'label' => 'CTA',
                        'fields' => [
                            'cta_title' => ['label' => 'Título', 'type' => 'text'],
                            'cta_subtitle' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 2],
                            'cta_button_text' => ['label' => 'Botão (texto)', 'type' => 'text'],
                            'cta_button_url' => ['label' => 'Botão (link)', 'type' => 'text'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'SEO',
                        'fields' => $seoFields,
                    ],
                ],
            ],
            'institucional_valores' => [
                'label' => 'Institucional: Valores',
                'sections' => [
                    'hero' => [
                        'label' => 'Hero',
                        'fields' => [
                            'hero_title' => ['label' => 'Título (parte 1)', 'type' => 'text'],
                            'hero_title_highlight' => ['label' => 'Título (destaque)', 'type' => 'text'],
                            'hero_subtitle' => ['label' => 'Subtítulo', 'type' => 'textarea', 'rows' => 3],
                        ],
                    ],
                    'valores' => [
                        'label' => 'Valores',
                        'fields' => [
                            'values_items' => [
                                'label' => 'Cards de valores',
                                'type' => 'repeater',
                                'fields' => [
                                    'icon' => ['label' => 'Ícone (FontAwesome)', 'type' => 'text'],
                                    'title' => ['label' => 'Título', 'type' => 'text'],
                                    'text' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 3],
                                    'quote' => ['label' => 'Frase (itálico)', 'type' => 'text'],
                                ],
                            ],
                        ],
                    ],
                    'quote' => [
                        'label' => 'Citação',
                        'fields' => [
                            'quote_text' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 3],
                            'quote_author' => ['label' => 'Autor', 'type' => 'text'],
                        ],
                    ],
                    'cta' => [
                        'label' => 'CTA',
                        'fields' => [
                            'cta_title' => ['label' => 'Título', 'type' => 'text'],
                            'cta_subtitle' => ['label' => 'Texto', 'type' => 'textarea', 'rows' => 2],
                            'cta_button_text' => ['label' => 'Botão (texto)', 'type' => 'text'],
                            'cta_button_url' => ['label' => 'Botão (link)', 'type' => 'text'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'SEO',
                        'fields' => $seoFields,
                    ],
                ],
            ],
            'institucional_contato' => [
                'label' => 'Institucional: Contato',
                'sections' => [
                    'hero' => [
                        'label' => 'Hero',
                        'fields' => [
                            'hero_title' => ['label' => 'Título (parte 1)', 'type' => 'text'],
                            'hero_title_highlight' => ['label' => 'Título (destaque)', 'type' => 'text'],
                            'hero_subtitle' => ['label' => 'Subtítulo', 'type' => 'textarea', 'rows' => 3],
                        ],
                    ],
                    'mapa' => [
                        'label' => 'Mapa',
                        'fields' => [
                            'map_title' => ['label' => 'Título da seção', 'type' => 'text'],
                            'map_embed_url' => ['label' => 'URL do mapa (embed)', 'type' => 'text'],
                        ],
                    ],
                    'seo' => [
                        'label' => 'SEO',
                        'fields' => $seoFields,
                    ],
                ],
            ],
            'about' => [
                'label' => 'Sobre (Seções)',
                'sections' => [
                    'secoes' => [
                        'label' => 'Seções',
                        'fields' => [
                            'manifesto' => ['label' => 'Manifesto (resumo)', 'type' => 'textarea', 'rows' => 4],
                            'vision' => ['label' => 'Visão (resumo)', 'type' => 'textarea', 'rows' => 4],
                            'values' => ['label' => 'Valores (resumo)', 'type' => 'textarea', 'rows' => 4],
                        ],
                    ],
                ],
            ],
            'footer' => [
                'label' => 'Rodapé',
                'sections' => [
                    'redes' => [
                        'label' => 'Redes sociais',
                        'fields' => [
                            'instagram_url' => ['label' => 'Instagram', 'type' => 'text'],
                            'linkedin_url' => ['label' => 'LinkedIn', 'type' => 'text'],
                            'youtube_url' => ['label' => 'YouTube', 'type' => 'text'],
                            'facebook_url' => ['label' => 'Facebook', 'type' => 'text'],
                        ],
                    ],
                ],
            ],
            default => [
                'label' => 'Home',
                'sections' => [
                    'hero' => [
                        'label' => 'Hero',
                        'fields' => [
                            'hero_title' => ['label' => 'Título do Hero', 'type' => 'text'],
                            'hero_subtitle' => ['label' => 'Subtítulo do Hero', 'type' => 'textarea', 'rows' => 3],
                            'hero_text' => ['label' => 'Texto do Hero', 'type' => 'textarea', 'rows' => 4],
                            'hero_image' => ['label' => 'Imagem de Fundo (Hero)', 'type' => 'image'],
                        ],
                    ],
                ],
            ],
        };
    }

    private function seoFields(): array
    {
        return [
            'title' => ['label' => 'Título da página (aba do navegador)', 'type' => 'text'],
            'meta_title' => ['label' => 'Meta Title', 'type' => 'text', 'help' => 'Se vazio, usa o título da página.'],
            'meta_description' => ['label' => 'Meta Description', 'type' => 'textarea', 'rows' => 3, 'help' => 'Se vazio, usa a descrição global em Configurações.'],
            'meta_keywords' => ['label' => 'Meta Keywords', 'type' => 'text'],
            'canonical' => ['label' => 'Canonical', 'type' => 'text', 'help' => 'Se vazio, usa a URL atual da página.'],
            'meta_robots' => ['label' => 'Robots', 'type' => 'text', 'placeholder' => 'index,follow'],
            'og_type' => ['label' => 'OG Type', 'type' => 'text', 'placeholder' => 'website'],
            'twitter_card' => ['label' => 'Twitter Card', 'type' => 'text', 'placeholder' => 'summary_large_image'],
            'meta_image' => ['label' => 'Imagem OG', 'type' => 'image'],
            'twitter_image' => ['label' => 'Imagem Twitter', 'type' => 'image'],
        ];
    }

    private function flattenFields(array $schema): array
    {
        $flat = [];
        $sections = (array) ($schema['sections'] ?? []);
        foreach ($sections as $section) {
            $fields = (array) ($section['fields'] ?? []);
            foreach ($fields as $key => $def) {
                $flat[$key] = is_array($def) ? $def : ['type' => (string) $def];
            }
        }

        return $flat;
    }

    private function decodeRepeaters(array $schema, array $contents): array
    {
        $decoded = [];
        $fields = $this->flattenFields($schema);

        foreach ($fields as $key => $def) {
            if (($def['type'] ?? '') !== 'repeater') {
                continue;
            }

            $raw = (string) ($contents[$key] ?? '');
            if (trim($raw) === '') {
                $decoded[$key] = [];
                continue;
            }

            $val = json_decode($raw, true);
            $decoded[$key] = is_array($val) ? $val : [];
        }

        return $decoded;
    }
}
