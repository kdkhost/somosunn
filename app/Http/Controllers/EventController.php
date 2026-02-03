<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     */
    public function index()
    {
        // Check if events feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_events', '1') === '1';
        
        if (!$isEnabled) {
            abort(404, 'Eventos temporariamente indisponível');
        }

        $events = Event::where('published', true)
            ->where('start_at', '>=', now())
            ->orderBy('start_at')
            ->get();

        // If no events exist, provide demo data
        if ($events->isEmpty()) {
            $events = collect([
                (object)[
                    'id' => 1,
                    'title' => 'Networking Premium - Edição São Paulo',
                    'speaker' => 'João Silva e Convidados',
                    'description' => 'O maior encontro de networking empresarial do Brasil. Conecte-se com mais de 200 empreendedores de sucesso em uma noite inesquecível de conexões estratégicas.',
                    'start_at' => now()->addDays(15)->setHour(19)->setMinute(0),
                    'end_at' => now()->addDays(15)->setHour(23)->setMinute(0),
                    'location' => 'Hotel Grand Hyatt',
                    'address' => 'Av. das Nações Unidas, 13301 - Itaim Bibi, São Paulo - SP',
                    'latitude' => -23.6230,
                    'longitude' => -46.6992,
                    'price' => 297.00,
                    'capacity' => 200,
                    'published' => true,
                    'color' => '#1F5EDB',
                    'is_demo' => true,
                ],
                (object)[
                    'id' => 2,
                    'title' => 'Masterclass: Vendas de Alto Impacto',
                    'speaker' => 'Maria Santos',
                    'description' => 'Aprenda as técnicas mais avançadas de fechamento de vendas com uma das maiores especialistas do país. Evento presencial com coffee break incluso.',
                    'start_at' => now()->addDays(22)->setHour(9)->setMinute(0),
                    'end_at' => now()->addDays(22)->setHour(18)->setMinute(0),
                    'location' => 'Centro de Convenções Frei Caneca',
                    'address' => 'R. Frei Caneca, 569 - Consolação, São Paulo - SP',
                    'latitude' => -23.5537,
                    'longitude' => -46.6523,
                    'price' => 497.00,
                    'capacity' => 100,
                    'published' => true,
                    'color' => '#10B981',
                    'is_demo' => true,
                ],
                (object)[
                    'id' => 3,
                    'title' => 'Happy Hour Empresarial - Rio de Janeiro',
                    'speaker' => 'Equipe UNN',
                    'description' => 'Evento gratuito de networking informal. Venha conhecer outros empreendedores em um ambiente descontraído com vista para o mar.',
                    'start_at' => now()->addDays(30)->setHour(18)->setMinute(30),
                    'end_at' => now()->addDays(30)->setHour(22)->setMinute(0),
                    'location' => 'Bar do Hotel Fasano',
                    'address' => 'Av. Vieira Souto, 80 - Ipanema, Rio de Janeiro - RJ',
                    'latitude' => -22.9878,
                    'longitude' => -43.2066,
                    'price' => 0,
                    'capacity' => 50,
                    'published' => true,
                    'color' => '#F59E0B',
                    'is_demo' => true,
                ],
            ]);
            
            return view('events.index', ['events' => $events, 'isDemo' => true]);
        }

        return view('events.index', compact('events'));
    }

    /**
     * Display a single event.
     */
    public function show($id)
    {
        $event = Event::findOrFail($id);
        return view('events.show', compact('event'));
    }
}

