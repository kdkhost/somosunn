<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobVacancy;

class OportunidadesTesteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = JobVacancy::where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('visibility')
                        ->orWhereIn('visibility', ['public', 'external', 'both']);
                });

            if ($request->filled('area')) {
                $query->where('type', 'like', '%' . $request->area . '%');
            }
            if ($request->filled('local')) {
                $query->where('location', 'like', '%' . $request->local . '%');
            }
            if ($request->filled('empresa')) {
                $query->where('company_name', 'like', '%' . $request->empresa . '%');
            }
            if ($request->filled('tipo')) {
                $query->where('type', 'like', '%' . $request->tipo . '%');
            }

            $vagas = $query->orderByDesc('expires_at')->paginate(12);
            return view('oportunidades', compact('vagas'));
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
