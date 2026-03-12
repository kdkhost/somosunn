<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\JobApplication;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $vacancies = JobVacancy::with('user')->withCount('applications')->latest()->get();
        return view('admin.jobs.index', compact('vacancies'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        return view('admin.jobs.form', ['vacancy' => new JobVacancy()]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'type' => 'required|string',
            'level' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_demo' => 'nullable|boolean',
        ]);

        $data['user_id'] = Auth::id();
        $data['is_active'] = $request->has('is_active');
        $data['is_demo'] = $request->has('is_demo');

        if ($request->hasFile('image')) {
            $imagePath = app(WatermarkService::class)->processStorageImage(
                $request->file('image'),
                'jobs',
                null,
                ['prefix' => 'job']
            );
            $data['image'] = 'storage/' . $imagePath;
        }

        JobVacancy::create($data);

        return redirect()->route('admin.jobs.index')->with('success', 'Vaga criada com sucesso!');
    }

    public function edit(JobVacancy $job)
    {
        $this->authorizeAdmin();
        return view('admin.jobs.form', ['vacancy' => $job]);
    }

    public function update(Request $request, JobVacancy $job)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'type' => 'required|string',
            'level' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_demo' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['is_demo'] = $request->has('is_demo');

        if ($request->hasFile('image')) {
            // Remover imagem antiga se existir
            if ($job->image && file_exists(public_path($job->image))) {
                @unlink(public_path($job->image));
            }
            $imagePath = app(WatermarkService::class)->processStorageImage(
                $request->file('image'),
                'jobs',
                null,
                ['prefix' => 'job']
            );
            $data['image'] = 'storage/' . $imagePath;
        }

        $job->update($data);

        return redirect()->route('admin.jobs.index')->with('success', 'Vaga atualizada com sucesso!');
    }

    public function destroy(JobVacancy $job)
    {
        $this->authorizeAdmin();
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('success', 'Vaga removida com sucesso!');
    }

    private function authorizeAdmin()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
