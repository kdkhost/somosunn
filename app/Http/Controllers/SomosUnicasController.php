<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Event;
use App\Models\Mentorship;
use App\Models\Testimonial;
use App\Support\ContentVisibility;
use Illuminate\Support\Facades\Log;

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

        $testimonials = collect();
        try {
            $testimonials = Testimonial::forSite()
                ->with('user')
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();
        } catch (\Throwable $exception) {
            Log::warning('Falha ao carregar depoimentos em Somos Únicas: ' . $exception->getMessage());
        }

        if ($testimonials->isEmpty()) {
            $testimonials = collect([
                new Testimonial(['author_name' => 'Carlos Eduardo', 'author_title' => 'CEO, Tech Solutions', 'content' => 'A UNN transformou minha forma de fazer negócios. Em 6 meses, fechei parcerias que mudaram minha empresa.', 'rating' => 5]),
                new Testimonial(['author_name' => 'Ana Paula Lima', 'author_title' => 'Fundadora, EcoModa', 'content' => 'O networking aqui é diferente. São conexões genuínas com pessoas que realmente querem ajudar.', 'rating' => 5]),
                new Testimonial(['author_name' => 'Roberto Silva', 'author_title' => 'Investidor Anjo', 'content' => 'Encontrei projetos incríveis para investir e empreendedores talentosos. A comunidade é de altíssimo nível.', 'rating' => 5]),
            ]);
        }

        return view('site.somos-unicas', compact('courses', 'events', 'mentorships', 'testimonials', 'pageData'));
    }
}
