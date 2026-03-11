<?php

namespace App\Http\Controllers;

use App\Support\UploadStorage;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadChunkController extends Controller
{
    private const ALLOWED_IMAGE_FORMATS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    private const ALLOWED_AUDIO_FORMATS = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'];

    public function storeChunk(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'upload_id' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'sometimes|integer',
        ]);

        $uploadId = $request->input('upload_id');
        $chunkIndex = $request->input('chunk_index');

        $dir = "uploads/tmp/{$uploadId}";
        $file = $request->file('file');

        Storage::disk('local')->putFileAs($dir, $file, "chunk_{$chunkIndex}");

        return response()->json(['ok' => true, 'chunk' => $chunkIndex]);
    }

    public function assemble(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|string',
            'filename' => 'required|string',
            'total_chunks' => 'required|integer',
        ]);

        $uploadId = $request->input('upload_id');
        $total = (int) $request->input('total_chunks');
        $originalName = (string) $request->input('filename');
        $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
            . '-' . time()
            . '.' . pathinfo($originalName, PATHINFO_EXTENSION);

        $dir = storage_path("app/uploads/tmp/{$uploadId}");
        if (!is_dir($dir)) {
            return response()->json(['error' => 'Upload nao encontrado'], 404);
        }

        $targetDisk = (string) config('uploads.disk', 'public');
        if ($targetDisk === '') {
            $targetDisk = 'public';
        }

        $publicDisk = Storage::disk('public');
        $outRelativePath = "uploads/{$filename}";
        $outPath = $publicDisk->path($outRelativePath);

        $outDir = dirname($outPath);
        if (!is_dir($outDir)) {
            mkdir($outDir, 0775, true);
        }

        $out = fopen($outPath, 'wb');
        if (!$out) {
            return response()->json(['error' => 'Falha ao criar arquivo de saida'], 500);
        }

        for ($i = 0; $i < $total; $i++) {
            $chunkFile = $dir . "/chunk_{$i}";
            if (!file_exists($chunkFile)) {
                fclose($out);
                @unlink($outPath);

                return response()->json(['error' => "Chunk {$i} faltando"], 422);
            }

            $in = fopen($chunkFile, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }

        fclose($out);

        array_map('unlink', glob("{$dir}/*"));
        rmdir($dir);

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedVideoFormats = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_video_formats', [])));
        $allowedDocumentFormats = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_document_formats', [])));

        $isImage = in_array($ext, self::ALLOWED_IMAGE_FORMATS, true);
        $isVideo = in_array($ext, $allowedVideoFormats, true);
        $isDocument = in_array($ext, $allowedDocumentFormats, true);
        $isAudio = in_array($ext, self::ALLOWED_AUDIO_FORMATS, true);

        if (!$isImage && !$isVideo && !$isDocument && !$isAudio) {
            $publicDisk->delete($outRelativePath);

            return response()->json(['error' => 'Formato de arquivo nao permitido'], 422);
        }

        $maxMb = (int) match (true) {
            $isImage => 6,
            $isVideo => config('uploads.video_max_mb', 1024),
            default => config('uploads.document_max_mb', 50),
        };
        $maxBytes = max(1, $maxMb) * 1024 * 1024;
        $size = (int) (@filesize($outPath) ?: 0);

        if ($size > $maxBytes) {
            $publicDisk->delete($outRelativePath);

            return response()->json(['error' => 'Arquivo excede o limite de tamanho'], 422);
        }

        $finalDisk = $targetDisk;
        $finalPath = $outRelativePath;

        if ($finalDisk !== 'public') {
            try {
                Storage::disk($finalDisk)->putFileAs('uploads', new File($outPath), $filename, ['visibility' => 'public']);
                $publicDisk->delete($outRelativePath);
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Falha ao enviar para o storage configurado'], 500);
            }
        }

        $url = UploadStorage::url($finalPath);

        return response()->json(['ok' => true, 'url' => $url, 'path' => $finalPath]);
    }
}
