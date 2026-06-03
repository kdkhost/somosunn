<?php

namespace App\Mail\Concerns;

use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;

trait UsesMailTemplate
{
    /**
     * Renderiza o email usando MailTemplate do banco.
     * Se o template não existir, cria com os defaults fornecidos.
     *
     * @param string $slug Slug do template no banco
     * @param array $data Variáveis para substituição (ex: ['user' => ['name' => 'João']])
     * @param array $defaults Valores padrão para criar o template se não existir
     * @return $this
     */
    protected function buildFromTemplate(string $slug, array $data, array $defaults = [])
    {
        $layout = app(SystemMailLayoutData::class)->make();
        $rendered = app(SystemMailTemplateService::class)->renderOrCreate($slug, $data, $defaults);

        if (!$rendered) {
            return $this;
        }

        return $this
            ->subject($rendered['subject'])
            ->view('emails.system', array_merge($layout, [
                'content' => $rendered['content'],
            ]));
    }
}
