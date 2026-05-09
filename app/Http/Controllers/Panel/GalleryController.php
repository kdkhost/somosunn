<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Services\WatermarkService;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class GalleryController extends Controller
{
    private const DIRECT_UPLOAD_MAX_MB = 50;
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'heic', 'heif'];

    /**
     * Display gallery media.
     * Members see their own uploads and organizers can manage media from their own events.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $baseQuery = $this->baseVisibleQuery($user);
        $query = (clone $baseQuery)->with(['event.galleryCoverMedia', 'user'])->latest();

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $media = $query->paginate(10);

        $events = Event::where('published', true)
            ->has('media')
            ->with('galleryCoverMedia')
            ->withCount('media')
            ->orderBy('start_at', 'desc')
            ->get();
        $selectedEvent = $request->filled('event_id')
            ? $events->firstWhere('id', (int) $request->event_id)
            : null;

        $stats = [
            'visible_total' => (clone $baseQuery)->count(),
            'event_coverage' => (clone $baseQuery)->distinct()->count('event_id'),
            'my_uploads' => EventMedia::query()->where('user_id', $user->id)->count(),
        ];

        $canManageSelectedEvent = $selectedEvent ? $this->canManageEvent($selectedEvent) : false;

        return view('panel.gallery.index', compact(
            'media',
            'events',
            'selectedEvent',
            'stats',
            'canManageSelectedEvent'
        ));
    }

    /**
     * Upload new media to the gallery.
     */
    public function upload(Request $request, WatermarkService $watermarkService)
    {
        $perFileLimitBytes = UploadStorage::effectiveUploadLimitBytes(self::DIRECT_UPLOAD_MAX_MB * 1024 * 1024)
            ?? (self::DIRECT_UPLOAD_MAX_MB * 1024 * 1024);
        $perFileLimitMegabytes = number_format($perFileLimitBytes / 1024 / 1024, 2, '.', '');

        if (!$request->hasFile('files')) {
            $message = "Nenhum arquivo chegou ao servidor. Verifique o limite de {$perFileLimitMegabytes} MB por arquivo e tente novamente com menos midias por vez.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->with('error', $message);
        }

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file',
        ]);

        $event = Event::findOrFail($request->event_id);
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
                if ($type === 'image') {
                    $path = $watermarkService->processEventImage($file, $event);
                    $watermarked = $shouldWatermark;
                } else {
                    $path = UploadStorage::storeUploadedFile($file, $targetDirectory);
                    $watermarked = false;
                }

                $uploadedMedia[] = EventMedia::create([
                    'event_id' => $event->id,
                    'user_id' => auth()->id(),
                    'file_path' => $path,
                    'type' => $type,
                    'watermarked' => $watermarked,
                ]);
            } catch (\Throwable $exception) {
                \Log::error('Gallery upload error: ' . $exception->getMessage(), [
                    'event_id' => $event->id,
                    'file' => $file->getClientOriginalName(),
                    'type' => $type,
                ]);

                try {
                    $path = UploadStorage::storeUploadedFile(
                        $file,
                        $targetDirectory,
                        null,
                        $type === 'image' ? ['prefix' => 'gallery-media'] : []
                    );

                    $uploadedMedia[] = EventMedia::create([
                        'event_id' => $event->id,
                        'user_id' => auth()->id(),
                        'file_path' => $path,
                        'type' => $type,
                        'watermarked' => false,
                    ]);
                } catch (\Throwable $fallbackException) {
                    \Log::error('Gallery upload fallback error: ' . $fallbackException->getMessage(), [
                        'event_id' => $event->id,
                        'file' => $file->getClientOriginalName(),
                        'type' => $type,
                    ]);

                    $failedFiles[] = $file->getClientOriginalName();
                }
            }
        }

        if ($uploadedMedia === []) {
            $message = 'Nenhum arquivo conseguiu ser enviado para a galeria.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'failed_files' => $failedFiles,
                ], 422);
            }

            return back()->with('error', $message);
        }

        $message = count($uploadedMedia) . ' arquivo(s) enviado(s) com sucesso.';
        if ($failedFiles !== []) {
            $message .= ' ' . count($failedFiles) . ' arquivo(s) falharam.';
        }

        if ($request->expectsJson()) {
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'message' => $message,
                'uploaded_count' => count($uploadedMedia),
                'failed_count' => count($failedFiles),
                'failed_files' => $failedFiles,
                'media' => $this->serializeMediaCollection($uploadedMedia),
                'stats' => [
                    'visible_total' => $this->baseVisibleQuery($user)->count(),
                    'my_uploads' => EventMedia::query()->where('user_id', $user->id)->count(),
                ],
            ]);
        }

        return back()->with('success', $message);
    }

    public function uploadCover(Request $request, Event $event, WatermarkService $watermarkService)
    {
        $this->abortUnlessCanManageEvent($event);

        $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $oldCustomCover = $event->gallery_cover_image;

        try {
            $path = $watermarkService->processStorageImage(
                $request->file('cover_image'),
                'events/' . $event->id . '/gallery/covers',
                null,
                ['prefix' => 'gallery-cover']
            );
        } catch (\Throwable $exception) {
            \Log::error('Panel gallery custom cover upload error: ' . $exception->getMessage(), [
                'event_id' => $event->id,
            ]);

            $path = UploadStorage::storeUploadedFile(
                $request->file('cover_image'),
                'events/' . $event->id . '/gallery/covers',
                null,
                ['prefix' => 'gallery-cover']
            );
        }

        if (!blank($oldCustomCover) && $oldCustomCover !== $path) {
            UploadStorage::delete($oldCustomCover);
        }

        $event->forceFill([
            'gallery_cover_image' => $path,
            'gallery_cover_media_id' => null,
        ])->save();

        return $this->coverResponse($request, $event, 'Capa personalizada do album atualizada com sucesso.');
    }

    public function setCoverFromMedia(Request $request, EventMedia $media)
    {
        $media->loadMissing('event');
        $event = $media->event;

        abort_if(!$event, 404);
        $this->abortUnlessCanManageEvent($event);

        if ($media->type !== 'image') {
            throw ValidationException::withMessages([
                'media' => 'A capa do album precisa ser uma imagem.',
            ]);
        }

        if (!blank($event->gallery_cover_image)) {
            UploadStorage::delete($event->gallery_cover_image);
        }

        $event->forceFill([
            'gallery_cover_image' => null,
            'gallery_cover_media_id' => $media->id,
        ])->save();

        return $this->coverResponse($request, $event, 'Capa do album definida a partir da foto selecionada.');
    }

    public function clearCover(Request $request, Event $event)
    {
        $this->abortUnlessCanManageEvent($event);

        if (!blank($event->gallery_cover_image)) {
            UploadStorage::delete($event->gallery_cover_image);
        }

        $event->forceFill([
            'gallery_cover_image' => null,
            'gallery_cover_media_id' => null,
        ])->save();

        return $this->coverResponse($request, $event, 'A capa personalizada do album foi removida.');
    }

    /**
     * Remove the specified media from storage.
     */
    public function destroy(Request $request, EventMedia $media)
    {
        $media->loadMissing('event');
        $event = $media->event;
        $user = auth()->user();

        $canDelete = $user->isAdmin()
            || (int) $media->user_id === (int) $user->id
            || ($event && (int) $event->user_id === (int) $user->id);

        if (!$canDelete) {
            return back()->with('error', 'Voce nao tem permissao para excluir esta midia.');
        }

        if ($event && (int) $event->gallery_cover_media_id === (int) $media->id) {
            $event->forceFill(['gallery_cover_media_id' => null])->save();
        }

        UploadStorage::delete($media->file_path);
        $media->delete();

        return back()->with('success', 'Midia excluida da galeria com sucesso.');
    }

    /**
     * @param  array<int, EventMedia>  $mediaItems
     * @return array<int, array<string, mixed>>
     */
    private function serializeMediaCollection(array $mediaItems): array
    {
        $user = auth()->user();

        return array_map(function (EventMedia $media) use ($user) {
            $media->loadMissing(['event.galleryCoverMedia', 'user']);
            $event = $media->event;
            $isEventOwner = $event && (int) $event->user_id === (int) $user->id;

            return [
                'id' => $media->id,
                'event_id' => $media->event_id,
                'type' => $media->type,
                'url' => UploadStorage::url($media->file_path, asset('img/default-user.svg')),
                'watermarked' => (bool) $media->watermarked,
                'event_title' => optional($event)->title ?: 'Evento sem titulo',
                'event_date' => optional(optional($event)->start_at)?->format('d/m/Y'),
                'owner_name' => optional($media->user)->name ?: 'Sistema',
                'owner_avatar' => optional($media->user)->profile_photo_url ?: '',
                'uploaded_at' => $media->created_at?->format('d/m/Y H:i'),
                'can_delete' => $user->isAdmin()
                    || (int) $media->user_id === (int) $user->id
                    || $isEventOwner,
                'can_set_cover' => $media->type === 'image' && ($user->isAdmin() || $isEventOwner),
                'is_cover' => $event
                    ? (blank($event->gallery_cover_image) && (int) $event->gallery_cover_media_id === (int) $media->id)
                    : false,
                'delete_url' => route('panel.gallery.destroy', $media),
                'set_cover_url' => route('panel.gallery.cover.media', $media),
            ];
        }, $mediaItems);
    }

    private function baseVisibleQuery($user): Builder
    {
        return EventMedia::query()
            ->when(!$user->isAdmin(), function (Builder $query) use ($user) {
                $query->where(function (Builder $scope) use ($user) {
                    $scope->where('user_id', $user->id)
                        ->orWhereHas('event', function (Builder $eventQuery) use ($user) {
                            $eventQuery->where('user_id', $user->id);
                        });
                });
            });
    }

    private function canManageEvent(Event $event): bool
    {
        $user = auth()->user();

        return $user->isAdmin() || (int) $event->user_id === (int) $user->id;
    }

    private function abortUnlessCanManageEvent(Event $event): void
    {
        if (!$this->canManageEvent($event)) {
            abort(403);
        }
    }

    private function coverResponse(Request $request, Event $event, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'cover_url' => $event->fresh()->gallery_cover_url,
                'event_id' => $event->id,
            ]);
        }

        return back()->with('success', $message);
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
}
