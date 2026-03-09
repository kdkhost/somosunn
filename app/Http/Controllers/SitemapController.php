<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Course;
use App\Models\Mentorship;
use App\Models\Event;

class SitemapController extends Controller
{
    public function index()
    {
        // 1. Static Pages
        $urls = [
            route('home'),
            route('portal'),
            route('planos'),
            route('sobre'),
            route('manifesto'),
            route('quem-somos'),
            route('como-funciona'),
            route('valores'),
            route('contato'),
            route('membros'),
            route('courses.index'),
            route('mentorships.index'),
            route('events.index'),
            route('marketplace.index'),
        ];

        // 2. Dynamic Content - Courses (Active/Published)
        $courses = Course::where('status', 'published')->get();
        foreach ($courses as $course) {
            $urls[] = route('courses.show', $course);
        }

        // 3. Dynamic Content - Mentorships (Active)
        $mentorships = Mentorship::where('status', 'active')->get(); // Adjust status if needed
        foreach ($mentorships as $mentorship) {
            $urls[] = route('mentorships.show', $mentorship);
        }

        // 4. Dynamic Content - Events (Active/Upcoming)
        $events = Event::where('status', 'published')->get(); // Adjust status if needed
        foreach ($events as $event) {
            $urls[] = route('events.show', $event);
        }

        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . $url . '</loc>';
            $xml .= '<lastmod>' . now()->toAtomString() . '</lastmod>'; // Ideally use model updated_at
            $xml .= '<changefreq>daily</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return Response::make($xml, 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}
