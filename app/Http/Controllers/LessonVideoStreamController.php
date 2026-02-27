<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LessonVideoStreamController extends Controller
{
    public function stream(Request $request, Course $course, Lesson $lesson, ?string $path = null)
    {
        $this->validarAula($course, $lesson);
        $this->garantirAcesso($course, $lesson);

        $disco = (string) ($lesson->video_storage_disk ?: 'local');
        $storage = Storage::disk($disco);

        $path = $this->normalizarPath($path ?? '');
        if ($path === '') {
            $path = 'master.m3u8';
        }

        // Fonte original protegida (fallback quando HLS nao estiver pronto).
        if ($path === 'source' || $path === 'source.mp4') {
            if ((bool) ($course->video_block_download ?? false)) {
                abort(403, 'A fonte original do video esta bloqueada para download.');
            }

            $caminhoFonte = $this->normalizarPath((string) ($lesson->video_storage_path ?? ''));
            if ($caminhoFonte !== '' && $storage->exists($caminhoFonte)) {
                return $this->responderArquivo($storage->path($caminhoFonte), $this->mimePorExtensao($caminhoFonte));
            }

            $pathLegado = $this->resolverPathLegadoPublico((string) ($lesson->video_url ?? ''));
            if ($pathLegado !== null && Storage::disk('public')->exists($pathLegado)) {
                return $this->responderArquivo(Storage::disk('public')->path($pathLegado), $this->mimePorExtensao($pathLegado));
            }

            abort(404);
        }

        $manifesto = $this->normalizarPath((string) ($lesson->video_hls_manifest_path ?? ''));
        $hlsPronto = $lesson->video_transcode_status === 'ready' && $manifesto !== '' && $storage->exists($manifesto);
        if (!$hlsPronto) {
            abort(404);
        }

        $dirManifesto = dirname($manifesto);
        $arquivoSolicitado = $path === 'master.m3u8'
            ? $manifesto
            : $this->normalizarPath($dirManifesto . '/' . $path);

        if ($arquivoSolicitado === '' || !str_starts_with($arquivoSolicitado, trim($dirManifesto, '/') . '/')) {
            abort(404);
        }

        if (!$storage->exists($arquivoSolicitado)) {
            abort(404);
        }

        $absoluto = $storage->path($arquivoSolicitado);
        $mime = $this->mimePorExtensao($arquivoSolicitado);
        return $this->responderArquivo($absoluto, $mime);
    }

    public function key(Request $request, Course $course, Lesson $lesson)
    {
        $this->validarAula($course, $lesson);
        $this->garantirAcesso($course, $lesson);

        $disco = (string) ($lesson->video_storage_disk ?: 'local');
        $storage = Storage::disk($disco);
        $chave = $this->normalizarPath((string) ($lesson->video_hls_key_path ?? ''));

        if ($chave === '' || !$storage->exists($chave)) {
            abort(404);
        }

        return $this->responderArquivo($storage->path($chave), 'application/octet-stream');
    }

    private function responderArquivo(string $absoluto, string $mime)
    {
        if (!is_file($absoluto)) {
            abort(404);
        }

        return response()->file($absoluto, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function garantirAcesso(Course $course, Lesson $lesson): void
    {
        if ($this->temAcessoCompleto($course)) {
            return;
        }

        if ((bool) $lesson->is_free_preview) {
            return;
        }

        abort(403, 'Voce nao possui acesso a esta aula.');
    }

    private function temAcessoCompleto(Course $course): bool
    {
        if (!Auth::check()) {
            return false;
        }

        if ((int) $course->user_id === (int) Auth::id()) {
            return true;
        }

        return $course->enrollments()->where('user_id', Auth::id())->exists();
    }

    private function validarAula(Course $course, Lesson $lesson): void
    {
        if ((int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }
    }

    private function normalizarPath(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        $path = trim($path, '/');
        if ($path === '' || str_contains($path, '..')) {
            return '';
        }
        return $path;
    }

    private function mimePorExtensao(string $path): string
    {
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'm3u8' => 'application/vnd.apple.mpegurl',
            'ts' => 'video/mp2t',
            'key' => 'application/octet-stream',
            'mp4' => 'video/mp4',
            default => 'application/octet-stream',
        };
    }

    private function resolverPathLegadoPublico(string $url): ?string
    {
        $url = trim(str_replace('\\', '/', $url));
        if ($url === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $url)) {
            $path = (string) parse_url($url, PHP_URL_PATH);
            $url = ltrim($path, '/');
        }

        $url = ltrim($url, '/');
        if (str_starts_with($url, 'storage/')) {
            $url = substr($url, strlen('storage/'));
        }
        if (str_starts_with($url, 'public/')) {
            $url = substr($url, strlen('public/'));
        }

        if (!preg_match('/^course-videos\//i', $url)) {
            return null;
        }

        return $url;
    }
}
