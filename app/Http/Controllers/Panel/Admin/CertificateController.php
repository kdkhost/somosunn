<?php

namespace App\Http\Controllers\Panel\Admin;

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
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function index()
    {
        $this->ensurePermission('certificates.view');
        $user = Auth::user();

        // Issued Certificates
        $queryIssued = Certificate::with(['user', 'course', 'mentorship', 'event'])->latest();

        // Pending Certificates
        $queryPending = Enrollment::with(['user', 'enrollable'])
            ->whereNotNull('completed_at');

        if (!$user->isAdmin()) {
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

        $issuedCertificates = $queryIssued->paginate(15, ['*'], 'issued_page');
        $issuedCertificates->appends(request()->all());

        $pendingEnrollments = $queryPending->get()->filter(function ($enrollment) {
            $query = Certificate::where('user_id', $enrollment->user_id);
            if ($enrollment->enrollable_type === Course::class)
                $query->where('course_id', $enrollment->enrollable_id);
            elseif ($enrollment->enrollable_type === Mentorship::class)
                $query->where('mentorship_id', $enrollment->enrollable_id);
            elseif ($enrollment->enrollable_type === Event::class)
                $query->where('event_id', $enrollment->enrollable_id);
            return !$query->exists();
        });

        return view('panel.admin.certificates.index', compact('issuedCertificates', 'pendingEnrollments'));
    }

    public function generate(Request $request)
    {
        $this->ensurePermission('certificates.create');
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'mentorship_id' => 'nullable|exists:mentorships,id',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $type = $request->filled('course_id') ? 'course' : ($request->filled('mentorship_id') ? 'mentorship' : ($request->filled('event_id') ? 'event' : null));
        $id = $data['course_id'] ?? $data['mentorship_id'] ?? $data['event_id'];

        if (!$type)
            return redirect()->back()->with('error', 'Selecione um produto.');

        $this->issueCertificate($data['user_id'], $type, $id);
        return redirect()->back()->with('success', 'Certificado gerado com sucesso!');
    }

    public function issueCertificate($userId, $type, $id)
    {
        $user = User::find($userId);
        $product = null;
        $certQuery = Certificate::where('user_id', $user->id);

        if ($type === 'course') {
            $product = Course::find($id);
            $certQuery->where('course_id', $id);
        } elseif ($type === 'mentorship') {
            $product = Mentorship::find($id);
            $certQuery->where('mentorship_id', $id);
        } elseif ($type === 'event') {
            $product = Event::find($id);
            $certQuery->where('event_id', $id);
        }

        if ($certQuery->exists())
            return $certQuery->first();

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
            'issued_at' => now(),
            'workload' => ($type === 'course' ? $product->total_hours : ($product->duration_hours ?? 0))
        ]);
    }

    private function generatePdfContent($user, $type, $product, $certHash)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', public_path());
        $dompdf = new Dompdf($options);

        $authorName = $type === 'course' ? $product->author_name : ($type === 'mentorship' ? ($product->mentor->name ?? 'Mentor') : ($product->user->name ?? 'Organizador'));
        $workload = $type === 'course' ? $product->total_hours : ($product->duration_hours ?? 0);

        $fontCss = app(CertificateFontCssGenerator::class)->buildFontCss($product->certificate_settings ?? [], false);

        $html = view('admin.certificates.template', [
            'user' => $user,
            'course' => $product,
            'certHash' => $certHash,
            'authorName' => $authorName,
            'workload' => $workload,
            'type' => $type,
            'fontCss' => $fontCss,
            'isPreview' => false
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    public function destroy(Certificate $certificate)
    {
        $this->ensurePermission('certificates.delete');
        if ($certificate->pdf_path)
            Storage::disk('public')->delete($certificate->pdf_path);
        $certificate->delete();
        return redirect()->back()->with('success', 'Certificado excluído.');
    }

    protected function ensurePermission(string $perm)
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->hasPermission($perm))
            abort(403);
    }
}
