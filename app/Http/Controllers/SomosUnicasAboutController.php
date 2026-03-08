<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;

class SomosUnicasAboutController extends Controller
{
    /**
     * Exibe a página sobre a comunidade Somos Únicas.
     */
    public function index()
    {
        // Buscar conteúdo SEO se existir
        $pageData = Page::dataBySlug('somos-unicas-sobre');
        return view('site.somos-unicas-about', compact('pageData'));
    }
}
