<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Mentorship;
use App\Models\MentorshipMedia;
use App\Services\ImageOptimizer;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class MentorshipMediaController extends Controller
{
    private const DIRECT_UPLOAD_MAX_MB = 50;
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'];

    public function store(Request $request, Mentorship $mentorship, WatermarkService $watermarkService)
    {
        if ($mentorship->mentor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|file',
        ]);

        $uploadedMedia = [];
        $failedFiles   = [];
        $optimizer     = app(ImageOptimizer::class);

        foreach ($this->validatedFiles($request->file('files')) as $fileData) {
            $file = $fileData['file'];
            $type = $fileData['type'];

            // otimiza e converte HEIC antes de processar
            if ($type === 'image') {
                try {
                    $file = $optimizer->process($file);
                } catch (\Throwable $e) {
                    $failedFiles[] = $fileData['file']->getClientOriginalName() . ' (' . $e->getMessage() . ')';
                    continue;
                }
            }
            $targetDirectory = $type === 'image'
                ? 'mentorships/' . $mentorship->id . '/gallery'
                : 'mentorships/' . $mentorship->id . '/gallery/videos';
            
            $shouldWatermark = $type === 'image'
                && $watermarkService->isWatermarkableImage($file)
                && $watermarkService->shouldWatermarkUpload($targetDirectory);

            try {
                try {
                    if ($type === 'image') {
                        $path = $watermarkService->processMentorshipImage($file, $mentorship);
                        $watermarked = $shouldWatermark;
                    } else {
                        $path = UploadStorage::storeUploadedFile($file, $targetDirectory);
                        $watermarked = false;
                    }
                } catch (\Throwable $exception) {
                    \Log::error('Erro ao processar midia de mentoria (panel).', [
                        'mentorship_id' => $mentorship->id,
                        'file' => $file->getClientOriginalName(),
                        'type' => $type,
                        'message' => $exception->getMessage(),
                    ]);

                    $path = UploadStorage::storeUploadedFile($file, $targetDirectory);
                    $watermarked = $shouldWatermark;
                }

                $uploadedMedia[] = MentorshipMedia::create([
                    'mentorship_id' => $mentorship->id,
                    'user_id' => auth()->id(),
                    'file_path' => $path,
                    'type' => $type,
                    'watermarked' => $watermarked,
                ]);
            } catch (\Throwable $exception) {
                \Log::error('Falha definitiva ao salvar midia de mentoria (panel).', [
                    'mentorship_id' => $mentorship->id,
                    'file' => $file->getClientOriginalName(),
                    'type' => $type,
                    'message' => $exception->getMessage(),
                ]);

                $failedFiles[] = $file->getClientOriginalName();
            }
        }

        if ($uploadedMedia === []) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum arquivo conseguiu ser enviado.',
                'failed_files' => $failedFiles,
            ], 422);
        }

        $message = count($uploadedMedia) . ' arquivo(s) enviado(s) com sucesso!';
        if ($failedFiles !== []) {
            $message .= ' ' . count($failedFiles) . ' arquivo(s) falharam.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'uploaded_count' => count($uploadedMedia),
            'failed_count' => count($failedFiles),
            'failed_files' => $failedFiles,
            'media' => $this->serializeMediaCollection($mentorship, $uploadedMedia),
        ]);
    }

    public function destroy(Mentorship $mentorship, MentorshipMedia $media)
    {
        if ($mentorship->mentor_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        if ($media->mentorship_id !== $mentorship->id) {
            return response()->json(['success' => false, 'message' => 'Midia invalida.'], 404);
        }

        UploadStorage::delete($media->file_path);
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Midia apagada.']);
    }

    private function validatedFiles(?array $files): array
    {
        $files = array_values(array_filter($files ?? []));
        $allowedVideoExtensions = $this->allowedVideoExtensions();
        $maxBytes = self::DIRECT_UPLOAD_MAX_MB * 1024 * 1024;
        $validated = [];
        $errors = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                $errors[] = 'Um dos arquivos enviados esta corrompido ou incompleto.';
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
            $isImage = in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true);
            $isVideo = in_array($extension, $allowedVideoExtensions, true);

            if (!$isImage && !$isVideo) {
                $errors[] = $file->getClientOriginalName() . ' possui um formato nao permitido.';
                continue;
            }

            if (($file->getSize() ?: 0) > $maxBytes) {
                $errors[] = $file->getClientOriginalName() . ' excede o limite de ' . self::DIRECT_UPLOAD_MAX_MB . ' MB por arquivo.';
                continue;
            }

            $validated[] = [
                'file' => $file,
                'type' => $isImage ? 'image' : 'video',
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['files' => $errors]);
        }

        return $validated;
    }

    private function allowedVideoExtensions(): array
    {
        $configured = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_video_formats', [])));
        return array_values(array_unique(array_merge($configured, ['mp4', 'mov', 'm4v', 'webm'])));
    }

    private function serializeMediaCollection(Mentorship $mentorship, array $mediaItems): array
    {
        return array_map(function (MentorshipMedia $media) use ($mentorship) {
            return [
                'id' => $media->id,
                'type' => $media->type,
                'url' => UploadStorage::url($media->file_path),
                'watermarked' => (bool) $media->watermarked,
                'uploaded_at' => $media->created_at?->format('d/m/Y H:i'),
                'delete_url' => route('panel.mentorships.media.destroy', [$mentorship, $media]),
            ];
        }, $mediaItems);
    }
}
