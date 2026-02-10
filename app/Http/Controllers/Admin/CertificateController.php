<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Mentorship;
use App\Models\Event;
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
        $queryIssued = Certificate::with(['user', 'course', 'mentorship', 'event'])->latest();

        // 2. Pending Certificates (Enrollment completed but no Certificate)
        // We look for all completion types (Course, Mentorship, Event)
        $queryPending = Enrollment::with(['user', 'enrollable'])
            ->whereNotNull('completed_at');

        // REFINING ACCESS CONTROL
        if (!$user->isAdmin()) {
            // Instructor Mode: Show only for courses/mentorships/events created by this user
            $myCourseIds = Course::where('user_id', $user->id)->pluck('id');
            $myMentorshipIds = Mentorship::where('mentor_id', $user->id)->pluck('id');
            $myEventIds = Event::where('user_id', $user->id)->pluck('id');

            $queryIssued->where(function ($q) use ($myCourseIds, $myMentorshipIds, $myEventIds) {
                $q->whereIn('course_id', $myCourseIds)
                    ->orWhereIn('mentorship_id', $myMentorshipIds)
                    ->orWhereIn('event_id', $myEventIds);
            });

            $queryPending->where(function ($q) use ($myCourseIds, $myMentorshipIds, $myEventIds) {
                $q->where(function ($sub) use ($myCourseIds) {
                    $sub->where('enrollable_type', Course::class)->whereIn('enrollable_id', $myCourseIds);
                })->orWhere(function ($sub) use ($myMentorshipIds) {
                    $sub->where('enrollable_type', Mentorship::class)->whereIn('enrollable_id', $myMentorshipIds);
                })->orWhere(function ($sub) use ($myEventIds) {
                    $sub->where('enrollable_type', Event::class)->whereIn('enrollable_id', $myEventIds);
                });
            });
        }

        $issuedCertificates = $queryIssued->get();

        // Better Pending Logic: filter out those who already have a certificate for that specific enrollment
        $pendingEnrollments = $queryPending->get()->filter(function ($enrollment) {
            $query = Certificate::where('user_id', $enrollment->user_id);

            if ($enrollment->enrollable_type === Course::class) {
                $query->where('course_id', $enrollment->enrollable_id);
            } elseif ($enrollment->enrollable_type === Mentorship::class) {
                $query->where('mentorship_id', $enrollment->enrollable_id);
            } elseif ($enrollment->enrollable_type === Event::class) {
                $query->where('event_id', $enrollment->enrollable_id);
            }

            return !$query->exists();
        });

        return view('admin.certificates.index', compact('issuedCertificates', 'pendingEnrollments'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'mentorship_id' => 'nullable|exists:mentorships,id',
            'event_id' => 'nullable|exists:events,id',
        ]);

        // Identify type and ID
        $type = null;
        $id = null;

        if ($request->filled('course_id')) {
            $type = 'course';
            $id = $data['course_id'];
        } elseif ($request->filled('mentorship_id')) {
            $type = 'mentorship';
            $id = $data['mentorship_id'];
        } elseif ($request->filled('event_id')) {
            $type = 'event';
            $id = $data['event_id'];
        }

        if (!$type) {
            return redirect()->back()->with('error', 'Selecione um produto para o certificado.');
        }

        $this->issueCertificate($data['user_id'], $type, $id);

        return redirect()->back()->with('success', 'Certificado gerado com sucesso!');
    }

    // Updated to support multiple types
    private function issueCertificate($userId, $type, $id)
    {
        $user = User::find($userId);

        $course = null;
        $mentorship = null;
        $event = null;
        $product = null;

        $certQuery = Certificate::where('user_id', $user->id);

        if ($type === 'course') {
            $product = $course = Course::find($id);
            $certQuery->where('course_id', $id);
        } elseif ($type === 'mentorship') {
            $product = $mentorship = Mentorship::find($id);
            $certQuery->where('mentorship_id', $id);
        } elseif ($type === 'event') {
            $product = $event = Event::find($id);
            $certQuery->where('event_id', $id);
        }

        // Check duplicate
        $exists = $certQuery->first();
        if ($exists)
            return $exists;

        $certHash = Str::random(24);

        // Use the same template, but it needs a unified "product" object for background/settings
        // We'll prepare specific labels for different product types
        $authorName = 'Instrutor';
        $workload = 0;

        if ($type === 'course') {
            $authorName = $product->author_name;
            $workload = $product->total_hours;
        } elseif ($type === 'mentorship') {
            $authorName = $product->mentor ? $product->mentor->name : 'Mentor';
            $workload = 0; // Or fetch from a field if added
        } elseif ($type === 'event') {
            $authorName = $product->user ? $product->user->name : 'Organizador';
            $workload = 0;
        }

        $html = view('admin.certificates.template', [
            'user' => $user,
            'course' => $product,
            'certHash' => $certHash,
            'authorName' => $authorName,
            'workload' => $workload,
            'type' => $type
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();

        $path = "certificates/{$certHash}.pdf";
        Storage::disk('public')->put($path, $output);

        return Certificate::create([
            'user_id' => $user->id,
            'course_id' => $type === 'course' ? $id : null,
            'mentorship_id' => $type === 'mentorship' ? $id : null,
            'event_id' => $type === 'event' ? $id : null,
            'cert_hash' => $certHash,
            'pdf_path' => $path,
            'issued_at' => now()
        ]);
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
        $path = storage_path('app/public/' . $cert->pdf_path);

        if (request()->has('download')) {
            return response()->download($path, "certificado_{$hash}.pdf");
        }

        return response()->file($path);
    }
}