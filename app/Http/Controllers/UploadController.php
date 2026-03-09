<?php

namespace App\Http\Controllers;

use App\Support\UploadStorage;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $allowedV = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_video_formats', [])));
        $allowedD = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_document_formats', [])));

        $isVideo = in_array($ext, $allowedV, true);
        $isDoc = in_array($ext, $allowedD, true);

        if(!$isVideo && !$isDoc){
            return response()->json(['error' => 'Formato de arquivo não permitido'], 422);
        }

        $maxMb = (int) ($isVideo ? config('uploads.video_max_mb', 1024) : config('uploads.document_max_mb', 50));
        $maxKb = max(1, $maxMb) * 1024;

        $request->validate([
            'file' => 'required|file|max:' . $maxKb,
        ]);

        $disk = (string) config('uploads.disk', 'public');
        if ($disk === '') {
            $disk = 'public';
        }

        $path = $file->storePublicly('uploads', $disk);

        return response()->json([
            'url' => UploadStorage::url($path),
            'path' => $path,
        ]);
    }
}
