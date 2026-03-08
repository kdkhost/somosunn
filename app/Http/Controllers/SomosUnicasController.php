<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Course;
use App\Models\Mentorship;
use App\Models\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SomosUnicasController extends Controller
{
    /**
     * Exibe a página pública da área "Somos Únicas".
     * Reúne eventos, cursos e mentorias marcadas com a flag.
     */
    public function index()
    {
        $now = Carbon::now();

        // 1) Cursos Somos Únicas (Publicados)
        $courses = Course::with(['creator'])
            ->whereIn('visibility', ['somos_unicas', 'ambos'])
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        // 2) Eventos Somos Únicas (Futuros ou recentes, limitando)
        $events = Event::whereIn('visibility', ['somos_unicas', 'ambos'])
            ->where('published', true)
            ->where('start_at', '>=', $now->subDays(1)) // Hoje em diante
            ->orderBy('start_at', 'asc')
            ->take(6)
            ->get();

        // 3) Mentorias Somos Únicas
        $mentorships = Mentorship::with(['mentor'])
            ->whereIn('visibility', ['somos_unicas', 'ambos'])
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        $pageData = \App\Models\Page::dataBySlug('somos-unicas');

        return view('site.somos-unicas', compact('courses', 'events', 'mentorships', 'pageData'));
    }
}
