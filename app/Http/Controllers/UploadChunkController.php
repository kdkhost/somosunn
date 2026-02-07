<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadChunkController extends Controller
{
    public function storeChunk(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'upload_id' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'sometimes|integer'
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
            'total_chunks' => 'required|integer'
        ]);

        $uploadId = $request->input('upload_id');
        $total = $request->input('total_chunks');
        $filename = Str::slug(pathinfo($request->input('filename'), PATHINFO_FILENAME)) . '-' . time() . '.' . pathinfo($request->input('filename'), PATHINFO_EXTENSION);

        $dir = storage_path("app/uploads/tmp/{$uploadId}");
        if(!is_dir($dir)) return response()->json(['error' => 'Upload não encontrado'], 404);

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
            return response()->json(['error' => 'Falha ao criar arquivo de saída'], 500);
        }

        for($i=0;$i<$total;$i++){
            $chunkFile = $dir . "/chunk_{$i}";
            if(!file_exists($chunkFile)){
                fclose($out);
                @unlink($outPath);
                return response()->json(['error' => "Chunk {$i} faltando"], 422);
            }
            $in = fopen($chunkFile, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }

        fclose($out);

        // Clean up chunks
        array_map('unlink', glob("{$dir}/*"));
        rmdir($dir);

        // Valida extensão + tamanho (de acordo com config/uploads.php)
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedV = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_video_formats', [])));
        $allowedD = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_document_formats', [])));

        $isVideo = in_array($ext, $allowedV, true);
        $isDoc = in_array($ext, $allowedD, true);

        if (!$isVideo && !$isDoc) {
            $publicDisk->delete($outRelativePath);
            return response()->json(['error' => 'Formato de arquivo não permitido'], 422);
        }

        $maxMb = (int) ($isVideo ? config('uploads.video_max_mb', 1024) : config('uploads.document_max_mb', 50));
        $maxBytes = max(1, $maxMb) * 1024 * 1024;
        $size = (int) (@filesize($outPath) ?: 0);

        if ($size > $maxBytes) {
            $publicDisk->delete($outRelativePath);
            return response()->json(['error' => 'Arquivo excede o limite de tamanho'], 422);
        }

        // Upload para o disco final (public/local ou S3)
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

        $url = Storage::disk($finalDisk)->url($finalPath);
        return response()->json(['ok' => true, 'url' => $url, 'path' => $finalPath]);
    }
}
