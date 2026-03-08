<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;

class SomosUnicasController extends Controller
{
    /**
     * Exibe a pagina publica da area "Somos Unicas".
     * Reune eventos, cursos e mentorias marcadas com a flag.
     */
    public function index()
    {
        $courses = Course::with(['creator'])
            ->whereIn('visibility', ['somos_unicas', 'ambos'])
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        $events = Event::query()
            ->whereNotNull('start_at')
            ->where('published', true)
            ->whereIn('visibility', ['somos_unicas', 'ambos'])
            ->publicUpcoming()
            ->orderBy('start_at', 'asc')
            ->take(6)
            ->get();

        $mentorships = Mentorship::with(['mentor'])
            ->whereIn('visibility', ['somos_unicas', 'ambos'])
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        $pageData = \App\Models\Page::dataBySlug('somos-unicas');

        return view('site.somos-unicas', compact('courses', 'events', 'mentorships', 'pageData'));
    }
}
