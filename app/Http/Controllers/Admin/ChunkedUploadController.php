<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChunkedUploadController extends Controller
{
    /**
     * Recebe um chunk de arquivo enviado pelo componente upload-global.
     * Suporta multipart/form-data (binario) ou application/json (base64).
     */
    public function chunk(Request $request)
    {
        $uploadId   = (string) $request->input('upload_id', '');
        $chunkIndex = (int)    $request->input('chunk_index', 0);
        $totalChunks = (int)   $request->input('total_chunks', 1);

        if ($uploadId === '' || !preg_match('/^[\w\-]+$/', $uploadId)) {
            return response()->json(['error' => 'upload_id invalido.'], 422);
        }

        $tmpDir = $this->tmpDir($uploadId);
        if (!is_dir($tmpDir) && !@mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
            return response()->json(['error' => 'Nao foi possivel criar diretorio temporario.'], 500);
        }

        $chunkPath = $tmpDir . '/chunk_' . $chunkIndex;

        // --- Modo base64 (JSON) ---
        if ($request->isJson() || $request->has('chunk_data')) {
            $chunkData = (string) $request->input('chunk_data', '');

            // Remove prefixo data:...;base64,
            if (($pos = strpos($chunkData, ',')) !== false) {
                $chunkData = substr($chunkData, $pos + 1);
            }

            $binaryData = base64_decode($chunkData, true);
            if ($binaryData === false) {
                return response()->json(['error' => 'Chunk base64 invalido.'], 422);
            }

            file_put_contents($chunkPath, $binaryData);

            return response()->json(['ok' => true, 'chunk' => $chunkIndex]);
        }

        // --- Modo multipart (binario) ---
        $request->validate(['file' => 'required|file']);
        $file = $request->file('file');

        if (!$file->isValid()) {
            return response()->json(['error' => 'Chunk invalido ou corrompido.'], 422);
        }

        $file->move($tmpDir, 'chunk_' . $chunkIndex);

        return response()->json(['ok' => true, 'chunk' => $chunkIndex]);
    }

    /**
     * Monta os chunks enviados em um arquivo final e o persiste no disco.
     */
    public function assemble(Request $request)
    {
        $request->validate([
            'upload_id'    => 'required|string',
            'filename'     => 'required|string|max:260',
            'total_chunks' => 'required|integer|min:1',
        ]);

        $uploadId    = (string) $request->input('upload_id');
        $filename    = (string) $request->input('filename');
        $totalChunks = (int)    $request->input('total_chunks');

        if (!preg_match('/^[\w\-]+$/', $uploadId)) {
            return response()->json(['error' => 'upload_id invalido.'], 422);
        }

        $tmpDir = $this->tmpDir($uploadId);

        // Verificar que todos os chunks existem
        for ($i = 0; $i < $totalChunks; $i++) {
            if (!file_exists($tmpDir . '/chunk_' . $i)) {
                return response()->json(['error' => "Chunk {$i} nao encontrado. Tente novamente."], 422);
            }
        }

        // Montar o arquivo final em um path temporario
        $ext          = strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: 'bin');
        $safeName     = Str::uuid() . '.' . $ext;
        $assembledTmp = sys_get_temp_dir() . '/' . $safeName;

        $out = fopen($assembledTmp, 'wb');
        if ($out === false) {
            return response()->json(['error' => 'Nao foi possivel criar arquivo final.'], 500);
        }

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $tmpDir . '/chunk_' . $i;
            $in = fopen($chunkPath, 'rb');
            if ($in !== false) {
                stream_copy_to_stream($in, $out);
                fclose($in);
            }
        }

        fclose($out);

        // Persistir no disco de uploads
        try {
            $disk = 'public';
            $storagePath = 'uploads/' . $safeName;

            $result = \Illuminate\Support\Facades\Storage::disk($disk)->put($storagePath, fopen($assembledTmp, 'rb'), 'public');

            @unlink($assembledTmp);
            $this->cleanTmp($tmpDir);

            if (!$result) {
                return response()->json(['error' => 'Falha ao persistir arquivo no servidor.'], 500);
            }

            return response()->json([
                'ok'   => true,
                'path' => $storagePath,
                'url'  => UploadStorage::url($storagePath),
            ]);
        } catch (\Throwable $e) {
            Log::error('ChunkedUpload assemble error: ' . $e->getMessage(), ['upload_id' => $uploadId]);
            @unlink($assembledTmp);
            $this->cleanTmp($tmpDir);
            return response()->json(['error' => 'Erro ao finalizar o upload: ' . $e->getMessage()], 500);
        }
    }

    // -----------------------------------------------------------------------

    private function tmpDir(string $uploadId): string
    {
        return sys_get_temp_dir() . '/chunked_' . $uploadId;
    }

    private function cleanTmp(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*');
        if (is_array($files)) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }

        @rmdir($dir);
    }
}
