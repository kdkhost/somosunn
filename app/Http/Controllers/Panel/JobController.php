<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Services\JobApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        $vacancies = JobVacancy::where(function ($q) {
            $q->where(function ($q2) {
                $q2->where('is_active', true)
                    ->where(function ($q3) {
                        $q3->whereNull('visibility')
                            ->orWhereIn('visibility', ['public', 'external', 'both']);
                    })
                    ->where(function ($q4) {
                        $q4->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    });
            })->orWhereHas('applications', function ($q5) {
                $q5->where('user_id', Auth::id());
            });
        })
            ->with([
                'applications' => function ($q) {
                    $q->where('user_id', Auth::id())->latest();
                }
            ])
            ->latest()
            ->get();

        return view('panel.jobs.index', compact('vacancies'));
    }

    public function show(JobVacancy $job)
    {
        $application = JobApplication::where('job_vacancy_id', $job->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        $visibility = strtolower((string) ($job->visibility ?? 'public'));
        $isPublicVisibility = in_array($visibility, ['public', 'external', 'both'], true);
        $isNotExpired = !$job->expires_at || $job->expires_at->isFuture();
        $canAccessAsOpenVacancy = $job->is_active && $isPublicVisibility && $isNotExpired;

        if (!$application && !$canAccessAsOpenVacancy) {
            abort(404);
        }

        return view('panel.jobs.show', compact('job', 'application'));
    }

    public function apply(Request $request, JobVacancy $job, JobApplicationService $jobApplicationService)
    {
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
}
