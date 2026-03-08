<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailTemplate;
use App\Http\Controllers\MailTestController;
use App\Services\Mail\SystemMailLayoutData;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use App\Services\Mail\SystemMailTemplateService;

class MailTemplateController extends Controller
{
    public function index()
    {
        $query = MailTemplate::orderBy('category')->orderBy('name');

        if (request()->has('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (request()->has('category') && request('category') != '') {
            $query->where('category', request('category'));
        }

        $templates = $query->paginate(20);

        if (request()->routeIs('panel.*')) {
            return view('panel.admin.mailtemplates.index', compact('templates'));
        }

        return view('admin.mailtemplates.index', compact('templates'));
    }

    public function create()
    {
        if (request()->routeIs('panel.*')) {
            if (request()->ajax()) {
                $template = new MailTemplate();
                return view('panel.admin.mailtemplates.partials.form-content', compact('template'))->render();
            }
            return view('panel.admin.mailtemplates.form', ['template' => new MailTemplate]);
        }
        return view('admin.mailtemplates.form', ['template' => new MailTemplate]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:mail_templates,slug',
            'category' => 'nullable|string',
            'locale' => 'nullable|string',
            'subject' => 'required',
            'body' => 'required',
            'is_active' => 'nullable|boolean'
        ]);
        $data['category'] = $data['category'] ?? 'sistema';
        $data['locale'] = $data['locale'] ?? 'pt-BR';
        $data['is_active'] = $request->boolean('is_active', true);

        if (isset($data['body'])) {
            // Decodificar Base64 (bypass firewall ModSecurity)
            $decoded = base64_decode($data['body'], true);
            if ($decoded !== false && base64_encode($decoded) === $data['body']) {
                $data['body'] = $decoded;
            }
            $data['body'] = app(SystemMailTemplateService::class)->stripBoilerplate($data['body']);
        }

        $template = MailTemplate::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Template criado com sucesso!',
                'redirect' => $request->routeIs('panel.*') ? route('panel.admin.mailtemplates.index') : route('admin.mailtemplates.index')
            ]);
        }

        if ($request->routeIs('panel.*')) {
            return redirect()->route('panel.admin.mailtemplates.index')->with('success', 'Template salvo');
        }
        return redirect()->route('admin.mailtemplates.index')->with('success', 'Template salvo');
    }

    public function edit(MailTemplate $mailtemplate)
    {
        if (request()->routeIs('panel.*')) {
            if (request()->ajax()) {
                return view('panel.admin.mailtemplates.partials.form-content', ['template' => $mailtemplate])->render();
            }
            return view('panel.admin.mailtemplates.form', ['template' => $mailtemplate]);
        }
        return view('admin.mailtemplates.form', ['template' => $mailtemplate]);
    }

    public function update(Request $request, MailTemplate $mailtemplate)
    {
        $data = $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:mail_templates,slug,' . $mailtemplate->id,
            'category' => 'nullable|string',
            'locale' => 'nullable|string',
            'subject' => 'required',
            'body' => 'required',
            'is_active' => 'nullable|boolean'
        ]);
        $data['category'] = $data['category'] ?? 'sistema';
        $data['locale'] = $data['locale'] ?? 'pt-BR';
        $data['is_active'] = $request->boolean('is_active', true);

        if (isset($data['body'])) {
            // Decodificar Base64 (bypass firewall ModSecurity)
            $decoded = base64_decode($data['body'], true);
            if ($decoded !== false && base64_encode($decoded) === $data['body']) {
                $data['body'] = $decoded;
            }
            $data['body'] = app(SystemMailTemplateService::class)->stripBoilerplate($data['body']);
        }

        $mailtemplate->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Template atualizado com sucesso!',
                'redirect' => $request->routeIs('panel.*') ? route('panel.admin.mailtemplates.index') : route('admin.mailtemplates.index')
            ]);
        }

        if ($request->routeIs('panel.*')) {
            return redirect()->route('panel.admin.mailtemplates.index')->with('success', 'Template atualizado');
        }
        return redirect()->route('admin.mailtemplates.index')->with('success', 'Template atualizado');
    }

    public function destroy(MailTemplate $mailtemplate)
    {
        $mailtemplate->delete();
        if (request()->routeIs('panel.*')) {
            return redirect()->route('panel.admin.mailtemplates.index')->with('success', 'Template removido');
        }
        return redirect()->route('admin.mailtemplates.index')->with('success', 'Template removido');
    }

    public function preview(MailTemplate $mailtemplate)
    {
        $data = $this->sampleData();
        $rendered = app(SystemMailTemplateService::class)->renderTemplate($mailtemplate, $data);

        $layoutData = app(SystemMailLayoutData::class)->make();
        $html = view('emails.system', array_merge($layoutData, [
            'content' => app(SystemMailTemplateService::class)->sanitizeHtml($rendered[1]),
        ]))->render();

        return response()->json(['html' => $html]);
    }

    public function sendPreview(Request $request, MailTemplate $mailtemplate)
    {
        $request->validate(['email' => 'required|email']);
        $data = $this->sampleData($request->input('email'));

        $rendered = app(SystemMailTemplateService::class)->renderFullHtml($mailtemplate->slug, $data);

        if (!$rendered) {
            // Em caso de template novo ou inativo, renderizamos manualmente para o teste
            $tplRendered = app(SystemMailTemplateService::class)->renderTemplate($mailtemplate, $data);
            $layoutData = app(SystemMailLayoutData::class)->make();
            $html = view('emails.system', array_merge($layoutData, [
                'content' => app(SystemMailTemplateService::class)->sanitizeHtml($tplRendered[1]),
            ]))->render();
            $subject = $tplRendered[0];
        } else {
            $html = $rendered['html'];
            $subject = $rendered['subject'];
        }

        try {
            \Mail::html($html, function ($message) use ($request, $subject) {
                $message->to($request->input('email'))
                    ->subject($subject);
            });

            return response()->json(['success' => true, 'message' => 'E-mail de teste enviado com sucesso!']);
        } catch (\Throwable $e) {
            \Log::error('sendPreview error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Falha ao enviar: ' . $e->getMessage()], 500);
        }
    }

    protected function sampleData($email = 'preview@example.com')
    {
        $logo = \App\Models\Setting::where('key', 'logo_admin')->value('value');
        if (!$logo)
            $logo = \App\Models\Setting::where('key', 'logo_front')->value('value');
        if (!$logo)
            $logo = \App\Models\Setting::where('key', 'logo_image')->value('value');
        $logoUrl = $logo ? asset($logo) : asset('img/logo.svg');

        return [
            'user' => [
                'name' => 'Usuário Exemplo',
                'email' => $email,
                'phone' => '(21) 99999-9999',
                'level' => 'sucesso',
                'points' => 1200
            ],
            'site' => [
                'name' => config('app.name'),
                'url' => url('/'),
                'support_email' => config('mail.from.address'),
                'logo' => $logoUrl
            ],
            'order' => [
                'id' => 'PED-12345',
                'total' => 'R$ 497,00',
                'status' => 'Aprovado',
                'date' => now()->format('d/m/Y H:i')
            ],
            'payment' => [
                'due_date' => now()->addDays(3)->format('d/m/Y'),
                'link' => url('/pagamento/boleto')
            ],
            'event' => [
                'title' => 'Mentoria Elite',
                'date' => now()->addWeek()->format('d/m/Y H:i'),
                'link' => url('/eventos/mentoria-elite')
            ],
            'course' => ['title' => 'Curso de Networking'],
            'mentorship' => ['title' => 'Mentoria Premium'],
            'abandoned_cart' => [
                'link' => url('/checkout/recuperar/PED-12345'),
                'items' => 'Curso de Networking, Mentoria Elite'
            ],
            // Variáveis de Candidatura (Jobs)
            'owner_name' => 'Marcello Dono da Vaga',
            'candidate' => 'João Candidato Silva',
            'vacancy_title' => 'Desenvolvedor Full Stack PHP',
            'company' => 'UNN Tecnologias',
            'location' => 'Rio de Janeiro, RJ (Híbrido)',
            'candidates_url' => url('/painel/admin/my-jobs/1/candidates'),
            'name' => 'Usuário Exemplo'
        ];
    }

    protected function renderVariables($body, $data)
    {
        // Este método foi mantido por compatibilidade interna, mas agora delega ao serviço
        $layoutData = app(SystemMailLayoutData::class)->make();
        $rendered = Blade::render($body, $data);

        return view('emails.system', array_merge($layoutData, [
            'content' => app(SystemMailTemplateService::class)->sanitizeHtml($rendered),
        ]))->render();
    }
}
