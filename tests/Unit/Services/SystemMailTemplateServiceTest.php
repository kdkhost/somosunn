<?php

namespace Tests\Unit\Services;

use App\Models\MailTemplate;
use App\Services\Mail\SystemMailTemplateService;
use Tests\TestCase;

class SystemMailTemplateServiceTest extends TestCase
{
    public function test_renderiza_template_generico_antigo_com_conteudo_html(): void
    {
        $template = new MailTemplate([
            'subject' => '{{message.subject}}',
            'body' => '{!! $message[\'content\'] ?? \'\' !!}',
        ]);

        [$subject, $body] = app(SystemMailTemplateService::class)->renderTemplate($template, [
            'message' => [
                'subject' => 'Backup falhou - SOMOS UNN',
                'content' => '<p><strong>Erro:</strong> falha no S3</p>',
            ],
        ]);

        $this->assertSame('Backup falhou - SOMOS UNN', $subject);
        $this->assertStringContainsString('<strong>Erro:</strong>', $body);
        $this->assertStringNotContainsString('$message', $body);
    }

    public function test_renderiza_template_generico_novo_com_placeholder_simples(): void
    {
        $template = new MailTemplate([
            'subject' => '{message.subject}',
            'body' => '{message.content}',
        ]);

        [$subject, $body] = app(SystemMailTemplateService::class)->renderTemplate($template, [
            'message' => [
                'subject' => 'Backup concluido - SOMOS UNN',
                'content' => '<p>Backup gerado em storage/app/backups/db/teste.sql.gz</p>',
            ],
        ]);

        $this->assertSame('Backup concluido - SOMOS UNN', $subject);
        $this->assertStringContainsString('storage/app/backups/db/teste.sql.gz', $body);
        $this->assertStringNotContainsString('message.content', $body);
    }
}
