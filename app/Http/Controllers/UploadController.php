<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // Basic validations: will be extended (file type/size)
        $maxMb = config('uploads.video_max_mb', 1024);
        $maxKb = $maxMb * 1024;

        $request->validate([
            'file' => 'required|file|max:'. $maxKb,
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $allowedV = config('uploads.allowed_video_formats', []);
        $allowedD = config('uploads.allowed_document_formats', []);
        $allowed = array_merge($allowedV, $allowedD);

        if(!in_array($ext, $allowed)){
            return response()->json(['error' => 'Formato de arquivo não permitido'], 422);
        }

        $path = $file->store('uploads', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path), 'path' => $path]);
    }
}