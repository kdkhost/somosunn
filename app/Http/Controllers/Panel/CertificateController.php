<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;

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
}
