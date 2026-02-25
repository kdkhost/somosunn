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
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $exists = JobApplication::where('job_vacancy_id', $job->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('error', 'Você já se candidatou para esta vaga.');
        }

        $resumePath = $request->file('cv_file')->store('resumes', 'public');

        JobApplication::create([
            'job_vacancy_id' => $job->id,
            'user_id' => Auth::id(),
            'resume_path' => $resumePath,
            'cover_letter' => $request->cover_letter,
            'status' => 'pending'
        ]);

        return redirect()->route('panel.jobs.show', $job)->with('success', 'Candidatura enviada com sucesso!');
    }
}
