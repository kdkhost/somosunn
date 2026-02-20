<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Models\User;
use App\Notifications\JobVacancyPublished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class MyJobController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();
        $vacancies = JobVacancy::where('user_id', Auth::id())->latest()->get();
        return view('panel.my-jobs.index', compact('vacancies'));
    }

    public function create()
    {
        $this->authorizeAccess();
        return view('panel.my-jobs.form', ['vacancy' => new JobVacancy()]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'type' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'visibility' => 'required|in:internal,external,both',
        ]);

        $data['user_id'] = Auth::id();
        $data['is_active'] = true;

        $vacancy = JobVacancy::create($data);

        // Notificar toda a comunidade
        $users = User::all();
        Notification::send($users, new JobVacancyPublished($vacancy));

        return redirect()->route('panel.my-jobs.index')->with('success', 'Vaga publicada e comunidade notificada com sucesso!');
    }

    public function edit(JobVacancy $my_job)
    {
        $this->authorizeAccess();
        if ($my_job->user_id !== Auth::id()) {
            abort(403);
        }

        return view('panel.my-jobs.form', ['vacancy' => $my_job]);
    }

    public function update(Request $request, JobVacancy $my_job)
    {
        $this->authorizeAccess();
        if ($my_job->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'type' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'salary_range' => 'nullable|string|max:255',
            'expires_at' => 'nullable|date',
            'visibility' => 'required|in:internal,external,both',
        ]);

        $my_job->update($data);

        return redirect()->route('panel.my-jobs.index')->with('success', 'Vaga atualizada com sucesso!');
    }

    public function destroy(JobVacancy $my_job)
    {
        $this->authorizeAccess();
        if ($my_job->user_id !== Auth::id()) {
            abort(403);
        }

        $my_job->delete();
        return redirect()->route('panel.my-jobs.index')->with('success', 'Vaga removida com sucesso!');
    }

    private function authorizeAccess()
    {
        if (!Auth::user()->canAccessFeature('vagas_create')) {
            abort(403, 'Seu plano atual não permite publicar vagas.');
        }
    }
}
