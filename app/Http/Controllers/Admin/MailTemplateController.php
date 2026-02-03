<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailTemplate;
use App\Http\Controllers\MailTestController;

class MailTemplateController extends Controller
{
    public function index()
    {
        $templates = MailTemplate::orderBy('category')->orderBy('name')->paginate(20);
        return view('admin.mailtemplates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.mailtemplates.form', ['template' => new MailTemplate]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:mail_templates,slug',
            'category'=>'nullable|string',
            'locale'=>'nullable|string',
            'subject'=>'required',
            'body'=>'required',
            'is_active'=>'nullable|boolean'
        ]);
        $data['category'] = $data['category'] ?? 'sistema';
        $data['locale'] = $data['locale'] ?? 'pt-BR';
        $data['is_active'] = $request->boolean('is_active', true);
        MailTemplate::create($data);
        return redirect()->route('admin.mailtemplates.index')->with('success','Template salvo');
    }

    public function edit(MailTemplate $mailtemplate)
    {
        return view('admin.mailtemplates.form', ['template' => $mailtemplate]);
    }

    public function update(Request $request, MailTemplate $mailtemplate)
    {
        $data = $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:mail_templates,slug,'.$mailtemplate->id,
            'category'=>'nullable|string',
            'locale'=>'nullable|string',
            'subject'=>'required',
            'body'=>'required',
            'is_active'=>'nullable|boolean'
        ]);
        $data['category'] = $data['category'] ?? 'sistema';
        $data['locale'] = $data['locale'] ?? 'pt-BR';
        $data['is_active'] = $request->boolean('is_active', true);
        $mailtemplate->update($data);
        return redirect()->route('admin.mailtemplates.index')->with('success','Template atualizado');
    }

    public function destroy(MailTemplate $mailtemplate)
    {
        $mailtemplate->delete();
        return redirect()->route('admin.mailtemplates.index')->with('success','Template removido');
    }

    public function preview(MailTemplate $mailtemplate)
    {
        $data = $this->sampleData();
        $html = $this->renderVariables($mailtemplate->body, $data);
        return response()->json(['html' => $html]);
    }

    public function sendPreview(Request $request, MailTemplate $mailtemplate)
    {
        $request->validate(['email'=>'required|email']);
        $data = $this->sampleData($request->input('email'));
        $html = $this->renderVariables($mailtemplate->body, $data);

        $mt = new MailTestController();
        return $mt->sendRaw($request->input('email'), $mailtemplate->subject, $html);
    }

    protected function sampleData($email = 'preview@example.com')
    {
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
                'logo' => asset('img/logo.svg')
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
        ];
    }

    protected function renderVariables($body, $data)
    {
        try {
            $rendered = \Blade::render($body, $data);
        } catch (\Throwable $e) {
            $rendered = $body;
            foreach($data as $k => $v){
                if(is_array($v)){
                    foreach($v as $subk => $subv){
                        $rendered = str_replace('{{'.$k.'.'.$subk.'}}', $subv, $rendered);
                    }
                } else {
                    $rendered = str_replace('{{'.$k.'}}', $v, $rendered);
                }
            }
        }
        $allowed = '<p><a><strong><em><ul><ol><li><br><img><table><tr><td><th><tbody><thead><h1><h2><h3><h4><h5><span><div>'; 
        return strip_tags($rendered, $allowed);
    }
}