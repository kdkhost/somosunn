<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificateIssued;

class CertificateController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Issued Certificates
        $queryIssued = Certificate::with(['user', 'course'])->latest();

        // 2. Pending Certificates (Enrollment completed but no Certificate)
        $queryPending = Enrollment::with(['user', 'enrollable'])
            ->where('enrollable_type', Course::class)
            ->whereNotNull('completed_at')
            ->whereDoesntHave('user.certificates', function ($q) {
                // Ensure check matches the specific course of the enrollment
                // This complex check is easier done in PHP for collection filtering or raw query 
                // but let's try a standard Eloquent approach:
                // We want enrollments where NO certificate exists for (user_id, course_id)
            });

        // REFINING ACCESS CONTROL
        if (!$user->isAdmin()) {
            // Instructor Mode: Show only certificates/pending for courses created by this user
            $myCourseIds = Course::where('user_id', $user->id)->pluck('id');

            $queryIssued->whereIn('course_id', $myCourseIds);

            $queryPending->whereHasMorph('enrollable', [Course::class], function ($q) use ($myCourseIds) {
                $q->whereIn('id', $myCourseIds);
            });
        }

        $issuedCertificates = $queryIssued->get(); // Limit if too many?

        // Fetch pending candidates
        // It's tricky to filter "DoesntHave certificate for THIS course" efficiently in one go without a join.
        // Let's fetch completed enrollments (filtered by permissions) and filter in PHP.
        // Since "completed" students shouldn't be huge in number (vs started), this is acceptable for now.
        $pendingEnrollments = $queryPending->get()->filter(function ($enrollment) {
            // Check if certificate already exists for this enrollment's course/user
            return !Certificate::where('user_id', $enrollment->user_id)
                ->where('course_id', $enrollment->enrollable_id)
                ->exists();
        });

        return view('admin.certificates.index', compact('issuedCertificates', 'pendingEnrollments'));
    }

    public function createForm()
    {
        $courses = Course::all();
        $users = User::all();
        return view('admin.certificates.form', compact('courses', 'users'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id', 'course_id' => 'required|exists:courses,id']);
        $this->issueCertificate($data['user_id'], $data['course_id']);

        return redirect()->back()->with('success', 'Certificado gerado com sucesso!');
    }

    // Reuse logic for both manual form and "Pending" list
    private function issueCertificate($userId, $courseId)
    {
        $user = User::find($userId);
        $course = Course::find($courseId);

        // Check duplicate
        $exists = Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($exists)
            return $exists;

        $certHash = Str::random(24);
        $html = view('admin.certificates.template', compact('user', 'course', 'certHash'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();

        $path = "certificates/{$certHash}.pdf";
        Storage::disk('public')->put($path, $output);

        $cert = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'cert_hash' => $certHash,
            'pdf_path' => $path,
            'issued_at' => now()
        ]);

        return $cert;
    }

    public function sendEmail(Certificate $certificate)
    {
        // Permission Check
        $user = auth()->user();
        if (!$user->isAdmin() && $certificate->course->user_id !== $user->id) {
            abort(403, 'Acesso negado');
        }

        try {
            Mail::to($certificate->user->email)->send(new CertificateIssued($certificate));
            return redirect()->back()->with('success', 'Certificado enviado por e-mail para ' . $certificate->user->email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }

    public function view($hash)
    {
        $cert = Certificate::where('cert_hash', $hash)->firstOrFail();
        return response()->file(storage_path('app/public/' . $cert->pdf_path));
    }
}