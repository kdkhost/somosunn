<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /** Campos de nível raiz editáveis no formulário (mapeados dentro de data[]). */
    private const FLAT_FIELDS = [
        'hero_title',
        'hero_subtitle',
        'cta_text',
        'cta_url',
        'body',
        'seo_title',
        'seo_description',
    ];

    public function index(): View
    {
        $pages = Page::orderBy('slug')->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page): View
    {
        $data = $page->data ?? [];

        // Flatteners para preencher o form a partir das diversas estruturas de slug
        $heroNode  = $data['hero'] ?? [];
        $seoNode   = $data['seo']  ?? [];

        $flat = [
            'hero_title'      => $data['hero_title']      ?? $heroNode['headline']    ?? $heroNode['title']    ?? '',
            'hero_subtitle'   => $data['hero_subtitle']   ?? $heroNode['subheadline'] ?? $heroNode['subtitle'] ?? '',
            'cta_text'        => $data['cta_text']        ?? $heroNode['cta_text']    ?? '',
            'cta_url'         => $data['cta_url']         ?? $heroNode['cta_url']     ?? '',
            'body'            => $data['body']            ?? $heroNode['body']        ?? '',
            'seo_title'       => $data['seo_title']       ?? $seoNode['title']        ?? '',
            'seo_description' => $data['seo_description'] ?? $seoNode['description']  ?? '',
        ];

        return view('admin.pages.edit', compact('page', 'flat'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'title'           => ['nullable', 'string', 'max:255'],
            'hero_title'      => ['nullable', 'string', 'max:255'],
            'hero_subtitle'   => ['nullable', 'string', 'max:255'],
            'cta_text'        => ['nullable', 'string', 'max:120'],
            'cta_url'         => ['nullable', 'string', 'max:255'],
            'body'            => ['nullable', 'string'],
            'seo_title'       => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ], [
            'title.max'           => 'O título não pode ultrapassar 255 caracteres.',
            'hero_title.max'      => 'O título do hero não pode ultrapassar 255 caracteres.',
            'hero_subtitle.max'   => 'O subtítulo não pode ultrapassar 255 caracteres.',
            'cta_text.max'        => 'O texto do botão não pode ultrapassar 120 caracteres.',
            'seo_title.max'       => 'O título SEO não pode ultrapassar 255 caracteres.',
            'seo_description.max' => 'A descrição SEO não pode ultrapassar 320 caracteres.',
        ]);

        // Mescla os campos editados dentro de data[] sem apagar chaves não mapeadas
        $existingData = $page->data ?? [];

        $flatUpdate = array_filter([
            'hero_title'      => $validated['hero_title']      ?? null,
            'hero_subtitle'   => $validated['hero_subtitle']   ?? null,
            'cta_text'        => $validated['cta_text']        ?? null,
            'cta_url'         => $validated['cta_url']         ?? null,
            'body'            => $validated['body']            ?? null,
            'seo_title'       => $validated['seo_title']       ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
        ], fn($v) => $v !== null);

        $page->update([
            'title' => $validated['title'] ?: $page->title,
            'data'  => array_merge($existingData, $flatUpdate),
        ]);

        return redirect()
            ->route('admin.pages.index')
            ->with('success', 'Página "' . $page->slug . '" salva com sucesso.');
    }
}
