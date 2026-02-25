<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Services\JobApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobPublicController extends Controller
{
    public function index()
    {
        $vacancies = JobVacancy::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('visibility')
                    ->orWhereIn('visibility', ['public', 'external', 'both']);
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        return view('site.jobs.index', compact('vacancies'));
    }

    public function show(JobVacancy $job)
    {
        if (!$job->is_active || !$this->isPublicVisibility($job)) {
            abort(404);
        }

        $application = null;
        if (Auth::check()) {
            $application = JobApplication::where('job_vacancy_id', $job->id)
                ->where('user_id', Auth::id())
                ->latest()
                ->first();
        }

        return view('site.jobs.show', compact('job', 'application'));
    }

    public function apply(Request $request, JobVacancy $job, JobApplicationService $jobApplicationService)
    {
        if (!$job->is_active || !$this->isPublicVisibility($job)) {
            return response()->json([
                'success' => false,
                'message' => 'Vaga nao encontrada.',
            ], 404);
        }

        $result = $jobApplicationService->apply(
            $job,
            Auth::user(),
            $request->input('file_data'),
            $request->input('file_name'),
            $request->input('cover_letter')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], (int) ($result['status'] ?? 500));
    }

    private function isPublicVisibility(JobVacancy $job): bool
    {
        $visibility = strtolower((string) ($job->visibility ?? 'public'));
        return in_array($visibility, ['public', 'external', 'both'], true);
    }
}

