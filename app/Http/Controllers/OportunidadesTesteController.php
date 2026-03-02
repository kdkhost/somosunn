<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobVacancy;
use App\Models\Page;
use App\Models\Partner;

class OportunidadesTesteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = JobVacancy::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('visibility')
                        ->orWhereIn('visibility', ['public', 'external', 'both']);
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                });

            if ($request->filled('area')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->area . '%')
                      ->orWhere('short_description', 'like', '%' . $request->area . '%')
                      ->orWhere('type', 'like', '%' . $request->area . '%');
                });
            }
            if ($request->filled('local')) {
                $query->where('location', 'like', '%' . $request->local . '%');
            }
            if ($request->filled('empresa')) {
                $query->where('company_name', 'like', '%' . $request->empresa . '%');
            }
            if ($request->filled('tipo')) {
                $query->where('type', $request->tipo);
            }

            $limit = (int) \App\Models\Setting::get('frontend_item_limit', 9);
            $vagas = $query->orderByDesc('created_at')->paginate($limit > 0 ? $limit : 9);

            // Nomes de empresas parceiras (lowercase para comparação case-insensitive)
            $partnerNames = Partner::active()->pluck('name')
                ->map(fn ($n) => mb_strtolower(trim($n)))->all();

            // Tipos distintos para o filtro
            $tiposDisponiveis = JobVacancy::where('is_active', true)
                ->whereNotNull('type')->where('type', '!=', '')
                ->distinct()->orderBy('type')->pluck('type');

            $pageData = Page::where('slug', 'vagas-abertas')->first()?->data ?? [];

            return view('oportunidades', compact('vagas', 'partnerNames', 'tiposDisponiveis', 'pageData'));
        } catch (\Throwable $e) {
            \Log::error("Erro ao carregar vagas-abertas: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            if (config('app.debug')) {
                throw $e;
            }

            abort(500, 'Erro ao carregar a página de vagas.');
        }
    }
}
