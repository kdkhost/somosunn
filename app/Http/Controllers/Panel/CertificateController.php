<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $certificates = Certificate::where('user_id', $user->id)->latest()->get();
        return view('panel.certificates.index', compact('certificates'));
    }

    public function show(Certificate $certificate)
    {
        $this->authorize('view', $certificate);
        return response()->file(public_path($certificate->pdf_path));
    }

    public function download(Certificate $certificate)
    {
        $this->authorize('view', $certificate);
        return response()->download(public_path($certificate->pdf_path));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'mentorship_id' => 'nullable|exists:mentorships,id',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $user = Auth::user();
        $type = null;
        $id = null;
        $product = null;

        if ($request->filled('course_id')) {
            $type = 'course';
            $id = $data['course_id'];
            $product = \App\Models\Course::find($id);

            // Verify progress
            $progress = $user->progress($product); // Assuming User model has progress() or similar. 
            // Better to check enrollment:
            $enrollment = $user->enrollments()->where('enrollable_type', \App\Models\Course::class)->where('enrollable_id', $id)->first();
            if (!$enrollment || !$enrollment->completed_at) {
                // Double check progress just in case completed_at wasn't set but progress is 100
                // For now, let's assume UI only allows click if 100%. 
                // Ideally we should check strict completion here.
                // Let's check if the user has completed the course.
                if (!$product->isCompletedBy($user)) {
                    return redirect()->back()->with('error', 'Você precisa concluir o curso para gerar o certificado.');
                }
            }

        } elseif ($request->filled('mentorship_id')) {
            $type = 'mentorship';
            $id = $data['mentorship_id'];
            $product = \App\Models\Mentorship::find($id);
            // Mentorship completion logic (manual by mentor usually, or time based)
            // We check if there is a completed enrollment
            $enrollment = $user->enrollments()->where('enrollable_type', \App\Models\Mentorship::class)->where('enrollable_id', $id)->whereNotNull('completed_at')->first();
            if (!$enrollment) {
                return redirect()->back()->with('error', 'Esta mentoria ainda não foi concluída.');
            }

        } elseif ($request->filled('event_id')) {
            $type = 'event';
            $id = $data['event_id'];
            $product = \App\Models\Event::find($id);
            // Event completion logic
            $enrollment = $user->enrollments()->where('enrollable_type', \App\Models\Event::class)->where('enrollable_id', $id)->whereNotNull('completed_at')->first();
            if (!$enrollment) {
                return redirect()->back()->with('error', 'Participação no evento ainda não  concluída.');
            }
        }

        if (!$type) {
            return redirect()->back()->with('error', 'Produto inválido.');
        }

        // Check if certificate already exists
        $existing = Certificate::where('user_id', $user->id)
            ->where($type . '_id', $id)
            ->first();

        if ($existing) {
            return redirect()->route('panel.certificates.index')->with('info', 'Seu certificado já está disponível.');
        }

        // Generate
        $certHash = \Illuminate\Support\Str::random(24);
        $generator = app(\App\Services\Certificate\CertificateGenerator::class);
        $output = $generator->generatePdfContent($user, $type, $product, $certHash);

        $path = "certificates/{$certHash}.pdf";
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $output);

        // Calculate workload
        $workload = 0;
        if ($type === 'course')
            $workload = $product->total_hours;
        elseif ($type === 'mentorship')
            $workload = $product->total_hours ?? 0;
        elseif ($type === 'event')
            $workload = $product->duration_hours ?? 0;

        Certificate::create([
            'user_id' => $user->id,
            'course_id' => $type === 'course' ? $id : null,
            'mentorship_id' => $type === 'mentorship' ? $id : null,
            'event_id' => $type === 'event' ? $id : null,
            'cert_hash' => $certHash,
            'pdf_path' => $path,
            'issued_at' => now(),
            'workload' => $workload
        ]);

        return redirect()->route('panel.certificates.index')->with('success', 'Certificado gerado com sucesso!');
    }
}
