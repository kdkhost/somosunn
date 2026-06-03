<?php

namespace Tests\Feature;

use App\Models\MailTemplate;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MandatoryMailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_creates_and_renders_editable_template_before_sending(): void
    {
        $rendered = app(SystemMailTemplateService::class)->renderOrCreate('test_mandatory_template', [
            'user' => ['name' => 'Maria'],
        ], [
            'name' => 'Teste Obrigatorio',
            'subject' => 'Ola {{user.name}}',
            'body' => '<p>Bem-vinda, {{user.name}}.</p>',
        ]);

        $this->assertNotNull($rendered);
        $this->assertSame('Ola Maria', $rendered['subject']);
        $this->assertStringContainsString('Bem-vinda, Maria.', $rendered['html']);
        $this->assertDatabaseHas('mail_templates', ['slug' => 'test_mandatory_template']);
    }

    public function test_application_has_no_raw_mail_dispatches(): void
    {
        $violations = [];
        $allowedDirectHtml = [
            app_path('Http/Controllers/Admin/MailTemplateController.php'),
            app_path('Services/Mail/SystemMailTemplateService.php'),
        ];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path())) as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if (str_contains($contents, 'Mail::raw(') || str_contains($contents, '\\Mail::raw(')) {
                $violations[] = $file->getPathname();
            }

            if (
                (str_contains($contents, 'Mail::html(') || str_contains($contents, '\\Mail::html('))
                && !in_array($file->getPathname(), $allowedDirectHtml, true)
            ) {
                $violations[] = $file->getPathname();
            }

            if (
                str_contains($contents, 'new MailMessage')
                && $file->getPathname() !== app_path('Services/Mail/SystemMailTemplateService.php')
            ) {
                $violations[] = $file->getPathname();
            }
        }

        $this->assertSame([], $violations, 'Todo email deve usar um MailTemplate editavel.');
    }

    public function test_every_mailable_uses_the_central_template_trait(): void
    {
        $violations = [];

        foreach (glob(app_path('Mail/*.php')) as $file) {
            $contents = file_get_contents($file);

            if (
                str_contains($contents, 'extends Mailable')
                && !str_contains($contents, 'UsesMailTemplate')
            ) {
                $violations[] = $file;
            }
        }

        $this->assertSame([], $violations, 'Todo Mailable deve usar UsesMailTemplate.');
    }
}
