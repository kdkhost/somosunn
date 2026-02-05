<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of members.
     */
    public function index()
    {
        // Check if members feature is enabled
        $isEnabled = \App\Models\Setting::get('feature_members', '1') === '1';

        if (!$isEnabled) {
            abort(404, 'Membros temporariamente indisponível');
        }

        $blockedUserIds = [];
        $connectedUserIds = [];

        if (auth()->check()) {
            $blockedUserIds = \App\Models\Connection::where(function ($q) {
                $q->where('requester_id', auth()->id())->orWhere('requested_id', auth()->id());
            })->where('status', 'blocked')->pluck('requester_id', 'requested_id')->flatten()->unique()->toArray();

            $connectedUserIds = \App\Models\Connection::where(function ($q) {
                $q->where('requester_id', auth()->id())->orWhere('requested_id', auth()->id());
            })->where('status', 'accepted')->pluck('requester_id', 'requested_id')->flatten()->unique()->toArray();
        }

        try {
            $query = User::where('role', '!=', 'superadmin')
                ->whereNotIn('id', $blockedUserIds);

            // Hide private profiles if not connected and not admin
            if (!auth()->user()?->isAdmin()) {
                $query->where(function ($q) use ($connectedUserIds) {
                    $q->where('hide_profile', false)
                        ->orWhereIn('id', $connectedUserIds);
                });
            }

            $members = $query->latest()
                ->take(12)
                ->get()
                ->map(function ($user) {
                    return (object) [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'bio' => $user->bio,
                        'avatar' => $user->photo ? asset($user->photo) : null,
                        'city' => trim(($user->city ?? '') . ($user->state ? ', ' . $user->state : '')),
                        'linkedin' => $user->linkedin,
                        'facebook' => $user->facebook,
                        'instagram' => $user->instagram,
                        'twitter' => $user->twitter,
                        'youtube' => $user->youtube,
                        'website' => $user->website,
                        'level' => $user->level ?? 'Iniciante',
                        'connections' => \App\Models\Connection::where(function ($q) use ($user) {
                            $q->where('requester_id', $user->id)->orWhere('requested_id', $user->id);
                        })->where('status', 'accepted')->count(),
                        'is_demo' => false,
                    ];
                });
        } catch (\Throwable $e) {
            // Fallback to demo data on any DB error
        }

        // If no members exist, provide demo data
        if ($members->isEmpty()) {
            $members = collect([
                (object) [
                    'id' => 1,
                    'name' => 'Carlos Eduardo Silva',
                    'email' => 'carlos@demo.com',
                    'bio' => 'Empreendedor serial com 15 anos de experiência em tecnologia e inovação.',
                    'company' => 'Tech Solutions LTDA',
                    'role' => 'CEO & Fundador',
                    'city' => 'São Paulo, SP',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Empresário de Sucesso',
                    'connections' => 234,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 2,
                    'name' => 'Ana Paula Costa',
                    'email' => 'ana@demo.com',
                    'bio' => 'Especialista em marketing digital e growth hacking. Mentora de startups.',
                    'company' => 'Growth Marketing Co.',
                    'role' => 'CMO',
                    'city' => 'Rio de Janeiro, RJ',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Mentor Premium',
                    'connections' => 189,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 3,
                    'name' => 'Roberto Mendes',
                    'email' => 'roberto@demo.com',
                    'bio' => 'Investidor anjo e conselheiro de empresas de médio porte.',
                    'company' => 'Mendes Investimentos',
                    'role' => 'Diretor de Investimentos',
                    'city' => 'Belo Horizonte, MG',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Investidor',
                    'connections' => 312,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 4,
                    'name' => 'Juliana Ferreira',
                    'email' => 'juliana@demo.com',
                    'bio' => 'Fundadora de e-commerce de moda sustentável. Palestrante.',
                    'company' => 'EcoFashion Brasil',
                    'role' => 'Fundadora',
                    'city' => 'Curitiba, PR',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Empreendedor',
                    'connections' => 156,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 5,
                    'name' => 'Fernando Oliveira',
                    'email' => 'fernando@demo.com',
                    'bio' => 'Consultor de negócios com foco em transformação digital.',
                    'company' => 'Digital Transform',
                    'role' => 'Consultor Sênior',
                    'city' => 'Porto Alegre, RS',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Consultor',
                    'connections' => 198,
                    'avatar' => null,
                    'is_demo' => true,
                ],
                (object) [
                    'id' => 6,
                    'name' => 'Mariana Santos',
                    'email' => 'mariana@demo.com',
                    'bio' => 'CEO de fintech premiada. Ex-executiva de grandes bancos.',
                    'company' => 'FinNext',
                    'role' => 'CEO',
                    'city' => 'São Paulo, SP',
                    'linkedin' => 'https://linkedin.com/in/demo',
                    'level' => 'Empresária de Sucesso',
                    'connections' => 287,
                    'avatar' => null,
                    'is_demo' => true,
                ],
            ]);

            return view('site.membros', ['members' => $members, 'isDemo' => true]);
        }

        return view('site.membros', compact('members'));
    }
}
