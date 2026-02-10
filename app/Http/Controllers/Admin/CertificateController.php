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

    public function regenerate(Certificate $certificate)
    {
        // 1. Check permissions (Admin or Instructor Owner)
        $user = auth()->user();
        $isOwner = false;

        $product = $certificate->course ?? $certificate->mentorship ?? $certificate->event;
        $type = $certificate->course ? 'course' : ($certificate->mentorship ? 'mentorship' : 'event');

        if ($type === 'course' && $product->user_id === $user->id)
            $isOwner = true;
        if ($type === 'mentorship' && $product->mentor_id === $user->id)
            $isOwner = true;
        if ($type === 'event' && $product->user_id === $user->id)
            $isOwner = true;

        if (!$user->isAdmin() && !$isOwner) {
            abort(403, 'Você não tem permissão para regenerar este certificado.');
        }

        // 2. Regenerate PDF content
        $output = $this->generatePdfContent($certificate->user, $type, $product, $certificate->cert_hash);

        // 3. Overwrite existing file
        Storage::disk('public')->put($certificate->pdf_path, $output);

        // 4. Update timestamp
        $certificate->touch();

        return redirect()->back()->with('success', 'Certificado regenerado com o design atual!');
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

        $output = $this->generatePdfContent($user, $type, $product, $certHash);

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

    private function generatePdfContent($user, $type, $product, $certHash)
    {
        // Use the same template, but it needs a unified "product" object for background/settings
        // We'll prepare specific labels for different product types
        $authorName = 'Instrutor';
        $workload = 0;

        if ($type === 'course') {
            $authorName = $product->author_name;
            // Use stored workload from certificate if available (passed via $product if it was a certificate object, but here $product is Course)
            // Wait, $product is the Course/Mentorship model. We need the Certificate object to get the stored workload.
            // The method signature is generatePdfContent($user, $type, $product, $certHash).
            // We can look up the certificate by hash to get the workload.
            $cert = Certificate::where('cert_hash', $certHash)->first();
            if ($cert && $cert->workload) {
                $workload = $cert->workload;
            } else {
                $workload = $product->total_hours;
            }
        } elseif ($type === 'mentorship') {
            $authorName = $product->mentor ? $product->mentor->name : 'Mentor';
            $cert = Certificate::where('cert_hash', $certHash)->first();
            if ($cert && $cert->workload) {
                $workload = $cert->workload;
            } else {
                $workload = $product->total_hours ?? 0;
            }
            // Mentorships might not have certificate settings yet, use defaults or course structure
        } elseif ($type === 'event') {
            $authorName = $product->user ? $product->user->name : 'Organizador';
            $cert = Certificate::where('cert_hash', $certHash)->first();
            if ($cert && $cert->workload) {
                $workload = $cert->workload;
            } else {
                $workload = $product->duration_hours ?? 0;
            }
        }

        // Configure DomPDF
        $options = new Options();
        $options->set('isRemoteEnabled', true); // Allow remote images (http/https)
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', public_path()); // Allow access to public folder

        $dompdf = new Dompdf($options);

        // Sanitize background path for Windows (DomPDF prefers forward slashes)
        if ($product->certificate_bg) {
            $product->certificate_bg = str_replace('\\', '/', $product->certificate_bg);
        }

        $html = view('admin.certificates.template', [
            'user' => $user,
            'course' => $product, // $product is passed as $course to the view
            'certHash' => $certHash,
            'authorName' => $authorName,
            'workload' => $workload,
            'type' => $type,
            'isPreview' => false // Explicitly set for PDF generation
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
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

        $pdfPath = $cert->pdf_path;
        // Construct path manually to avoid static analysis warnings with generic Filesystem disk
        $path = storage_path('app/public/' . $pdfPath);

        if (!file_exists($path)) {
            // Check if public/storage is a real dir (common in some hostings)
            $altPath = public_path('storage/' . $pdfPath);
            if (file_exists($altPath)) {
                $path = $altPath;
            } else {
                return response()->json(['error' => 'Arquivo do certificado não encontrado no servidor.'], 404);
            }
        }

        if (request()->has('download')) {
            return response()->download($path, "certificado_{$hash}.pdf");
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificado.pdf"'
        ]);
    }

    public function previewHtml($hash)
    {
        $cert = Certificate::where('cert_hash', $hash)->firstOrFail();
        $user = $cert->user;

        $product = $cert->course ?? $cert->mentorship ?? $cert->event;
        $type = $cert->course ? 'course' : ($cert->mentorship ? 'mentorship' : 'event');

        $authorName = 'Instrutor';
        $workload = 0;

        if ($type === 'course') {
            $authorName = $product->author_name;
            $workload = $cert->workload > 0 ? $cert->workload : $product->total_hours;
        } elseif ($type === 'mentorship') {
            $authorName = $product->mentor ? $product->mentor->name : 'Mentor';
            $workload = $cert->workload > 0 ? $cert->workload : ($product->total_hours ?? 0);
        } elseif ($type === 'event') {
            $authorName = $product->user ? $product->user->name : 'Organizador';
            $workload = $cert->workload > 0 ? $cert->workload : ($product->duration_hours ?? 0);
        }

        return view('admin.certificates.template', [
            'user' => $user,
            'course' => $product,
            'certHash' => $cert->cert_hash,
            'authorName' => $authorName,
            'workload' => $workload,
            'type' => $type,
            'isPreview' => true
        ]);
    }

    public function destroy(Certificate $certificate)
    {
        // Permission Check: ONLY SUPERADMIN
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Acesso negado. Apenas administradores podem excluir certificados.');
        }

        // Optional: Stricter check if you have a 'superadmin' string column
        // if ($user->role !== 'superadmin') ...

        try {
            // 1. Delete PDF file
            if ($certificate->pdf_path && Storage::disk('public')->exists($certificate->pdf_path)) {
                Storage::disk('public')->delete($certificate->pdf_path);
            }

            // 2. Delete Record
            $certificate->delete();

            return redirect()->back()->with('success', 'Certificado excluído com sucesso! O aluno agora aparece como pendente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir: ' . $e->getMessage());
        }
    }
}