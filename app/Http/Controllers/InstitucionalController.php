<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class InstitucionalController extends Controller
{
    public function sobre(): View
    {
        $page = Page::findBySlug('sobre') ?? new Page();
        return view('site.institucional.sobre', compact('page'));
    }

    public function manifesto(): View
    {
        $page = Page::findBySlug('manifesto') ?? new Page();
        return view('site.institucional.manifesto', compact('page'));
    }

    public function valores(): View
    {
        $page = Page::findBySlug('valores') ?? new Page();
        return view('site.institucional.valores', compact('page'));
    }

    public function comoFunciona(): View
    {
        $page = Page::findBySlug('como-funciona') ?? new Page();
        return view('site.institucional.como-funciona', compact('page'));
    }

    public function quemSomos(): View
    {
        $page = Page::findBySlug('quem-somos') ?? new Page();
        return view('site.institucional.quem-somos', compact('page'));
    }
}
