<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Support\ContentVisibility;

class SomosUnicasController extends Controller
{
    /**
     * Exibe a pagina publica da area "Somos Unicas".
     * Reune eventos, cursos e mentorias marcadas com a flag.
     */
    public function index()
    {
        $courses = ContentVisibility::applySomosUnicasFilter(
            Course::with(['creator'])
                ->where('status', 'published'),
            'courses'
        )
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        $events = ContentVisibility::applySomosUnicasFilter(
            Event::query()
                ->whereNotNull('start_at')
                ->where('published', true),
            'events'
        )
            ->publicUpcoming()
            ->orderBy('start_at', 'asc')
            ->take(6)
            ->get();

        $mentorships = ContentVisibility::applySomosUnicasFilter(
            Mentorship::with(['mentor']),
            'mentorships'
        )
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        $pageData = \App\Models\Page::dataBySlug('somos-unicas');

        return view('site.somos-unicas', compact('courses', 'events', 'mentorships', 'pageData'));
    }
}
