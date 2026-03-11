<?php

namespace App\Http\Controllers;

use App\Support\UploadStorage;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $dir = storage_path("app/uploads/tmp/{$uploadId}");
        $file = $request->file('file');
        $filename = "chunk_{$chunkIndex}";

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return response()->json(['error' => 'Chunk de upload invalido'], 422);
        }

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return response()->json(['error' => 'Nao foi possivel preparar o diretorio temporario'], 500);
        }

        try {
            $file->move($dir, $filename);
        } catch (\Throwable $e) {
            Log::error('Failed to store upload chunk.', [
                'upload_id' => $uploadId,
                'chunk_index' => $chunkIndex,
                'dir' => $dir,
                'message' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Falha ao gravar o chunk enviado'], 500);
        }

        $chunkPath = $dir . DIRECTORY_SEPARATOR . $filename;
        clearstatcache(true, $chunkPath);
        if (!is_file($chunkPath)) {
            Log::error('Upload chunk file was not persisted after move.', [
                'upload_id' => $uploadId,
                'chunk_index' => $chunkIndex,
                'chunk_path' => $chunkPath,
            ]);

            return response()->json(['error' => 'O servidor nao confirmou o armazenamento do chunk enviado'], 500);
        }

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

        $relativeDir = "uploads/tmp/{$uploadId}";
        $dir = storage_path("app/{$relativeDir}");
        if (!is_dir($dir)) {
            Log::warning('Upload chunk directory not found during assemble.', [
                'upload_id' => $uploadId,
                'relative_dir' => $relativeDir,
                'resolved_dir' => $dir,
            ]);
            return response()->json(['error' => 'Upload nao encontrado'], 404);
        }

        $targetDisk = (string) config('uploads.disk', 'public');
        if ($targetDisk === '') {
            $targetDisk = 'public';
        }

        $outRelativePath = "uploads/{$filename}";
        $publicRoot = (string) config(
            'filesystems.disks.public.root',
            is_dir(public_path('storage')) ? public_path('storage') : storage_path('app/public')
        );
        $outPath = rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $outRelativePath);

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

                Log::warning('Missing upload chunk during assemble.', [
                    'upload_id' => $uploadId,
                    'missing_chunk' => $i,
                    'resolved_dir' => $dir,
                    'available_files' => array_map('basename', glob($dir . '/*') ?: []),
                ]);

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
            @unlink($outPath);

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
            @unlink($outPath);

            return response()->json(['error' => 'Arquivo excede o limite de tamanho'], 422);
        }

        $finalDisk = $targetDisk;
        $finalPath = $outRelativePath;

        if ($finalDisk !== 'public') {
            try {
                Storage::disk($finalDisk)->putFileAs('uploads', new File($outPath), $filename, ['visibility' => 'public']);
                @unlink($outPath);
            } catch (\Throwable $e) {
                return response()->json(['error' => 'Falha ao enviar para o storage configurado'], 500);
            }
        }

        $url = UploadStorage::url($finalPath);

        return response()->json(['ok' => true, 'url' => $url, 'path' => $finalPath]);
    }
}
