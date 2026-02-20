<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;

class JobPublicController extends Controller
{
    public function index()
    {
        $vacancies = JobVacancy::where('is_active', true)
            ->whereIn('visibility', ['external', 'both'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->get();

        return view('site.jobs.index', compact('vacancies'));
    }

    public function show(JobVacancy $job)
    {
        if (!$job->is_active || $job->visibility === 'internal') {
            abort(404);
        }

        return view('site.jobs.show', compact('job'));
    }
}
