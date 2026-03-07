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
        $courses = Course::with(['author'])
            ->where('is_somos_unicas', true)
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        // 2) Eventos Somos Únicas (Futuros ou recentes, limitando)
        $events = Event::where('is_somos_unicas', true)
            ->where('published', true)
            ->where('date', '>=', $now->subDays(1)) // Hoje em diante
            ->orderBy('date', 'asc')
            ->take(6)
            ->get();

        // 3) Mentorias Somos Únicas
        $mentorships = Mentorship::with(['mentor'])
            ->where('is_somos_unicas', true)
            ->orderBy('id', 'desc')
            ->take(6)
            ->get();

        return view('site.somos-unicas', compact('courses', 'events', 'mentorships'));
    }
}
