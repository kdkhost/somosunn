<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class EventMediaController extends Controller
{
    private const DIRECT_UPLOAD_MAX_MB = 50;
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function store(Request $request, Event $event, WatermarkService $watermarkService)
    {
        if ($event->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'required|file',
        ]);

        $uploadedMedia = [];
        $failedFiles = [];

        foreach ($this->validatedFiles($request->file('files')) as $fileData) {
            $file = $fileData['file'];
            $type = $fileData['type'];
            $targetDirectory = $type === 'image'
                ? 'events/' . $event->id . '/gallery'
                : 'events/' . $event->id . '/gallery/videos';
            $shouldWatermark = $type === 'image'
                && $watermarkService->isWatermarkableImage($file)
                && $watermarkService->shouldWatermarkUpload($targetDirectory);

            try {
                try {
                    if ($type === 'image') {
                        $path = UploadStorage::storeUploadedFile(
                            $file,
                            $targetDirectory,
                            null,
                            ['prefix' => 'gallery-media']
                        );
                        $watermarked = false;
                    } else {
                        $path = UploadStorage::storeUploadedFile(
                            $file,
                            $targetDirectory
                        );
                        $watermarked = false;
                    }
                } catch (\Throwable $exception) {
                    \Log::error('Erro ao processar midia de evento (panel).', [
                        'event_id' => $event->id,
                        'file' => $file->getClientOriginalName(),
                        'type' => $type,
                        'message' => $exception->getMessage(),
                    ]);

                    $path = UploadStorage::storeUploadedFile($file, $targetDirectory);
                    $watermarked = $shouldWatermark;
                }

                $uploadedMedia[] = EventMedia::create([
                    'event_id' => $event->id,
                    'user_id' => auth()->id(),
                    'file_path' => $path,
                    'type' => $type,
                    'watermarked' => $watermarked,
                ]);
            } catch (\Throwable $exception) {
                \Log::error('Falha definitiva ao salvar midia de evento (panel).', [
                    'event_id' => $event->id,
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
            'media' => $this->serializeMediaCollection($event, $uploadedMedia),
        ]);
    }

    public function destroy(Event $event, EventMedia $media)
    {
        if ($event->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        if ($media->event_id !== $event->id) {
            return response()->json(['success' => false, 'message' => 'Midia invalida.'], 404);
        }

        UploadStorage::delete($media->file_path);
        $media->delete();

        return response()->json(['success' => true, 'message' => 'Midia apagada.']);
    }

    /**
     * @param  array<int, UploadedFile>|null  $files
     * @return array<int, array{file: UploadedFile, type: string}>
     */
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
            throw ValidationException::withMessages([
                'files' => $errors,
            ]);
        }

        return $validated;
    }

    /**
     * @return array<int, string>
     */
    private function allowedVideoExtensions(): array
    {
        $configured = array_map('strtolower', array_map('trim', (array) config('uploads.allowed_video_formats', [])));

        return array_values(array_unique(array_merge($configured, ['mp4', 'mov', 'm4v', 'webm'])));
    }

    /**
     * @param  array<int, EventMedia>  $mediaItems
     * @return array<int, array<string, mixed>>
     */
    private function serializeMediaCollection(Event $event, array $mediaItems): array
    {
        return array_map(function (EventMedia $media) use ($event) {
            return [
                'id' => $media->id,
                'type' => $media->type,
                'url' => UploadStorage::url($media->file_path),
                'watermarked' => (bool) $media->watermarked,
                'uploaded_at' => $media->created_at?->format('d/m/Y H:i'),
                'delete_url' => route('panel.events.media.destroy', [$event, $media]),
            ];
        }, $mediaItems);
    }
}
