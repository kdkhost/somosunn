<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function createForm()
    {
        $courses = Course::all();
        $users = User::all();
        return view('admin.certificates.form', compact('courses','users'));
    }

    public function generate(Request $request)
    {
        $data = $request->validate(['user_id'=>'required|exists:users,id','course_id'=>'required|exists:courses,id']);
        $user = User::find($data['user_id']);
        $course = Course::find($data['course_id']);

        $certHash = Str::random(24);
        $html = view('admin.certificates.template', compact('user','course','certHash'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();

        $path = "certificates/{$certHash}.pdf";
        Storage::disk('public')->put($path, $output);

        $cert = Certificate::create(['user_id'=>$user->id,'course_id'=>$course->id,'cert_hash'=>$certHash,'pdf_path'=>$path,'issued_at'=>now()]);

        return redirect()->route('admin.dashboard')->with('success','Certificado gerado: '.$certHash);
    }

    public function view($hash)
    {
        $cert = Certificate::where('cert_hash',$hash)->firstOrFail();
        return response()->file(Storage::disk('public')->path($cert->pdf_path));
    }
}