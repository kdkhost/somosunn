<?php

namespace App\Services\Mail;

use App\Models\MailTemplate;
use Illuminate\Support\Facades\Blade;

class SystemMailTemplateService
{
    /**
     * @param array<string,mixed> $data
     * @return array{subject:string,content:string,template:MailTemplate}|null
     */
    public function renderBySlug(string $slug, array $data): ?array
    {
        $template = MailTemplate::query()
            ->where('slug', $slug)
            ->first();

        if (!$template || !(bool) $template->is_active) {
            return null;
        }

        [$subject, $content] = $this->renderTemplate($template, $data);

        $subject = trim(preg_replace('/\\s+/', ' ', strip_tags($subject)));
        if ($subject === '') {
            $subject = (string) ($template->name ?? 'Notificação');
        }

        $content = $this->sanitizeHtml($content);

        return [
            'subject' => $subject,
            'content' => $content,
            'template' => $template,
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @return array{0:string,1:string}
     */
    public function renderTemplate(MailTemplate $template, array $data): array
    {
        $subject = (string) ($template->subject ?? '');
        $body = (string) ($template->body ?? '');

        try {
            $subjectRendered = (string) Blade::render($subject, $data);
            $bodyRendered = (string) Blade::render($body, $data);
            return [$subjectRendered, $bodyRendered];
        } catch (\Throwable $e) {
            return [
                $this->replacePlaceholders($subject, $data),
                $this->replacePlaceholders($body, $data),
            ];
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    public function replacePlaceholders(string $text, array $data): string
    {
        $rendered = $text;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue)) {
                        continue;
                    }
                    $pattern = '/\\{\\{\\s*' . preg_quote((string) $key . '.' . (string) $subKey, '/') . '\\s*\\}\\}/';
                    $rendered = preg_replace($pattern, (string) $subValue, $rendered);
                }
                continue;
            }

            $pattern = '/\\{\\{\\s*' . preg_quote((string) $key, '/') . '\\s*\\}\\}/';
            $rendered = preg_replace($pattern, (string) $value, $rendered);
        }

        return (string) $rendered;
    }

    public function sanitizeHtml(string $html): string
    {
        $allowed = '<p><a><strong><em><ul><ol><li><br><img><table><tr><td><th><tbody><thead><h1><h2><h3><h4><h5><span><div><style><center>';
        return strip_tags($html, $allowed);
    }

    /**
     * Renders the template and wraps it in the system layout.
     */
    public function renderFullHtml(string $slug, array $data): ?array
    {
        $rendered = $this->renderBySlug($slug, $data);
        if (!$rendered) {
            return null;
        }

        $layoutData = app(\App\Services\Mail\SystemMailLayoutData::class)->make();

        // Merge site data if available in the input $data
        if (!empty($data['site']['name'])) {
            $layoutData['siteName'] = (string) $data['site']['name'];
        }
        if (!empty($data['site']['logo'])) {
            $layoutData['logoUrl'] = (string) $data['site']['logo'];
        }

        $fullHtml = view('emails.system', array_merge($layoutData, [
            'content' => $rendered['content'],
        ]))->render();

        return [
            'subject' => $rendered['subject'],
            'html' => $fullHtml,
            'template' => $rendered['template']
        ];
    }
}

