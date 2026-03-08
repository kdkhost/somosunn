<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMedia;
use App\Services\WatermarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventMediaController extends Controller
{
    /**
     * Store new media (photo/video) for a specific event
     */
    public function store(Request $request, Event $event, WatermarkService $watermarkService)
    {
        // Apenas o organizador pode enviar mídias (ou admin verificação via middleware)
        if ($event->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'files.*' => 'required|file|mimes:jpeg,png,jpg,mp4,mov|max:20480', // 20MB Max
        ]);

        $uploadedMedia = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $type = explode('/', $file->getMimeType())[0]; // 'image' ou 'video'

                if ($type === 'image') {
                    // Processa a imagem e adiciona marca d'água
                    try {
                        $path = $watermarkService->processEventImage($file, $event);
                        $watermarked = true;
                    } catch (\Exception $e) {
                        \Log::error('Erro ao processar imagem de evento: ' . $e->getMessage());
                        // Fallback: salva original sem marca d'agua
                        $path = $file->store('events/' . $event->id . '/gallery', 'public');
                        $watermarked = false;
                    }
                } else {
                    // Vídeos não levam marca d'água por enquanto (requer ffmpeg no servidor)
                    $path = $file->store('events/' . $event->id . '/gallery/videos', 'public');
                    $watermarked = false;
                }

                $media = EventMedia::create([
                    'event_id' => $event->id,
                    'user_id' => auth()->id(),
                    'file_path' => $path,
                    'type' => $type,
                    'watermarked' => $watermarked
                ]);

                $uploadedMedia[] = $media;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedMedia) . ' arquivo(s) enviado(s) com sucesso!',
            'media' => $uploadedMedia
        ]);
    }

    /**
     * Delete a specific media
     */
    public function destroy(Event $event, EventMedia $media)
    {
        // Verifica permissões
        if ($event->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        if ($media->event_id !== $event->id) {
            return response()->json(['success' => false, 'message' => 'Mídia inválida.'], 404);
        }

        // Delete from Storage
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json(['success' => true, 'message' => 'Mídia apagada.']);
    }
}
