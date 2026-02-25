<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        $vacancies = JobVacancy::where('is_active', true)
            ->whereIn('visibility', ['internal', 'both'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        return view('panel.jobs.index', compact('vacancies'));
    }

    public function show(JobVacancy $job)
    {
        if (!$job->is_active) {
            abort(404);
        }

        $hasApplied = JobApplication::where('job_vacancy_id', $job->id)
            ->where('user_id', Auth::id())
            ->exists();

        return view('panel.jobs.show', compact('job', 'hasApplied'));
    }

    public function apply(Request $request, JobVacancy $job)
    {
        $request->validate([
            'cover_letter' => 'nullable|string',
            'file_data' => 'required|string',
            'file_name' => 'required|string|max:255',
        ]);

        $exists = JobApplication::where('job_vacancy_id', $job->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Você já se candidatou para esta vaga.');
        }

        // Decodifica o Base64 e salva o arquivo
        $fileData = $request->input('file_data');
        $fileName = $request->input('file_name');

        // Valida extensão
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            return back()->with('error', 'Formato de arquivo inválido. Use PDF, DOC ou DOCX.');
        }

        // Remove o header do data URL (data:application/pdf;base64,)
        if (str_contains($fileData, ';base64,')) {
            $fileData = substr($fileData, strpos($fileData, ';base64,') + 8);
        }

        $decoded = base64_decode($fileData);
        if (!$decoded || strlen($decoded) > 2 * 1024 * 1024) {
            return back()->with('error', 'Arquivo inválido ou muito grande (máx 2MB).');
        }

        $uniqueName = 'resumes/' . \Str::uuid() . '.' . $ext;
        \Storage::disk('public')->put($uniqueName, $decoded);

        JobApplication::create([
            'job_vacancy_id' => $job->id,
            'user_id' => Auth::id(),
            'resume_path' => $uniqueName,
            'cover_letter' => $request->cover_letter,
            'status' => 'pending'
        ]);

        return redirect()->route('panel.jobs.show', $job)->with('success', 'Candidatura enviada com sucesso!');
    }
}
