<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobVacancy;

class OportunidadesTesteController extends Controller
{
    public function index(Request $request)
    {
        $query = JobVacancy::where('is_active', true)
            ->where('visibility', 'public');

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

        $vagas = $query->orderByDesc('expires_at')->paginate(8);
        return view('oportunidades', compact('vagas'));
    }
}
