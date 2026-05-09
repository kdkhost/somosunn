<?php

namespace App\Mail\Concerns;

use App\Models\MailTemplate;
use App\Services\Mail\SystemMailLayoutData;

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
        $template = MailTemplate::where('slug', $slug)->where('is_active', true)->first();

        if (!$template && !empty($defaults)) {
            $template = MailTemplate::firstOrCreate(
                ['slug' => $slug],
                array_merge([
                    'name' => $defaults['name'] ?? ucfirst(str_replace(['_', '-'], ' ', $slug)),
                    'category' => $defaults['category'] ?? 'sistema',
                    'subject' => $defaults['subject'] ?? '{{site.name}}',
                    'body' => $defaults['body'] ?? '<p>Conteúdo do email.</p>',
                    'is_active' => true,
                    'locale' => 'pt-BR',
                ], $defaults)
            );
        }

        if (!$template) {
            // Fallback: usar view padrão se existir
            return $this;
        }

        $layout = app(SystemMailLayoutData::class)->make();

        // Adicionar dados do site automaticamente
        if (!isset($data['site'])) {
            $data['site'] = [
                'name' => $layout['siteName'],
                'logo' => $layout['logoUrl'],
                'primary_color' => $layout['primaryColor'],
                'url' => url('/'),
            ];
        }

        // Renderizar template com substituição de variáveis
        $rendered = (string) ($template->body ?? '');
        $subject = (string) ($template->subject ?? '');

        foreach ($data as $key => $values) {
            if (is_array($values)) {
                foreach ($values as $k => $v) {
                    $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\.' . preg_quote($k, '/') . '\s*\}\}/';
                    $rendered = preg_replace($pattern, (string) ($v ?? ''), $rendered);
                    $subject = preg_replace($pattern, (string) ($v ?? ''), $subject);
                }
            } else {
                $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/';
                $rendered = preg_replace($pattern, (string) ($values ?? ''), $rendered);
                $subject = preg_replace($pattern, (string) ($values ?? ''), $subject);
            }
        }

        return $this
            ->subject($subject)
            ->view('emails.system', array_merge($layout, [
                'content' => $rendered,
            ]));
    }
}
