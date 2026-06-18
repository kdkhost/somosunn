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
use App\Services\Certificate\CertificateFontCssGenerator;

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
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->withoutCertificate();

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

        $pendingEnrollments = $queryPending->get();

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
        $generator = app(\App\Services\Certificate\CertificateGenerator::class);
        $output = $generator->generatePdfContent(
            $certificate->user,
            $type,
            $product,
            $certificate->cert_hash,
            $certificate->workload ?? 0
        );

        // 3. Ensure pdf_path exists, create if null (for legacy certificates)
        if (!$certificate->pdf_path) {
            $certificate->pdf_path = "certificates/{$certificate->cert_hash}.pdf";
        }

        // Overwrite or create file
        Storage::disk('public')->put($certificate->pdf_path, $output);

        // 4. Update timestamp and save path
        $certificate->touch();
        $certificate->save();

        return redirect()->back()->with('success', 'Certificado regenerado com o design atual!');
    }

    // Updated to support multiple types
    public function issueCertificate($userId, $type, $id)
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

        // Use Service
        $generator = app(\App\Services\Certificate\CertificateGenerator::class);
        $output = $generator->generatePdfContent($user, $type, $product, $certHash);

        $path = "certificates/{$certHash}.pdf";
        Storage::disk('public')->put($path, $output);

        // Calculate workload to save in DB
        $workload = 0;
        if ($type === 'course')
            $workload = $product->total_hours;
        elseif ($type === 'mentorship')
            $workload = $product->total_hours ?? 0;
        elseif ($type === 'event')
            $workload = $product->duration_hours ?? 0;

        return Certificate::create([
            'user_id' => $user->id,
            'course_id' => $type === 'course' ? $id : null,
            'mentorship_id' => $type === 'mentorship' ? $id : null,
            'event_id' => $type === 'event' ? $id : null,
            'cert_hash' => $certHash,
            'pdf_path' => $path,
            'issued_at' => now(),
            'workload' => $workload
        ]);
    }

    // Removed generatePdfContent as it is now in Service

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
        $type = null;
        $id = null;
        $path = $this->resolverCaminhoPdfCertificado($cert->pdf_path);

        // If PDF doesn't exist OR regeneration is forced
        if (!$path || request()->has('regenerate')) {
            if ($cert->course_id) {
                $type = 'course';
                $id = $cert->course_id;
            } elseif ($cert->mentorship_id) {
                $type = 'mentorship';
                $id = $cert->mentorship_id;
            } elseif ($cert->event_id) {
                $type = 'event';
                $id = $cert->event_id;
            }

            if ($type && $id) {
                $user = $cert->user;
                $product = null;

                if ($type === 'course') {
                    $product = \App\Models\Course::find($id);
                } elseif ($type === 'mentorship') {
                    $product = \App\Models\Mentorship::find($id);
                } elseif ($type === 'event') {
                    $product = \App\Models\Event::find($id);
                }

                if ($product) {
                    $generator = app(\App\Services\Certificate\CertificateGenerator::class);
                    $output = $generator->generatePdfContent(
                        $user,
                        $type,
                        $product,
                        $cert->cert_hash,
                        $cert->workload ?? 0
                    );

                    $newPath = "certificates/{$cert->cert_hash}.pdf";
                    \Storage::disk('public')->put($newPath, $output);
                    $cert->update(['pdf_path' => $newPath]);
                    $path = $this->resolverCaminhoPdfCertificado($newPath);
                }
            }
        }


        if (!$path || !is_file($path)) {
            \Log::error('Certificate PDF not found or regeneration failed', [
                'cert_id' => $cert->id,
                'cert_hash' => $cert->cert_hash,
                'pdf_path' => $cert->pdf_path,
                'type' => $type ?? 'unknown',
                'product_id' => $id ?? null,
                'path_attempted' => $path
            ]);

            return response()->json([
                'error' => 'Não foi possível gerar o certificado.',
                'details' => 'O PDF não pôde ser criado. Verifique os logs do servidor.',
                'cert_id' => $cert->id
            ], 500);
        }

        if (request()->has('download')) {
            return response()->download($path, "certificado_{$hash}.pdf");
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="certificado.pdf"'
        ]);
    }

    private function resolverCaminhoPdfCertificado(?string $pdfPath): ?string
    {
        $pdfPath = trim((string) ($pdfPath ?? ''));
        if ($pdfPath === '' || $pdfPath === '/' || $pdfPath === '\\') {
            return null;
        }

        $normalizado = str_replace('\\', '/', $pdfPath);
        $normalizado = ltrim($normalizado, '/');
        foreach (['storage/', 'public/', 'app/public/'] as $prefixo) {
            if (str_starts_with($normalizado, $prefixo)) {
                $normalizado = substr($normalizado, strlen($prefixo));
                break;
            }
        }

        $candidatos = [];
        try {
            if (Storage::disk('public')->exists($normalizado)) {
                $candidatos[] = Storage::disk('public')->path($normalizado);
            }
        } catch (\Throwable $e) {
            // Continua com fallback de caminhos locais
        }

        $candidatos[] = storage_path('app/public/' . $normalizado);
        $candidatos[] = public_path('storage/' . $normalizado);
        $candidatos[] = storage_path('app/' . $normalizado);
        $candidatos[] = public_path($normalizado);

        foreach ($candidatos as $caminho) {
            if (is_string($caminho) && $caminho !== '' && is_file($caminho)) {
                return $caminho;
            }
        }

        return null;
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

        $fontCss = app(CertificateFontCssGenerator::class)->buildFontCss($product->certificate_settings ?? [], true);

        return view('admin.certificates.template', [
            'user' => $user,
            'course' => $product,
            'certHash' => $cert->cert_hash,
            'authorName' => $authorName,
            'workload' => $workload,
            'type' => $type,
            'fontCss' => $fontCss,
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
