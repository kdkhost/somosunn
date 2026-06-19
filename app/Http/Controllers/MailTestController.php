<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use App\Services\Mail\SystemMailLayoutData;
use App\Services\Mail\SystemMailTemplateService;
use Illuminate\Http\Request;

class MailTestController extends Controller
{
    public function showForm()
    {
        $templates = MailTemplate::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['name', 'slug', 'category']);

        return view('admin.mail_test', compact('templates'));
    }

    public function sendTest(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|email',
            'template_slug' => 'nullable|string|max:120',
        ]);

        $slug = trim((string) ($data['template_slug'] ?? '')) ?: 'smtp_test';
        $layout = app(SystemMailLayoutData::class)->make();

        $sent = app(SystemMailTemplateService::class)->send($slug, $data['to'], [
            'user' => [
                'name' => 'Teste SMTP',
                'email' => $data['to'],
            ],
            'site' => [
                'name' => $layout['siteName'],
                'logo' => $layout['logoUrl'],
                'primary_color' => $layout['primaryColor'],
                'secondary_color' => $layout['secondaryColor'],
                'url' => url('/'),
            ],
            'test' => [
                'sent_at' => now()->format('d/m/Y H:i:s'),
            ],
        ], [
            'name' => 'Teste SMTP',
            'category' => 'sistema',
            'locale' => 'pt-BR',
            'subject' => 'Teste SMTP - {{site.name}}',
            'body' => '<h1>Ola, {{user.name}}!</h1><p>Este e um e-mail de teste enviado por template personalizado em {{test.sent_at}}.</p>',
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.mailtest.index')
            ->with($sent ? 'success' : 'error', $sent ? 'E-mail de teste enviado por template.' : 'Nao foi possivel enviar o e-mail de teste.');
    }
}
