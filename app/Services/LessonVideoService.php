<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class LessonVideoService
{
    public const STATUS_NONE = 'none';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';

    public function processarUploadVideo(Lesson $lesson, UploadedFile $arquivo): array
    {
        $disco = 'local';
        $storage = Storage::disk($disco);

        $this->limparArquivosVideo($lesson);

        $extensao = strtolower((string) ($arquivo->getClientOriginalExtension() ?: $arquivo->extension() ?: 'mp4'));
        $baseDir = 'lessons/' . (int) $lesson->course_id . '/' . (int) $lesson->id . '/' . Str::uuid()->toString();
        $caminhoFonte = $baseDir . '/source.' . $extensao;
        $diretorioHls = $baseDir . '/hls';
        $caminhoManifest = $diretorioHls . '/master.m3u8';
        $caminhoChave = $diretorioHls . '/enc.key';

        $storage->putFileAs(dirname($caminhoFonte), $arquivo, basename($caminhoFonte));

        $lesson->forceFill([
            'video_url' => null,
            'video_storage_disk' => $disco,
            'video_storage_path' => $caminhoFonte,
            'video_hls_manifest_path' => null,
            'video_hls_key_path' => null,
            'video_transcode_status' => self::STATUS_PROCESSING,
            'video_transcode_error' => null,
        ])->save();

        if (!(bool) config('uploads.video_hls_enabled', true)) {
            $lesson->forceFill([
                'video_transcode_status' => self::STATUS_READY,
                'video_transcode_error' => null,
            ])->save();

            return [
                'status' => self::STATUS_READY,
                'message' => 'Video salvo em area protegida. Conversao HLS desativada nas configuracoes.',
            ];
        }

        $binarioFfmpeg = trim((string) config('uploads.video_ffmpeg_binary', 'ffmpeg'));
        if (!$this->ffmpegDisponivel($binarioFfmpeg)) {
            $lesson->forceFill([
                'video_transcode_status' => self::STATUS_FAILED,
                'video_transcode_error' => 'FFmpeg nao encontrado no servidor.',
            ])->save();

            return [
                'status' => self::STATUS_FAILED,
                'message' => 'Video salvo em area protegida, mas FFmpeg nao esta disponivel para gerar HLS.',
            ];
        }

        try {
            $this->transcodificarParaHls(
                $lesson,
                $disco,
                $caminhoFonte,
                $diretorioHls,
                $caminhoManifest,
                $caminhoChave,
                $binarioFfmpeg
            );

            $lesson->forceFill([
                'video_hls_manifest_path' => $caminhoManifest,
                'video_hls_key_path' => $caminhoChave,
                'video_transcode_status' => self::STATUS_READY,
                'video_transcode_error' => null,
            ])->save();

            return [
                'status' => self::STATUS_READY,
                'message' => 'Video processado com sucesso em HLS (m3u8) com entrega protegida.',
            ];
        } catch (\Throwable $e) {
            Log::warning('Falha ao transcodificar video da aula #' . $lesson->id . ': ' . $e->getMessage());

            $lesson->forceFill([
                'video_hls_manifest_path' => null,
                'video_hls_key_path' => null,
                'video_transcode_status' => self::STATUS_FAILED,
                'video_transcode_error' => mb_substr($e->getMessage(), 0, 2000),
            ])->save();

            return [
                'status' => self::STATUS_FAILED,
                'message' => 'Video salvo em area protegida, mas houve falha na conversao para HLS.',
            ];
        }
    }

    public function definirVideoExterno(Lesson $lesson, string $url): void
    {
        $this->limparArquivosVideo($lesson);

        $lesson->forceFill([
            'video_url' => $url,
            'video_storage_disk' => null,
            'video_storage_path' => null,
            'video_hls_manifest_path' => null,
            'video_hls_key_path' => null,
            'video_transcode_status' => self::STATUS_NONE,
            'video_transcode_error' => null,
        ])->save();
    }

    public function limparArquivosVideo(Lesson $lesson): void
    {
        $disco = (string) ($lesson->video_storage_disk ?: 'local');
        $storage = Storage::disk($disco);

        $pastasParaLimpar = [];

        $caminhoFonte = $this->normalizarRelativo((string) ($lesson->video_storage_path ?? ''));
        if ($caminhoFonte !== '') {
            try {
                if ($storage->exists($caminhoFonte)) {
                    $storage->delete($caminhoFonte);
                }
            } catch (\Throwable $e) {
                Log::warning('Falha ao remover fonte de video da aula #' . $lesson->id . ': ' . $e->getMessage());
            }
            $pastasParaLimpar[] = dirname($caminhoFonte);
        }

        $manifesto = $this->normalizarRelativo((string) ($lesson->video_hls_manifest_path ?? ''));
        if ($manifesto !== '') {
            $pastasParaLimpar[] = dirname($manifesto);
        }

        $chave = $this->normalizarRelativo((string) ($lesson->video_hls_key_path ?? ''));
        if ($chave !== '') {
            $pastasParaLimpar[] = dirname($chave);
        }

        $pastasParaLimpar = array_values(array_unique(array_filter($pastasParaLimpar)));
        foreach ($pastasParaLimpar as $pasta) {
            $this->removerPastaRecursiva($storage, $pasta);
        }
    }

    private function transcodificarParaHls(
        Lesson $lesson,
        string $disco,
        string $caminhoFonte,
        string $diretorioHls,
        string $caminhoManifest,
        string $caminhoChave,
        string $binarioFfmpeg
    ): void {
        $storage = Storage::disk($disco);
        $storage->makeDirectory($diretorioHls);

        $fonteAbsoluta = $storage->path($caminhoFonte);
        $manifestoAbsoluto = $storage->path($caminhoManifest);
        $chaveAbsoluta = $storage->path($caminhoChave);
        $padraoSegmentoAbsoluto = $storage->path($diretorioHls . '/segment_%05d.ts');
        $arquivoKeyInfoAbsoluto = $storage->path($diretorioHls . '/enc.keyinfo');

        if (!is_file($fonteAbsoluta)) {
            throw new \RuntimeException('Arquivo de video de origem nao encontrado para transcodificacao.');
        }

        $chaveBinaria = random_bytes(16);
        $storage->put($caminhoChave, $chaveBinaria);

        $urlChave = route('courses.lessons.stream.key', [$lesson->course_id, $lesson->id], false);
        $conteudoKeyInfo = $urlChave . PHP_EOL . $chaveAbsoluta . PHP_EOL;
        file_put_contents($arquivoKeyInfoAbsoluto, $conteudoKeyInfo);

        $segmentoSegundos = max(2, (int) config('uploads.video_hls_segment_seconds', 6));
        $crf = max(18, min(35, (int) config('uploads.video_hls_crf', 25)));
        $preset = trim((string) config('uploads.video_hls_preset', 'veryfast'));
        if ($preset === '') {
            $preset = 'veryfast';
        }

        $filtroVideo = 'scale=w=min(1280\\,iw):h=-2';
        $comentarioProtecao = $this->textoProtecaoMetadado();

        $comandoBase = [
            $binarioFfmpeg,
            '-y',
            '-i',
            $fonteAbsoluta,
            '-c:v',
            'libx264',
            '-preset',
            $preset,
            '-crf',
            (string) $crf,
            '-vf',
            $filtroVideo,
            '-c:a',
            'aac',
            '-b:a',
            '128k',
            '-ac',
            '2',
            '-movflags',
            '+faststart',
            '-metadata',
            'comment=' . $comentarioProtecao,
            '-f',
            'hls',
            '-hls_time',
            (string) $segmentoSegundos,
            '-hls_playlist_type',
            'vod',
            '-hls_flags',
            'independent_segments',
            '-hls_segment_filename',
            $padraoSegmentoAbsoluto,
            '-hls_key_info_file',
            $arquivoKeyInfoAbsoluto,
            $manifestoAbsoluto,
        ];

        $caminhoMarcaDagua = $this->resolverArquivoMarcaDagua();
        if ($caminhoMarcaDagua !== null) {
            $comandoComMarca = [
                $binarioFfmpeg,
                '-y',
                '-i',
                $fonteAbsoluta,
                '-i',
                $caminhoMarcaDagua,
                '-filter_complex',
                '[0:v]scale=w=min(1280\\,iw):h=-2[base];[1:v]scale=220:-1[wm];[base][wm]overlay=W-w-20:H-h-20[vout]',
                '-map',
                '[vout]',
                '-map',
                '0:a?',
                '-c:v',
                'libx264',
                '-preset',
                $preset,
                '-crf',
                (string) $crf,
                '-c:a',
                'aac',
                '-b:a',
                '128k',
                '-ac',
                '2',
                '-movflags',
                '+faststart',
                '-metadata',
                'comment=' . $comentarioProtecao,
                '-f',
                'hls',
                '-hls_time',
                (string) $segmentoSegundos,
                '-hls_playlist_type',
                'vod',
                '-hls_flags',
                'independent_segments',
                '-hls_segment_filename',
                $padraoSegmentoAbsoluto,
                '-hls_key_info_file',
                $arquivoKeyInfoAbsoluto,
                $manifestoAbsoluto,
            ];

            if (!$this->executarComandoFfmpeg($comandoComMarca)) {
                if (!$this->executarComandoFfmpeg($comandoBase)) {
                    @unlink($arquivoKeyInfoAbsoluto);
                    throw new \RuntimeException('Falha na execucao do FFmpeg para gerar HLS.');
                }
            }
        } else {
            if (!$this->executarComandoFfmpeg($comandoBase)) {
                @unlink($arquivoKeyInfoAbsoluto);
                throw new \RuntimeException('Falha na execucao do FFmpeg para gerar HLS.');
            }
        }

        @unlink($arquivoKeyInfoAbsoluto);

        if (!$storage->exists($caminhoManifest)) {
            throw new \RuntimeException('Manifesto HLS nao foi gerado.');
        }
    }

    private function executarComandoFfmpeg(array $comando): bool
    {
        $process = new Process($comando);
        $process->setTimeout(null);
        $process->run();

        if ($process->isSuccessful()) {
            return true;
        }

        Log::warning('FFmpeg stderr: ' . trim((string) $process->getErrorOutput()));
        return false;
    }

    private function ffmpegDisponivel(string $binario): bool
    {
        if ($binario === '') {
            return false;
        }

        try {
            $process = new Process([$binario, '-version']);
            $process->setTimeout(10);
            $process->run();
            return $process->isSuccessful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function resolverArquivoMarcaDagua(): ?string
    {
        $marcaAtiva = (string) Setting::get('video_watermark_enabled', '0') === '1';
        if (!$marcaAtiva) {
            return null;
        }

        $valor = trim((string) (Setting::get('watermark_image') ?: ''));
        if ($valor === '') {
            return null;
        }

        $valor = ltrim(str_replace('\\', '/', $valor), '/');

        $candidatos = [
            public_path($valor),
            storage_path('app/public/' . $valor),
        ];

        if (Str::startsWith($valor, 'storage/')) {
            $semPrefixoStorage = substr($valor, strlen('storage/'));
            $candidatos[] = public_path('storage/' . $semPrefixoStorage);
            $candidatos[] = storage_path('app/public/' . $semPrefixoStorage);
        }

        foreach ($candidatos as $arquivo) {
            if (is_string($arquivo) && $arquivo !== '' && is_file($arquivo)) {
                return $arquivo;
            }
        }

        return null;
    }

    private function textoProtecaoMetadado(): string
    {
        $nomeSite = trim((string) (Setting::get('app_name') ?: config('app.name', 'UNN')));
        if ($nomeSite === '') {
            $nomeSite = 'UNN';
        }

        return 'Conteudo protegido - anti-pirataria - ' . $nomeSite;
    }

    private function removerPastaRecursiva($storage, string $pasta): void
    {
        $pasta = $this->normalizarRelativo($pasta);
        if ($pasta === '') {
            return;
        }

        try {
            if ($storage->exists($pasta)) {
                $storage->deleteDirectory($pasta);
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao remover pasta de video: ' . $pasta . ' -> ' . $e->getMessage());
        }
    }

    private function normalizarRelativo(string $caminho): string
    {
        $caminho = trim(str_replace('\\', '/', $caminho));
        $caminho = trim($caminho, '/');
        if ($caminho === '' || str_contains($caminho, '..')) {
            return '';
        }
        return $caminho;
    }
}
