<?php

namespace App\Http\Controllers;

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
        $chunkPath = "{$dir}/chunk_{$chunkIndex}";

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

        $outPath = storage_path("app/public/uploads/{$filename}");
        $out = fopen($outPath, 'ab');

        for($i=0;$i<$total;$i++){
            $chunkFile = $dir . "/chunk_{$i}";
            if(!file_exists($chunkFile)){
                fclose($out);
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

        // Return public URL
        $url = Storage::disk('public')->url("uploads/{$filename}");
        return response()->json(['ok' => true, 'url' => $url, 'path' => "uploads/{$filename}"]);
    }
}